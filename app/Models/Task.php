<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    protected $fillable = [
        'title',
        'assigned_to',
        'created_by',
        'priority',
        'timing',
        'due_date',
        'status',
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
