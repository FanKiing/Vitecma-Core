# Dashboard synchronization

The application uses Livewire 4 actions and DOM morphing, with Laravel Echo/Reverb for private event notifications. Legacy REST endpoints are retained for integrations. All dashboard viewers authenticate through the existing web session and private channels.

## Defects reproduced

Two isolated browser sessions were connected and subscribed successfully before the fixes. Editing a plate/owner/category did not update the screen. Starting an inspection did not update its technician/lane on the screen. Deleting the last row returned `totalCount: 0` while the screen displayed `1`.

The old code maintained several independent JavaScript row renderers in addition to the Blade renderer. It used administrator column indexes for the screen table, rendered restored rows as `libre`, ignored edits in received events, and sometimes sent deletion notifications before deleting the record. Fetch omitted `X-Socket-ID`, so `toOthers()` did not exclude its sender. Reverting a printed inspection also cleared `started_at`, causing the next return to `en_cours` to lose its timer. Slow technician-list requests could reset a user's selection. External CDN failures made confirmation buttons fail with `Swal is not defined`.

## Current flow: Livewire 4 + Reverb

1. The existing dialogs invoke explicit `InspectionTable::command` actions through Livewire. Every command authorizes the current user and reuses the validated application operations. No inspection mutation Fetch competes with Livewire morphing.
2. Reverb sends notifications on private channels. The PHP component declares `echo-private` listeners for inspection, maintenance and general dashboard changes, which refresh its server-rendered rows.
3. Active inspections, archives and trash share one role-aware component. Scope is locked, archive/trash require the administrator role, and pagination is managed by Livewire. The snapshot endpoint remains available to existing API consumers; the component reuses the same builder internally without a second HTTP request.
4. Only Livewire morphs table/pagination markup. JavaScript handles dialogs, filters, ephemeral checkbox selection and external indicators. Stable row keys and ignored timer text prevent competing renderers. Metadata events update counts, branding and maintenance after the morph.
5. Private subscriptions, restored visibility and online/BFCache recovery refresh the component. `wire:poll.30s.visible` provides a bounded fallback. The navbar reports live, fallback, reconnecting or interrupted synchronization.

Livewire's bundled Alpine starts once through the Vite ESM entry. Layouts declare `@livewireScriptConfig`; adding another Alpine runtime or manually replacing table HTML would reintroduce the earlier ownership conflict.

Inspections, technician changes, branding, maintenance and trash clearing notify other dashboards. Bulk deletion is transactional and sends a single notification after all selected free rows are deleted. Broadcast transport errors are reported by Laravel but do not turn an already-saved mutation into a failed HTTP command; Reverb requests have bounded connection/request timeouts. The HTTP fallback remains available while Reverb is unavailable.

## Timers and colours

The timer uses persisted `started_at` and the VL/PL duration, not the time a row arrived in the browser. A server-time anchor advances with `performance.now()`, independent of the client's wall clock. Snapshots recalibrate the anchor, and expired or stopped timers have their intervals cleared. Repeated starts and reversions preserve the original start; returning to `libre` clears it.

Status/category colours use shared Blade components and `resources/css/interface.css`, so the same status has the same light/dark palette after a command, event or reload. Primary actions use green, warnings amber, and destructive/rejected actions red. Confirmation dialogs are bundled with Vite instead of depending on a CDN.

## Runtime requirements

Run the PHP application and `php artisan reverb:start`. Events use `ShouldBroadcastNow`, so these notifications do not depend on a queue worker. Set the Reverb app credentials and the browser-reachable `VITE_REVERB_HOST`, port and scheme, then rebuild assets. The PHP broadcaster may use a different internal `REVERB_HOST`. Use hostname-only `REVERB_ALLOWED_ORIGINS` entries, not full URLs. A TLS deployment needs a reachable WSS endpoint and an HTTPS application session.

## Verification

- `php artisan test`: authentication, authorization, timer preservation, deletion counts, role-specific snapshots, archive/trash scopes, restoration and broadcast-outage behavior.
- `npm run test:js`: serialized refreshes, 100-event coalescing, stale-response rejection and recovery after errors.
- `npm run build`: local assets and shared colours.
- End-to-end validation used two real Chromium contexts, an isolated SQLite database, an actual Reverb server, and a screen clock shifted by five minutes. The checked workflow includes edit, start, category change, both validation results, revert, print, delete/restore, bulk deletion, maintenance, missed-event recovery and the HTTP fallback. No production users or inspection data are needed for these checks.
