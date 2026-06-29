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

    // 💡 تصحيح الخطأ: إزالة كلمة clone الممنوعة في تعريف المتغيرات
    public $inspection; 
    public $actionType; 

    /**
     * استقبال بيانات المركبة ونوع الحركة عند إطلاق الإشارة.
     * أنواع الحركات الممكنة: 'create', 'update', 'delete', 'revert'
     */
    public function __construct(Inspection $inspection, string $actionType = 'update')
    {
        $this->inspection = $inspection;
        $this->actionType = $actionType;
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
     * (ملاحظة: في الجافاسكريبت يجب وضع نقطة قبل الاسم هكذا: listen('.inspection.changed'))
     */
    public function broadcastAs(): string
    {
        return 'inspection.changed';
    }

    /**
     * 💡 تحديد هيكل البيانات المرسل صراحة لضمان عدم ضياع أي حقل في الـ JSON.
     */
    public function broadcastWith(): array
    {
        return [
            'inspection' => $this->inspection->toArray(),
            'actionType' => $this->actionType,
        ];
    }
}