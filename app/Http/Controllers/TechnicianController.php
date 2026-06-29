<?php

namespace App\Http\Controllers;

use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TechnicianController extends Controller
{
    /**
     * التحقق من أن المستخدم Admin
     */
    private function checkAdmin(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * عرض قائمة التقنيين (للمسؤول فقط)
     */
    public function index()
    {
        $this->checkAdmin();
        $technicians = Technician::all();
        return response()->json(['success' => true, 'technicians' => $technicians]);
    }

    /**
     * إضافة تقني جديد
     */
    public function store(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'required|string|unique:technicians|max:50',
            'password' => 'required|string|min:4',
        ]);

        $technician = Technician::create([
            'name' => $validated['name'],
            'identifier' => $validated['identifier'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة التقني بنجاح',
            'technician' => $technician
        ]);
    }

    /**
     * عرض بيانات تقني واحد
     */
    public function show($id)
    {
        $this->checkAdmin();
        $technician = Technician::findOrFail($id);
        return response()->json(['success' => true, 'technician' => $technician]);
    }

    /**
     * تحديث بيانات تقني
     */
    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $technician = Technician::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'identifier' => 'sometimes|string|unique:technicians,identifier,' . $id . '|max:50',
            'password' => 'sometimes|string|min:4',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $technician->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات التقني بنجاح',
            'technician' => $technician
        ]);
    }

    /**
     * حذف تقني
     */
    public function destroy($id)
    {
        $this->checkAdmin();
        $technician = Technician::findOrFail($id);
        $technician->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التقني بنجاح'
        ]);
    }

    /**
     * تعطيل أو تفعيل تقني
     */
    public function toggleActive($id)
    {
        $this->checkAdmin();
        $technician = Technician::findOrFail($id);
        $technician->is_active = !$technician->is_active;
        $technician->save();

        return response()->json([
            'success' => true,
            'message' => $technician->is_active ? 'تم تفعيل التقني' : 'تم تعطيل التقني',
            'is_active' => $technician->is_active
        ]);
    }

    /**
     * التحقق من التقني (identifier + password) - يستخدم عند بدء الفحص
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $technician = Technician::where('identifier', $validated['identifier'])
                                ->where('is_active', true)
                                ->first();

        if (!$technician || !Hash::check($validated['password'], $technician->password)) {
            throw ValidationException::withMessages([
                'identifier' => 'الكود أو كلمة المرور غير صحيحة',
            ]);
        }

        return response()->json([
            'success' => true,
            'technician' => [
                'id' => $technician->id,
                'name' => $technician->name,
                'identifier' => $technician->identifier,
            ]
        ]);
    }

    /**
     * جلب قائمة التقنيين النشطين (للاستخدام في المودال)
     */
    public function getActiveTechnicians()
    {
        $technicians = Technician::where('is_active', true)
                                 ->select('id', 'name', 'identifier')
                                 ->get();

        return response()->json([
            'success' => true,
            'technicians' => $technicians
        ]);
    }
}