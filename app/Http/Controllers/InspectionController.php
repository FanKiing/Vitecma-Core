<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Events\InspectionStatusUpdated;

class InspectionController extends Controller
{
    private function checkAdmin(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }

    public function index(Request $request): View
    {
        $today = Carbon::today();
        $dailyStats = [
            'total'       => Inspection::whereDate('created_at', $today)->count(),
            'en_cours'    => Inspection::whereDate('created_at', $today)->where('status', 'en_cours')->count(),
            'favorable'   => Inspection::whereDate('created_at', $today)->where('result', 'favorable')->count(),
            'defavorable' => Inspection::whereDate('created_at', $today)->where('result', 'defavorable')->count(),
        ];

        $inspections = Inspection::with('technician') // ✅ جلب بيانات التقني مع الفحص
                                  ->where('status', '!=', 'imprimer')
                                  ->orderBy('created_at', 'desc')
                                  ->paginate(53);

        return view('dashboard', compact('inspections', 'dailyStats'));
    }

    /**
     * 📊 جلب إحصائيات اليوم عبر AJAX لزر Summary
     */
    public function dailyStats(): JsonResponse
    {
        $today = Carbon::today();
        $stats = [
            'total'       => Inspection::whereDate('created_at', $today)->count(),
            'en_cours'    => Inspection::whereDate('created_at', $today)->where('status', 'en_cours')->count(),
            'favorable'   => Inspection::whereDate('created_at', $today)->where('result', 'favorable')->count(),
            'defavorable' => Inspection::whereDate('created_at', $today)->where('result', 'defavorable')->count(),
        ];
        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * 🔄 تحديث حالة الفحص (مع دعم التقني والممر)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $this->checkAdmin();
        
        $validated = $request->validate([
            'status' => 'required|in:libre,en_cours,valider,imprimer',
            'result' => 'nullable|in:favorable,defavorable',
            'technician_identifier' => 'nullable|string|required_if:status,en_cours',
            'technician_password' => 'nullable|string|required_if:status,en_cours',
            'lane' => 'nullable|string|required_if:status,en_cours|in:VL2,VL4,PL1',
        ]);

        $inspection = Inspection::findOrFail($id);

        // ✅ عند بدء الفحص (en_cours): التحقق من التقني والممر
        if ($validated['status'] === 'en_cours') {
            // التحقق من التقني
            $technician = Technician::where('identifier', $validated['technician_identifier'])
                                    ->where('is_active', true)
                                    ->first();

            if (!$technician || !Hash::check($validated['technician_password'], $technician->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات التقني غير صحيحة'
                ], 422);
            }

            // حفظ بيانات التقني والممر في الفحص
            $inspection->technician_id = $technician->id;
            $inspection->technician_name = $technician->name;
            $inspection->lane = $validated['lane'];
        }

        $archivedAt = ($validated['status'] === 'imprimer') ? now() : $inspection->archived_at;

        $inspection->update([
            'status'      => $validated['status'],
            'started_at'  => ($validated['status'] === 'en_cours') ? Carbon::now() : $inspection->started_at,
            'result'      => ($validated['status'] === 'valider') ? ($validated['result'] ?? $inspection->result) : $inspection->result,
            'archived_at' => $archivedAt,
        ]);

        $inspection->refresh();
        $inspection->load('technician'); // ✅ جلب بيانات التقني مع الفحص

        broadcast(new InspectionStatusUpdated($inspection, 'update'));

        return response()->json([
            'success'    => true,
            'inspection' => $inspection
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->checkAdmin();
        $inspection = Inspection::with('technician')->findOrFail($id);
        return response()->json($inspection);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20',
            'owner_name'   => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'category'     => 'required|in:VL,PL',
        ]);

        $inspection = Inspection::findOrFail($id);
        $cleanPlateNumber = strtoupper(trim($validated['plate_number']));

        $inspection->update([
            'plate_number' => $cleanPlateNumber,
            'owner_name'   => $validated['owner_name'],
            'phone_number' => $validated['phone_number'] ?? $inspection->phone_number,
            'category'     => $validated['category'],
        ]);

        $inspection->load('technician');
        broadcast(new InspectionStatusUpdated($inspection, 'update'));

        return response()->json([
            'success'    => true,
            'message'    => 'L\'inspection a été mise à jour avec succès.',
            'inspection' => $inspection
        ]);
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20',
            'owner_name'   => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'category'     => 'required|in:VL,PL',
        ]);

        $cleanPlateNumber = strtoupper(trim($validated['plate_number']));

        $inspection = Inspection::create([
            'plate_number' => $cleanPlateNumber,
            'owner_name'   => $validated['owner_name'],
            'phone_number' => $validated['phone_number'] ?? null,
            'category'     => $validated['category'],
            'status'       => 'libre',
        ]);

        $inspection->load('technician');
        broadcast(new InspectionStatusUpdated($inspection, 'create'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'تمت إضافة السيارة بنجاح',
                'inspection' => $inspection
            ]);
        }

        return back()->with('success', 'تمت إضافة السيارة بنجاح');
    }

    public function revertStatus(int $id): JsonResponse
    {
        $this->checkAdmin();
        $inspection = Inspection::findOrFail($id);

        $newStatus = 'libre';
        if ($inspection->status === 'imprimer') { $newStatus = 'valider'; }
        elseif ($inspection->status === 'valider') { $newStatus = 'en_cours'; }

        $inspection->update([
            'status'     => $newStatus,
            'started_at' => ($newStatus === 'en_cours') ? $inspection->started_at : null,
            'result'     => ($newStatus === 'valider') ? $inspection->result : null
        ]);

        $inspection->refresh();
        $inspection->load('technician');
        broadcast(new InspectionStatusUpdated($inspection, 'revert'));

        return response()->json([
            'success'    => true,
            'inspection' => $inspection
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->checkAdmin();
        $inspection = Inspection::findOrFail($id);
        broadcast(new InspectionStatusUpdated($inspection, 'delete'));
        $inspection->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم نقل الفحص إلى السلة بنجاح'
        ]);
    }

    /**
     * 🗑️ الحذف المتعدد للفحوصات (فقط Libre)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $this->checkAdmin();
        
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:inspections,id',
        ]);

        // ✅ التأكد من أن جميع الفحوصات المحددة بحالة Libre
        $inspections = Inspection::whereIn('id', $validated['ids'])
                                  ->where('status', 'libre')
                                  ->get();

        if ($inspections->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد فحوصات بحالة Libre للحذف'
            ], 422);
        }

        $deletedIds = [];

        foreach ($inspections as $inspection) {
            $deletedIds[] = $inspection->id;
            broadcast(new InspectionStatusUpdated($inspection, 'delete'));
            $inspection->delete();
        }

        // ✅ بث حدث للحذف المتعدد (يمكن استخدامه لتحديث العدد)
        $firstInspection = $inspections->first();
        broadcast(new InspectionStatusUpdated($firstInspection, 'bulk_delete'));

        return response()->json([
            'success' => true,
            'message' => "تم حذف {$inspections->count()} فحص(ات) بنجاح",
            'deleted_ids' => $deletedIds,
            'count' => $inspections->count()
        ]);
    }

    public function trash(): View
    {
        $this->checkAdmin();
        $inspections = Inspection::onlyTrashed()->with('technician')->paginate(10);
        return view('inspections.trash', compact('inspections'));
    }

    public function restore(int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAdmin();
        $inspection = Inspection::withTrashed()->findOrFail($id);
        $inspection->restore();

        $inspection->load('technician');
        broadcast(new InspectionStatusUpdated($inspection, 'create'));

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Restauré avec succès']);
        }
        return back()->with('success', 'Restauré avec succès');
    }

    public function forceDestroy(int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAdmin();
        $inspection = Inspection::onlyTrashed()->findOrFail($id);
        $inspection->forceDelete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Supprimé définitivement']);
        }
        return back()->with('success', 'Supprimé définitivement');
    }

    public function emptyTrash(): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAdmin();
        Inspection::onlyTrashed()->forceDelete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Corbeille vidée']);
        }
        return back()->with('success', 'Corbeille vidée');
    }

    public function archive(Request $request): View
    {
        $this->checkAdmin();
        $inspections = Inspection::where('status', 'imprimer')
                                 ->with('technician')
                                 ->orderBy('archived_at', 'desc')
                                 ->paginate(10);

        return view('inspections.archive', compact('inspections'));
    }
}