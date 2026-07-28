@extends('layouts.app')
@section('content')

<div class="objects-page-header">
    <div class="page-title">Объекты</div>
    <button class="new-object-button" type="button" onclick="openObjectModal()">+ Новый объект</button>
</div>

<div class="objects-list">
    @forelse($objects as $object)
        <section class="object-board" data-object-id="{{ $object->id }}">
            <div class="object-board-header">
                <div class="object-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-2M9 9v.01M9 13v.01M9 17v.01M16 13v.01M16 17v.01"/>
                    </svg>
                </div>
                <div class="object-heading">
                    <div class="object-name">{{ $object->name }}</div>
                    <div class="object-count">{{ $object->items->where('is_completed', false)->count() }} активных пунктов</div>
                </div>
                <div class="object-actions">
                    <button class="object-action-button" type="button"
                        onclick='openRenameObjectModal({{ $object->id }}, @js($object->name))'
                        title="Переименовать объект">✎</button>
                    <button class="object-new-section" type="button"
                        onclick="openSectionModal({{ $object->id }})">+ Раздел</button>
                </div>
                <button class="object-collapse" type="button" onclick="toggleObject({{ $object->id }})"
                    title="Свернуть объект" aria-label="Свернуть объект" aria-expanded="true">⌃</button>
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
                            <div class="object-section-actions">
                                <button type="button" class="object-section-action"
                                    onclick='openSectionModal({{ $object->id }}, {{ $section->id }}, @js($section->name))'
                                    title="Переименовать раздел">✎</button>
                                <button type="button" class="object-section-action object-section-delete"
                                    onclick='deleteSection({{ $section->id }}, @js($section->name), {{ $items->count() }})'
                                    title="Удалить раздел">×</button>
                            </div>
                            <button type="button" class="object-add-item"
                                onclick='openItemModal({{ $object->id }}, @js($section->key), @js($section->name))'
                                title="Добавить пункт">+</button>
                        </div>

                        <div class="object-items {{ $items->isEmpty() ? 'is-empty' : '' }}">
                            @foreach($items as $item)
                                <div class="task-card object-item {{ $item->is_completed ? 'object-item-completed' : '' }}">
                                    <button type="button" class="task-checkbox {{ $item->is_completed ? 'checked' : '' }}"
                                        onclick="toggleObjectItem({{ $item->id }}, {{ $item->is_completed ? 'false' : 'true' }})"
                                        aria-label="{{ $item->is_completed ? 'Вернуть пункт' : 'Отметить выполненным' }}">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
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
            <input type="text" id="section-name" class="form-input" placeholder="Например, Сметы">
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
        <button type="button" class="btn-submit" onclick="createObjectItem()">Добавить</button>
        <button type="button" class="btn-cancel" onclick="closeItemModal()">Отмена</button>
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
.object-board {
    margin-bottom: 30px;
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
    align-items: center;
    gap: 7px;
    margin-left: auto;
}
.object-action-button,
.object-section-action {
    border: 0;
    background: transparent;
    color: #aaaab6;
    cursor: pointer;
}
.object-action-button {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    font-size: 17px;
}
.object-new-section {
    border: 1px solid #e6e6ec;
    border-radius: 16px;
    background: #fff;
    color: #777784;
    padding: 6px 11px;
    font-size: 12px;
    font-weight: 650;
    cursor: pointer;
}
.object-collapse {
    width: 34px;
    height: 34px;
    margin-left: 2px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #aaaab6;
    font-size: 17px;
    cursor: pointer;
}
.object-section { margin-top: 11px; }
.object-section-header {
    display: flex;
    align-items: center;
}
.object-section-header .task-section-label {
    margin: 0;
    font-size: 25px;
}
.object-section-actions {
    display: flex;
    align-items: center;
    gap: 2px;
    margin-left: auto;
    opacity: 0;
    transition: opacity .15s ease;
}
.object-section-header:hover .object-section-actions,
.object-section-actions:focus-within {
    opacity: 1;
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
    margin-left: auto;
    color: #aaa;
    font-size: 12px;
    white-space: nowrap;
}
@media (max-width: 768px) {
    .objects-page-header { align-items: flex-start; }
    .new-object-button { padding: 9px 13px; }
    .object-name { font-size: 18px; }
    .object-actions { gap: 2px; }
    .object-new-section { padding: 6px 8px; }
    .object-section-actions { opacity: 1; }
    .object-section-header .task-section-label { font-size: 14px; letter-spacing: .5px; }
}
</style>

<script>
let currentObjectId = null;
let currentSection = null;
let editingObjectId = null;
let editingSectionId = null;

function openObjectModal() {
    document.getElementById('object-modal').style.display = 'flex';
    document.getElementById('object-name').focus();
}

function closeObjectModal(event) {
    if (!event || event.target === document.getElementById('object-modal')) {
        document.getElementById('object-modal').style.display = 'none';
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
    if (!title || !currentObjectId || !currentSection) return;

    const response = await fetch(`/objects/${currentObjectId}/items`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ title, comment, section: currentSection }),
    });

    if (response.ok) window.location.reload();
    else alert('Не удалось добавить пункт. Попробуйте ещё раз.');
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

function toggleObject(objectId) {
    const sections = document.getElementById(`object-sections-${objectId}`);
    const button = document.querySelector(`[data-object-id="${objectId}"] .object-collapse`);
    const collapsed = !sections.hidden;
    sections.hidden = collapsed;
    button.textContent = collapsed ? '⌄' : '⌃';
    button.title = collapsed ? 'Развернуть объект' : 'Свернуть объект';
    button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

    const stored = new Set(JSON.parse(localStorage.getItem('collapsed-objects') || '[]'));
    if (collapsed) stored.add(objectId);
    else stored.delete(objectId);
    localStorage.setItem('collapsed-objects', JSON.stringify([...stored]));
}

document.addEventListener('DOMContentLoaded', () => {
    const collapsed = new Set(JSON.parse(localStorage.getItem('collapsed-objects') || '[]'));
    collapsed.forEach(objectId => {
        const sections = document.getElementById(`object-sections-${objectId}`);
        const button = document.querySelector(`[data-object-id="${objectId}"] .object-collapse`);
        if (!sections || !button) return;
        sections.hidden = true;
        button.textContent = '⌄';
        button.title = 'Развернуть объект';
        button.setAttribute('aria-expanded', 'false');
    });
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        closeObjectModal();
        closeRenameObjectModal();
        closeSectionModal();
        closeItemModal();
    }
});
</script>

@endsection
