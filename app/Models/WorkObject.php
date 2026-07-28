<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkObject extends Model
{
    protected $fillable = ['name', 'created_by'];

    public function items()
    {
        return $this->hasMany(ObjectItem::class)
            ->orderBy('is_completed')
            ->orderByDesc('completed_at')
            ->orderBy('created_at');
    }

    public function sections()
    {
        return $this->hasMany(ObjectSection::class)
            ->orderBy('position')
            ->orderBy('id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
