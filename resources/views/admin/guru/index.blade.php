@extends('layouts.app')

@section('content')
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-primary">Daftar Guru</h1>
        <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addGuruModal">
            <i class="bi bi-plus-lg me-2"></i> Tambah Guru
        </button>
    </div>
    
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.guru.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari guru berdasarkan NIP atau nama..." value="{{ request('search') }}">
                
                <input type="hidden" name="per_page" value="{{ request('per_page', 5) }}">
                
                <button type="submit" class="btn btn-outline-secondary">Cari</button>
            </form>
        </div>
    </div>
    
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col" class="text-center" style="width: 5%">No</th>
                            <th scope="col">NIP</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Email</th>
                            <th scope="col" class="text-center" style="width: 20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guru as $g)
                            <tr>
                                <td class="text-center">{{ ($guru->currentPage() - 1) * $guru->perPage() + $loop->iteration }}</td>
                                <td>{{ $g->nip }}</td>
                                <td>{{ $g->nama }}</td>
                                <td>{{ $g->email }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#detailGuruModal"
                                                data-nama="{{ $g->nama }}" data-email="{{ $g->email }}" data-nip="{{ $g->nip }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm text-white" data-bs-toggle="modal" data-bs-target="#editGuruModal"
                                                data-id="{{ $g->id }}" data-nama="{{ $g->nama }}" data-email="{{ $g->email }}" data-nip="{{ $g->nip }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="{{ route('admin.guru.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-exclamation-circle me-2"></i> Tidak ada data guru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- PERBAIKAN UTAMA: Selalu Tampilkan Pagination Footer --}}
            <div class="card-footer bg-white border-0">
                <div class="d-flex justify-content-center">
                    
                    {{-- Kita tidak bisa menggunakan $guru->links() karena dia hanya muncul jika hasPages() true. --}}
                    {{-- Solusi: Kita buat manual tombol Prev dan Next berdasarkan properti paginator. --}}
                    
                    <nav class="d-flex" role="navigation" aria-label="Pagination">
                        {{-- Tombol Prev --}}
                        <a 
                            @if ($guru->onFirstPage())
                                class="btn btn-outline-secondary me-2 disabled" aria-disabled="true"
                            @else
                                class="btn btn-outline-secondary me-2" href="{{ $guru->previousPageUrl() }}"
                            @endif
                        >
                            Prev
                        </a>
                        
                        {{-- Tombol Next --}}
                        <a 
                            @if ($guru->hasMorePages())
                                class="btn btn-outline-secondary" href="{{ $guru->nextPageUrl() }}"
                            @else
                                class="btn btn-outline-secondary disabled" aria-disabled="true"
                            @endif
                        >
                            Next
                        </a>
                    </nav>

                </div>
            </div>
            
        </div>
    </div>

</div>

{{-- SISA KODE MODAL DAN SCRIPT JAVASCRIPT ANDA DI SINI (TIDAK BERUBAH) --}}
<div class="modal fade" id="addGuruModal" tabindex="-1" aria-labelledby="addGuruModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addGuruModalLabel">Tambah Guru Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.guru.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nip" class="form-label">NIP</label>
                        <input type="text" class="form-control" id="nip" name="nip" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editGuruModal" tabindex="-1" aria-labelledby="editGuruModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editGuruModalLabel">Edit Data Guru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGuruForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editNip" class="form-label">NIP</label>
                        <input type="text" class="form-control" id="editNip" name="nip" required>
                    </div>
                    <div class="mb-3">
                        <label for="editNama" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="editNama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPassword" class="form-label">Password (Opsional)</label>
                        <input type="password" class="form-control" id="editPassword" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="detailGuruModal" tabindex="-1" aria-labelledby="detailGuruModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="detailGuruModalLabel">Detail Guru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>NIP:</strong> <span id="detailNip"></span>
                </div>
                <div class="mb-3">
                    <strong>Nama:</strong> <span id="detailNama"></span>
                </div>
                <div class="mb-3">
                    <strong>Email:</strong> <span id="detailEmail"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Logika untuk mengisi data pada Modal Edit Guru
        const editGuruModal = document.getElementById('editGuruModal');
        editGuruModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const guruId = button.getAttribute('data-id');
            const guruNip = button.getAttribute('data-nip');
            const guruNama = button.getAttribute('data-nama');
            const guruEmail = button.getAttribute('data-email');
            
            const form = document.getElementById('editGuruForm');
            form.action = `{{ url('admin/guru') }}/${guruId}`; 
            
            const modalTitle = editGuruModal.querySelector('.modal-title');
            const inputNip = editGuruModal.querySelector('#editNip');
            const inputNama = editGuruModal.querySelector('#editNama');
            const inputEmail = editGuruModal.querySelector('#editEmail');
            
            modalTitle.textContent = `Edit Data Guru: ${guruNama}`;
            inputNip.value = guruNip;
            inputNama.value = guruNama;
            inputEmail.value = guruEmail;
            editGuruModal.querySelector('#editPassword').value = ''; 
        });

        // Logika untuk mengisi data pada Modal Detail Guru
        const detailGuruModal = document.getElementById('detailGuruModal');
        detailGuruModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const guruNip = button.getAttribute('data-nip');
            const guruNama = button.getAttribute('data-nama');
            const guruEmail = button.getAttribute('data-email');
            
            const modalTitle = detailGuruModal.querySelector('.modal-title');
            const detailNip = detailGuruModal.querySelector('#detailNip');
            const detailNama = detailGuruModal.querySelector('#detailNama');
            const detailEmail = detailGuruModal.querySelector('#detailEmail');
            
            modalTitle.textContent = `Detail Guru: ${guruNama}`;
            detailNip.textContent = guruNip;
            detailNama.textContent = guruNama;
            detailEmail.textContent = guruEmail;
        });
    });
</script>

<style>
    .btn-outline-secondary:hover:not(.disabled) {
        background-color: #0d6efd;   /* biru bootstrap */
        color: white;
        border-color: #0d6efd;
    }
</style>

@endsection