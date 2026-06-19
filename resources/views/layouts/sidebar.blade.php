<div class="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-book"></i>
        <span>Perpustakaan UB</span>
    </div>

    @if(Auth::check() && Auth::user()->role === 'admin')
        @include('partials.sidebar.admin')
    @elseif(session()->has('member_id'))
        @include('partials.sidebar.member')
    @endif
</div>