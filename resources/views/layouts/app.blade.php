<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    @include('partials.head')

    <style>
        @font-face {
            font-family: "Tappsk Bebas";
            src: url("/fonts/BebasNeue-Regular.otf") format("opentype");
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        /* Tappsk-style design */
        :root {
            --sidebar-bg: #ffffff;
            --sidebar-text: #666;
            --sidebar-active: #ffffff;
            --sidebar-hover: #f4fbff;
            --accent: #8b5cf6;
            --accent-light: #f1ecff;
            --accent-text: #7650db;
            --tappsk-blue: #2da5f4;
            --tappsk-cyan: #61ccef;
            --tappsk-muted: #b6b7c6;
            --high: #ff5c5c;
            --high-bg: #fff0f0;
            --medium: #f4a223;
            --medium-bg: #fff8ec;
            --low: #38c97b;
            --low-bg: #edfbf3;
            --overdue-color: #ff5c5c;
        }
        html,
        body {
            background: #ffffff;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif; background: #ffffff; color: #17171d; }
        .sidebar {
            width: 240px; min-height: 100vh; background: var(--sidebar-bg);
            display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 50;
            border-right: 1px solid #f0f0f4;
        }
        .sidebar-logo {
            padding: 24px 20px 4px; font-size: 20px; font-weight: 750;
            color: #1a1a2e; letter-spacing: -0.5px;
        }
        .sidebar-logo span { color: var(--accent); }
        .sidebar-section { display: none; }
        .sidebar-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 16px; margin: 2px 10px; border-radius: 7px;
            color: #25252b; text-decoration: none; font-size: 14px; font-weight: 400;
            transition: all 0.15s;
        }
        .sidebar-item:hover { background: var(--sidebar-hover); color: #25252b; }
        .sidebar-item.active { background: #eaf8ff; color: #1e1e24; }
        .sidebar-item.active { font-weight: 500; }
        .sidebar-item svg { width: 18px; height: 18px; flex-shrink: 0; color: #9698a8; }
        .sidebar-item.active svg { color: #27272e; }
        .sidebar-avatar {
            margin: auto 16px 20px; padding: 12px 16px; border-radius: 12px;
            background: #f7f7fb; display: flex; align-items: center; gap: 10px;
        }
        .sidebar-avatar-circle {
            width: 34px; height: 34px; border-radius: 50%; background: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 600; color: #fff; flex-shrink: 0;
        }
        .sidebar-avatar-name { font-size: 13px; font-weight: 500; color: #1a1a2e; line-height: 1.3; }
        .sidebar-avatar-role { font-size: 11px; color: var(--sidebar-text); }
        .main { position: relative; margin-left: 240px; min-height: 100vh; padding: 30px 26px 78px 24px; background: #ffffff; }
        .page-title { font-size: 24px; font-weight: 750; color: #17171d; margin-bottom: 6px; letter-spacing: -.4px; }
        .page-subtitle { font-size: 14px; color: #888; margin-bottom: 32px; }

        /* Task list */
        .task-section { margin-bottom: 0; }
        .task-section-label {
            font-family: "Tappsk Bebas", sans-serif;
            font-size: 25px; font-weight: 400; text-transform: uppercase; letter-spacing: .15px;
            color: var(--tappsk-cyan); margin-bottom: 3px; margin-top: 10px; display: flex; align-items: center; gap: 8px;
        }
        .task-section-label.overdue { color: #ff5c5c; }
        .task-section-count {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background: #fff; border: 1px solid #ececf2; border-radius: 20px; padding: 3px 9px;
            font-size: 12px; line-height: 1.1; color: #9999aa; font-weight: 500; letter-spacing: 0;
        }
        .task-progress-count { min-width: 40px; text-align: center; }
        .task-card {
            background: transparent; border-radius: 0; margin-bottom: 0;
            display: flex; align-items: center; gap: 12px; padding: 4px 0;
            min-height: 30px; box-shadow: none; border-bottom: 0; transition: none;
        }
        .task-card.task-priority-high {
            margin-left: -16px;
            margin-right: -16px;
            padding-left: 16px;
            padding-right: 16px;
            border-radius: 6px;
            background: #fff3f2;
        }
        .task-card.task-priority-high:hover {
            background: #ffefee;
        }

        .task-card:hover {
            box-shadow: none;
            background: #eaf8ff;
            border-radius: 6px;
            margin-left: -16px;
            margin-right: -16px;
            padding-left: 16px;
            padding-right: 16px;
        }
        .task-checkbox {
            width: 18px; height: 18px; border-radius: 4px; border: 2px solid #8b5cf6;
            display: flex; align-items: center; justify-content: center;
            align-self: flex-start; margin-top: 3px;
            cursor: pointer; flex-shrink: 0; transition: all 0.2s;
        }
        .task-checkbox:hover { border-color: var(--accent); }
        .task-checkbox.checked { background: var(--accent); border-color: var(--accent); }
        .task-checkbox svg { width: 12px; height: 12px; color: #fff; display: none; }
        .task-checkbox.checked svg { display: block; }
        .task-title {
            display: block;
            min-height: 18px;
            font-size: 14px;
            line-height: 18px;
            color: #1c1c22;
            font-weight: 400;
        }
        .task-title.done { text-decoration: line-through; color: #bbb; }
        .task-delete {
            width: 28px; height: 28px; margin-left: 5px; border: 0; border-radius: 50%;
            background: transparent; color: #b8b8c2; font-size: 20px; line-height: 1;
            cursor: pointer; opacity: 0;
            transition: opacity .15s ease, color .15s ease, background .15s ease;
        }
        .task-card:hover .task-delete, .task-delete:focus { opacity: 1; }
        .task-delete:hover { color: #e05d68; background: #fff1f2; }
        .task-badges {
            display: grid;
            grid-template-columns: 76px 72px;
            align-items: center;
            flex-shrink: 0;
        }
        .task-priority-slot,
        .task-date-slot { display: flex; align-items: center; }
        .task-priority-slot { justify-content: flex-start; }
        .task-date-slot { justify-content: flex-end; }
        .badge {
            font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 20px; white-space: nowrap;
        }
        .badge-high { background: transparent; color: #ff6969; }
        .badge-medium { background: transparent; color: #e9a433; }
        .badge-low { background: transparent; color: #43cf83; }
        .badge-date { background: transparent; color: #c6c6d1; padding-right: 0; }
        .badge-today { background: transparent; color: #56bdf1; padding-right: 0; }
        .badge-later { background: transparent; color: #aaaab5; padding-right: 0; }

        /* Employee cards (director view) */
        .employee-card {
            background: #fff; border-radius: 0; padding: 12px 0;
            display: flex; align-items: center; gap: 14px; margin-bottom: 10px;
            cursor: pointer; box-shadow: none; border-bottom: 1px solid #f3f3f6; transition: all 0.15s;
        }
        .employee-card:hover { box-shadow: none; background: #fbfbfd; transform: none; }
        .emp-avatar {
            width: 44px; height: 44px; border-radius: 12px;
            background: var(--accent-light); color: var(--accent-text);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; font-weight: 700; flex-shrink: 0;
        }
        .emp-name { font-size: 15px; font-weight: 600; color: #1a1a2e; }
        .emp-stats { font-size: 12px; color: #888; margin-top: 2px; }
        .emp-arrow { color: #ccc; margin-left: auto; }
        .emp-open-count {
            background: var(--accent-light); color: var(--accent-text);
            font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
        }

        /* Add task button */
        .fab {
            position: fixed; bottom: 0; left: 240px; right: 0;
            width: auto; height: 58px; border-radius: 0;
            background: rgba(255,255,255,.97); color: #aeb0c1;
            display: flex; align-items: center; justify-content: flex-start; gap: 18px;
            padding: 0 26px; cursor: pointer; box-shadow: none;
            font-size: 0; line-height: 1; border: none; border-top: 1px solid #f0f0f4;
            transition: background .15s;
        }
        .fab::before { content: "+"; font-size: 27px; font-weight: 300; }
        .fab::after { content: "Новая задача"; font-size: 14px; font-weight: 400; }
        .fab:hover { transform: none; box-shadow: none; background: #fbfdff; }

        /* Modal */
        .modal-backdrop {
            position: fixed; inset: 0; background: rgba(0,0,0,0.4);
            display: flex; align-items: center; justify-content: center; z-index: 200; padding: 20px;
        }
        .modal {
            background: #fff; border-radius: 12px; padding: 24px;
            width: 100%; max-width: 420px; box-shadow: 0 18px 55px rgba(26,26,46,0.16);
        }
        .modal-title { font-size: 18px; font-weight: 700; color: #1a1a2e; margin-bottom: 20px; }
        .form-label { font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: block; }
        .form-input {
            width: 100%; padding: 10px 12px; border: 1px solid #e5e5eb; border-radius: 7px;
            font-size: 14px; color: #1a1a2e; background: #fafafa;
            outline: none; transition: border-color 0.15s;
        }
        .form-input:focus { border-color: var(--accent); background: #fff; }
        .assignee-select { position: relative; }
        .assignee-select select {
            width: 100%; min-height: 44px; padding: 10px 42px;
            border: 1px solid #e5e5eb; border-radius: 10px;
            appearance: none; -webkit-appearance: none;
            background: #f8f8fb; color: #27272f;
            font-size: 14px; font-weight: 500; cursor: pointer;
            outline: none; transition: border-color .15s, background .15s, box-shadow .15s;
        }
        .assignee-select select:hover { background: #f4f2fb; border-color: #dcd8eb; }
        .assignee-select select:focus {
            background: #fff; border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, .11);
        }
        .assignee-select-icon,
        .assignee-select-arrow {
            position: absolute; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px; pointer-events: none;
        }
        .assignee-select-icon { left: 14px; color: var(--accent); }
        .assignee-select-arrow { right: 14px; width: 14px; height: 14px; color: #9b9baa; }
        .form-group { margin-bottom: 16px; }
        .priority-pills { display: flex; gap: 8px; }
        .priority-pill {
            flex: 1; padding: 9px; border-radius: 10px; border: 1.5px solid #eee;
            font-size: 13px; font-weight: 600; cursor: pointer; text-align: center;
            transition: all 0.15s; background: #fafafa; color: #888;
        }
        .priority-pill.active-high { background: var(--high-bg); border-color: var(--high); color: var(--high); }
        .priority-pill.active-medium { background: var(--medium-bg); border-color: var(--medium); color: var(--medium); }
        .priority-pill.active-low { background: var(--low-bg); border-color: var(--low); color: var(--low); }
        .timing-pills { display: flex; gap: 8px; flex-wrap: wrap; }
        .timing-pill {
            padding: 8px 14px; border-radius: 10px; border: 1.5px solid #eee;
            font-size: 13px; font-weight: 500; cursor: pointer; background: #fafafa; color: #888;
            transition: all 0.15s;
        }
        .timing-pill.active { background: var(--accent-light); border-color: var(--accent); color: var(--accent-text); }
        .btn-submit {
            width: 100%; padding: 12px; border-radius: 8px; background: var(--accent);
            color: #fff; font-size: 15px; font-weight: 700; border: none; cursor: pointer;
            margin-top: 8px; transition: opacity 0.15s;
        }
        .btn-submit:hover { opacity: 0.9; }
        .btn-cancel {
            width: 100%; padding: 11px; border-radius: 12px; background: none;
            color: #888; font-size: 14px; border: none; cursor: pointer; margin-top: 6px;
        }

        /* Back button */
        .back-btn {
            display: inline-flex; align-items: center; gap: 6px;
            color: var(--accent-text); font-size: 14px; font-weight: 500;
            margin-bottom: 20px; cursor: pointer; background: none; border: none; padding: 0;
        }
        .back-btn:hover { opacity: 0.75; }
        .empty-state { text-align: center; padding: 48px 0; color: #bbb; font-size: 14px; }
        .empty-state svg { width: 48px; height: 48px; margin: 0 auto 12px; display: block; }
        .personal-task-section {
            min-height: 38px;
            border: 1px dashed transparent;
            border-radius: 10px;
            transition: background .15s, border-color .15s;
        }
        .personal-task-section.personal-drop-active {
            background: var(--accent-light);
            border-color: var(--accent);
        }
        .personal-task-section .task-card {
            cursor: grab;
            touch-action: pan-y;
            user-select: none;
        }
        .personal-task-section .task-card.personal-dragging { opacity: .35; }
        .personal-task-section:first-of-type { margin-top: 30px; }
        .personal-task-section[data-personal-section="today"] .task-section-label { color: var(--tappsk-blue); }
        .personal-task-section[data-personal-section="tomorrow"] .task-section-label { color: var(--tappsk-cyan); }
        .personal-task-section[data-personal-section="later"] .task-section-label { color: var(--tappsk-muted); }

        .hamburger {
            display: none;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.35);
            z-index: 90;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .sidebar-overlay.open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        @media (max-width: 768px) {
            body {
                background: #ffffff;
            }

            .main {
                margin-left: 0;
                padding: 82px 18px 74px;
            }

            .hamburger {
                display: flex;
                position: fixed;
                top: 24px;
                left: 22px;
                z-index: 120;
                width: 44px;
                height: 44px;
                border-radius: 14px;
                background: #fff;
                border: 1px solid #eee;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            }

            .sidebar {
                width: 280px;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                z-index: 110;
                border-right: none;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-logo {
                margin-left: 54px;
                margin-top: 7px;
                padding-bottom: 14px;
            }

            .page-header {
                display: none;
            }

            .form-input {
                font-size: 16px;
            }

            .fab {
                left: 0;
                height: 56px;
                padding: 0 18px;
            }
        }
            </style>
</head>
<body class="h-full">

{{-- Sidebar --}}
<button class="hamburger" onclick="toggleSidebar()" aria-label="Меню">
    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>
<div class="sidebar">
    <div class="sidebar-logo">Task<span>sk</span></div>

    <div class="sidebar-section"></div>

    <a href="{{ route('dashboard', ['view' => 'tasks']) }}"
        class="sidebar-item {{ request('view', 'tasks') === 'tasks' ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Мои задачи
    </a>

    <a href="{{ route('dashboard', ['view' => 'team']) }}"
        class="sidebar-item {{ request('view') === 'team' ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Команда
    </a>

    <a href="{{ route('dashboard', ['view' => 'objects']) }}"
        class="sidebar-item {{ request('view') === 'objects' ? 'active' : '' }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-2M9 9v.01M9 13v.01M9 17v.01M16 13v.01M16 17v.01"/>
        </svg>
        Объекты
    </a>

    <div style="flex: 1;"></div>

    <a href="{{ route('dashboard', ['view' => 'history']) }}"
        style="margin:0 16px;padding:9px 16px;border-radius:10px;color:#4a4a6a;font-size:14px;text-decoration:none;display:flex;align-items:center;gap:10px;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2M3.05 11a9 9 0 1 0 .5-3M3 4v4h4"/>
        </svg>
        История изменений
    </a>

    <form method="POST" action="/logout" style="margin: 0 16px 8px;">
        @csrf
        <button type="submit" style="width:100%;padding:9px 16px;border-radius:10px;background:none;border:none;color:#4a4a6a;font-size:14px;cursor:pointer;text-align:left;display:flex;align-items:center;gap:10px;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Выйти
        </button>
    </form>

    <div class="sidebar-avatar">
        <div class="sidebar-avatar-circle">{{ auth()->user()->initials() }}</div>
        <div>
            <div class="sidebar-avatar-name">{{ auth()->user()->name }}</div>
            <div class="sidebar-avatar-role">{{ auth()->user()->isDirector() ? 'Директор' : 'Сотрудник' }}</div>
        </div>
    </div>
</div>

{{-- Main --}}
<div class="main">
    @yield('content')
</div>

@if(isset($modal))
    {{ $modal }}
@endif
<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    sidebar.classList.toggle('open');
    overlay.classList.toggle('open', sidebar.classList.contains('open'));
}

function closeSidebar() {
    document.querySelector('.sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('open');
}

async function deletePersonalTask(taskId, button) {
    if (!confirm('Удалить задачу?')) return;

    const card = button.closest('.task-card');
    const section = card?.closest('.personal-task-section');
    const wasDone = card?.querySelector('.task-checkbox')?.classList.contains('checked');

    const response = await fetch(`/tasks/${taskId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    });

    if (!response.ok) {
        alert('Не удалось удалить задачу. Попробуйте ещё раз.');
        return;
    }

    const counter = section?.querySelector('.task-progress-count');
    if (counter) {
        const total = Math.max(0, Number(counter.dataset.total || 0) - 1);
        const done = Math.max(0, Number(counter.dataset.done || 0) - (wasDone ? 1 : 0));
        counter.dataset.total = total;
        counter.dataset.done = done;
        counter.textContent = `${done}/${total}`;
    }

    card?.remove();
}

function personalLocalDate(offset = 0) {
    const date = new Date();
    date.setHours(12, 0, 0, 0);
    date.setDate(date.getDate() + offset);
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

function updateTaskProgress(checkbox, change) {
    const section = checkbox.closest('.personal-task-section');
    if (!section) return;

    const counter = section.querySelector('.task-progress-count');
    if (!counter) return;

    const total = Number(counter.dataset.total || 0);
    const done = Math.max(0, Math.min(total, Number(counter.dataset.done || 0) + change));
    counter.dataset.done = done;
    counter.textContent = `${done}/${total}`;
}

function personalSectionUpdate(section) {
    return {
        overdue: { timing: 'date', due_date: personalLocalDate(-1) },
        today: { timing: 'today', due_date: null },
        tomorrow: { timing: 'date', due_date: personalLocalDate(1) },
        later: { timing: 'later', due_date: null },
    }[section];
}

function changeSectionTotal(section, change) {
    const counter = section?.querySelector('.task-progress-count');
    if (!counter) return;

    const done = Number(counter.dataset.done || 0);
    const total = Math.max(done, Number(counter.dataset.total || 0) + change);
    counter.dataset.total = total;
    counter.textContent = `${done}/${total}`;
}

function personalTimingBadge(section, dueDate) {
    if (section === 'today') return '<span class="badge badge-today">Сегодня</span>';
    if (section === 'later') return '<span class="badge badge-later">Отложено</span>';
    if (!dueDate) return '';

    const date = new Date(`${dueDate}T12:00:00`);
    return `<span class="badge badge-date">${date.toLocaleDateString('ru', { day: 'numeric', month: 'short' })}</span>`;
}

async function movePersonalTask(taskId, section, sourceSection) {
    const updates = personalSectionUpdate(section);
    if (!taskId || !updates) return;

    const card = document.getElementById(`task-${taskId}`);
    const targetSection = document.querySelector(`.personal-task-section[data-personal-section="${section}"]`);
    if (!card || !sourceSection || !targetSection) return;

    const orderedTaskIds = [...targetSection.querySelectorAll('.task-card')]
        .map(item => Number(item.id.replace('task-', '')));

    const response = await fetch(`/tasks/${taskId}/move`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ ...updates, ordered_task_ids: orderedTaskIds }),
    });

    if (!response.ok) {
        alert('Не удалось перенести задачу. Попробуйте ещё раз.');
        window.location.reload();
        return;
    }

    if (sourceSection !== targetSection) {
        changeSectionTotal(sourceSection, -1);
        changeSectionTotal(targetSection, 1);
    }

    const timingSlot = card.querySelector('.task-date-slot');
    if (timingSlot) timingSlot.innerHTML = personalTimingBadge(section, updates.due_date);
}

function initPersonalTaskDrag() {
    const sections = [...document.querySelectorAll('.personal-task-section')];
    if (!sections.length) return;

    let draggedId = null;
    let sourceSection = null;
    let touchTarget = null;
    const clearHighlights = () => sections.forEach(section => section.classList.remove('personal-drop-active'));

    sections.forEach(section => {
        section.addEventListener('dragover', event => {
            if (!draggedId) return;
            event.preventDefault();
            clearHighlights();
            section.classList.add('personal-drop-active');
            const card = document.getElementById(`task-${draggedId}`);
            const cards = [...section.querySelectorAll('.task-card:not(.personal-dragging)')];
            const before = cards.find(item => event.clientY < item.getBoundingClientRect().top + item.offsetHeight / 2);
            section.insertBefore(card, before || null);
        });
        section.addEventListener('drop', event => {
            event.preventDefault();
            clearHighlights();
            movePersonalTask(draggedId, section.dataset.personalSection, sourceSection);
        });
    });

    document.querySelectorAll('.personal-task-section .task-card').forEach(card => {
        const taskId = card.id?.replace('task-', '');
        card.draggable = true;

        card.addEventListener('dragstart', event => {
            if (event.target.closest('.task-checkbox')) {
                event.preventDefault();
                return;
            }
            draggedId = taskId;
            sourceSection = card.closest('.personal-task-section');
            card.classList.add('personal-dragging');
            event.dataTransfer.effectAllowed = 'move';
        });
        card.addEventListener('dragend', () => {
            window.personalSuppressClickUntil = Date.now() + 300;
            draggedId = null;
            sourceSection = null;
            card.classList.remove('personal-dragging');
            clearHighlights();
        });

        let timer = null;
        let active = false;
        let startX = 0;
        let startY = 0;

        card.addEventListener('touchstart', event => {
            if (event.target.closest('.task-checkbox')) return;
            const touch = event.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
            timer = setTimeout(() => {
                active = true;
                draggedId = taskId;
                sourceSection = card.closest('.personal-task-section');
                card.classList.add('personal-dragging');
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
            clearHighlights();
            touchTarget = document.elementFromPoint(touch.clientX, touch.clientY)?.closest('.personal-task-section') || null;
            touchTarget?.classList.add('personal-drop-active');
        }, { passive: false });

        card.addEventListener('touchend', () => {
            clearTimeout(timer);
            card.classList.remove('personal-dragging');
            clearHighlights();
            if (active) window.personalSuppressClickUntil = Date.now() + 500;
            if (active && touchTarget) {
                touchTarget.appendChild(card);
                movePersonalTask(draggedId, touchTarget.dataset.personalSection, sourceSection);
            }
            draggedId = null;
            sourceSection = null;
            touchTarget = null;
            active = false;
        });
    });
}

document.addEventListener('DOMContentLoaded', initPersonalTaskDrag);
</script>

@fluxScripts
</body>
</html>
