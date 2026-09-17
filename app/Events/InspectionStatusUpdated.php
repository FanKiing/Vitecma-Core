<?php

namespace App\Events;

use App\Models\Inspection;
use Illuminate\Broadcasting\PrivateChannel;
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
     * Réception des données du véhicule et du type d'action lors du déclenchement de l'événement.
     * Types d'actions possibles : 'create', 'update', 'delete', 'revert', 'bulk_delete'
     */
    public function __construct(Inspection $inspection, string $actionType = 'update', ?int $totalCount = null)
    {
        $this->inspection = $inspection;
        $this->actionType = $actionType;
        
        // ✅ Calcul du nombre total de véhicules (hors imprimés)
        $this->totalCount = $totalCount ?? Inspection::where('status', '!=', 'imprimer')->count();
    }

    /**
     * Définit le canal privé (authentification requise) sur lequel l'événement sera diffusé.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('inspections-channel'),
        ];
    }

    /**
     * Nomme l'événement avec un alias personnalisé.
     * (en JavaScript : listen('.inspection.changed'))
     */
    public function broadcastAs(): string
    {
        return 'inspection.changed';
    }

    /**
     * Définit explicitement la structure des données envoyées.
     */
    public function broadcastWith(): array
    {
        return [
            'inspection' => $this->inspection->toArray(),
            'actionType' => $this->actionType,
            'totalCount' => $this->totalCount, // ✅ Envoi du total à chaque diffusion
        ];
    }
}