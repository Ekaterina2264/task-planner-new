<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('object_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_object_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['work_object_id', 'key']);
        });

        $now = now();
        $sections = [
            ['key' => 'documents', 'name' => 'Документы', 'position' => 0],
            ['key' => 'crew', 'name' => 'Задачи для выхода монтажной бригады', 'position' => 1],
            ['key' => 'materials', 'name' => 'Материалы', 'position' => 2],
        ];

        DB::table('work_objects')
            ->orderBy('id')
            ->each(function ($workObject) use ($sections, $now) {
                foreach ($sections as $section) {
                    DB::table('object_sections')->insert([
                        'work_object_id' => $workObject->id,
                        ...$section,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_sections');
    }
};
