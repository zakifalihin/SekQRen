@extends('layouts.app')

@section('content')
<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-primary">
            👨‍🎓 Daftar Siswa
            @if(isset($kelasId))
                - {{ App\Models\Kelas::find($kelasId)->nama_kelas }}
            @endif
        </h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#importSiswaModal">
                <i class="bi bi-upload me-2"></i> Impor Data
            </button>
            <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addSiswaModal">
                <i class="bi bi-plus-lg me-2"></i> Tambah Siswa
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="alert alert-primary shadow-sm">
        <i class="bi bi-people-fill"></i> Total Siswa: <strong>{{ $totalSiswa }}</strong>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.siswa.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari siswa berdasarkan NISN atau nama..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col" style="width: 5%">No</th>
                            <th scope="col">NISN</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Agama</th>
                            <th scope="col">Jenis Kelamin</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Nomor Telepon</th>
                            <th scope="col" style="width: 20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($siswa as $s)
                            <tr>
                                {{-- PERBAIKAN 1: Penomoran Berkelanjutan --}}
                                <td>{{ ($siswa->currentPage() - 1) * $siswa->perPage() + $loop->iteration }}</td>
                                <td>{{ $s->nisn }}</td>
                                <td>{{ $s->nama }}</td>
                                <td>{{ $s->agama }}</td>
                                <td>{{ ($s->jenis_kelamin == 'L') ? 'Laki-laki' : 'Perempuan' }}</td>
                                <td>{{ $s->kelas->nama_kelas }}</td>
                                <td>{{ $s->nomor_telepon ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#detailSiswaModal"
                                                data-nama="{{ $s->nama }}" data-nisn="{{ $s->nisn }}" data-jenis_kelamin="{{ $s->jenis_kelamin }}" data-kelas="{{ $s->kelas->nama_kelas }}" data-alamat="{{ $s->alamat }}" data-agama="{{ $s->agama }}" data-nomor_telepon="{{ $s->nomor_telepon }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm text-white" data-bs-toggle="modal" data-bs-target="#editSiswaModal"
                                                data-id="{{ $s->id }}" data-nama="{{ $s->nama }}" data-nisn="{{ $s->nisn }}" data-jenis_kelamin="{{ $s->jenis_kelamin }}" data-kelas_id="{{ $s->kelas_id }}" data-alamat="{{ $s->alamat }}" data-agama="{{ $s->agama }}" data-nomor_telepon="{{ $s->nomor_telepon }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm text-white" data-bs-toggle="modal" data-bs-target="#qrSiswaModal"
                                                data-nama="{{ $s->nama }}" data-qr_code="{{ Storage::url($s->qr_code) }}">
                                            <i class="bi bi-qr-code"></i>
                                        </button>
                                        <form action="{{ route('admin.siswa.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?')">
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
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-exclamation-circle me-2"></i> Tidak ada data siswa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PERBAIKAN 2: Pagination Tetap Ada (Stay) --}}
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-center">
                    <nav class="d-flex" role="navigation" aria-label="Pagination">
                        {{-- Tombol Prev --}}
                        <a 
                            @if ($siswa->onFirstPage())
                                class="btn btn-outline-secondary me-2 disabled" aria-disabled="true"
                            @else
                                class="btn btn-outline-primary me-2" href="{{ $siswa->previousPageUrl() }}"
                            @endif
                        >
                            Prev
                        </a>
                        
                        {{-- Tombol Next --}}
                        <a 
                            @if ($siswa->hasMorePages())
                                class="btn btn-outline-primary" href="{{ $siswa->nextPageUrl() }}"
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
{{-- Saya menghilangkan semua modal dan script di sini agar kode tidak terlalu panjang, --}}
{{-- tetapi asumsikan modal dan script JavaScript Anda yang asli masih berada di bawah. --}}

<div class="modal fade" id="addSiswaModal" tabindex="-1" aria-labelledby="addSiswaModalLabel" aria-hidden="true">
    {{-- ... Isi Modal Add Siswa ... --}}
</div>
<div class="modal fade" id="editSiswaModal" tabindex="-1" aria-labelledby="editSiswaModalLabel" aria-hidden="true">
    {{-- ... Isi Modal Edit Siswa ... --}}
</div>
<div class="modal fade" id="detailSiswaModal" tabindex="-1" aria-labelledby="detailSiswaModalLabel" aria-hidden="true">
    {{-- ... Isi Modal Detail Siswa ... --}}
</div>
<div class="modal fade" id="qrSiswaModal" tabindex="-1" aria-labelledby="qrSiswaModalLabel" aria-hidden="true">
    {{-- ... Isi Modal QR Siswa ... --}}
</div>
<div class="modal fade" id="importSiswaModal" tabindex="-1" aria-labelledby="importSiswaModalLabel" aria-hidden="true">
    {{-- ... Isi Modal Import Siswa ... --}}
</div>

<script>
    // ... Seluruh logika JavaScript Anda yang asli ...

    document.addEventListener('DOMContentLoaded', function () {
        // Logika Modal Edit Siswa
        const editSiswaModal = document.getElementById('editSiswaModal');
        if (editSiswaModal) {
            editSiswaModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const siswaId = button.getAttribute('data-id');
                const siswaNama = button.getAttribute('data-nama');
                // ... (Ambil data lainnya)
                
                const form = document.getElementById('editSiswaForm');
                // Perbaiki action URL
                form.action = `{{ url('admin/siswa') }}/${siswaId}`; 
                
                // ... (Isi input modal)
                editSiswaModal.querySelector('.modal-title').textContent = `Edit Data Siswa: ${siswaNama}`;
                editSiswaModal.querySelector('#editNama').value = siswaNama;
                // ... (Set nilai input lainnya)
            });
        }
        
        // Logika Modal Detail Siswa
        const detailSiswaModal = document.getElementById('detailSiswaModal');
        if (detailSiswaModal) {
            detailSiswaModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const siswaNama = button.getAttribute('data-nama');
                // ... (Ambil data lainnya)
                
                detailSiswaModal.querySelector('.modal-title').textContent = `Detail Siswa: ${siswaNama}`;
                // ... (Set teks detail)
            });
        }

        // Logika Modal QR Siswa dan Print
        const qrSiswaModal = document.getElementById('qrSiswaModal');
        if (qrSiswaModal) {
            qrSiswaModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const siswaNama = button.getAttribute('data-nama');
                const qrCodePath = button.getAttribute('data-qr_code');

                qrSiswaModal.querySelector('#qrSiswaNama').textContent = siswaNama;
                qrSiswaModal.querySelector('#qrCodeImage').src = qrCodePath;
            });
        }
        
        const printQrButton = document.getElementById('printQrButton');
        if (printQrButton) {
            printQrButton.addEventListener('click', function() {
                const qrPrintArea = document.getElementById('qr-print-area');
                const originalContent = document.body.innerHTML;

                document.body.innerHTML = qrPrintArea.innerHTML;
                window.print();
                document.body.innerHTML = originalContent;
                window.location.reload();
            });
        }
        
        // Logika Drag and Drop (Import)
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');
        const fileMessage = document.getElementById('file-message');
        
        // ... (Fungsi dan event listener drag/drop/input Anda yang asli)
        if (fileInput) {
            fileInput.addEventListener('change', () => {
                updateFileDisplay(fileInput.files);
            });
        }
        if (dropArea) {
             dropArea.addEventListener('click', () => fileInput.click());
        }
        
        function updateFileDisplay(files) {
            if (files.length > 0) {
                fileMessage.textContent = `File terpilih: ${files[0].name}`;
                fileMessage.classList.add('fw-bold');
            } else {
                fileMessage.textContent = 'Seret & lepas file di sini atau klik untuk mengunggah.';
                fileMessage.classList.remove('fw-bold');
            }
        }
    });
</script>

@endsection