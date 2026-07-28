<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('object_item_id')
                ->nullable()
                ->after('id')
                ->unique()
                ->constrained('object_items')
                ->cascadeOnDelete();
        });

        $now = now();

        DB::table('object_items')
            ->whereNotNull('assigned_to')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->each(function ($item) use ($now) {
                DB::table('tasks')->insert([
                    'object_item_id' => $item->id,
                    'title' => $item->title,
                    'assigned_to' => $item->assigned_to,
                    'created_by' => $item->created_by,
                    'priority' => 'medium',
                    'timing' => 'later',
                    'due_date' => null,
                    'status' => $item->is_completed ? 'done' : 'new',
                    'comment' => $item->comment,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('object_item_id');
        });
    }
};
