<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Task extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'object_item_id',
        'title',
        'assigned_to',
        'created_by',
        'priority',
        'timing',
        'due_date',
        'status',
        'position',
        'comment',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function objectItem()
    {
        return $this->belongsTo(ObjectItem::class);
    }

    public function isOverdue(): bool
    {
        if ($this->timing === 'date' && $this->due_date) {
            return $this->due_date->isPast() && $this->status !== 'done';
        }
        return $this->timing === 'overdue';
    }

    public function getEffectiveTiming(): string
    {
        if ($this->timing === 'date' && $this->due_date && $this->due_date->isPast() && $this->status !== 'done') {
            return 'overdue';
        }
        return $this->timing;
    }
}
