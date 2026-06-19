<nav class="sidebar-nav">
    <a href="{{ route('admin.dashboard') }}" class="{{ Route::currentRouteName() === 'admin.dashboard' ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>

    <a href="{{ route('admin.books.index') }}" class="{{ str_starts_with(Route::currentRouteName(), 'admin.books') ? 'active' : '' }}">
        <i class="bi bi-book"></i>
        <span>Kelola Buku</span>
    </a>

    <a href="{{ route('admin.categories.index') }}" class="{{ str_starts_with(Route::currentRouteName(), 'admin.categories') ? 'active' : '' }}">
        <i class="bi bi-tags"></i>
        <span>Kelola Kategori</span>
    </a>

    <a href="{{ route('admin.members.index') }}" class="{{ str_starts_with(Route::currentRouteName(), 'admin.members') ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span>Kelola Anggota</span>
    </a>

    <a href="{{ route('admin.borrowings.index') }}" class="{{ str_starts_with(Route::currentRouteName(), 'admin.borrowings') ? 'active' : '' }}">
        <i class="bi bi-journal-check"></i>
        <span>Kelola Peminjaman</span>
    </a>

    <a href="{{ route('admin.reports') }}" class="{{ Route::currentRouteName() === 'admin.reports' ? 'active' : '' }}">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span>Laporan</span>
    </a>
</nav>
