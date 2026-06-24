<x-layouts::app :title="__('Мои задачи')">

    {{-- Отступ снизу чтобы контент не перекрывался нижней панелью --}}
    <div class="pb-14">

        {{-- ПРОСРОЧЕНЫ --}}
        @if($overdueTasks->isNotEmpty())
            <div class="px-8 pt-8 pb-4">
                <div class="flex items-center gap-3 mb-3">
                    <h2 class="text-xl font-black uppercase tracking-widest text-red-400">Просрочены</h2>
                    <span class="text-xs text-zinc-400 bg-zinc-100 rounded-full px-2 py-0.5 font-medium">
                        {{ $overdueTasks->where('status','done')->count() }}/{{ $overdueTasks->count() }}
                    </span>
                </div>
                <div class="divide-y divide-zinc-100">
                    @foreach($overdueTasks as $task)
                        @include('partials.task-row', ['task' => $task])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- СЕГОДНЯ --}}
        <div class="px-8 pt-8 pb-4">
            <div class="flex items-center gap-3 mb-3">
                <h2 class="text-xl font-black uppercase tracking-widest {{ $todayTasks->isNotEmpty() ? 'text-sky-400' : 'text-zinc-300' }}">
                    Сегодня
                </h2>
                @if($todayTasks->isNotEmpty())
                    <span class="text-xs text-zinc-400 bg-zinc-100 rounded-full px-2 py-0.5 font-medium">
                        {{ $todayTasks->where('status','done')->count() }}/{{ $todayTasks->count() }}
                    </span>
                @endif
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse($todayTasks as $task)
                    @include('partials.task-row', ['task' => $task])
                @empty
                    <p class="py-2 text-sm text-zinc-300">Нет задач</p>
                @endforelse
            </div>
        </div>

        {{-- ЗАВТРА --}}
        <div class="px-8 pt-8 pb-4">
            <div class="flex items-center gap-3 mb-3">
                <h2 class="text-xl font-black uppercase tracking-widest {{ $tomorrowTasks->isNotEmpty() ? 'text-sky-400' : 'text-zinc-300' }}">
                    Завтра
                </h2>
                @if($tomorrowTasks->isNotEmpty())
                    <span class="text-xs text-zinc-400 bg-zinc-100 rounded-full px-2 py-0.5 font-medium">
                        {{ $tomorrowTasks->where('status','done')->count() }}/{{ $tomorrowTasks->count() }}
                    </span>
                @endif
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse($tomorrowTasks as $task)
                    @include('partials.task-row', ['task' => $task])
                @empty
                    <p class="py-2 text-sm text-zinc-300">Нет задач</p>
                @endforelse
            </div>
        </div>

        {{-- НА НЕДЕЛЕ --}}
        <div class="px-8 pt-8 pb-4">
            <div class="flex items-center gap-3 mb-3">
                <h2 class="text-xl font-black uppercase tracking-widest {{ $weekTasks->isNotEmpty() ? 'text-sky-400' : 'text-zinc-300' }}">
                    На неделе
                </h2>
                @if($weekTasks->isNotEmpty())
                    <span class="text-xs text-zinc-400 bg-zinc-100 rounded-full px-2 py-0.5 font-medium">
                        {{ $weekTasks->where('status','done')->count() }}/{{ $weekTasks->count() }}
                    </span>
                @endif
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse($weekTasks as $task)
                    @include('partials.task-row', ['task' => $task])
                @empty
                    <p class="py-2 text-sm text-zinc-300">Нет задач</p>
                @endforelse
            </div>
        </div>

        {{-- ПОТОМ --}}
        <div class="px-8 pt-8 pb-4">
            <div class="flex items-center gap-3 mb-3">
                <h2 class="text-xl font-black uppercase tracking-widest {{ $laterTasks->isNotEmpty() ? 'text-sky-400' : 'text-zinc-300' }}">
                    Потом
                </h2>
                @if($laterTasks->isNotEmpty())
                    <span class="text-xs text-zinc-400 bg-zinc-100 rounded-full px-2 py-0.5 font-medium">
                        {{ $laterTasks->where('status','done')->count() }}/{{ $laterTasks->count() }}
                    </span>
                @endif
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse($laterTasks as $task)
                    @include('partials.task-row', ['task' => $task])
                @empty
                    <p class="py-2 text-sm text-zinc-300">Нет задач</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- НИЖНЯЯ ПАНЕЛЬ
         Flux sidebar = w-64 = 256px на десктопе.
         На мобиле сайдбара нет — left:0.
         Используем data-атрибут сайдбара чтобы определить схлопнут ли он.
    --}}
    <div
        x-data="quickBar()"
        id="quick-bar"
        class="fixed bottom-0 right-0 z-40 bg-white border-t border-zinc-100 px-8 py-3"
        style="left: 256px"
    >
        <div x-show="!editing"
            @click="open()"
            class="flex items-center gap-2 text-zinc-400 cursor-pointer hover:text-sky-400 transition select-none">
            <span class="text-lg font-light">+</span>
            <span class="text-sm">Новая задача</span>
        </div>
        <div x-show="editing" @keydown.escape.window="editing = false; title = ''">
            <input
                x-ref="quickInput"
                x-model="title"
                @keydown.enter="submit()"
                @keydown.escape="editing = false; title = ''"
                type="text"
                placeholder="Название задачи — Enter чтобы добавить детали"
                class="w-full bg-transparent text-sm text-zinc-800 placeholder:text-zinc-300 outline-none"
            >
        </div>
    </div>

    {{-- На мобиле сайдбар скрыт — сдвигаем панель влево --}}
    <style>
        @media (max-width: 1023px) {
            #quick-bar { left: 0 !important; }
        }
        /* Если сайдбар схлопнут (collapsed) на десктопе — он становится w-14 = 56px */
        [data-flux-sidebar][data-flux-sidebar-collapsed-desktop] ~ * #quick-bar {
            left: 56px !important;
        }
    </style>

    {{-- МОДАЛЬНЫЙ ПОПАП деталей задачи --}}
    <div x-data="taskModal()" x-init="init()">
        <template x-if="showForm">
            <div
                class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center px-4 pb-4 sm:pb-0"
                @keydown.escape.window="close()"
            >
                <div class="absolute inset-0 bg-black/20" @click="close()"></div>
                <div class="relative w-full max-w-lg rounded-2xl bg-white border border-zinc-200 p-6 shadow-xl space-y-4">

                    <h3 class="text-base font-semibold text-zinc-800">Новая задача</h3>

                    <input x-model="form.title" type="text" placeholder="Название задачи"
                        class="w-full border-b border-zinc-200 bg-transparent py-2 text-base text-zinc-800 placeholder:text-zinc-300 focus:outline-none focus:border-sky-400">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-zinc-400 mb-1.5">Объект</label>
                            <select x-model="form.project_id" class="w-full rounded-lg border border-zinc-200 bg-transparent px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:border-sky-400">
                                <template x-for="p in projects" :key="p.id">
                                    <option :value="p.id" x-text="p.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-zinc-400 mb-1.5">Отдел</label>
                            <select x-model="form.department_id" class="w-full rounded-lg border border-zinc-200 bg-transparent px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:border-sky-400">
                                <template x-for="d in departments" :key="d.id">
                                    <option :value="d.id" x-text="d.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-zinc-400 mb-1.5">Исполнитель</label>
                            <select x-model="form.assigned_to" class="w-full rounded-lg border border-zinc-200 bg-transparent px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:border-sky-400">
                                <template x-for="u in users" :key="u.id">
                                    <option :value="u.id" x-text="u.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-zinc-400 mb-1.5">Срок</label>
                            <input x-model="form.due_date" type="date"
                                class="w-full rounded-lg border border-zinc-200 bg-transparent px-3 py-2 text-sm text-zinc-800 focus:outline-none focus:border-sky-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-zinc-400 mb-1.5">Приоритет</label>
                        <div class="flex gap-2">
                            <template x-for="p in [{val:'low',label:'Низкий'},{val:'medium',label:'Средний'},{val:'high',label:'Высокий'}]" :key="p.val">
                                <button type="button" @click="form.priority = p.val"
                                    :class="form.priority === p.val ? 'bg-sky-400 text-white border-sky-400' : 'border-zinc-200 text-zinc-400 hover:border-zinc-300'"
                                    class="flex-1 rounded-lg border px-3 py-1.5 text-xs font-semibold uppercase tracking-wide transition"
                                    x-text="p.label"></button>
                            </template>
                        </div>
                    </div>

                    <textarea x-model="form.description" rows="2" placeholder="Описание (необязательно)"
                        class="w-full resize-none rounded-lg border border-zinc-200 bg-transparent px-3 py-2 text-sm text-zinc-600 placeholder:text-zinc-300 focus:outline-none focus:border-sky-400"></textarea>

                    <div class="flex justify-end gap-3 pt-1">
                        <button @click="close()" class="px-4 py-2 text-sm text-zinc-400 hover:text-zinc-600 transition">Отмена</button>
                        <button @click="submit()" :disabled="!form.title.trim()"
                            class="rounded-full bg-sky-400 px-6 py-2 text-sm font-semibold text-white hover:bg-sky-500 disabled:opacity-30 disabled:cursor-not-allowed transition">
                            Создать
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
    function taskModal() {
        return {
            showForm: false,
            projects: [], departments: [], users: [],
            form: { title: '', project_id: '', department_id: '', assigned_to: '{{ auth()->id() }}', due_date: '', priority: 'medium', description: '' },
            init() {
                window.addEventListener('open-task-modal', (e) => {
                    this.form.title = e.detail?.title ?? '';
                    this.showForm = true;
                    this.loadFormData();
                });
            },
            async loadFormData() {
                const res = await fetch('{{ route('tasks.form-data') }}');
                const d = await res.json();
                this.projects = d.projects; this.departments = d.departments; this.users = d.users;
                if (this.projects.length && !this.form.project_id) this.form.project_id = this.projects[0].id;
                if (this.departments.length && !this.form.department_id) this.form.department_id = this.departments[0].id;
            },
            close() {
                this.showForm = false;
                this.form = { title: '', project_id: '', department_id: '', assigned_to: '{{ auth()->id() }}', due_date: '', priority: 'medium', description: '' };
            },
            async submit() {
                if (!this.form.title.trim()) return;
                await fetch('{{ route('tasks.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(this.form)
                });
                this.close();
                window.location.reload();
            }
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('quickBar', () => ({
            editing: false, title: '',
            open() { this.editing = true; this.$nextTick(() => this.$refs.quickInput?.focus()); },
            submit() {
                window.dispatchEvent(new CustomEvent('open-task-modal', { detail: { title: this.title } }));
                this.title = ''; this.editing = false;
            }
        }));
    });
    </script>

</x-layouts::app>
