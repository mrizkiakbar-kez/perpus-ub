<nav class="sidebar-nav">
    <a href="{{ route('member.dashboard') }}" class="{{ Route::currentRouteName() === 'member.dashboard' ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('books.index') }}" class="{{ Route::currentRouteName() === 'books.index' || Route::currentRouteName() === 'books.show' ? 'active' : '' }}">
        <i class="bi bi-book"></i>
        <span>Books</span>
    </a>

    <a href="{{ route('borrowings.index') }}" class="{{ str_starts_with(Route::currentRouteName(), 'borrowings') ? 'active' : '' }}">
        <i class="bi bi-journal-check"></i>
        <span>My Borrowings</span>
    </a>

    <a href="{{ route('member.profile') }}" class="{{ Route::currentRouteName() === 'member.profile' ? 'active' : '' }}">
        <i class="bi bi-person-gear"></i>
        <span>Profile</span>
    </a>
</nav>
