@extends('layouts.app')

@section('content')
<div class="container py-5">

    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h1 class="fw-bold text-primary mb-0">📚 Daftar Mata Pelajaran</h1>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-primary d-flex align-items-center mx-auto mx-md-0" 
                data-bs-toggle="modal" data-bs-target="#addMapelModal">
                <i class="bi bi-plus-lg me-2"></i> Tambah Mata Pelajaran
            </button>
        </div>
    </div>

    <!-- Alert -->
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

    <!-- Table -->
    <div class="table-responsive shadow rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary text-center">
                <tr>
                    <th style="width:5%">#</th>
                    <th style="width:35%">Nama Mata Pelajaran</th>
                    <th style="width:15%">Total Jadwal</th>
                    <th style="width:20%">Detail Jadwal</th>
                    <th style="width:25%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mataPelajaran as $index => $mapel)
                <tr>
                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                    <td>{{ $mapel->nama_mapel }}</td>
                    <td class="text-center">
                        <span class="badge bg-info rounded-pill">{{ $mapel->jadwal->count() }}</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#jadwal-{{ $mapel->id }}">
                            <i class="bi bi-eye"></i> Lihat
                        </button>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-warning btn-sm text-white me-1"
                            data-bs-toggle="modal" data-bs-target="#editMapelModal"
                            data-id="{{ $mapel->id }}" data-nama_mapel="{{ $mapel->nama_mapel }}">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <form action="{{ route('admin.mapel.destroy', $mapel->id) }}" method="POST" 
                            class="d-inline" onsubmit="return confirm('Yakin hapus mata pelajaran ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <!-- Collapse detail jadwal -->
                <tr class="collapse bg-light" id="jadwal-{{ $mapel->id }}">
                    <td colspan="5">
                        <div class="p-3">
                            @if($mapel->jadwal->count() > 0)
                                <div class="list-group">
                                    @foreach ($mapel->jadwal as $jadwal)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-calendar-event me-2 text-primary"></i>
                                                Kelas {{ $jadwal->kelas->nama_kelas }} | Hari: {{ $jadwal->hari }} | Jam: {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
                                            </div>
                                            <span class="badge bg-secondary">{{ $jadwal->ruangan }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted fst-italic">Belum ada jadwal untuk mata pelajaran ini.</div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-exclamation-circle me-2"></i> Belum ada data mata pelajaran.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addMapelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Mata Pelajaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.mapel.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="form-label fw-semibold">Nama Mata Pelajaran</label>
                    <input type="text" class="form-control" name="nama_mapel" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editMapelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Mata Pelajaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editMapelForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <label class="form-label fw-semibold">Nama Mata Pelajaran</label>
                    <input type="text" class="form-control" id="editNamaMapel" name="nama_mapel" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const editMapelModal = document.getElementById('editMapelModal');
        editMapelModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama_mapel');

            const form = document.getElementById('editMapelForm');
            form.action = `{{ route('admin.mapel.update', ['id' => '__id__']) }}`.replace('__id__', id);
            document.getElementById('editNamaMapel').value = nama;
        });
    });
</script>
@endpush
@endsection
