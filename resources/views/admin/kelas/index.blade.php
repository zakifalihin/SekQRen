@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="bg-white rounded-4 shadow-sm p-4 mb-5 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-building me-2 text-primary"></i> Daftar Kelas
            </h2>
            <small class="text-muted">Kelola data kelas, wali kelas, siswa, dan jadwal</small>
        </div>
        <button type="button" class="btn btn-primary d-flex align-items-center px-4 py-2 rounded-3 shadow-sm"
                data-bs-toggle="modal" data-bs-target="#addKelasModal">
            <i class="bi bi-plus-lg me-2"></i> Tambah Kelas
        </button>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Cards --}}
    <div class="row g-4">
        @forelse ($kelas as $k)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm class-card rounded-4">
                    <div class="card-body p-4 d-flex flex-column">

                        {{-- Header Card --}}
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle bg-gradient-primary text-white me-3">
                                <i class="bi bi-house-door-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">{{ $k->nama_kelas }}</h5>
                                <small class="text-muted">Wali: {{ $k->waliKelas->nama }}</small>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="d-flex justify-content-between mt-2 mb-4">
                            <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill">
                                <i class="bi bi-people-fill me-1"></i> {{ $k->siswa->count() }} Siswa
                            </span>
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                <i class="bi bi-calendar-event me-1"></i> {{ $k->jadwalMapel->count() }} Jadwal
                            </span>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-2 justify-content-end mt-auto pt-3 border-top">
                            <a href="{{ route('admin.siswa.index', ['kelas_id' => $k->id]) }}" 
                               class="btn btn-sm btn-light text-info shadow-sm" data-bs-toggle="tooltip" title="Lihat Siswa">
                                <i class="bi bi-person-fill"></i>
                            </a>
                            <a href="{{ route('admin.jadwal.index', ['kelas_id' => $k->id]) }}" 
                               class="btn btn-sm btn-light text-success shadow-sm" data-bs-toggle="tooltip" title="Lihat Jadwal">
                                <i class="bi bi-calendar-event"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-light text-warning shadow-sm"
                                    data-bs-toggle="modal" data-bs-target="#editKelasModal"
                                    data-id="{{ $k->id }}" data-nama_kelas="{{ $k->nama_kelas }}"
                                    data-wali_kelas_id="{{ $k->waliKelas->id }}" data-bs-toggle="tooltip" title="Edit Kelas">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form action="{{ route('admin.kelas.destroy', $k->id) }}" method="POST" 
                                  onsubmit="return confirm('Yakin hapus kelas ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger shadow-sm" data-bs-toggle="tooltip" title="Hapus Kelas">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border text-center shadow-sm rounded-3">
                    <i class="bi bi-exclamation-circle me-2"></i> Belum ada data kelas.
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal Tambah Kelas --}}
<div class="modal fade" id="addKelasModal" tabindex="-1" aria-labelledby="addKelasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Kelas Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.kelas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if ($errors->any() && old('_token') && !old('id'))
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label for="nama_kelas" class="form-label fw-semibold">Nama Kelas</label>
                        <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" required value="{{ old('nama_kelas') }}">
                    </div>
                    <div class="mb-3">
                        <label for="wali_kelas_id" class="form-label fw-semibold">Wali Kelas</label>
                        <select class="form-select" id="wali_kelas_id" name="wali_kelas_id" required>
                            <option value="" disabled selected>Pilih Wali Kelas</option>
                            @foreach ($waliKelasOptions as $guru)
                                <option value="{{ $guru->id }}" {{ old('wali_kelas_id') == $guru->id ? 'selected' : '' }}>{{ $guru->nama }}</option>
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

{{-- Modal Edit Kelas --}}
<div class="modal fade" id="editKelasModal" tabindex="-1" aria-labelledby="editKelasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg rounded-3">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Data Kelas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editKelasForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @if ($errors->any() && old('id'))
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label for="editNamaKelas" class="form-label fw-semibold">Nama Kelas</label>
                        <input type="text" class="form-control" id="editNamaKelas" name="nama_kelas" required>
                    </div>
                    <div class="mb-3">
                        <label for="editWaliKelasId" class="form-label fw-semibold">Wali Kelas</label>
                        <select class="form-select" id="editWaliKelasId" name="wali_kelas_id" required>
                            @foreach ($waliKelasOptions as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning text-white shadow-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .class-card {
        border-radius: 20px;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .class-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 1rem 2rem rgba(0,0,0,0.12);
    }
    .icon-circle {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0d6efd, #4dabf7);
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    }
    .bg-info-subtle {
        background: #e8f7fd;
    }
    .bg-success-subtle {
        background: #eafaf1;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editKelasModal = document.getElementById('editKelasModal');
    if (editKelasModal) {
        editKelasModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const kelasId = button.getAttribute('data-id');
            const kelasNama = button.getAttribute('data-nama_kelas');
            const kelasWaliId = button.getAttribute('data-wali_kelas_id');
            
            const form = document.getElementById('editKelasForm');
            form.action = `{{ route('admin.kelas.update', ['id' => '__id__']) }}`.replace('__id__', kelasId);
            
            editKelasModal.querySelector('#editNamaKelas').value = kelasNama;
            editKelasModal.querySelector('#editWaliKelasId').value = kelasWaliId;
        });
    }

    const addKelasModal = document.getElementById('addKelasModal');
    if (addKelasModal) {
        addKelasModal.addEventListener('show.bs.modal', function (event) {
        });
    }
});
</script>
@endpush
@endsection
