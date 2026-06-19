<div class="empty-state">
    <i class="bi {{ $icon ?? 'bi-inbox' }}"></i>
    <h5>{{ $title ?? 'No Data' }}</h5>
    <p class="text-muted mb-3">{{ $description ?? 'No items to display.' }}</p>
    @if($action ?? false)
        <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm">{{ $actionText ?? 'Add Item' }}</a>
    @endif
</div>
