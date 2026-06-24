<?php $__env->startSection('content'); ?>

<div class="page-title">Мои задачи</div>
<div class="page-subtitle"><?php echo e(now()->translatedFormat('l, j F Y')); ?></div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($overdue->isNotEmpty()): ?>
<div class="task-section">
    <div class="task-section-label overdue">
        Просроченные
        <span class="task-section-count"><?php echo e($overdue->count()); ?></span>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $overdue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php echo $__env->make('partials.task-row', ['task' => $task], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<div class="task-section">
    <div class="task-section-label">
        Сегодня
        <span class="task-section-count"><?php echo e($todayT->count()); ?></span>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $todayT; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php echo $__env->make('partials.task-row', ['task' => $task], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div class="empty-state">
            <svg fill="none" viewBox="0 0 24 24" stroke="#ddd"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Задач на сегодня нет
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($scheduled->isNotEmpty()): ?>
<div class="task-section">
    <div class="task-section-label">
        Запланированные
        <span class="task-section-count"><?php echo e($scheduled->count()); ?></span>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $scheduled; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php echo $__env->make('partials.task-row', ['task' => $task], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($laterT->isNotEmpty()): ?>
<div class="task-section">
    <div class="task-section-label">
        Отложенные
        <span class="task-section-count"><?php echo e($laterT->count()); ?></span>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $laterT; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php echo $__env->make('partials.task-row', ['task' => $task], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<button class="fab" onclick="openModal()" title="Новая задача">+</button>


<div id="task-modal" class="modal-backdrop" style="display:none" onclick="closeModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-title">Новая задача</div>

        <form id="task-form">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label class="form-label">Название</label>
                <input type="text" id="task-title" class="form-input" placeholder="Что нужно сделать?" autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Приоритет</label>
                <div class="priority-pills">
                    <div class="priority-pill" data-val="high" onclick="setPriority('high')">🔴 Высокий</div>
                    <div class="priority-pill active-medium" data-val="medium" onclick="setPriority('medium')">🟡 Средний</div>
                    <div class="priority-pill" data-val="low" onclick="setPriority('low')">🟢 Низкий</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Срок</label>
                <div class="timing-pills">
                    <div class="timing-pill active" data-val="today" onclick="setTiming('today')">Сегодня</div>
                    <div class="timing-pill" data-val="later" onclick="setTiming('later')">Отложить</div>
                    <div class="timing-pill" data-val="date" onclick="setTiming('date')">Конкретная дата</div>
                </div>
                <div id="date-field" style="display:none;margin-top:10px">
                    <input type="date" id="task-date" class="form-input">
                </div>
            </div>

            <button type="button" class="btn-submit" onclick="submitTask()">Создать задачу</button>
            <button type="button" class="btn-cancel" onclick="closeModal()">Отмена</button>
        </form>
    </div>
</div>

<script>
let priority = 'medium';
let timing = 'today';

function openModal() {
    document.getElementById('task-modal').style.display = 'flex';
    document.getElementById('task-title').focus();
}
function closeModal(e) {
    if (!e || e.target === document.getElementById('task-modal')) {
        document.getElementById('task-modal').style.display = 'none';
    }
}
function setPriority(val) {
    priority = val;
    document.querySelectorAll('.priority-pill').forEach(p => {
        p.className = 'priority-pill';
        if (p.dataset.val === val) p.classList.add('active-' + val);
    });
}
function setTiming(val) {
    timing = val;
    document.querySelectorAll('.timing-pill').forEach(p => {
        p.classList.toggle('active', p.dataset.val === val);
    });
    document.getElementById('date-field').style.display = val === 'date' ? 'block' : 'none';
}
async function submitTask() {
    const title = document.getElementById('task-title').value.trim();
    if (!title) { document.getElementById('task-title').focus(); return; }

    const body = {
        title,
        priority,
        timing,
        assigned_to: <?php echo e(auth()->id()); ?>,
        _token: '<?php echo e(csrf_token()); ?>'
    };
    if (timing === 'date') body.due_date = document.getElementById('task-date').value;

    await fetch('<?php echo e(route("tasks.store")); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
        body: JSON.stringify(body)
    });
    window.location.reload();
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/artemartemov/Herd/task-planner-new/resources/views/employee/dashboard.blade.php ENDPATH**/ ?>