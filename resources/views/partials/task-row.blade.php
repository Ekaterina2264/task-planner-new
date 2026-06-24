<div class="task-card" id="task-{{ $task->id }}" x-data="{ done: {{ $task->status === 'done' ? 'true' : 'false' }} }">
    <div
        class="task-checkbox"
        :class="{ 'checked': done }"
        @click="
            done = !done;
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

    <span class="task-title" :class="{ 'done': done }">{{ $task->title }}</span>

    <div class="task-badges">
        @if($task->priority === 'high')
            <span class="badge badge-high">Высокий</span>
        @elseif($task->priority === 'low')
            <span class="badge badge-low">Низкий</span>
        @endif

        @if($task->timing === 'today')
            <span class="badge badge-today">Сегодня</span>
        @elseif($task->timing === 'later')
            <span class="badge badge-later">Отложено</span>
        @elseif($task->timing === 'date' && $task->due_date)
            <span class="badge badge-date">{{ $task->due_date->format('j M') }}</span>
        @endif
    </div>
</div>
