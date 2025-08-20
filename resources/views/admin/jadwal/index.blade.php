@extends('layouts.app')

@section('content')
<div class="container py-5">

    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="fw-bold text-primary mb-0">🗓️ Jadwal Kelas {{ $kelas->nama_kelas }}</h1>
            <p class="text-muted mb-0">Atur mata pelajaran, guru, dan jam pelajaran.</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
            <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i> Kembali ke Kelas
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJadwalModal">
                <i class="bi bi-plus-lg me-2"></i> Tambah Jadwal
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TABLE --}}
    <div class="table-responsive shadow-sm rounded-3 border">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-center small text-uppercase text-muted">
                    <th style="width:6%">#</th>
                    <th style="width:14%">Hari</th>
                    <th style="width:28%">Mata Pelajaran</th>
                    <th style="width:22%">Guru</th>
                    <th style="width:15%">Jam Mulai</th>
                    <th style="width:15%">Jam Selesai</th>
                    <th style="width:10%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jadwal as $index => $j)
                    <tr>
                        <td class="text-center fw-semibold">{{ $index + 1 }}</td>
                        <td>{{ $j->hari }}</td>
                        <td>{{ $j->mataPelajaran->nama_mapel ?? '-' }}</td>
                        <td>{{ $j->guru->nama ?? '-' }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button"
                                    class="btn btn-warning btn-sm text-white"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editJadwalModal"
                                    data-id="{{ $j->id }}"
                                    data-hari="{{ $j->hari }}"
                                    data-mapel_id="{{ $j->mata_pelajaran_id }}"
                                    data-guru_id="{{ $j->guru_id }}"
                                    data-jam_mulai="{{ $j->jam_mulai }}"
                                    data-jam_selesai="{{ $j->jam_selesai }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteJadwalModal"
                                    data-id="{{ $j->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-exclamation-circle me-2"></i> Tidak ada jadwal untuk kelas ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================== MODALS ================== --}}

{{-- ADD JADWAL --}}
<div class="modal fade" id="addJadwalModal" tabindex="-1" aria-labelledby="addJadwalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addJadwalModalLabel">Tambah Jadwal • {{ $kelas->nama_kelas }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addJadwalForm" action="{{ route('admin.jadwal.store') }}" method="POST">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <div class="modal-body">
                    {{-- Error Add --}}
                    @if ($errors->any() && !old('id'))
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="hari" class="form-label fw-semibold">Hari</label>
                        <select class="form-select" id="hari" name="hari" required>
                            <option value="" disabled selected>Pilih Hari</option>
                            @foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                                <option value="{{ $h }}" {{ old('hari')===$h ? 'selected':'' }}>{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="mata_pelajaran_id" class="form-label fw-semibold">Mata Pelajaran</label>
                        <select class="form-select" id="mata_pelajaran_id" name="mata_pelajaran_id" required>
                            <option value="" disabled selected>Pilih Mata Pelajaran</option>
                            @foreach ($mapelOptions as $mapel)
                                <option value="{{ $mapel->id }}" {{ old('mata_pelajaran_id')==$mapel->id ? 'selected':'' }}>
                                    {{ $mapel->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="guru_id" class="form-label fw-semibold">Guru</label>
                        <select class="form-select" id="guru_id" name="guru_id" required>
                            <option value="" disabled selected>Pilih Guru</option>
                            @foreach ($guruOptions as $guru)
                                <option value="{{ $guru->id }}" {{ old('guru_id')==$guru->id ? 'selected':'' }}>
                                    {{ $guru->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col">
                            <label for="jam_mulai" class="form-label fw-semibold">Jam Mulai</label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required value="{{ old('jam_mulai') }}">
                        </div>
                        <div class="col">
                            <label for="jam_selesai" class="form-label fw-semibold">Jam Selesai</label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required value="{{ old('jam_selesai') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT JADWAL --}}
<div class="modal fade" id="editJadwalModal" tabindex="-1" aria-labelledby="editJadwalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg rounded-3">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editJadwalModalLabel">Edit Jadwal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editJadwalForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <input type="hidden" name="id" id="editHiddenId"> {{-- penting untuk old('id') --}}
                <div class="modal-body">
                    {{-- Error Edit --}}
                    @if ($errors->any() && old('id'))
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="editHari" class="form-label fw-semibold">Hari</label>
                        <select class="form-select" id="editHari" name="hari" required>
                            @foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                                <option value="{{ $h }}">{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="editMataPelajaranId" class="form-label fw-semibold">Mata Pelajaran</label>
                        <select class="form-select" id="editMataPelajaranId" name="mata_pelajaran_id" required>
                            @foreach ($mapelOptions as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="editGuruId" class="form-label fw-semibold">Guru</label>
                        <select class="form-select" id="editGuruId" name="guru_id" required>
                            @foreach ($guruOptions as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col">
                            <label for="editJamMulai" class="form-label fw-semibold">Jam Mulai</label>
                            <input type="time" class="form-control" id="editJamMulai" name="jam_mulai" required>
                        </div>
                        <div class="col">
                            <label for="editJamSelesai" class="form-label fw-semibold">Jam Selesai</label>
                            <input type="time" class="form-control" id="editJamSelesai" name="jam_selesai" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE JADWAL --}}
<div class="modal fade" id="deleteJadwalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Apakah kamu yakin ingin menghapus jadwal ini?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteJadwalForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ====== EDIT MODAL (prefill) ======
    const editJadwalModal = document.getElementById('editJadwalModal');
    if (editJadwalModal) {
        editJadwalModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const form   = document.getElementById('editJadwalForm');

            const id          = button.getAttribute('data-id');
            const hari        = button.getAttribute('data-hari');
            const mapelId     = button.getAttribute('data-mapel_id');
            const guruId      = button.getAttribute('data-guru_id');
            const jamMulai    = button.getAttribute('data-jam_mulai');
            const jamSelesai  = button.getAttribute('data-jam_selesai');

            form.action = `{{ route('admin.jadwal.update', ':id') }}`.replace(':id', id);
            document.getElementById('editHiddenId').value = id;
            document.getElementById('editHari').value = hari;
            document.getElementById('editMataPelajaranId').value = mapelId;
            document.getElementById('editGuruId').value = guruId;
            document.getElementById('editJamMulai').value = jamMulai;
            document.getElementById('editJamSelesai').value = jamSelesai;
        });
    }

    // ====== DELETE MODAL (set action) ======
    const deleteJadwalModal = document.getElementById('deleteJadwalModal');
    if (deleteJadwalModal) {
        deleteJadwalModal.addEventListener('show.bs.modal', function (event) {
            const id = event.relatedTarget.getAttribute('data-id');
            const form = document.getElementById('deleteJadwalForm');
            form.action = `{{ route('admin.jadwal.destroy', ':id') }}`.replace(':id', id);
        });
    }

    // ====== VALIDASI JAM (client-side kecil) ======
    function validateJam(form) {
        const jm = form.querySelector('[name="jam_mulai"]')?.value;
        const js = form.querySelector('[name="jam_selesai"]')?.value;
        if (jm && js && jm >= js) {
            alert("Jam selesai harus lebih besar dari jam mulai.");
            return false;
        }
        return true;
    }
    document.getElementById('addJadwalForm')?.addEventListener('submit', function(e){
        if (!validateJam(this)) e.preventDefault();
    });
    document.getElementById('editJadwalForm')?.addEventListener('submit', function(e){
        if (!validateJam(this)) e.preventDefault();
    });
    
    if (hasErrors) {
        if (oldId) {
            // buka modal EDIT dan isi dari old()
            const el = document.getElementById('editJadwalModal');
            if (el && window.bootstrap) {
                const form = document.getElementById('editJadwalForm');
                form.action = `{{ route('admin.jadwal.update', ':id') }}`.replace(':id', oldId);
                document.getElementById('editHiddenId').value = oldId;
                if (oldVals) {
                    if (oldVals.hari) document.getElementById('editHari').value = oldVals.hari;
                    if (oldVals.mata_pelajaran_id) document.getElementById('editMataPelajaranId').value = oldVals.mata_pelajaran_id;
                    if (oldVals.guru_id) document.getElementById('editGuruId').value = oldVals.guru_id;
                    if (oldVals.jam_mulai) document.getElementById('editJamMulai').value = oldVals.jam_mulai;
                    if (oldVals.jam_selesai) document.getElementById('editJamSelesai').value = oldVals.jam_selesai;
                }
                bootstrap.Modal.getOrCreateInstance(el).show();
            }
        } else {
            const el = document.getElementById('addJadwalModal');
            if (el && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(el).show();
            }
        }
    }
});
</script>
@endpush
