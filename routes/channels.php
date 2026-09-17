<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Diffusion en temps réel des inspections et de l'état de maintenance :
// réservée aux utilisateurs authentifiés (mêmes routes que le tableau de bord).
Broadcast::channel('inspections-channel', function ($user) {
    return (bool) $user;
});

Broadcast::channel('maintenance-channel', function ($user) {
    return (bool) $user;
});
