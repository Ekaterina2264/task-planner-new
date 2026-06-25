@extends('layouts.app')
@section('content')

<div class="page-title">Команда</div>
<div class="page-subtitle">Список сотрудников и их задачи</div>

{{-- Список сотрудников --}}
<div id="employees-view">
    <div id="employees-list">
        <div class="empty-state">
            <svg fill="none" viewBox="0 0 24 24" stroke="#ddd"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Загрузка...
        </div>
    </div>
</div>

{{-- Задачи сотрудника --}}
<div id="employee-tasks-view" style="display:none">
    <button class="back-btn" onclick="showEmployees()">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Назад к команде
    </button>

    <div id="emp-header" style="display:flex;align-items:center;gap:14px;margin-bottom:28px;">
        <div class="emp-avatar" id="emp-initials" style="width:52px;height:52px;font-size:18px;border-radius:14px;"></div>
        <div>
            <div class="page-title" id="emp-name" style="margin-bottom:2px;"></div>
            <div style="font-size:13px;color:#888;">Задачи сотрудника</div>
        </div>
    </div>

    <div id="emp-tasks-container"></div>
</div>

{{-- FAB --}}
<button class="fab" id="add-task-fab" style="display:none" onclick="openAddModal()" title="Добавить задачу сотруднику">+</button>

{{-- Modal --}}
<div id="task-modal" class="modal-backdrop" style="display:none" onclick="closeModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-title">Задача сотруднику</div>
        <div class="form-group">
            <label class="form-label">Название</label>
            <input type="text" id="task-title" class="form-input" placeholder="Что нужно сделать?">
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

<script>
const avatarColors = ['#7c6ff7','#38c97b','#ff5c5c','#f4a223','#2f86d4','#e040fb'];
let currentEmpId = null;
let priority = 'medium';
let timing = 'today';

function initials(name) {
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}
function avatarColor(id) {
    return avatarColors[id % avatarColors.length];
}

function priorityLabel(p) {
    if (p === 'high') return '<span class="badge badge-high">Высокий</span>';
    if (p === 'low')  return '<span class="badge badge-low">Низкий</span>';
    return '';
}
function timingLabel(t, date) {
    if (t === 'today') return '<span class="badge badge-today">Сегодня</span>';
    if (t === 'later') return '<span class="badge badge-later">Отложено</span>';
    if (t === 'date' && date) {
        const d = new Date(date);
        return `<span class="badge badge-date">${d.getDate()} ${d.toLocaleString('ru', {month:'short'})}</span>`;
    }
    return '';
}

async function loadEmployees() {
    const res = await fetch('/api/employees');
    const employees = await res.json();
    const el = document.getElementById('employees-list');

    if (!employees.length) {
        el.innerHTML = '<div class="empty-state">Сотрудников нет</div>';
        return;
    }

    el.innerHTML = employees.map(e => `
        <div class="employee-card" onclick="viewEmployee(${e.id}, '${e.name}')">
            <div class="emp-avatar" style="background:${avatarColor(e.id)}22;color:${avatarColor(e.id)}">
                ${initials(e.name)}
            </div>
            <div style="flex:1">
                <div class="emp-name">${e.name}</div>
                <div class="emp-stats">${e.open_tasks_count || 0} открытых задач</div>
            </div>
            ${e.open_tasks_count > 0 ? `<span class="emp-open-count">${e.open_tasks_count}</span>` : ''}
            <svg class="emp-arrow" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>
    `).join('');
}

async function viewEmployee(id, name) {
    currentEmpId = id;
    document.getElementById('employees-view').style.display = 'none';
    document.getElementById('employee-tasks-view').style.display = 'block';
    document.getElementById('add-task-fab').style.display = 'flex';

    const color = avatarColor(id);
    document.getElementById('emp-initials').textContent = initials(name);
    document.getElementById('emp-initials').style.background = color + '22';
    document.getElementById('emp-initials').style.color = color;
    document.getElementById('emp-name').textContent = name;
    document.getElementById('emp-tasks-container').innerHTML = '<div class="empty-state">Загрузка...</div>';

    const res = await fetch(`/api/employees/${id}/tasks`);
    const { tasks } = await res.json();
    renderTasks(tasks);
}

function renderTasks(tasks) {
    const container = document.getElementById('emp-tasks-container');
    if (!tasks.length) {
        container.innerHTML = '<div class="empty-state"><svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#ddd" style="margin:0 auto 12px;display:block"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Нет задач</div>';
        return;
    }

    const today = new Date().toDateString();
    const groups = { overdue: [], today: [], date: [], later: [] };
    tasks.forEach(t => {
        if (t.status === 'done') return;
        if (t.timing === 'date' && t.due_date && new Date(t.due_date) < new Date() && new Date(t.due_date).toDateString() !== today) {
            groups.overdue.push(t);
        } else {
            groups[t.timing]?.push(t) ?? groups.date.push(t);
        }
    });

    const sections = [
        ['overdue', 'Просроченные', true],
        ['today',   'Сегодня',      false],
        ['date',    'Запланированные', false],
        ['later',   'Отложенные',   false],
    ];

    let html = '';
    sections.forEach(([key, label, isOverdue]) => {
        if (!groups[key].length) return;
        html += `<div class="task-section">
            <div class="task-section-label ${isOverdue ? 'overdue' : ''}">
                ${label} <span class="task-section-count">${groups[key].length}</span>
            </div>`;
        groups[key].forEach(t => {
            html += `<div class="task-card">
                <div class="task-checkbox" style="border-color:#7c6ff7;cursor:default"></div>
                <span class="task-title">${t.title}</span>
                <div class="task-badges">
                    ${priorityLabel(t.priority)}
                    ${timingLabel(t.timing, t.due_date)}
                </div>
            </div>`;
        });
        html += '</div>';
    });

    container.innerHTML = html || '<div class="empty-state">Все задачи выполнены ✓</div>';
}

function showEmployees() {
    currentEmpId = null;
    document.getElementById('employees-view').style.display = 'block';
    document.getElementById('employee-tasks-view').style.display = 'none';
    document.getElementById('add-task-fab').style.display = 'none';
    loadEmployees();
}

function openAddModal() {
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
    if (!title || !currentEmpId) return;
    const body = { title, priority, timing, assigned_to: currentEmpId };
    if (timing === 'date') body.due_date = document.getElementById('task-date').value;

    await fetch('{{ route("tasks.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(body)
    });
    closeModal();
    viewEmployee(currentEmpId, document.getElementById('emp-name').textContent);
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

loadEmployees();
</script>

@endsection
