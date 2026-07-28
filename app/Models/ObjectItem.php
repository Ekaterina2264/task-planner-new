<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObjectItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'work_object_id',
        'section',
        'title',
        'comment',
        'assigned_to',
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

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
