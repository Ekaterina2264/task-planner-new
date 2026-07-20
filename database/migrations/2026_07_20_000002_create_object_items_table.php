<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('object_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_object_id')->constrained()->cascadeOnDelete();
            $table->string('section');
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['work_object_id', 'section', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_items');
    }
};
