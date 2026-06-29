<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. استدعاء مكتبة الحذف الناعم 🗑️

class Inspection extends Model
{
    use HasFactory, SoftDeletes; // 2. تفعيل الميزة داخل الموديل

    protected $fillable = [
        'plate_number', 
        'owner_name', 
        'category', 
        'status', 
        'started_at',
        'result',
        'archived_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /**
     * حساب الدقائق المتبقية بناءً على صنف السيارة
     */
    public function getRemainingMinutes()
    {
        if ($this->status !== 'en_cours' || !$this->started_at) {
            return 0;
        }

        $duration = ($this->category === 'VL') ? 20 : 30;

        $endTime = $this->started_at->copy()->addMinutes($duration);
        $remaining = now()->diffInMinutes($endTime, false);

        return $remaining > 0 ? (int)$remaining : 0;
    }
}