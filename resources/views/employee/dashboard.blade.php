@extends('layouts.app')
@section('content')

<div class="page-title">Мои задачи</div>
<div class="page-subtitle">{{ now()->translatedFormat('l, j F Y') }}</div>

{{-- Просроченные --}}
@if($overdue->isNotEmpty())
<div class="task-section">
    <div class="task-section-label overdue">
        Просроченные
        <span class="task-section-count">{{ $overdue->count() }}</span>
    </div>
    @foreach($overdue as $task)
        @include('partials.task-row', ['task' => $task])
    @endforeach
</div>
@endif

{{-- Сегодня --}}
<div class="task-section">
    <div class="task-section-label">
        Сегодня
        <span class="task-section-count">{{ $todayT->count() }}</span>
    </div>
    @forelse($todayT as $task)
        @include('partials.task-row', ['task' => $task])
    @empty
        <div class="empty-state">
            <svg fill="none" viewBox="0 0 24 24" stroke="#ddd"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Задач на сегодня нет
        </div>
    @endforelse
</div>

{{-- По дате --}}
@if($scheduled->isNotEmpty())
<div class="task-section">
    <div class="task-section-label">
        Запланированные
        <span class="task-section-count">{{ $scheduled->count() }}</span>
    </div>
    @foreach($scheduled as $task)
        @include('partials.task-row', ['task' => $task])
    @endforeach
</div>
@endif

{{-- Отложенные --}}
@if($laterT->isNotEmpty())
<div class="task-section">
    <div class="task-section-label">
        Отложенные
        <span class="task-section-count">{{ $laterT->count() }}</span>
    </div>
    @foreach($laterT as $task)
        @include('partials.task-row', ['task' => $task])
    @endforeach
</div>
@endif

{{-- FAB --}}
<button class="fab" onclick="openModal()" title="Новая задача">+</button>

{{-- Modal --}}
<div id="task-modal" class="modal-backdrop" style="display:none" onclick="closeModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-title">Новая задача</div>

        <form id="task-form">
            @csrf
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
        assigned_to: {{ auth()->id() }},
        _token: '{{ csrf_token() }}'
    };
    if (timing === 'date') body.due_date = document.getElementById('task-date').value;

    await fetch('{{ route("tasks.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(body)
    });
    window.location.reload();
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
});
</script>

@endsection
