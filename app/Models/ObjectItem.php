<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectItem extends Model
{
    protected $fillable = [
        'work_object_id',
        'section',
        'title',
        'comment',
        'is_completed',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function workObject()
    {
        return $this->belongsTo(WorkObject::class);
    }
}
