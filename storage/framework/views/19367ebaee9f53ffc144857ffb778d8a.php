<div class="task-card" id="task-<?php echo e($task->id); ?>" x-data="{ done: <?php echo e($task->status === 'done' ? 'true' : 'false'); ?> }">
    <div
        class="task-checkbox"
        :class="{ 'checked': done }"
        @click="
            done = !done;
            fetch('<?php echo e(route('tasks.update', $task)); ?>', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                body: JSON.stringify({ status: done ? 'done' : 'new' })
            });
        "
    >
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <span class="task-title" :class="{ 'done': done }"><?php echo e($task->title); ?></span>

    <div class="task-badges">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->priority === 'high'): ?>
            <span class="badge badge-high">Высокий</span>
        <?php elseif($task->priority === 'low'): ?>
            <span class="badge badge-low">Низкий</span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->timing === 'today'): ?>
            <span class="badge badge-today">Сегодня</span>
        <?php elseif($task->timing === 'later'): ?>
            <span class="badge badge-later">Отложено</span>
        <?php elseif($task->timing === 'date' && $task->due_date): ?>
            <span class="badge badge-date"><?php echo e($task->due_date->format('j M')); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /Users/artemartemov/Herd/task-planner-new/resources/views/partials/task-row.blade.php ENDPATH**/ ?>