<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectSection extends Model
{
    protected $fillable = [
        'work_object_id',
        'key',
        'name',
        'position',
    ];

    public function workObject()
    {
        return $this->belongsTo(WorkObject::class);
    }
}
