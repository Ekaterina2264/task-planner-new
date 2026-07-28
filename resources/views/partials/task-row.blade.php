<div class="task-card" id="task-{{ $task->id }}" x-data="{ done: {{ $task->status === 'done' ? 'true' : 'false' }} }">
    <div
        class="task-checkbox"
        :class="{ 'checked': done }"
        @click.stop="
            done = !done;
            updateTaskProgress($el, done ? 1 : -1);
            fetch('{{ route('tasks.update', $task) }}', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ status: done ? 'done' : 'new' })
            });
        "
    >
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <div style="flex:1;cursor:pointer;" onclick="if (window.personalSuppressClickUntil && Date.now() < window.personalSuppressClickUntil) return; openEditModal({{ $task->id }}, '{{ addslashes($task->title) }}', '{{ $task->priority }}', '{{ $task->timing }}', '{{ $task->due_date?->format('Y-m-d') }}', '{{ addslashes($task->comment ?? '') }}')">
        <span class="task-title" :class="{ 'done': done }">{{ $task->title }}</span>
        @if($task->objectItem?->workObject)
            <div style="font-size:11px;color:var(--tappsk-blue);margin-top:3px;">
                Объект: {{ $task->objectItem->workObject->name }}
            </div>
        @endif
        @if($task->comment)
            <div style="font-size:12px;color:#aaa;margin-top:3px;">{{ $task->comment }}</div>
        @endif
    </div>

    <div class="task-badges">
        <span class="task-priority-slot">
            @if($task->priority === 'high')
                <span class="badge badge-high">Высокий</span>
            @elseif($task->priority === 'low')
                <span class="badge badge-low">Низкий</span>
            @endif
        </span>
        <span class="task-date-slot">
            @if($task->timing === 'today')
                <span class="badge badge-today">Сегодня</span>
            @elseif($task->timing === 'later')
                <span class="badge badge-later">Отложено</span>
            @elseif($task->timing === 'date' && $task->due_date)
                <span class="badge badge-date">{{ $task->due_date->locale('ru')->isoFormat('D MMM') }}</span>
            @endif
        </span>
    </div>
</div>
