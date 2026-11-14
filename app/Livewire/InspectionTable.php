<?php

namespace App\Livewire;

use App\Http\Controllers\InspectionController;
use App\Http\Controllers\MaintenanceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class InspectionTable extends Component
{
    use WithPagination;

    #[Locked]
    public string $scope = 'dashboard';

    public function mount(string $scope = 'dashboard'): void
    {
        $this->scope = $scope;
        $this->authorizeScope();
    }

    private function authorizeScope(): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless(in_array($this->scope, ['dashboard', 'archive', 'trash'], true), 403);
        abort_if($this->scope !== 'dashboard' && auth()->user()->role !== 'admin', 403);
    }

    public function getListeners(): array
    {
        return [
            'echo-private:inspections-channel,.inspection.changed' => 'refreshState',
            'echo-private:inspections-channel,.dashboard.changed' => 'refreshState',
            'echo-private:maintenance-channel,.maintenance.changed' => 'refreshState',
        ];
    }

    public function refreshState(): void
    {
        $this->authorizeScope();
    }

    public function command(string $operation, ?int $id = null, array $payload = []): array
    {
        $this->authorizeScope();
        abort_unless(auth()->user()->role === 'admin', 403);
        $request = Request::create('/', 'POST', $payload, server: ['HTTP_ACCEPT' => 'application/json', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        $request->setUserResolver(fn () => auth()->user());
        $controller = app(InspectionController::class);
        try {
            // Fixed commands reuse the validated application operations, never a client-provided controller or route.
            $response = match ($operation) {
                'create' => $controller->store($request),
                'edit' => $controller->update($request, $id ?? 0),
                'status' => $controller->updateStatus($request, $id ?? 0),
                'revert' => $controller->revertStatus($id ?? 0),
                'delete' => $controller->destroy($id ?? 0),
                'bulk-delete' => $controller->bulkDelete($request),
                'restore' => $controller->restore($id ?? 0),
                'force-delete' => $controller->forceDestroy($id ?? 0),
                'empty-trash' => $controller->emptyTrash(),
                'maintenance' => app(MaintenanceController::class)->toggle($request),
                default => abort(404),
            };

            return $response instanceof JsonResponse ? $response->getData(true) : ['success' => true];
        } catch (ValidationException $exception) {
            return ['success' => false, 'message' => collect($exception->errors())->flatten()->first(), 'errors' => $exception->errors()];
        }
    }

    public function render()
    {
        $this->authorizeScope();
        $request = Request::create('/', 'GET', ['scope' => $this->scope, 'page' => max(1, (int) $this->getPage())]);
        $request->setUserResolver(fn () => auth()->user());
        $state = app(InspectionController::class)->snapshot($request)->getData(true);
        if ($state['page'] !== $this->getPage()) {
            $this->paginators['page'] = $state['page'];
        }
        $metadata = collect($state)->except(['html', 'paginationHtml'])->all();
        $this->dispatch('inspection-state', state: $metadata);

        return view('livewire.inspection-table', compact('state'));
    }
}
