@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Anggota (Daftar Pengguna)</h2>
</div>

@if($members->count())
    <div class="card border-0 rounded-3 shadow" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="py-3 px-4" style="color: var(--text-muted);">Nama Lengkap</th>
                            <th class="py-3" style="color: var(--text-muted);">Username</th>
                            <th class="py-3" style="color: var(--text-muted);">Email</th>
                            <th class="py-3" style="color: var(--text-muted);">Role</th>
                            <th class="py-3" style="color: var(--text-muted);">Tanggal Daftar</th>
                            <th class="py-3" style="color: var(--text-muted);">Status</th>
                            <th class="py-3 px-4 text-end" style="color: var(--text-muted);">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $m)
                            @php
                                $emailParts = explode('@', $m->email);
                                $namePart = $emailParts[0];
                                $domainPart = $emailParts[1] ?? '';
                                $maskedName = strlen($namePart) <= 2 ? $namePart . '***' : substr($namePart, 0, 2) . '***' . substr($namePart, -1);
                                $maskedEmail = $maskedName . '@' . $domainPart;
                            @endphp
                            <tr>
                                <td class="py-3 px-4 fw-bold text-white">{{ $m->nama }}</td>
                                <td class="py-3 text-secondary">{{ $m->kode_anggota }}</td>
                                <td class="py-3 text-muted" title="Disamarkan untuk privasi">{{ $maskedEmail }}</td>
                                <td class="py-3 text-secondary"><span class="badge bg-secondary">{{ ucfirst($m->user_role) }}</span></td>
                                <td class="py-3 text-muted">{{ $m->created_at->format('d M Y') }}</td>
                                <td class="py-3">
                                    <span class="badge bg-success">Active</span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('admin.members.show', $m->id) }}" class="btn btn-sm btn-primary px-3">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                        @if(Auth::check() && Auth::user()->role === 'admin')
                                            <button type="button" 
                                                class="btn btn-sm btn-danger px-3 btn-remove-member"
                                                data-id="{{ $m->id }}"
                                                data-name="{{ $m->nama }}"
                                                data-reg-date="{{ $m->created_at->format('d M Y') }}"
                                                data-active-borrowings="{{ $m->active_borrowings_count }}"
                                                data-overdue-borrowings="{{ $m->overdue_borrowings_count }}"
                                                data-role="{{ $m->user_role }}"
                                                data-is-self="{{ $m->user_id === Auth::id() ? 'true' : 'false' }}">
                                                <i class="bi bi-trash me-1"></i>Remove
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="card" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
        <div class="empty-state py-5 text-center text-muted">
            <i class="bi bi-people" style="font-size: 3rem;"></i>
            <h5 class="mt-3">Belum ada anggota</h5>
            <p class="mb-0">Tidak ditemukan data anggota.</p>
        </div>
    </div>
@endif

<!-- Remove Member Modal -->
<div class="modal fade" id="removeMemberModal" tabindex="-1" aria-labelledby="removeMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white border-0" style="background-color: var(--bg-secondary) !important; border: 1px solid var(--border-color) !important;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="removeMemberModalLabel"><i class="bi bi-person-x me-2"></i>Remove Member</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-3 text-muted">Please review the member's details below before removal.</p>

                <div class="bg-dark p-3 rounded mb-3" style="border: 1px solid var(--border-color); background-color: var(--bg-dark) !important;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Member Name:</span>
                        <span class="text-white fw-bold" id="modal-member-name">-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Registration Date:</span>
                        <span class="text-white fw-medium" id="modal-reg-date">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Current Borrowing Status:</span>
                        <span id="modal-borrowing-status">-</span>
                    </div>
                </div>

                <div class="alert alert-danger" id="modal-validation-error" role="alert" style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid var(--accent-danger); color: var(--accent-danger); display: none;">
                </div>

                <p class="text-warning small mb-0 mt-3">
                    <i class="bi bi-info-circle me-1"></i> Are you sure you want to remove this member? This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex gap-2 justify-content-end">
                <form id="form-remove-member" action="" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4" id="btn-modal-remove-confirm">Remove Member</button>
                </form>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.btn-remove-member').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var regDate = $(this).data('reg-date');
        var activeBorrowings = parseInt($(this).data('active-borrowings')) || 0;
        var overdueBorrowings = parseInt($(this).data('overdue-borrowings')) || 0;
        var role = $(this).data('role');
        var isSelf = $(this).data('is-self') === true || $(this).data('is-self') === 'true';

        // Populate modal fields
        $('#modal-member-name').text(name);
        $('#modal-reg-date').text(regDate);

        // Calculate and set borrowing status text
        if (activeBorrowings === 0) {
            $('#modal-borrowing-status').html('<span class="text-success fw-medium"><i class="bi bi-check-circle-fill me-1"></i>No active borrowings</span>');
        } else {
            var overdueText = overdueBorrowings > 0 ? ' (' + overdueBorrowings + ' overdue)' : '';
            $('#modal-borrowing-status').html('<span class="text-warning fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>' + activeBorrowings + ' active borrowing(s)' + overdueText + '</span>');
        }

        // Set form action
        var actionUrl = "{{ route('admin.members.destroy', ':id') }}".replace(':id', id);
        $('#form-remove-member').attr('action', actionUrl);

        // Validation message and button states
        var errorMsgElement = $('#modal-validation-error');
        var removeBtn = $('#btn-modal-remove-confirm');

        errorMsgElement.hide().text('');
        removeBtn.prop('disabled', false);

        if (isSelf) {
            errorMsgElement.text('Tidak dapat menghapus Admin yang sedang login.').show();
            removeBtn.prop('disabled', true);
        } else if (role === 'admin') {
            errorMsgElement.text('Tidak dapat menghapus akun Admin.').show();
            removeBtn.prop('disabled', true);
        } else if (activeBorrowings > 0) {
            errorMsgElement.text('This member cannot be removed because they still have active borrowing records.').show();
            removeBtn.prop('disabled', true);
        }

        // Show modal
        $('#removeMemberModal').modal('show');
    });
});
</script>

@endsection
