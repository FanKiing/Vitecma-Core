import test from 'node:test';
import assert from 'node:assert/strict';
import { createReconciler } from '../../resources/js/reconcile.js';

function deferred() {
    let resolve;
    const promise = new Promise(done => { resolve = done; });
    return { promise, resolve };
}

test('an older response cannot overwrite an event received during its request', async () => {
    const first = deferred();
    const rendered = [];
    let calls = 0;
    const sync = createReconciler({ load: () => ++calls === 1 ? first.promise : Promise.resolve('current'), apply: data => rendered.push(data) });
    const pending = sync.refresh();
    const latest = sync.refresh();
    first.resolve('stale');
    await Promise.all([pending, latest]);
    assert.deepEqual(rendered, ['current']);
    assert.equal(calls, 2);
});

test('a burst of events shares a serialized refresh and retains the final state', async () => {
    const first = deferred();
    let calls = 0;
    let inFlight = 0;
    let maxInFlight = 0;
    const rendered = [];
    const sync = createReconciler({
        async load() {
            calls++; inFlight++; maxInFlight = Math.max(maxInFlight, inFlight);
            const result = calls === 1 ? await first.promise : 'final';
            inFlight--; return result;
        },
        apply: data => rendered.push(data),
    });
    const pending = Array.from({ length: 100 }, () => sync.refresh());
    first.resolve('stale');
    await Promise.all(pending);
    assert.equal(calls, 2);
    assert.equal(maxInFlight, 1);
    assert.deepEqual(rendered, ['final']);
});

test('failed fetches release waiters and a later reconnect recovers', async () => {
    const failures = [];
    const rendered = [];
    let broken = true;
    const sync = createReconciler({
        load: async () => { if (broken) throw new Error('offline'); return 'recovered'; },
        apply: data => rendered.push(data),
        onError: error => failures.push(error.message),
    });
    await sync.refresh();
    broken = false;
    await sync.refresh();
    assert.deepEqual(failures, ['offline']);
    assert.deepEqual(rendered, ['recovered']);
});
