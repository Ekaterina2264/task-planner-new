@extends('layouts.app')
@section('content')

<div class="history-header">
    <div class="page-title">История изменений</div>
</div>

<div class="history-list">
    @forelse($activities->groupBy(fn ($activity) => $activity->created_at->format('Y-m-d')) as $date => $dayActivities)
        <section class="history-day">
            <div class="history-date">
                @if($date === now()->format('Y-m-d'))
                    Сегодня
                @elseif($date === now()->subDay()->format('Y-m-d'))
                    Вчера
                @else
                    {{ $dayActivities->first()->created_at->format('d.m.Y') }}
                @endif
            </div>

            @foreach($dayActivities as $activity)
                <div class="history-entry">
                    <div class="history-avatar">
                        {{ $activity->user?->initials() ?? '—' }}
                    </div>
                    <div class="history-entry-content">
                        <div class="history-entry-top">
                            <span class="history-user">{{ $activity->user?->name ?? 'Удалённый пользователь' }}</span>
                            <span class="history-time">{{ $activity->created_at->format('H:i') }}</span>
                        </div>
                        <div class="history-description">{{ $activity->description }}</div>
                        @if($activity->context)
                            <div class="history-context">{{ $activity->context }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>
    @empty
        <div class="empty-state">История пока пуста</div>
    @endforelse
</div>

<style>
.history-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 24px;
}
.history-list {
    width: 100%;
}
.history-day + .history-day {
    margin-top: 26px;
}
.history-date {
    margin-bottom: 8px;
    color: #aaaab6;
    font-size: 12px;
    font-weight: 650;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.history-entry {
    display: flex;
    gap: 12px;
    padding: 10px 0;
}
.history-avatar {
    display: flex;
    flex: 0 0 34px;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #f1ecff;
    color: #7650db;
    font-size: 11px;
    font-weight: 700;
}
.history-entry-content {
    flex: 1;
    min-width: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f4;
}
.history-entry-top {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
}
.history-user {
    color: #252537;
    font-size: 13px;
    font-weight: 650;
}
.history-time {
    color: #b1b1bb;
    font-size: 11px;
}
.history-description {
    margin-top: 3px;
    color: #555563;
    font-size: 13px;
    line-height: 1.45;
}
.history-context {
    margin-top: 3px;
    color: #9999a5;
    font-size: 11px;
}
</style>

@endsection
