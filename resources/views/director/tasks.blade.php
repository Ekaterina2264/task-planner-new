@extends('layouts.app')
@section('content')

<div class="page-title">Мои задачи</div>
<div class="page-subtitle">{{ now()->locale('ru')->translatedFormat('l, j F Y') }}</div>

@if($overdue->isNotEmpty())
<div class="task-section">
    <div class="task-section-label overdue">
        Просроченные <span class="task-section-count">{{ $overdue->count() }}</span>
    </div>
    @foreach($overdue as $task)
        @include('partials.task-row', ['task' => $task])
    @endforeach
</div>
@endif

<div class="task-section">
    <div class="task-section-label">
        Сегодня <span class="task-section-count">{{ $todayT->count() }}</span>
    </div>
    @forelse($todayT as $task)
        @include('partials.task-row', ['task' => $task])
    @empty
        <div class="empty-state">Задач на сегодня нет</div>
    @endforelse
</div>

@if($tomorrowT->isNotEmpty())
<div class="task-section">
    <div class="task-section-label">
        Завтра <span class="task-section-count">{{ $tomorrowT->count() }}</span>
    </div>
    @foreach($tomorrowT as $task)
        @include('partials.task-row', ['task' => $task])
    @endforeach
</div>
@endif

@if($weekT->isNotEmpty())
<div class="task-section">
    <div class="task-section-label">
        На неделе <span class="task-section-count">{{ $weekT->count() }}</span>
    </div>
    @foreach($weekT as $task)
        @include('partials.task-row', ['task' => $task])
    @endforeach
</div>
@endif

@if($laterT->isNotEmpty())
<div class="task-section">
    <div class="task-section-label">
        Потом <span class="task-section-count">{{ $laterT->count() }}</span>
    </div>
    @foreach($laterT as $task)
        @include('partials.task-row', ['task' => $task])
    @endforeach
</div>
@endif

<button class="fab" onclick="openModal()" title="Новая задача">+</button>

<div id="task-modal" class="modal-backdrop" style="display:none" onclick="closeModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-title">Новая задача</div>
        <div class="form-group">
            <label class="form-label">Название</label>
            <input type="text" id="task-title" class="form-input" placeholder="Что нужно сделать?">
        </div>
        <div class="form-group">
            <label class="form-label">Комментарий</label>
            <textarea id="task-comment" class="form-input" rows="2" placeholder="Заметки..."></textarea>
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
    </div>
</div>

<div id="edit-modal" class="modal-backdrop" style="display:none" onclick="closeEditModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-title">Редактировать задачу</div>
        <input type="hidden" id="edit-task-id">
        <div class="form-group">
            <label class="form-label">Название</label>
            <input type="text" id="edit-title" class="form-input">
        </div>
        <div class="form-group">
            <label class="form-label">Приоритет</label>
            <div class="priority-pills">
                <div class="priority-pill" data-val="high" onclick="setEditPriority('high')">🔴 Высокий</div>
                <div class="priority-pill" data-val="medium" onclick="setEditPriority('medium')">🟡 Средний</div>
                <div class="priority-pill" data-val="low" onclick="setEditPriority('low')">🟢 Низкий</div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Срок</label>
            <div class="timing-pills">
                <div class="timing-pill" data-val="today" onclick="setEditTiming('today')">Сегодня</div>
                <div class="timing-pill" data-val="later" onclick="setEditTiming('later')">Отложить</div>
                <div class="timing-pill" data-val="date" onclick="setEditTiming('date')">Конкретная дата</div>
            </div>
            <div id="edit-date-field" style="display:none;margin-top:10px">
                <input type="date" id="edit-date" class="form-input">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Комментарий</label>
            <textarea id="edit-comment" class="form-input" rows="3" placeholder="Заметки к задаче..."></textarea>
        </div>
        <button type="button" class="btn-submit" onclick="saveEdit()">Сохранить</button>
        <button type="button" class="btn-cancel" onclick="closeEditModal()">Отмена</button>
    </div>
</div>

<script>
let priority = 'medium', timing = 'today', editPriority = 'medium', editTiming = 'today';

function openModal() { document.getElementById('task-modal').style.display = 'flex'; document.getElementById('task-title').focus(); }
function closeModal(e) { if (!e || e.target === document.getElementById('task-modal')) document.getElementById('task-modal').style.display = 'none'; }
function setPriority(val) { priority = val; document.querySelectorAll('#task-modal .priority-pill').forEach(p => { p.className = 'priority-pill'; if (p.dataset.val === val) p.classList.add('active-' + val); }); }
function setTiming(val) { timing = val; document.querySelectorAll('#task-modal .timing-pill').forEach(p => p.classList.toggle('active', p.dataset.val === val)); document.getElementById('date-field').style.display = val === 'date' ? 'block' : 'none'; }
async function submitTask() {
    const title = document.getElementById('task-title').value.trim();
    if (!title) return;
    const body = { title, priority, timing, assigned_to: {{ auth()->id() }}, comment: document.getElementById('task-comment').value };
    if (timing === 'date') body.due_date = document.getElementById('task-date').value;
    await fetch('/tasks', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify(body) });
    window.location.reload();
}
function openEditModal(id, title, priority, timing, date, comment) {
    document.getElementById('edit-task-id').value = id;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-comment').value = comment;
    document.getElementById('edit-date').value = date || '';
    setEditPriority(priority); setEditTiming(timing);
    document.getElementById('edit-modal').style.display = 'flex';
}
function closeEditModal(e) { if (!e || e.target === document.getElementById('edit-modal')) document.getElementById('edit-modal').style.display = 'none'; }
function setEditPriority(val) { editPriority = val; document.querySelectorAll('#edit-modal .priority-pill').forEach(p => { p.className = 'priority-pill'; if (p.dataset.val === val) p.classList.add('active-' + val); }); }
function setEditTiming(val) { editTiming = val; document.querySelectorAll('#edit-modal .timing-pill').forEach(p => p.classList.toggle('active', p.dataset.val === val)); document.getElementById('edit-date-field').style.display = val === 'date' ? 'block' : 'none'; }
async function saveEdit() {
    const id = document.getElementById('edit-task-id').value;
    const body = { title: document.getElementById('edit-title').value.trim(), priority: editPriority, timing: editTiming, comment: document.getElementById('edit-comment').value };
    if (editTiming === 'date') body.due_date = document.getElementById('edit-date').value;
    await fetch(`/tasks/${id}`, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify(body) });
    closeEditModal(); window.location.reload();
}
</script>

@endsection