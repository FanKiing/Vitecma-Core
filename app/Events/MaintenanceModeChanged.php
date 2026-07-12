<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceModeChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $enabled;
    public ?string $message;

    public function __construct(bool $enabled, ?string $message = null)
    {
        $this->enabled = $enabled;
        $this->message = $message;
    }

    /**
     * Canal public dédié à l'état de maintenance.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('maintenance-channel'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'maintenance.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'enabled' => $this->enabled,
            'message' => $this->message,
        ];
    }
}