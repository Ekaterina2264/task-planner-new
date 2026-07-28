<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'description',
        'context',
    ];

    public static function record(string $event, string $description, ?string $context = null): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'description' => $description,
            'context' => $context,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
