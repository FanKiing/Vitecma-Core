<?php

namespace App\Events;

use App\Models\Inspection;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InspectionStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $inspection;
    public $actionType;
    public $totalCount; 

    /**
     * استقبال بيانات المركبة ونوع الحركة عند إطلاق الإشارة.
     * أنواع الحركات الممكنة: 'create', 'update', 'delete', 'revert', 'bulk_delete'
     */
    public function __construct(Inspection $inspection, string $actionType = 'update', ?int $totalCount = null)
    {
        $this->inspection = $inspection;
        $this->actionType = $actionType;
        
        // ✅ حساب العدد الإجمالي للسيارات (ما عدا المطبوعة)
        $this->totalCount = $totalCount ?? Inspection::where('status', '!=', 'imprimer')->count();
    }

    /**
     * تحديد القناة العامة التي سيتم البث عبرها.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('inspections-channel'),
        ];
    }

    /**
     * تسمية الإشارة باسم مخصص.
     * (في الجافاسكريبت: listen('.inspection.changed'))
     */
    public function broadcastAs(): string
    {
        return 'inspection.changed';
    }

    /**
     * تحديد هيكل البيانات المرسل صراحة.
     */
    public function broadcastWith(): array
    {
        return [
            'inspection' => $this->inspection->toArray(),
            'actionType' => $this->actionType,
            'totalCount' => $this->totalCount, // ✅ إرسال العدد مع كل بث
        ];
    }
}