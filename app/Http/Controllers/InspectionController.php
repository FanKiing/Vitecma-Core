<?php

namespace App\Http\Controllers;

use App\Events\DashboardChanged;
use App\Events\InspectionStatusUpdated;
use App\Models\Inspection;
use App\Models\Technician;
use App\Support\Branding;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InspectionController extends Controller
{
    private function currentTotalCount(): int
    {
        return Inspection::where('status', '!=', 'imprimer')->count();
    }

    public function index(Request $request): View
    {
        $today = Carbon::today();
        $dailyStats = [
            'total' => Inspection::whereDate('created_at', $today)->count(),
            'en_cours' => Inspection::whereDate('created_at', $today)->where('status', 'en_cours')->count(),
            'favorable' => Inspection::whereDate('created_at', $today)->where('result', 'favorable')->count(),
            'defavorable' => Inspection::whereDate('created_at', $today)->where('result', 'defavorable')->count(),
        ];

        $inspections = Inspection::with('technician') // ✅ Récupérer les données du technicien avec l'inspection
            ->where('status', '!=', 'imprimer')
            ->orderBy('created_at', 'desc')
            ->orderByDesc('id')
            ->paginate(53);

        $maintenanceMode = MaintenanceController::isEnabled();
        $maintenanceMessage = MaintenanceController::getMessage();

        return view('dashboard', compact('inspections', 'dailyStats', 'maintenanceMode', 'maintenanceMessage'));
    }

    public function snapshot(Request $request): JsonResponse
    {
        $request->validate(['page' => ['sometimes', 'integer', 'min:1'], 'scope' => ['sometimes', 'in:dashboard,archive,trash']]);
        $scope = $request->input('scope', 'dashboard');
        abort_if($scope !== 'dashboard' && $request->user()->role !== 'admin', 403);
        $query = Inspection::with('technician');
        if ($scope === 'trash') {
            $query->onlyTrashed()->orderByDesc('deleted_at');
        } elseif ($scope === 'archive') {
            $query->where('status', 'imprimer')->orderByDesc('archived_at');
        } else {
            $query->where('status', '!=', 'imprimer')->orderByDesc('created_at');
        }
        $perPage = $scope === 'dashboard' ? 53 : 10;
        $total = (clone $query)->count();
        $page = min($request->integer('page', 1), max(1, (int) ceil($total / $perPage)));
        $route = ['dashboard' => 'inspections.index', 'archive' => 'inspections.archive', 'trash' => 'inspections.trash'][$scope];
        $inspections = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page)->withPath(route($route));
        $partial = $scope === 'dashboard' ? '' : $scope.'-';

        return response()->json([
            'html' => view('inspections.partials.'.$partial.'rows', compact('inspections'))->render(),
            'paginationHtml' => view('inspections.partials.'.$partial.'pagination', compact('inspections'))->render(),
            'page' => $page,
            'totalCount' => $total,
            'dailyStats' => $this->dailyStats()->getData(true)['stats'],
            'maintenance' => ['enabled' => MaintenanceController::isEnabled(), 'message' => MaintenanceController::getMessage()],
            'technicians' => $request->user()->role === 'admin'
                ? Technician::where('is_active', true)->orderBy('name')->get(['id', 'name', 'identifier']) : [],
            'branding' => app(Branding::class)->data(),
            'serverTime' => (int) round(microtime(true) * 1000),
        ])->header('Cache-Control', 'no-store, private');
    }

    /**
     * 📊 Récupérer les statistiques du jour via AJAX pour le bouton Résumé
     */
    public function dailyStats(): JsonResponse
    {
        $today = Carbon::today();
        $stats = [
            'total' => Inspection::whereDate('created_at', $today)->count(),
            'en_cours' => Inspection::whereDate('created_at', $today)->where('status', 'en_cours')->count(),
            'favorable' => Inspection::whereDate('created_at', $today)->where('result', 'favorable')->count(),
            'defavorable' => Inspection::whereDate('created_at', $today)->where('result', 'defavorable')->count(),
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * 🔄 Mettre à jour le statut de l'inspection (avec support du technicien et de la voie)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:libre,en_cours,valider,imprimer',
            'result' => 'nullable|in:favorable,defavorable',
            'technician_identifier' => 'nullable|string|required_if:status,en_cours',
            'technician_password' => 'nullable|string|required_if:status,en_cours',
            'lane' => 'nullable|string|required_if:status,en_cours|in:VL2,VL4,PL1',
        ]);

        $inspection = Inspection::findOrFail($id);

        // ✅ Lors du démarrage de l'inspection (en_cours) : vérification du technicien et de la voie
        if ($validated['status'] === 'en_cours') {
            // Vérification du technicien
            $technician = Technician::where('identifier', $validated['technician_identifier'])
                ->where('is_active', true)
                ->first();

            if (! $technician || ! Hash::check($validated['technician_password'], $technician->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiants du technicien incorrects',
                ], 422);
            }

            // Enregistrer les données du technicien et de la voie dans l'inspection
            $inspection->technician_id = $technician->id;
            $inspection->technician_name = $technician->name;
            $inspection->lane = $validated['lane'];
        }

        $archivedAt = ($validated['status'] === 'imprimer') ? now() : $inspection->archived_at;

        $inspection->update([
            'status' => $validated['status'],
            'started_at' => ($validated['status'] === 'en_cours') ? ($inspection->started_at ?? Carbon::now()) : $inspection->started_at,
            'result' => ($validated['status'] === 'valider') ? ($validated['result'] ?? $inspection->result) : $inspection->result,
            'archived_at' => $archivedAt,
        ]);

        $inspection->refresh();
        $inspection->load('technician'); // ✅ Récupérer les données du technicien avec l'inspection

        broadcast(new InspectionStatusUpdated($inspection, 'update'))->toOthers();

        return response()->json([
            'success' => true,
            'inspection' => $inspection,
            'totalCount' => $this->currentTotalCount(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $inspection = Inspection::with('technician')->findOrFail($id);

        return response()->json($inspection);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20',
            'owner_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'category' => 'required|in:VL,PL',
        ]);

        $inspection = Inspection::findOrFail($id);
        $cleanPlateNumber = strtoupper(trim($validated['plate_number']));

        $inspection->update([
            'plate_number' => $cleanPlateNumber,
            'owner_name' => $validated['owner_name'],
            'phone_number' => $validated['phone_number'] ?? $inspection->phone_number,
            'category' => $validated['category'],
        ]);

        $inspection->load('technician');
        broadcast(new InspectionStatusUpdated($inspection, 'update'))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'L\'inspection a été mise à jour avec succès.',
            'inspection' => $inspection,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20',
            'owner_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'category' => 'required|in:VL,PL',
        ]);

        $cleanPlateNumber = strtoupper(trim($validated['plate_number']));

        $inspection = Inspection::create([
            'plate_number' => $cleanPlateNumber,
            'owner_name' => $validated['owner_name'],
            'phone_number' => $validated['phone_number'] ?? null,
            'category' => $validated['category'],
            'status' => 'libre',
        ]);

        $inspection->load('technician');
        broadcast(new InspectionStatusUpdated($inspection, 'create'))->toOthers();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Le véhicule a été ajouté avec succès',
                'inspection' => $inspection,
                'totalCount' => $this->currentTotalCount(),
            ]);
        }

        return back()->with('success', 'Le véhicule a été ajouté avec succès');
    }

    public function revertStatus(int $id): JsonResponse
    {
        $inspection = Inspection::findOrFail($id);

        $newStatus = 'libre';
        if ($inspection->status === 'imprimer') {
            $newStatus = 'valider';
        } elseif ($inspection->status === 'valider') {
            $newStatus = 'en_cours';
        }

        $inspection->update([
            'status' => $newStatus,
            'started_at' => ($newStatus === 'libre') ? null : $inspection->started_at,
            'result' => ($newStatus === 'valider') ? $inspection->result : null,
            'technician_id' => ($newStatus === 'libre') ? null : $inspection->technician_id,
            'technician_name' => ($newStatus === 'libre') ? null : $inspection->technician_name,
            'lane' => ($newStatus === 'libre') ? null : $inspection->lane,
        ]);

        $inspection->refresh();
        $inspection->load('technician');
        broadcast(new InspectionStatusUpdated($inspection, 'revert'))->toOthers();

        return response()->json([
            'success' => true,
            'inspection' => $inspection,
            'totalCount' => $this->currentTotalCount(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $inspection = Inspection::findOrFail($id);
        $inspection->delete();
        broadcast(new InspectionStatusUpdated($inspection, 'delete'))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Le véhicule a été déplacé vers la corbeille avec succès',
            'totalCount' => $this->currentTotalCount(),
        ]);
    }

    /**
     * 🗑️ Suppression multiple des inspections (uniquement Libre)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:inspections,id',
        ]);

        $inspections = DB::transaction(function () use ($validated) {
            $rows = Inspection::whereIn('id', $validated['ids'])->where('status', 'libre')->lockForUpdate()->get();
            foreach ($rows as $row) {
                $row->delete();
            }

            return $rows;
        });

        if ($inspections->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => "Aucune inspection à l'état Libre à supprimer",
            ], 422);
        }

        $deletedIds = $inspections->modelKeys();

        // ✅ Diffuser un événement de suppression multiple (utilisable pour mettre à jour le compteur)
        $firstInspection = $inspections->first();
        broadcast(new InspectionStatusUpdated($firstInspection, 'bulk_delete'))->toOthers();

        return response()->json([
            'success' => true,
            'message' => "{$inspections->count()} inspection(s) supprimée(s) avec succès",
            'deleted_ids' => $deletedIds,
            'count' => $inspections->count(),
            'totalCount' => $this->currentTotalCount(),
        ]);
    }

    public function trash(): View
    {
        $inspections = Inspection::onlyTrashed()->with('technician')->orderByDesc('deleted_at')->orderByDesc('id')->paginate(10);

        return view('inspections.trash', compact('inspections'));
    }

    public function restore(int $id): JsonResponse|RedirectResponse
    {
        $inspection = Inspection::withTrashed()->findOrFail($id);
        $inspection->restore();

        $inspection->load('technician');
        broadcast(new InspectionStatusUpdated($inspection, 'create'))->toOthers();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Restauré avec succès']);
        }

        return back()->with('success', 'Restauré avec succès');
    }

    public function forceDestroy(int $id): JsonResponse|RedirectResponse
    {
        $inspection = Inspection::onlyTrashed()->findOrFail($id);
        $inspection->forceDelete();
        broadcast(new DashboardChanged)->toOthers();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Supprimé définitivement']);
        }

        return back()->with('success', 'Supprimé définitivement');
    }

    public function emptyTrash(): JsonResponse|RedirectResponse
    {
        Inspection::onlyTrashed()->forceDelete();
        broadcast(new DashboardChanged)->toOthers();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Corbeille vidée']);
        }

        return back()->with('success', 'Corbeille vidée');
    }

    public function archive(Request $request): View
    {
        $inspections = Inspection::where('status', 'imprimer')
            ->with('technician')
            ->orderBy('archived_at', 'desc')
            ->orderByDesc('id')
            ->paginate(10);

        return view('inspections.archive', compact('inspections'));
    }
}
