@extends('layouts.app')
@section('content')

@if($objects->isNotEmpty())
    <div class="team-objects" aria-label="Объекты">
        @foreach($objects as $object)
            <a class="team-object-link"
                href="{{ route('dashboard', ['view' => 'objects']) }}#object-{{ $object->id }}"
                title="{{ $object->name }}">
                <span class="team-object-circle">
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-2M9 9v.01M9 13v.01M9 17v.01M16 13v.01M16 17v.01"/>
                    </svg>
                    <span>{{ $object->active_items_count }}/{{ $object->items_count }}</span>
                </span>
                <span class="team-object-name">{{ $object->name }}</span>
            </a>
        @endforeach
    </div>
@endif

<div id="team-board">
    <div class="empty-state">Загрузка...</div>
</div>

<div class="team-nav" id="team-nav" style="display:none">
    <button type="button" class="team-nav-previous" onclick="scrollToEmployee(-1)" title="Предыдущий сотрудник" aria-label="Предыдущий сотрудник">↑</button>
    <button type="button" class="team-nav-next" onclick="scrollToEmployee(1)">Следующий сотрудник ↓</button>
</div>

<div id="task-modal" class="modal-backdrop" style="display:none" onclick="closeModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-title">Задача сотруднику</div>
        <div class="form-group">
            <label class="form-label">Название</label>
            <input type="text" id="task-title" class="form-input" placeholder="Что нужно сделать?">
        </div>
        <div class="form-group">
            <label class="form-label">Комментарий</label>
            <textarea id="task-comment" class="form-input" rows="2" placeholder="Комментарий к задаче..."></textarea>
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
        <div class="form-group">
            <label class="form-label">Название</label>
            <input type="text" id="edit-title" class="form-input">
        </div>
        <div class="form-group">
            <label class="form-label">Комментарий</label>
            <textarea id="edit-comment" class="form-input" rows="3" placeholder="Комментарий к задаче..."></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Исполнитель</label>
            <div class="assignee-select">
                <svg class="assignee-select-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/>
                    <circle cx="9.5" cy="7" r="4"/>
                    <path stroke-linecap="round" d="M18 8v6M15 11h6"/>
                </svg>
                <select id="edit-assigned-to"></select>
                <svg class="assignee-select-arrow" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4"/>
                </svg>
            </div>
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
        <button type="button" class="btn-submit" onclick="saveEdit()">Сохранить</button>
        <button type="button" class="btn-cancel" onclick="closeEditModal()">Отмена</button>
    </div>
</div>

<style>
.team-objects {
    display: flex;
    gap: 16px;
    margin-top: 0;
    padding: 2px 0 12px;
    overflow-x: auto;
    scrollbar-width: none;
}
.team-objects::-webkit-scrollbar {
    display: none;
}
.team-object-link {
    display: flex;
    flex: 0 0 86px;
    flex-direction: column;
    align-items: center;
    gap: 7px;
    color: #858593;
    text-align: center;
    text-decoration: none;
}
.team-object-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    width: 68px;
    height: 68px;
    border: 3px solid #dff3ff;
    border-radius: 50%;
    background: linear-gradient(145deg, #45bafa, #258ee8);
    box-shadow: inset 0 0 0 3px #fff, 0 4px 12px rgba(45, 165, 244, .16);
    color: #fff;
    transition: transform .15s, box-shadow .15s;
}
.team-object-circle span {
    margin-top: -1px;
    font-size: 11px;
    line-height: 1;
}
.team-object-link:hover .team-object-circle {
    transform: translateY(-2px);
    box-shadow: inset 0 0 0 3px #fff, 0 7px 16px rgba(45, 165, 244, .24);
}
.team-object-name {
    display: -webkit-box;
    max-width: 86px;
    overflow: hidden;
    font-size: 12px;
    line-height: 1.25;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
#team-board {
    margin-top: 14px;
}
.employee-board {
    margin-bottom: 30px;
    scroll-margin-top: 24px;
}
.employee-board-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 9px;
    border-bottom: 1px solid #f0f0f4;
}
.employee-board-name {
    color: #1a1a2e;
    font-size: 18px;
    font-weight: 650;
}
.employee-board-count {
    color: #999;
    font-size: 12px;
    margin-top: 2px;
}
.employee-add-task {
    width: 34px;
    height: 34px;
    margin-left: auto;
    padding: 0;
    border: 0;
    border-radius: 50%;
    background: #f1ecff;
    color: #7650db;
    display: grid;
    place-items: center;
    cursor: pointer;
}
.employee-add-task svg {
    width: 17px;
    height: 17px;
    display: block;
}
.employee-collapse {
    width: 34px;
    height: 34px;
    margin-left: auto;
    padding: 0;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #aaaab6;
    display: grid;
    place-items: center;
    cursor: pointer;
    transition: background .15s;
}
.employee-collapse svg {
    width: 16px;
    height: 16px;
    display: block;
    transition: transform .18s ease;
}
.employee-collapse.is-collapsed svg {
    transform: rotate(180deg);
}
.employee-collapse:hover {
    background: #f5f5f8;
}
.employee-collapse + .employee-add-task {
    margin-left: 0;
}
.team-task-section {
    margin: 5px 0 0;
    border: 1px dashed transparent;
    border-radius: 10px;
    transition: background .15s, border-color .15s;
}
.team-drop-zone {
    min-height: 38px;
    border: 1px dashed transparent;
    border-radius: 10px;
    transition: background .15s, border-color .15s;
}
.team-drop-zone.is-empty {
    min-height: 18px;
    margin-bottom: -14px;
    position: relative;
    z-index: 1;
}
.team-task-section[data-team-section="today"] .task-section-label { color: var(--tappsk-blue); }
.team-task-section[data-team-section="tomorrow"] .task-section-label { color: var(--tappsk-cyan); }
.team-task-section[data-team-section="week"] .task-section-label,
.team-task-section[data-team-section="later"] .task-section-label { color: var(--tappsk-muted); }
.team-task-section.drop-active {
    background: var(--accent-light);
    border-color: var(--accent);
}
.team-task-card {
    touch-action: pan-y;
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
    -webkit-touch-callout: none;
}
.team-task-card * {
    user-select: none;
    -webkit-user-select: none;
    -webkit-touch-callout: none;
}
.team-task-card:active {
    cursor: grabbing;
}
.team-task-card.dragging {
    opacity: .35;
}
.team-nav {
    position: fixed;
    right: 36px;
    bottom: 28px;
    z-index: 70;
    display: flex;
    align-items: center;
    gap: 8px;
}
.team-nav button {
    border: 0;
    background: var(--tappsk-blue);
    color: #fff;
    box-shadow: 0 4px 18px rgba(124,111,247,.3);
    cursor: pointer;
    font-size: 13px;
    font-weight: 650;
}
.team-nav-previous {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}
.team-nav-next {
    height: 40px;
    padding: 0 17px;
    border-radius: 20px;
}
.team-nav button:hover {
    filter: brightness(.96);
}
@media (max-width: 768px) {
    .employee-board { margin-bottom: 34px; }
    .employee-board-name { font-size: 18px; }
    .team-task-card { align-items: flex-start; flex-wrap: nowrap; }
    .team-task-card .task-priority-slot { display: none; }
    .team-task-card .task-badges {
        grid-template-columns: 72px;
        width: 72px;
        margin-left: auto;
    }
    .team-nav { right: 18px; bottom: 18px; }
}
</style>

<script>
const currentUserId = {{ auth()->id() }};
const currentUserIsDirector = {{ auth()->user()->isDirector() ? 'true' : 'false' }};
const avatarColors = ['#7c6ff7','#38c97b','#ff5c5c','#f4a223','#2f86d4','#e040fb'];
const sections = [
    ['overdue', 'Важное', true, true],
    ['today', 'Сегодня', false, true],
    ['tomorrow', 'Завтра', false, true],
    ['later', 'Потом', false, true],
];

let employees = [];
let currentEmpId = null;
let draggedTask = null;
let editingTask = null;
let suppressTaskClickUntil = 0;
const collapsedEmployees = new Set();
let priority = 'medium';
let timing = 'today';
let editPriority = 'medium';
let editTiming = 'today';

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[char]);
}

function initials(name) {
    return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2);
}

function avatarColor(id) {
    return avatarColors[id % avatarColors.length];
}

function localDate(offset = 0) {
    const date = new Date();
    date.setHours(12, 0, 0, 0);
    date.setDate(date.getDate() + offset);
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

function taskDate(task) {
    return task.due_date ? String(task.due_date).slice(0, 10) : null;
}

function groupTasks(tasks, includeDone = false) {
    const groups = { overdue: [], today: [], tomorrow: [], later: [] };
    const today = localDate();
    const tomorrow = localDate(1);

    tasks.filter(task => {
        if (task.status !== 'done') return true;
        if (!includeDone || !task.updated_at) return false;
        return new Date(task.updated_at).toDateString() === new Date().toDateString();
    }).forEach(task => {
        const dueDate = taskDate(task);

        if (task.timing === 'today') {
            const effectiveDate = dueDate || String(task.created_at || '').slice(0, 10);
            if (effectiveDate && effectiveDate < today) groups.overdue.push(task);
            else groups.today.push(task);
        }
        else if (task.timing === 'later') groups.later.push(task);
        else if (task.timing === 'date' && dueDate) {
            if (dueDate < today) groups.overdue.push(task);
            else if (dueDate === today) groups.today.push(task);
            else if (dueDate === tomorrow) groups.tomorrow.push(task);
            else groups.later.push(task);
        } else groups.later.push(task);
    });

    return groups;
}

function priorityLabel(priority) {
    if (priority === 'high') return '<span class="badge badge-high">Высокий</span>';
    if (priority === 'low') return '<span class="badge badge-low">Низкий</span>';
    return '';
}

function timingLabel(task) {
    if (task.timing === 'today') return '<span class="badge badge-today">Сегодня</span>';
    if (task.timing === 'later') return '<span class="badge badge-later">Отложено</span>';
    const dueDate = taskDate(task);
    if (!dueDate) return '';
    const date = new Date(`${dueDate}T12:00:00`);
    return `<span class="badge badge-date">${date.toLocaleDateString('ru', {day:'numeric', month:'short'})}</span>`;
}

function taskCard(task, employeeId) {
    return `<div class="task-card team-task-card ${task.priority === 'high' ? 'task-priority-high' : ''}" draggable="true" data-task-id="${task.id}" data-employee-id="${employeeId}">
        <div class="task-checkbox ${task.status === 'done' ? 'checked' : ''}" onclick="toggleTask(event, ${employeeId}, ${task.id}, this)" draggable="false">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div style="flex:1;min-width:140px;cursor:pointer" onclick="openEditModal(event, ${employeeId}, ${task.id})">
            <span class="task-title ${task.status === 'done' ? 'done' : ''}">${escapeHtml(task.title)}</span>
            ${task.object_item?.work_object ? `<div style="font-size:11px;color:var(--tappsk-blue);margin-top:3px">Объект: ${escapeHtml(task.object_item.work_object.name)}</div>` : ''}
            ${task.comment ? `<div style="font-size:12px;color:#aaa;margin-top:2px">${escapeHtml(task.comment)}</div>` : ''}
        </div>
        <div class="task-badges">\n            <span class="task-priority-slot">${priorityLabel(task.priority)}</span>\n            <span class="task-date-slot">${timingLabel(task)}</span>\n        </div>
        <button type="button" class="task-delete" draggable="false"
            onclick="deleteTeamTask(event, ${employeeId}, ${task.id})"
            title="Удалить задачу" aria-label="Удалить задачу">×</button>
    </div>`;
}

function renderBoard() {
    const board = document.getElementById('team-board');

    if (!employees.length) {
        board.innerHTML = '<div class="empty-state">Сотрудников нет</div>';
        return;
    }

    board.innerHTML = employees.map(employee => {
        const groups = groupTasks(employee.tasks, true);
        const allGroups = groups;
        const openCount = employee.tasks.filter(task => task.status !== 'done').length;
        const color = avatarColor(employee.id);

        const collapsed = collapsedEmployees.has(employee.id);

        return `<section class="employee-board" data-employee-board-id="${employee.id}">
            <div class="employee-board-header">
                <div class="emp-avatar" style="background:${color}22;color:${color}">${escapeHtml(initials(employee.name))}</div>
                <div>
                    <div class="employee-board-name">${escapeHtml(employee.name)}</div>
                    <div class="employee-board-count">${openCount} открытых задач</div>
                </div>
                <button class="employee-collapse ${collapsed ? 'is-collapsed' : ''}" onclick="toggleEmployee(${employee.id})"
                    title="${collapsed ? 'Развернуть задачи' : 'Свернуть задачи'}"
                    aria-label="${collapsed ? 'Развернуть задачи' : 'Свернуть задачи'}"
                    aria-expanded="${collapsed ? 'false' : 'true'}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 15 6-6 6 6"/></svg>
                </button>
                <button class="employee-add-task" onclick="openAddModal(${employee.id})" title="Добавить задачу" aria-label="Добавить задачу">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                </button>
            </div>
            <div class="employee-board-tasks" ${collapsed ? 'hidden' : ''}>
            ${sections.map(([key, label, isOverdue, canDrop]) => `
                <div class="team-task-section" data-team-section="${key}"
                    data-employee-id="${employee.id}" data-can-drop="${canDrop ? '1' : '0'}">
                    <div class="task-section-label ${isOverdue ? 'overdue' : ''}">
                        ${label} <span class="task-section-count task-progress-count">${allGroups[key].filter(task => task.status === 'done').length}/${allGroups[key].length}</span>
                    </div>
                    <div class="team-drop-zone ${groups[key].length ? '' : 'is-empty'}"
                        data-employee-id="${employee.id}" data-section="${key}" data-can-drop="${canDrop ? '1' : '0'}">
                        ${groups[key].length ? groups[key].map(task => taskCard(task, employee.id)).join('') : ''}
                    </div>
                </div>`).join('')}
            </div>
        </section>`;
    }).join('');

    document.getElementById('team-nav').style.display = employees.length > 1 ? 'flex' : 'none';
    bindDragAndDrop();
}

function toggleEmployee(employeeId) {
    if (collapsedEmployees.has(employeeId)) collapsedEmployees.delete(employeeId);
    else collapsedEmployees.add(employeeId);
    renderBoard();
}

function scrollToEmployee(direction) {
    const boards = [...document.querySelectorAll('.employee-board')];
    if (!boards.length) return;

    let currentIndex = boards.findIndex(board => board.getBoundingClientRect().bottom > 120);
    if (currentIndex < 0) currentIndex = boards.length - 1;

    const targetIndex = (currentIndex + direction + boards.length) % boards.length;
    boards[targetIndex].scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function loadTeam() {
    const board = document.getElementById('team-board');

    try {
        const response = await fetch('/api/employees');
        if (!response.ok) throw new Error('Не удалось загрузить сотрудников');
        const people = await response.json();
        employees = await Promise.all(people.map(async employee => {
            const tasksResponse = await fetch(`/api/employees/${employee.id}/tasks`);
            if (!tasksResponse.ok) throw new Error('Не удалось загрузить задачи');
            const data = await tasksResponse.json();
            return { ...employee, tasks: data.tasks };
        }));
        renderBoard();
    } catch (error) {
        board.innerHTML = `<div class="empty-state">${escapeHtml(error.message)}. Обновите страницу.</div>`;
    }
}

function bindDragAndDrop() {
    document.querySelectorAll('.team-task-card').forEach(card => {
        card.addEventListener('dragstart', event => {
            if (event.target.closest('.task-checkbox')) {
                event.preventDefault();
                return;
            }
            draggedTask = getTask(card.dataset.employeeId, card.dataset.taskId);
            card.classList.add('dragging');
            event.dataTransfer.effectAllowed = 'move';
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            clearDropHighlights();
            suppressTaskClickUntil = Date.now() + 300;
            draggedTask = null;
        });
        bindTouchDrag(card);
    });

    document.querySelectorAll('.team-task-section[data-can-drop="1"]').forEach(section => {
        section.addEventListener('dragover', event => {
            if (!draggedTask || Number(section.dataset.employeeId) !== Number(draggedTask.assigned_to)) return;
            event.preventDefault();
            clearDropHighlights();
            section.classList.add('drop-active');
            const zone = section.querySelector('.team-drop-zone');
            const card = document.querySelector(`.team-task-card[data-task-id="${draggedTask.id}"]`);
            const cards = [...zone.querySelectorAll('.team-task-card:not(.dragging)')];
            const before = cards.find(item => event.clientY < item.getBoundingClientRect().top + item.offsetHeight / 2);
            zone.insertBefore(card, before || null);
        });
        section.addEventListener('drop', event => {
            event.preventDefault();
            const orderedTaskIds = [...section.querySelectorAll('.team-task-card')].map(card => Number(card.dataset.taskId));
            moveTask(draggedTask, section.dataset.teamSection, orderedTaskIds);
        });
    });
}

function bindTouchDrag(card) {
    card.addEventListener('contextmenu', event => event.preventDefault());
    card.addEventListener('selectstart', event => event.preventDefault());

    let timer = null;
    let active = false;
    let startX = 0;
    let startY = 0;
    let targetZone = null;

    card.addEventListener('touchstart', event => {
        if (event.target.closest('.task-checkbox')) return;
        const touch = event.touches[0];
        startX = touch.clientX;
        startY = touch.clientY;
        timer = setTimeout(() => {
            active = true;
            draggedTask = getTask(card.dataset.employeeId, card.dataset.taskId);
            card.classList.add('dragging');
            if (navigator.vibrate) navigator.vibrate(30);
        }, 300);
    }, { passive: true });

    card.addEventListener('touchmove', event => {
        const touch = event.touches[0];
        if (!active) {
            if (Math.abs(touch.clientX - startX) > 8 || Math.abs(touch.clientY - startY) > 8) clearTimeout(timer);
            return;
        }

        event.preventDefault();
        clearDropHighlights();
        const element = document.elementFromPoint(touch.clientX, touch.clientY);
        const zone = element?.closest('.team-task-section[data-can-drop="1"]');
        targetZone = zone && Number(zone.dataset.employeeId) === Number(draggedTask.assigned_to) ? zone : null;
        targetZone?.classList.add('drop-active');

        if (targetZone) {
            const dropZone = targetZone.querySelector('.team-drop-zone');
            const cards = [...dropZone.querySelectorAll('.team-task-card:not(.dragging)')];
            const before = cards.find(item => touch.clientY < item.getBoundingClientRect().top + item.offsetHeight / 2);
            dropZone.insertBefore(card, before || null);
        }
    }, { passive: false });

    card.addEventListener('touchend', () => {
        clearTimeout(timer);
        card.classList.remove('dragging');
        clearDropHighlights();
        if (active && targetZone) {
            const orderedTaskIds = [...targetZone.querySelectorAll('.team-task-card')]
                .map(item => Number(item.dataset.taskId));
            moveTask(draggedTask, targetZone.dataset.teamSection, orderedTaskIds);
        }
        if (active) suppressTaskClickUntil = Date.now() + 500;
        active = false;
        targetZone = null;
        draggedTask = null;
    });
}

function getTask(employeeId, taskId) {
    return employees.find(employee => employee.id === Number(employeeId))?.tasks.find(task => task.id === Number(taskId));
}

function clearDropHighlights() {
    document.querySelectorAll('.drop-active').forEach(zone => zone.classList.remove('drop-active'));
}

async function deleteTeamTask(event, employeeId, taskId) {
    event.preventDefault();
    event.stopPropagation();
    if (!confirm('Удалить задачу?')) return;

    const response = await fetch(`/tasks/${taskId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    });

    if (!response.ok) {
        alert('Не удалось удалить задачу. Попробуйте ещё раз.');
        return;
    }

    const employee = employees.find(item => item.id === Number(employeeId));
    if (employee) employee.tasks = employee.tasks.filter(task => task.id !== Number(taskId));
    renderBoard();
}

async function toggleTask(event, employeeId, taskId, checkbox) {
    event.preventDefault();
    event.stopPropagation();

    const task = getTask(employeeId, taskId);
    if (!task) return;

    const completed = task.status !== 'done';
    const title = checkbox.closest('.team-task-card').querySelector('.task-title');
    checkbox.classList.toggle('checked', completed);
    title.classList.toggle('done', completed);

    const response = await fetch(`/tasks/${task.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ status: completed ? 'done' : 'new' }),
    });

    if (response.ok) {
        task.status = completed ? 'done' : 'new';
        task.updated_at = new Date().toISOString();
        renderBoard();
        return;
    }

    checkbox.classList.toggle('checked', !completed);
    title.classList.toggle('done', !completed);
    alert('Не удалось изменить статус задачи. Попробуйте ещё раз.');
}

function openEditModal(event, employeeId, taskId) {
    event.stopPropagation();
    if (Date.now() < suppressTaskClickUntil) return;

    editingTask = getTask(employeeId, taskId);
    if (!editingTask) return;

    document.getElementById('edit-title').value = editingTask.title;
    document.getElementById('edit-comment').value = editingTask.comment || '';
    document.getElementById('edit-date').value = taskDate(editingTask) || '';
    const assigneeSelect = document.getElementById('edit-assigned-to');
    assigneeSelect.innerHTML = employees.map(employee => `<option value="${employee.id}">${escapeHtml(employee.name)}</option>`).join('');
    assigneeSelect.value = editingTask.assigned_to;
    setEditPriority(editingTask.priority);
    setEditTiming(editingTask.timing);
    document.getElementById('edit-modal').style.display = 'flex';
    document.getElementById('edit-title').focus();
}

function closeEditModal(event) {
    if (!event || event.target === document.getElementById('edit-modal')) {
        document.getElementById('edit-modal').style.display = 'none';
        editingTask = null;
    }
}

function setEditPriority(value) {
    editPriority = value;
    document.querySelectorAll('#edit-modal .priority-pill').forEach(pill => {
        pill.className = 'priority-pill';
        if (pill.dataset.val === value) pill.classList.add(`active-${value}`);
    });
}

function setEditTiming(value) {
    editTiming = value;
    document.querySelectorAll('#edit-modal .timing-pill').forEach(pill => pill.classList.toggle('active', pill.dataset.val === value));
    document.getElementById('edit-date-field').style.display = value === 'date' ? 'block' : 'none';
}

async function saveEdit() {
    if (!editingTask) return;

    const title = document.getElementById('edit-title').value.trim();
    if (!title) {
        document.getElementById('edit-title').focus();
        return;
    }

    const task = editingTask;
    const updates = {
        title,
        comment: document.getElementById('edit-comment').value,
        priority: editPriority,
        timing: editTiming,
        due_date: editTiming === 'date' ? document.getElementById('edit-date').value : null,
        assigned_to: Number(document.getElementById('edit-assigned-to').value),
    };

    if (editTiming === 'date' && !updates.due_date) {
        document.getElementById('edit-date').focus();
        return;
    }

    const response = await fetch(`/tasks/${task.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(updates),
    });

    if (!response.ok) {
        alert('Не удалось сохранить изменения. Попробуйте ещё раз.');
        return;
    }

    const reassigned = Number(task.assigned_to) !== Number(updates.assigned_to);
    Object.assign(task, updates);
    closeEditModal();
    if (reassigned) await loadTeam();
    else renderBoard();
}

async function moveTask(task, section, orderedTaskIds = null) {
    if (!task) return;

    const updates = {
        overdue: { timing: 'date', due_date: localDate(-1) },
        today: { timing: 'today', due_date: localDate() },
        tomorrow: { timing: 'date', due_date: localDate(1) },
        later: { timing: 'later', due_date: null },
    }[section];

    if (!updates) return;

    const previous = { timing: task.timing, due_date: task.due_date };
    if (!orderedTaskIds) {
        const target = document.querySelector(`.team-task-section[data-employee-id="${task.assigned_to}"][data-team-section="${section}"] .team-drop-zone`);
        target?.appendChild(document.querySelector(`.team-task-card[data-task-id="${task.id}"]`));
        orderedTaskIds = [...(target?.querySelectorAll('.team-task-card') || [])].map(card => Number(card.dataset.taskId));
    }

    const response = await fetch(`/tasks/${task.id}/move`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ ...updates, ordered_task_ids: orderedTaskIds }),
    });

    if (!response.ok) {
        Object.assign(task, previous);
        renderBoard();
        alert('Не удалось перенести задачу. Попробуйте ещё раз.');
        return;
    }

    Object.assign(task, updates);
    const employee = employees.find(item => item.id === Number(task.assigned_to));
    const positions = new Map(orderedTaskIds.map((id, index) => [id, index + 1]));
    employee?.tasks.forEach(item => { if (positions.has(item.id)) item.position = positions.get(item.id); });
    employee?.tasks.sort((a, b) => (a.position || 0) - (b.position || 0));
    renderBoard();
}

function openAddModal(employeeId) {
    currentEmpId = employeeId;
    document.getElementById('task-modal').style.display = 'flex';
    document.getElementById('task-title').focus();
}

function closeModal(event) {
    if (!event || event.target === document.getElementById('task-modal')) {
        document.getElementById('task-modal').style.display = 'none';
    }
}

function setPriority(value) {
    priority = value;
    document.querySelectorAll('#task-modal .priority-pill').forEach(pill => {
        pill.className = 'priority-pill';
        if (pill.dataset.val === value) pill.classList.add(`active-${value}`);
    });
}

function setTiming(value) {
    timing = value;
    document.querySelectorAll('#task-modal .timing-pill').forEach(pill => pill.classList.toggle('active', pill.dataset.val === value));
    document.getElementById('date-field').style.display = value === 'date' ? 'block' : 'none';
}

async function submitTask() {
    if (window.taskCreatePending) return;
    const title = document.getElementById('task-title').value.trim();
    if (!title || !currentEmpId) return;
    const submitButton = document.querySelector('#task-modal .btn-submit');
    window.taskCreatePending = true;
    submitButton.disabled = true;
    submitButton.textContent = 'Сохраняем…';

    const body = {
        title,
        priority,
        timing,
        assigned_to: currentEmpId,
        comment: document.getElementById('task-comment').value,
    };
    if (timing === 'date') body.due_date = document.getElementById('task-date').value;

    const response = await fetch('{{ route("tasks.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(body),
    });

    if (response.ok) {
        closeModal();
        document.getElementById('task-title').value = '';
        document.getElementById('task-comment').value = '';
        window.taskCreatePending = false;
        submitButton.disabled = false;
        submitButton.textContent = 'Создать задачу';
        if (response.headers.get('X-Tasksk-Queued') !== '1') await loadTeam();
        return;
    }

    window.taskCreatePending = false;
    submitButton.disabled = false;
    submitButton.textContent = 'Создать задачу';
}

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        closeModal();
        closeEditModal();
    }
});
loadTeam();
</script>

@endsection
