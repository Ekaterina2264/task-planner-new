<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    @include('partials.head')

    <style>
        /* Tappsk-style design */
        :root {
            --sidebar-bg: #ffffff;
            --sidebar-text: #666;
            --sidebar-active: #ffffff;
            --sidebar-hover: #f5f5fa;
            --accent: #7c6ff7;
            --accent-light: #ede9ff;
            --accent-text: #5b52d4;
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
        body { font-family: -apple-system, BlinkMacSystemFont, 'Inter', sans-serif; background: #ffffff; }
        .sidebar {
            width: 240px; min-height: 100vh; background: var(--sidebar-bg);
            display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 50;
            border-right: 1px solid #eee;
        }
        .sidebar-logo {
            padding: 24px 20px 20px; font-size: 20px; font-weight: 700;
            color: #1a1a2e; letter-spacing: -0.5px;
        }
        .sidebar-logo span { color: var(--accent); }
        .sidebar-section { padding: 6px 12px; font-size: 10px; font-weight: 600;
            letter-spacing: 1px; text-transform: uppercase; color: #4a4a6a; margin-top: 8px; }
        .sidebar-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 16px; margin: 1px 8px; border-radius: 10px;
            color: var(--sidebar-text); text-decoration: none; font-size: 14px;
            transition: all 0.15s;
        }
        .sidebar-item:hover { background: var(--sidebar-hover); color:  var(--accent-text); }
        .sidebar-item.active { background: var(--accent); color: #fff; }
        .sidebar-item svg { width: 18px; height: 18px; flex-shrink: 0; }
        .sidebar-avatar {
            margin: auto 16px 20px; padding: 12px 16px; border-radius: 12px;
            background: #f5f5fa; display: flex; align-items: center; gap: 10px;
        }
        .sidebar-avatar-circle {
            width: 34px; height: 34px; border-radius: 50%; background: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 600; color: #fff; flex-shrink: 0;
        }
        .sidebar-avatar-name { font-size: 13px; font-weight: 500; color: #1a1a2e; line-height: 1.3; }
        .sidebar-avatar-role { font-size: 11px; color: var(--sidebar-text); }
        .main { margin-left: 240px; min-height: 100vh; padding: 32px 40px; background: #ffffff; }
        .page-title { font-size: 26px; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; }
        .page-subtitle { font-size: 14px; color: #888; margin-bottom: 32px; }

        /* Task list */
        .task-section { margin-bottom: 24px; }
        .task-section-label {
            font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;
            color: #5bc8f5; margin-bottom: 8px; margin-top: 16px; display: flex; align-items: center; gap: 8px;
        }
        .task-section-label.overdue { color: #ff5c5c; }
        .task-section-count {
            background: #f0f0f5; border-radius: 20px; padding: 1px 8px;
            font-size: 12px; color: #aaa; font-weight: 600;
        }
        .task-card {
            background: transparent; border-radius: 0; margin-bottom: 0;
            display: flex; align-items: center; gap: 12px; padding: 10px 0;
            box-shadow: none; border-bottom: 1px solid #f5f5f5; transition: none;
        }
        .task-card:hover { box-shadow: none; }
        .task-checkbox {
            width: 20px; height: 20px; border-radius: 4px; border: 2px solid #c5b8fb;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; flex-shrink: 0; transition: all 0.2s;
        }
        .task-checkbox:hover { border-color: var(--accent); }
        .task-checkbox.checked { background: var(--accent); border-color: var(--accent); }
        .task-checkbox svg { width: 12px; height: 12px; color: #fff; display: none; }
        .task-checkbox.checked svg { display: block; }
        .task-title { flex: 1; font-size: 14px; color: #1a1a2e; font-weight: 500; line-height: 1.4; }
        .task-title.done { text-decoration: line-through; color: #bbb; }
        .task-badges { display: flex; gap: 6px; align-items: center; flex-shrink: 0; }
        .badge {
            font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 20px; white-space: nowrap;
        }
        .badge-high { background: var(--high-bg); color: var(--high); }
        .badge-medium { background: var(--medium-bg); color: var(--medium); }
        .badge-low { background: var(--low-bg); color: var(--low); }
        .badge-date { background: #ede9ff; color: #5b52d4; }
        .badge-today { background: #e8f4ff; color: #2f86d4; }
        .badge-later { background: #f5f5f8; color: #888; }

        /* Employee cards (director view) */
        .employee-card {
            background: #fff; border-radius: 14px; padding: 16px 20px;
            display: flex; align-items: center; gap: 14px; margin-bottom: 10px;
            cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.15s;
        }
        .employee-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,0.1); transform: translateY(-1px); }
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
            position: fixed; bottom: 32px; right: 40px;
            width: 52px; height: 52px; border-radius: 50%;
            background: var(--accent); color: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 4px 20px rgba(124,111,247,0.4);
            font-size: 26px; line-height: 1; border: none;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .fab:hover { transform: scale(1.08); box-shadow: 0 6px 24px rgba(124,111,247,0.5); }

        /* Modal */
        .modal-backdrop {
            position: fixed; inset: 0; background: rgba(0,0,0,0.4);
            display: flex; align-items: center; justify-content: center; z-index: 200; padding: 20px;
        }
        .modal {
            background: #fff; border-radius: 20px; padding: 28px;
            width: 100%; max-width: 440px; box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        .modal-title { font-size: 18px; font-weight: 700; color: #1a1a2e; margin-bottom: 20px; }
        .form-label { font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: block; }
        .form-input {
            width: 100%; padding: 11px 14px; border: 1.5px solid #eee; border-radius: 10px;
            font-size: 14px; color: #1a1a2e; background: #fafafa;
            outline: none; transition: border-color 0.15s;
        }
        .form-input:focus { border-color: var(--accent); background: #fff; }
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
            width: 100%; padding: 13px; border-radius: 12px; background: var(--accent);
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
                padding: 84px 22px 24px;
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

    @if(auth()->user()->isDirector())
    <a href="{{ route('director.tasks') }}" class="sidebar-item {{ request()->routeIs('director.tasks') ? 'active' : '' }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Мои задачи
    </a>
    @endif

    <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        @if(auth()->user()->isDirector()) Команда @else Мои задачи @endif
    </a>


    <div style="flex: 1;"></div>

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
</script>

@fluxScripts
</body>
</html>
