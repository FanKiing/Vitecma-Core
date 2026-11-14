// Coalesce bursts and never let an older response overwrite a newer mutation.
export function createReconciler({ load, apply, onError = () => {} }) {
    let version = 0;
    let completed = 0;
    let running = false;
    const waiters = [];

    async function drain() {
        if (running) return;
        running = true;
        try {
            while (completed < version) {
                const target = version;
                try {
                    const snapshot = await load();
                    if (target === version) apply(snapshot);
                } catch (error) {
                    onError(error);
                }
                completed = target;
            }
        } finally {
            running = false;
            if (completed < version) void drain();
            else waiters.splice(0).forEach(resolve => resolve());
        }
    }

    return {
        refresh() {
            version++;
            const result = new Promise(resolve => waiters.push(resolve));
            void drain();
            return result;
        },
    };
}
