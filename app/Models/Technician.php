<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    protected $fillable = [
        'name',
        'identifier',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }
}