@extends('layouts.app')

@section('content')
<div class="container my-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-primary">🏫 Daftar Kelas</h1>
        <button type="button" class="btn btn-primary d-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#addKelasModal">
            <i class="bi bi-plus-lg me-2"></i> Tambah Kelas
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4 mb-5">
    @forelse ($kelas as $k)
        <div class="col">
            <div class="card h-100 border-0 shadow-sm overflow-hidden class-card">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px;">
                            <i class="bi bi-house-door-fill fs-4"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">{{ $k->nama_kelas }}</h5>
                            <small class="text-muted">Wali Kelas: {{ $k->waliKelas->nama }}</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
                        <div class="text-center w-50">
                            <h6 class="fw-bold mb-0">{{ $k->siswa->count() }}</h6>
                            <small class="text-muted">Total Siswa</small>
                        </div>
                        <div class="text-center w-50">
                            <h6 class="fw-bold mb-0">{{ $k->jadwalMapel->count() }}</h6>
                            <small class="text-muted">Total Jadwal</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end mt-auto pt-3 border-top">
                        <a href="{{ route('admin.siswa.index', ['kelas_id' => $k->id]) }}" class="btn btn-sm btn-info text-white shadow-sm">
                            <i class="bi bi-person-fill"></i> Siswa
                        </a>
                        <button type="button" class="btn btn-sm btn-warning text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#editKelasModal"
                                data-id="{{ $k->id }}" data-nama_kelas="{{ $k->nama_kelas }}" data-wali_kelas_id="{{ $k->waliKelas->id }}">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <form action="{{ route('admin.kelas.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Yakin hapus kelas ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm shadow-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-secondary text-center shadow-sm">
                <i class="bi bi-exclamation-circle me-2"></i> Tidak ada data kelas.
            </div>
        </div>
    @endforelse
    </div>
</div>

<!-- Modal Tambah Kelas -->
<div class="modal fade" id="addKelasModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Kelas Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.kelas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_kelas" class="form-label">Nama Kelas</label>
                        <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" required>
                    </div>
                    <div class="mb-3">
                        <label for="wali_kelas_id" class="form-label">Wali Kelas</label>
                        <select class="form-select" id="wali_kelas_id" name="wali_kelas_id" required>
                            <option value="" disabled selected>Pilih Wali Kelas</option>
                            @foreach ($waliKelasOptions as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kelas -->
<div class="modal fade" id="editKelasModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg rounded-3">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Data Kelas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editKelasForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editNamaKelas" class="form-label">Nama Kelas</label>
                        <input type="text" class="form-control" id="editNamaKelas" name="nama_kelas" required>
                    </div>
                    <div class="mb-3">
                        <label for="editWaliKelasId" class="form-label">Wali Kelas</label>
                        <select class="form-select" id="editWaliKelasId" name="wali_kelas_id" required>
                            @foreach ($waliKelasOptions as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary shadow-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .class-card {
        transition: transform .2s ease, box-shadow .2s ease;
        border-radius: 12px;
    }
    .class-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.75rem 1.5rem rgba(0,0,0,0.1);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editKelasModal = document.getElementById('editKelasModal');
        editKelasModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const kelasId = button.getAttribute('data-id');
            const kelasNama = button.getAttribute('data-nama_kelas');
            const kelasWaliId = button.getAttribute('data-wali_kelas_id');
            
            const form = document.getElementById('editKelasForm');
            form.action = `{{ route('admin.kelas.update', ['id' => '__id__']) }}`.replace('__id__', kelasId);
            
            const inputNamaKelas = editKelasModal.querySelector('#editNamaKelas');
            const selectWaliKelasId = editKelasModal.querySelector('#editWaliKelasId');
            
            inputNamaKelas.value = kelasNama;
            selectWaliKelasId.value = kelasWaliId;
        });
    });
</script>
@endpush
@endsection
