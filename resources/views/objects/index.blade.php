@extends('layouts.app')
@section('content')
@php($avatarColors = ['#7c6ff7', '#38c97b', '#ff5c5c', '#f4a223', '#2f86d4', '#e040fb'])

<div class="objects-page-header">
    <div class="page-title">Объекты</div>
    <div class="objects-page-actions">
        <button class="new-object-button" type="button" onclick="openObjectModal()">+ Новый объект</button>
        <button class="trash-button" type="button" onclick="openTrashModal()"
            title="Корзина" aria-label="Корзина">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 10v6M14 10v6"/>
            </svg>
        </button>
    </div>
</div>

<div class="objects-list">
    @forelse($objects as $object)
        <section class="object-board" id="object-{{ $object->id }}" data-object-id="{{ $object->id }}">
            <div class="object-board-header">
                <div class="object-icon">
                    <svg width="21" height="21" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-2M9 9v.01M9 13v.01M9 17v.01M16 13v.01M16 17v.01"/>
                    </svg>
                </div>
                <div class="object-heading">
                    <div class="object-name">{{ $object->name }}</div>
                    <div class="object-count">{{ $object->items->where('is_completed', false)->count() }} активных пунктов</div>
                </div>
                <div class="object-actions">
                    <div class="object-menu">
                        <button class="object-menu-toggle" type="button"
                            onclick="toggleObjectMenu(event, {{ $object->id }})"
                            title="Действия с объектом" aria-label="Действия с объектом">•••</button>
                        <div class="object-menu-dropdown" id="object-menu-{{ $object->id }}" hidden>
                            <button type="button" data-name="{{ $object->name }}"
                                onclick="openRenameObjectModal({{ $object->id }}, this.dataset.name)">Переименовать</button>
                            <button type="button"
                                onclick="openSectionModal({{ $object->id }})">Добавить раздел</button>
                            <button type="button" class="is-danger" data-name="{{ $object->name }}"
                                onclick="deleteObject({{ $object->id }}, this.dataset.name)">Удалить объект</button>
                        </div>
                    </div>
                </div>
                <button class="object-collapse" type="button" onclick="toggleObject({{ $object->id }})"
                    title="Свернуть объект" aria-label="Свернуть объект" aria-expanded="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 15 6-6 6 6"/>
                    </svg>
                </button>
            </div>

            <div class="object-sections" id="object-sections-{{ $object->id }}">
                @foreach($object->sections as $section)
                    @php($items = $object->items->where('section', $section->key))
                    <div class="object-section">
                        <div class="object-section-header">
                            <div class="task-section-label">
                                {{ $section->name }}
                                <span class="task-section-count task-progress-count">{{ $items->where('is_completed', true)->count() }}/{{ $items->count() }}</span>
                            </div>
                            <div class="object-section-controls">
                                <button type="button" class="object-add-item"
                                    data-section="{{ $section->key }}"
                                    data-name="{{ $section->name }}"
                                    onclick="openItemModal({{ $object->id }}, this.dataset.section, this.dataset.name)"
                                    title="Добавить пункт">+</button>
                                <div class="object-section-actions">
                                    <button type="button" class="object-section-action"
                                        data-name="{{ $section->name }}"
                                        onclick="openSectionModal({{ $object->id }}, {{ $section->id }}, this.dataset.name)"
                                        title="Переименовать раздел">✎</button>
                                    <button type="button" class="object-section-action object-section-delete"
                                        data-name="{{ $section->name }}"
                                        onclick="deleteSection({{ $section->id }}, this.dataset.name, {{ $items->count() }})"
                                        title="Удалить раздел">×</button>
                                </div>
                            </div>
                        </div>

                        <div class="object-items {{ $items->isEmpty() ? 'is-empty' : '' }}">
                            @foreach($items as $item)
                                <div class="task-card object-item {{ $item->is_completed ? 'object-item-completed' : '' }}">
                                    <button type="button" class="task-checkbox {{ $item->is_completed ? 'checked' : '' }}"
                                        onclick="toggleObjectItem({{ $item->id }}, {{ $item->is_completed ? 'false' : 'true' }})"
                                        aria-label="{{ $item->is_completed ? 'Вернуть пункт' : 'Отметить выполненным' }}">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <div class="object-item-content">
                                        <span class="task-title {{ $item->is_completed ? 'done' : '' }}">{{ $item->title }}</span>
                                        @if($item->comment)
                                            <div class="object-item-comment">{{ $item->comment }}</div>
                                        @endif
                                    </div>
                                    @if($item->completed_at)
                                        <span class="object-completed-date">{{ $item->completed_at->format('d.m.Y') }}</span>
                                    @endif
                                    @if($item->assignee)
                                        @php($assigneeColor = $avatarColors[$item->assigned_to % count($avatarColors)])
                                        <button type="button" class="object-assignee-avatar"
                                            style="background: {{ $assigneeColor }}22; color: {{ $assigneeColor }}"
                                            onclick="openAssigneeModal({{ $item->id }}, {{ $item->assigned_to }})"
                                            title="Ответственный: {{ $item->assignee->name }}">
                                            {{ $item->assignee->initials() }}
                                        </button>
                                    @else
                                        <button type="button" class="object-assignee-avatar is-empty"
                                            onclick="openAssigneeModal({{ $item->id }}, null)"
                                            title="Назначить ответственного">+</button>
                                    @endif
                                    <button type="button" class="object-item-delete"
                                        onclick="deleteObjectItem({{ $item->id }})"
                                        title="Удалить пункт" aria-label="Удалить пункт">×</button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        </section>
    @empty
        <div class="empty-state">Объектов пока нет</div>
    @endforelse
</div>

@if($objects->count() > 1)
    <div class="objects-nav">
        <button type="button" class="objects-nav-previous" onclick="scrollToObject(-1)"
            title="Предыдущий объект" aria-label="Предыдущий объект">↑</button>
        <button type="button" class="objects-nav-next" onclick="scrollToObject(1)">Следующий объект ↓</button>
    </div>
@endif

<div id="trash-modal" class="modal-backdrop" style="display:none" onclick="closeTrashModal(event)">
    <div class="modal trash-modal" onclick="event.stopPropagation()">
        <div class="modal-title">Корзина</div>
        <div class="trash-modal-list">
            @forelse($deletedItems->groupBy('work_object_id') as $objectItems)
                @php($deletedObject = $objectItems->first()->workObject)
                @if($deletedObject)
                    <div class="trash-object-group">
                        <div class="trash-object-name">{{ $deletedObject->name }}</div>
                        @foreach($objectItems as $item)
                            @php($sectionName = $deletedObject->sections->firstWhere('key', $item->section)?->name ?? 'Удалённый раздел')
                            <div class="object-trash-item">
                                <div class="object-trash-item-content">
                                    <span class="object-trash-item-title">{{ $item->title }}</span>
                                    <span class="object-trash-section">{{ $sectionName }}</span>
                                </div>
                                <button type="button" class="object-restore-button"
                                    onclick="restoreObjectItem({{ $item->id }})">Восстановить</button>
                            </div>
                        @endforeach
                    </div>
                @endif
            @empty
                <div class="trash-empty">Удалённых пунктов нет</div>
            @endforelse
        </div>
        <button type="button" class="btn-cancel" onclick="closeTrashModal()">Закрыть</button>
    </div>
</div>

<div id="object-modal" class="modal-backdrop" style="display:none" onclick="closeObjectModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-title">Новый объект</div>
        <div class="form-group">
            <label class="form-label">Название объекта</label>
            <input type="text" id="object-name" class="form-input" placeholder="Введите название">
        </div>
        <button type="button" class="btn-submit" onclick="createObject()">Создать объект</button>
        <button type="button" class="btn-cancel" onclick="closeObjectModal()">Отмена</button>
    </div>
</div>

<div id="rename-object-modal" class="modal-backdrop" style="display:none" onclick="closeRenameObjectModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-title">Переименовать объект</div>
        <div class="form-group">
            <label class="form-label">Название объекта</label>
            <input type="text" id="rename-object-name" class="form-input" placeholder="Введите название">
        </div>
        <button type="button" class="btn-submit" onclick="renameObject()">Сохранить</button>
        <button type="button" class="btn-cancel" onclick="closeRenameObjectModal()">Отмена</button>
    </div>
</div>

<div id="section-modal" class="modal-backdrop" style="display:none" onclick="closeSectionModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-title" id="section-modal-title">Новый раздел</div>
        <div class="form-group">
            <label class="form-label">Название раздела</label>
            <input type="text" id="section-name" class="form-input" placeholder="Введите название раздела">
        </div>
        <button type="button" class="btn-submit" id="section-submit" onclick="saveSection()">Добавить</button>
        <button type="button" class="btn-cancel" onclick="closeSectionModal()">Отмена</button>
    </div>
</div>

<div id="item-modal" class="modal-backdrop" style="display:none" onclick="closeItemModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-title" id="item-modal-title">Новый пункт</div>
        <div class="form-group">
            <label class="form-label">Название</label>
            <input type="text" id="item-title" class="form-input" placeholder="Что нужно добавить?">
        </div>
        <div class="form-group">
            <label class="form-label">Комментарий</label>
            <textarea id="item-comment" class="form-input" rows="3" placeholder="Дополнительная информация..."></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Ответственный</label>
            <div class="object-select-field">
                <select id="item-assignee" class="form-input object-select">
                    <option value="">Не назначен</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m8 10 4 4 4-4"/>
                </svg>
            </div>
        </div>
        <button type="button" class="btn-submit" onclick="createObjectItem()">Добавить</button>
        <button type="button" class="btn-cancel" onclick="closeItemModal()">Отмена</button>
    </div>
</div>

<div id="assignee-modal" class="modal-backdrop" style="display:none" onclick="closeAssigneeModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-title">Ответственный</div>
        <div class="form-group">
            <label class="form-label">Сотрудник</label>
            <div class="object-select-field">
                <select id="assignee-select" class="form-input object-select">
                    <option value="">Не назначен</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m8 10 4 4 4-4"/>
                </svg>
            </div>
        </div>
        <button type="button" class="btn-submit" onclick="saveAssignee()">Сохранить</button>
        <button type="button" class="btn-cancel" onclick="closeAssigneeModal()">Отмена</button>
    </div>
</div>

<style>
.objects-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
}
.new-object-button {
    border: 0;
    border-radius: 20px;
    background: var(--tappsk-blue);
    color: #fff;
    padding: 10px 17px;
    font-size: 13px;
    font-weight: 650;
    cursor: pointer;
}
.objects-page-actions {
    display: flex;
    align-items: center;
    gap: 9px;
}
.trash-button {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border: 1px solid #e6e6ec;
    border-radius: 50%;
    background: #fff;
    color: #92929f;
    padding: 0;
    cursor: pointer;
}
.trash-button:hover {
    border-color: #d9d9e2;
    background: #f8f8fa;
    color: #6f6f7c;
}
.trash-button svg {
    width: 17px;
    height: 17px;
}
.object-board {
    margin-bottom: 30px;
    scroll-margin-top: 24px;
}
.object-board-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 9px;
    border-bottom: 1px solid #f0f0f4;
}
.object-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #eef9ff;
    color: var(--tappsk-blue);
}
.object-icon svg { width: 21px; height: 21px; }
.object-name {
    color: #1a1a2e;
    font-size: 18px;
    font-weight: 650;
}
.object-heading { min-width: 0; }
.object-count {
    color: #999;
    font-size: 12px;
    margin-top: 2px;
}
.object-actions {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    margin-left: auto;
}
.object-section-action {
    border: 0;
    background: transparent;
    color: #aaaab6;
    cursor: pointer;
}
.object-menu {
    position: relative;
}
.object-menu-toggle {
    width: 34px;
    height: 34px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #aaaab6;
    font-size: 15px;
    letter-spacing: 1px;
    cursor: pointer;
}
.object-menu-toggle:hover,
.object-menu-toggle[aria-expanded="true"] {
    background: #f5f5f8;
    color: #777784;
}
.object-menu-dropdown {
    position: absolute;
    z-index: 20;
    top: calc(100% + 4px);
    right: 0;
    width: 168px;
    padding: 5px;
    border: 1px solid #ececf1;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(27, 27, 45, .1);
}
.object-menu-dropdown button {
    width: 100%;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: #555563;
    padding: 8px 9px;
    font-size: 12px;
    text-align: left;
    cursor: pointer;
}
.object-menu-dropdown button:hover {
    background: #f5f5f8;
}
.object-menu-dropdown button.is-danger {
    color: #d95763;
}
.object-menu-dropdown button.is-danger:hover {
    background: #fff1f2;
}
.object-collapse {
    flex: 0 0 34px;
    width: 34px;
    min-width: 34px;
    height: 34px;
    margin-left: 2px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #aaaab6;
    cursor: pointer;
}
.object-collapse svg {
    display: block;
    width: 18px;
    height: 18px;
    margin: auto;
    transition: transform .15s ease;
}
.object-collapse.is-collapsed svg {
    transform: rotate(180deg);
}
.object-section { margin-top: 8px; }
.object-section-header {
    display: flex;
    align-items: center;
}
.object-section-header .task-section-label {
    margin: 0;
    font-size: 21px;
}
.object-section-controls {
    display: flex;
    align-items: center;
    gap: 1px;
    margin-left: auto;
    opacity: 0;
    transition: opacity .15s ease;
}
.object-section-header:hover .object-section-controls,
.object-section-controls:focus-within {
    opacity: 1;
}
.object-section-actions {
    display: flex;
    align-items: center;
    gap: 2px;
}
.object-section-action {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-size: 15px;
}
.object-section-delete {
    font-size: 20px;
}
.object-add-item {
    width: 30px;
    height: 30px;
    margin-left: 2px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #aaaab6;
    font-size: 19px;
    cursor: pointer;
}
.object-section:nth-child(3n + 1) .task-section-label { color: var(--tappsk-blue); }
.object-section:nth-child(3n + 2) .task-section-label { color: var(--tappsk-cyan); }
.object-section:nth-child(3n) .task-section-label { color: var(--tappsk-muted); }
.object-items { min-height: 18px; margin-top: 3px; }
.object-item .task-checkbox {
    padding: 0;
    background: #fff;
}
.object-item .task-checkbox.checked { background: var(--accent); }
.object-item-completed { order: 2; }
.object-item-content { flex: 1; min-width: 0; }
.object-item-comment {
    color: #aaa;
    font-size: 12px;
    line-height: 1.45;
    margin-top: 3px;
    white-space: pre-wrap;
}
.object-completed-date {
    min-width: 70px;
    margin-left: auto;
    color: #aaa;
    font-size: 12px;
    text-align: right;
    white-space: nowrap;
}
.object-assignee-avatar {
    display: inline-flex;
    flex: 0 0 28px;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    margin-left: 8px;
    border: 0;
    border-radius: 50%;
    font-size: 10px;
    font-weight: 750;
    letter-spacing: .2px;
    cursor: pointer;
}
.object-assignee-avatar.is-empty {
    border: 1px dashed #d4d4dd;
    background: #fff;
    color: #b1b1bc;
    font-size: 16px;
    font-weight: 400;
    opacity: 0;
    transition: opacity .15s ease;
}
.object-item:hover .object-assignee-avatar.is-empty,
.object-assignee-avatar.is-empty:focus {
    opacity: 1;
}
.object-select-field {
    position: relative;
}
.object-select {
    width: 100%;
    min-height: 46px;
    padding: 0 42px 0 14px;
    appearance: none;
    -webkit-appearance: none;
    background: #fff;
    color: #30303f;
    cursor: pointer;
}
.object-select-field > svg {
    position: absolute;
    top: 50%;
    right: 14px;
    width: 17px;
    height: 17px;
    color: #9b9ba7;
    pointer-events: none;
    transform: translateY(-50%);
}
.object-item-delete {
    width: 28px;
    height: 28px;
    margin-left: 5px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #b8b8c2;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    opacity: 0;
    transition: opacity .15s ease, color .15s ease, background .15s ease;
}
.object-item:hover .object-item-delete,
.object-item-delete:focus {
    opacity: 1;
}
.object-item-delete:hover {
    color: #e05d68;
    background: #fff1f2;
}
.trash-modal {
    width: min(560px, calc(100vw - 32px));
    max-height: min(680px, calc(100vh - 40px));
    overflow-y: auto;
}
.trash-modal-list {
    margin: 2px 0 14px;
}
.trash-object-group + .trash-object-group {
    margin-top: 17px;
    padding-top: 17px;
    border-top: 1px solid #eeeeF3;
}
.trash-object-name {
    margin-bottom: 5px;
    color: #25253a;
    font-size: 14px;
    font-weight: 700;
}
.object-trash-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
}
.object-trash-item-content {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-width: 0;
}
.object-trash-item-title {
    overflow: hidden;
    color: #7f7f8c;
    font-size: 13px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.object-trash-section {
    margin-top: 2px;
    color: #b1b1ba;
    font-size: 11px;
}
.object-restore-button {
    border: 1px solid #e1e1e8;
    border-radius: 15px;
    background: #fff;
    color: var(--tappsk-blue);
    padding: 6px 10px;
    font-size: 11px;
    font-weight: 650;
    cursor: pointer;
}
.trash-empty {
    padding: 20px 0;
    color: #aaaab6;
    font-size: 13px;
    text-align: center;
}
.objects-nav {
    position: fixed;
    right: 36px;
    bottom: 28px;
    z-index: 70;
    display: flex;
    align-items: center;
    gap: 8px;
}
.objects-nav button {
    border: 0;
    background: var(--tappsk-blue);
    color: #fff;
    box-shadow: 0 4px 18px rgba(124, 111, 247, .3);
    cursor: pointer;
    font-size: 13px;
    font-weight: 650;
}
.objects-nav-previous {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}
.objects-nav-next {
    height: 40px;
    padding: 0 17px;
    border-radius: 20px;
}
.objects-nav button:hover {
    filter: brightness(.96);
}
@media (max-width: 768px) {
    .objects-page-header { align-items: flex-start; }
    .new-object-button { padding: 9px 13px; }
    .objects-page-actions { gap: 5px; }
    .trash-button { width: 36px; height: 36px; }
    .object-name { font-size: 18px; }
    .object-actions { gap: 2px; }
    .object-section-controls { opacity: 1; }
    .object-item-delete { opacity: 1; }
    .object-assignee-avatar.is-empty { opacity: 1; }
    .object-section-header .task-section-label { font-size: 14px; letter-spacing: .5px; }
    .objects-nav { right: 18px; bottom: 18px; }
}
</style>

<script>
let currentObjectId = null;
let currentSection = null;
let editingObjectId = null;
let editingSectionId = null;
let editingItemId = null;

function openObjectModal() {
    document.getElementById('object-modal').style.display = 'flex';
    document.getElementById('object-name').focus();
}

function closeObjectModal(event) {
    if (!event || event.target === document.getElementById('object-modal')) {
        document.getElementById('object-modal').style.display = 'none';
    }
}

function closeObjectMenus(exceptId = null) {
    document.querySelectorAll('.object-menu-dropdown').forEach(menu => {
        if (menu.id === `object-menu-${exceptId}`) return;
        menu.hidden = true;
        menu.previousElementSibling?.setAttribute('aria-expanded', 'false');
    });
}

function toggleObjectMenu(event, objectId) {
    event.stopPropagation();
    const menu = document.getElementById(`object-menu-${objectId}`);
    const willOpen = menu.hidden;
    closeObjectMenus(objectId);
    menu.hidden = !willOpen;
    event.currentTarget.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
}

function openTrashModal() {
    document.getElementById('trash-modal').style.display = 'flex';
}

function closeTrashModal(event) {
    if (!event || event.target === document.getElementById('trash-modal')) {
        document.getElementById('trash-modal').style.display = 'none';
    }
}

function openRenameObjectModal(objectId, objectName) {
    editingObjectId = objectId;
    const input = document.getElementById('rename-object-name');
    input.value = objectName;
    document.getElementById('rename-object-modal').style.display = 'flex';
    input.focus();
    input.select();
}

function closeRenameObjectModal(event) {
    if (!event || event.target === document.getElementById('rename-object-modal')) {
        document.getElementById('rename-object-modal').style.display = 'none';
        editingObjectId = null;
    }
}

function openSectionModal(objectId, sectionId = null, sectionName = '') {
    currentObjectId = objectId;
    editingSectionId = sectionId;
    const isEditing = Boolean(sectionId);
    const input = document.getElementById('section-name');
    input.value = sectionName;
    document.getElementById('section-modal-title').textContent = isEditing ? 'Переименовать раздел' : 'Новый раздел';
    document.getElementById('section-submit').textContent = isEditing ? 'Сохранить' : 'Добавить';
    document.getElementById('section-modal').style.display = 'flex';
    input.focus();
    if (isEditing) input.select();
}

function closeSectionModal(event) {
    if (!event || event.target === document.getElementById('section-modal')) {
        document.getElementById('section-modal').style.display = 'none';
        currentObjectId = null;
        editingSectionId = null;
        document.getElementById('section-name').value = '';
    }
}

function openItemModal(objectId, section, sectionTitle) {
    currentObjectId = objectId;
    currentSection = section;
    document.getElementById('item-modal-title').textContent = sectionTitle;
    document.getElementById('item-modal').style.display = 'flex';
    document.getElementById('item-title').focus();
}

function closeItemModal(event) {
    if (!event || event.target === document.getElementById('item-modal')) {
        document.getElementById('item-modal').style.display = 'none';
        currentObjectId = null;
        currentSection = null;
        document.getElementById('item-assignee').value = '';
    }
}

function openAssigneeModal(itemId, assigneeId) {
    editingItemId = itemId;
    document.getElementById('assignee-select').value = assigneeId ?? '';
    document.getElementById('assignee-modal').style.display = 'flex';
}

function closeAssigneeModal(event) {
    if (!event || event.target === document.getElementById('assignee-modal')) {
        document.getElementById('assignee-modal').style.display = 'none';
        editingItemId = null;
    }
}

async function createObject() {
    const name = document.getElementById('object-name').value.trim();
    if (!name) return document.getElementById('object-name').focus();

    const response = await fetch('/objects', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ name }),
    });

    if (response.ok) window.location.reload();
    else alert('Не удалось создать объект. Попробуйте ещё раз.');
}

async function renameObject() {
    const name = document.getElementById('rename-object-name').value.trim();
    if (!name || !editingObjectId) return;

    const response = await fetch(`/objects/${editingObjectId}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ name }),
    });

    if (response.ok) window.location.reload();
    else alert('Не удалось переименовать объект. Попробуйте ещё раз.');
}

async function deleteObject(objectId, objectName) {
    const message = `Удалить объект «${objectName}»? Все его разделы и пункты будут удалены окончательно.`;
    if (!confirm(message)) return;

    const response = await fetch(`/objects/${objectId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    });

    if (response.ok) window.location.reload();
    else alert('Не удалось удалить объект. Попробуйте ещё раз.');
}

async function saveSection() {
    const name = document.getElementById('section-name').value.trim();
    if (!name || (!currentObjectId && !editingSectionId)) return;

    const response = await fetch(
        editingSectionId ? `/object-sections/${editingSectionId}` : `/objects/${currentObjectId}/sections`,
        {
            method: editingSectionId ? 'PATCH' : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ name }),
        }
    );

    if (response.ok) window.location.reload();
    else alert('Не удалось сохранить раздел. Попробуйте ещё раз.');
}

async function deleteSection(sectionId, sectionName, itemsCount) {
    const details = itemsCount
        ? ` В нём ${itemsCount} пункт(а/ов), они тоже будут удалены.`
        : '';

    if (!confirm(`Удалить раздел «${sectionName}»?${details}`)) return;

    const response = await fetch(`/object-sections/${sectionId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    });

    if (response.ok) window.location.reload();
    else alert('Не удалось удалить раздел. Попробуйте ещё раз.');
}

async function createObjectItem() {
    const title = document.getElementById('item-title').value.trim();
    const comment = document.getElementById('item-comment').value.trim();
    const assignedTo = document.getElementById('item-assignee').value;
    if (!title || !currentObjectId || !currentSection) return;

    const response = await fetch(`/objects/${currentObjectId}/items`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            title,
            comment,
            section: currentSection,
            assigned_to: assignedTo ? Number(assignedTo) : null,
        }),
    });

    if (response.ok) window.location.reload();
    else alert('Не удалось добавить пункт. Попробуйте ещё раз.');
}

async function saveAssignee() {
    if (!editingItemId) return;

    const assigneeId = document.getElementById('assignee-select').value;
    const response = await fetch(`/object-items/${editingItemId}/assignee`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ assigned_to: assigneeId ? Number(assigneeId) : null }),
    });

    if (response.ok) window.location.reload();
    else alert('Не удалось назначить ответственного. Попробуйте ещё раз.');
}

async function toggleObjectItem(itemId, completed) {
    const response = await fetch(`/object-items/${itemId}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ is_completed: completed }),
    });

    if (response.ok) window.location.reload();
    else alert('Не удалось изменить статус. Попробуйте ещё раз.');
}

async function deleteObjectItem(itemId) {
    if (!confirm('Удалить этот пункт? Его можно будет восстановить.')) return;

    const response = await fetch(`/object-items/${itemId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    });

    if (response.ok) window.location.reload();
    else alert('Не удалось удалить пункт. Попробуйте ещё раз.');
}

async function restoreObjectItem(itemId) {
    const response = await fetch(`/object-items/${itemId}/restore`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    });

    if (response.ok) window.location.reload();
    else alert('Не удалось восстановить пункт. Возможно, его раздел уже удалён.');
}

function toggleObject(objectId) {
    const sections = document.getElementById(`object-sections-${objectId}`);
    const button = document.querySelector(`[data-object-id="${objectId}"] .object-collapse`);
    const collapsed = !sections.hidden;
    sections.hidden = collapsed;
    button.classList.toggle('is-collapsed', collapsed);
    button.title = collapsed ? 'Развернуть объект' : 'Свернуть объект';
    button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

    const stored = new Set(JSON.parse(localStorage.getItem('collapsed-objects') || '[]'));
    if (collapsed) stored.add(objectId);
    else stored.delete(objectId);
    localStorage.setItem('collapsed-objects', JSON.stringify([...stored]));
}

function scrollToObject(direction) {
    const boards = [...document.querySelectorAll('.object-board')];
    if (!boards.length) return;

    let currentIndex = boards.findIndex(board => board.getBoundingClientRect().bottom > 120);
    if (currentIndex < 0) currentIndex = boards.length - 1;

    const targetIndex = (currentIndex + direction + boards.length) % boards.length;
    boards[targetIndex].scrollIntoView({ behavior: 'smooth', block: 'start' });
}

document.addEventListener('DOMContentLoaded', () => {
    const collapsed = new Set(JSON.parse(localStorage.getItem('collapsed-objects') || '[]'));
    collapsed.forEach(objectId => {
        const sections = document.getElementById(`object-sections-${objectId}`);
        const button = document.querySelector(`[data-object-id="${objectId}"] .object-collapse`);
        if (!sections || !button) return;
        sections.hidden = true;
        button.classList.add('is-collapsed');
        button.title = 'Развернуть объект';
        button.setAttribute('aria-expanded', 'false');
    });

    const target = window.location.hash ? document.querySelector(window.location.hash) : null;
    if (target?.classList.contains('object-board')) {
        const objectId = Number(target.dataset.objectId);
        const sections = document.getElementById(`object-sections-${objectId}`);
        const button = target.querySelector('.object-collapse');

        if (sections && button) {
            sections.hidden = false;
            button.classList.remove('is-collapsed');
            button.title = 'Свернуть объект';
            button.setAttribute('aria-expanded', 'true');
        }

        requestAnimationFrame(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }));
    }
});

document.addEventListener('click', () => closeObjectMenus());

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        closeObjectMenus();
        closeTrashModal();
        closeObjectModal();
        closeRenameObjectModal();
        closeSectionModal();
        closeItemModal();
        closeAssigneeModal();
    }
});
</script>

@endsection
