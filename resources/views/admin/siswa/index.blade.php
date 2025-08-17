@extends('layouts.app')

@section('content')
<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-primary">👨‍🎓 Daftar Siswa</h1>
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
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
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
                                <td>{{ $loop->iteration }}</td>
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
        </div>
    </div>
</div>

<div class="modal fade" id="addSiswaModal" tabindex="-1" aria-labelledby="addSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addSiswaModalLabel">Tambah Siswa Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.siswa.store') }}" method="POST">
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
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" required value="{{ old('nama') }}">
                    </div>
                    <div class="mb-3">
                        <label for="nisn" class="form-label">NISN</label>
                        <input type="text" class="form-control" id="nisn" name="nisn" required value="{{ old('nisn') }}">
                    </div>
                    <div class="mb-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="" disabled selected>Pilih Jenis Kelamin</option>
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="agama" class="form-label">Agama</label>
                        <select class="form-select" id="agama" name="agama" required>
                            <option value="" disabled selected>Pilih Agama</option>
                            <option value="Islam" {{ old('agama') == 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Kristen" {{ old('agama') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                            <option value="Katolik" {{ old('agama') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                            <option value="Hindu" {{ old('agama') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddha" {{ old('agama') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                            <option value="Konghucu" {{ old('agama') == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                            <option value="Lainnya" {{ old('agama') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon') }}">
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat">{{ old('alamat') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="kelas_id" class="form-label">Kelas</label>
                        <select class="form-select" id="kelas_id" name="kelas_id" required>
                            <option value="" disabled selected>Pilih Kelas</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
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

<div class="modal fade" id="editSiswaModal" tabindex="-1" aria-labelledby="editSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editSiswaModalLabel">Edit Data Siswa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSiswaForm" action="" method="POST">
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
                        <label for="editNama" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="editNama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="editNisn" class="form-label">NISN</label>
                        <input type="text" class="form-control" id="editNisn" name="nisn" required>
                    </div>
                    <div class="mb-3">
                        <label for="editJenisKelamin" class="form-label">Jenis Kelamin</label>
                        <select class="form-select" id="editJenisKelamin" name="jenis_kelamin" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="editAgama" class="form-label">Agama</label>
                        <select class="form-select" id="editAgama" name="agama" required>
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Buddha">Buddha</option>
                            <option value="Konghucu">Konghucu</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editNomorTelepon" class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" id="editNomorTelepon" name="nomor_telepon">
                    </div>
                    <div class="mb-3">
                        <label for="editAlamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="editAlamat" name="alamat"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editKelasId" class="form-label">Kelas</label>
                        <select class="form-select" id="editKelasId" name="kelas_id" required>
                             @foreach ($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
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

<div class="modal fade" id="detailSiswaModal" tabindex="-1" aria-labelledby="detailSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="detailSiswaModalLabel">Detail Siswa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>NISN:</strong> <span id="detailNisn"></span>
                </div>
                <div class="mb-3">
                    <strong>Nama:</strong> <span id="detailNama"></span>
                </div>
                <div class="mb-3">
                    <strong>Jenis Kelamin:</strong> <span id="detailJenisKelamin"></span>
                </div>
                <div class="mb-3">
                    <strong>Agama:</strong> <span id="detailAgama"></span>
                </div>
                <div class="mb-3">
                    <strong>Nomor Telepon:</strong> <span id="detailNomorTelepon"></span>
                </div>
                <div class="mb-3">
                    <strong>Kelas:</strong> <span id="detailKelas"></span>
                </div>
                <div class="mb-3">
                    <strong>Alamat:</strong> <span id="detailAlamat"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qrSiswaModal" tabindex="-1" aria-labelledby="qrSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="qrSiswaModalLabel">QR Code Siswa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="qr-print-area">
                <p class="fw-bold mb-2">QR Code untuk <span id="qrSiswaNama"></span></p>
                <img id="qrCodeImage" src="" alt="QR Code Siswa" class="img-fluid" style="width: 200px; height: 200px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                <button id="printQrButton" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Cetak QR Code
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importSiswaModal" tabindex="-1" aria-labelledby="importSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="importSiswaModalLabel">Impor Data Siswa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Pastikan file yang Anda unggah berformat .xlsx, .xls, atau .csv.</p>
                    <div class="mb-3">
                        <label for="file-input" class="form-label">Pilih File Excel/CSV</label>
                        <div class="border rounded p-4 text-center" id="drop-area">
                            <i class="bi bi-cloud-upload display-4 text-muted mb-2"></i>
                            <p class="mb-2 text-muted" id="file-message">Seret & lepas file di sini atau klik untuk mengunggah.</p>
                            <input class="form-control" type="file" id="file-input" name="file" required style="display: none;">
                            <label for="file-input" class="btn btn-sm btn-outline-primary">Pilih File</label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small>Unduh template untuk panduan pengisian data.</small>
                        <a href="{{ route('admin.siswa.export-template') }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-2"></i> Unduh Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-success">Unggah dan Impor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editSiswaModal = document.getElementById('editSiswaModal');
        editSiswaModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const siswaId = button.getAttribute('data-id');
            const siswaNama = button.getAttribute('data-nama');
            const siswaNisn = button.getAttribute('data-nisn');
            const siswaJenisKelamin = button.getAttribute('data-jenis_kelamin');
            const siswaAgama = button.getAttribute('data-agama');
            const siswaNomorTelepon = button.getAttribute('data-nomor_telepon');
            const siswaKelasId = button.getAttribute('data-kelas_id');
            const siswaAlamat = button.getAttribute('data-alamat');
            
            const form = document.getElementById('editSiswaForm');
            form.action = `{{ route('admin.siswa.update', ['id' => '__id__']) }}`.replace('__id__', siswaId);
            
            const modalTitle = editSiswaModal.querySelector('.modal-title');
            const inputNama = editSiswaModal.querySelector('#editNama');
            const inputNisn = editSiswaModal.querySelector('#editNisn');
            const selectJenisKelamin = editSiswaModal.querySelector('#editJenisKelamin');
            const selectAgama = editSiswaModal.querySelector('#editAgama');
            const inputNomorTelepon = editSiswaModal.querySelector('#editNomorTelepon');
            const selectKelasId = editSiswaModal.querySelector('#editKelasId');
            const inputAlamat = editSiswaModal.querySelector('#editAlamat');
            
            modalTitle.textContent = `Edit Data Siswa: ${siswaNama}`;
            inputNama.value = siswaNama;
            inputNisn.value = siswaNisn;
            selectJenisKelamin.value = siswaJenisKelamin;
            selectAgama.value = siswaAgama;
            inputNomorTelepon.value = siswaNomorTelepon;
            selectKelasId.value = siswaKelasId;
            inputAlamat.value = siswaAlamat;
        });

        const detailSiswaModal = document.getElementById('detailSiswaModal');
        detailSiswaModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const siswaNama = button.getAttribute('data-nama');
            const siswaNisn = button.getAttribute('data-nisn');
            const siswaJenisKelamin = button.getAttribute('data-jenis_kelamin');
            const siswaAgama = button.getAttribute('data-agama');
            const siswaNomorTelepon = button.getAttribute('data-nomor_telepon');
            const siswaKelas = button.getAttribute('data-kelas');
            const siswaAlamat = button.getAttribute('data-alamat');
            
            const modalTitle = detailSiswaModal.querySelector('.modal-title');
            const detailNama = detailSiswaModal.querySelector('#detailNama');
            const detailNisn = detailSiswaModal.querySelector('#detailNisn');
            const detailJenisKelamin = detailSiswaModal.querySelector('#detailJenisKelamin');
            const detailAgama = detailSiswaModal.querySelector('#detailAgama');
            const detailNomorTelepon = detailSiswaModal.querySelector('#detailNomorTelepon');
            const detailKelas = detailSiswaModal.querySelector('#detailKelas');
            const detailAlamat = detailSiswaModal.querySelector('#detailAlamat');
            
            modalTitle.textContent = `Detail Siswa: ${siswaNama}`;
            detailNama.textContent = siswaNama;
            detailNisn.textContent = siswaNisn;
            detailJenisKelamin.textContent = (siswaJenisKelamin == 'L') ? 'Laki-laki' : 'Perempuan';
            detailAgama.textContent = siswaAgama;
            detailNomorTelepon.textContent = siswaNomorTelepon;
            detailKelas.textContent = siswaKelas;
            detailAlamat.textContent = siswaAlamat;
        });

        const qrSiswaModal = document.getElementById('qrSiswaModal');
        qrSiswaModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const siswaNama = button.getAttribute('data-nama');
            const qrCodePath = button.getAttribute('data-qr_code');

            const qrSiswaNama = qrSiswaModal.querySelector('#qrSiswaNama');
            const qrCodeImage = qrSiswaModal.querySelector('#qrCodeImage');
            
            qrSiswaNama.textContent = siswaNama;
            qrCodeImage.src = qrCodePath;
        });

        const printQrButton = document.getElementById('printQrButton');
        printQrButton.addEventListener('click', function() {
            const qrPrintArea = document.getElementById('qr-print-area');
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = qrPrintArea.innerHTML;
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        });
        
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');
        const fileMessage = document.getElementById('file-message');
        
        fileInput.addEventListener('change', () => {
            updateFileDisplay(fileInput.files);
        });

        dropArea.addEventListener('click', () => fileInput.click());
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.add('bg-light'), false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.remove('bg-light'), false);
        });
        
        dropArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            updateFileDisplay(files);
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
