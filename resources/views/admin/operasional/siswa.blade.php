@extends('layouts.app')

@section('title', 'Status Absensi Siswa')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-gradient-primary">
        <h5 class="m-0 font-weight-bold text-white"><i class="bi bi-person-lines-fill me-2"></i>Status Absensi Siswa Per Mata Pelajaran</h5>
        <p class="text-white-50 mb-0">Lihat status kehadiran siswa yang dicatat oleh guru melalui aplikasi mobile.</p>
    </div>
    <div class="card-body">
        
        <!-- Filter Form -->
        <form id="filter-form" class="row g-3 mb-4 p-3 border rounded-3 bg-light">
            <div class="col-md-3">
                <label for="start_date" class="form-label fw-bold">Dari Tanggal</label>
                <!-- Nilai default 1 bulan lalu -->
                <input type="date" class="form-control rounded-pill" id="start_date" name="start_date" value="{{ date('Y-m-d', strtotime('-1 month')) }}" required>
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label fw-bold">Sampai Tanggal</label>
                <input type="date" class="form-control rounded-pill" id="end_date" name="end_date" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
                <label for="kelas_id" class="form-label fw-bold">Filter Kelas</label>
                <select class="form-select rounded-pill" id="kelas_id" name="kelas_id">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="mapel_id" class="form-label fw-bold">Filter Mapel</label>
                <select class="form-select rounded-pill" id="mapel_id" name="mapel_id">
                    <option value="">-- Semua Mapel --</option>
                    @foreach($mapel as $m)
                        <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Tambahkan filter Guru PJ (hanya Admin yang bisa) -->
            <div class="col-md-2">
                <label for="guru_id" class="form-label fw-bold">Filter Guru PJ</label>
                <select class="form-select rounded-pill" id="guru_id" name="guru_id">
                    <option value="">-- Semua Guru --</option>
                    @foreach($guru as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 me-2 rounded-pill"><i class="bi bi-funnel-fill"></i> Filter</button>
            </div>
        </form>

        <hr>

        <!-- Aksi Export -->
        <div class="d-flex justify-content-between align-items-center mb-3">
             <span class="fs-6 text-muted" id="total-data-info"></span>
             <button type="button" class="btn btn-success rounded-pill" id="export-excel-btn" disabled><i class="bi bi-file-earmark-excel"></i> Export Laporan</button>
        </div>
       

        <!-- Tabel Laporan -->
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="absensiTable" width="100%" cellspacing="0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Tanggal</th>
                        <th>Waktu Scan</th>
                        <th>NISN</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Status</th>
                        <th>Guru PJ</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody id="absensi-data-body">
                    <tr><td colspan="9" class="text-center text-muted">Silakan atur filter dan klik 'Filter' untuk menampilkan data.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Pastikan URL di-set dengan benar
    // Gunakan fungsi Laravel asset() atau url() yang sesuai jika Anda tidak menggunakan Blade
    const API_URL_LAPORAN = '{{ url("api/laporan/absensi") }}';
    const API_URL_EXPORT = '{{ url("api/laporan/export/excel") }}';

    // Mendapatkan CSRF Token untuk request AJAX POST (jika diperlukan)
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); 
    
    // Fungsi untuk mengambil data dari API (Task B1)
    async function fetchData(params) {
        const body = document.getElementById('absensi-data-body');
        const totalInfo = document.getElementById('total-data-info');
        const exportBtn = document.getElementById('export-excel-btn');
        
        body.innerHTML = '<tr><td colspan="9" class="text-center"><i class="bi bi-arrow-repeat spin me-2"></i>Mengambil data...</td></tr>';
        exportBtn.disabled = true;

        // Siapkan Query String dari parameters
        const queryString = new URLSearchParams(params).toString();
        const url = `${API_URL_LAPORAN}?${queryString}`;

        try {
            const response = await fetch(url, {
                method: 'GET',
                // Otentikasi Admin melalui Session/Cookie Laravel
                headers: { 'Content-Type': 'application/json' },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Gagal mengambil data (${response.status}).`);
            }

            const data = await response.json();
            renderTable(data.data);
            
            totalInfo.textContent = `Total Data: ${data.data.length} entri`;
            exportBtn.disabled = data.data.length === 0;

        } catch (error) {
            body.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Error: ${error.message}</td></tr>`;
            console.error('Fetch Error:', error);
            totalInfo.textContent = `Total Data: 0 entri`;
        }
    }

    // Fungsi untuk merender data ke tabel
    function renderTable(data) {
        const body = document.getElementById('absensi-data-body');
        body.innerHTML = '';

        if (data.length === 0) {
            body.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Tidak ada data absensi yang ditemukan pada rentang waktu ini.</td></tr>';
            return;
        }

        data.forEach(item => {
            const row = body.insertRow();
            row.insertCell().textContent = item.tanggal;
            row.insertCell().textContent = item.waktu_scan;
            row.insertCell().textContent = item.siswa.nisn;
            row.insertCell().textContent = item.siswa.nama;
            row.insertCell().textContent = item.kelas;
            row.insertCell().textContent = item.mata_pelajaran;
            
            // Cell Status dengan warna
            const statusCell = row.insertCell();
            let badgeClass;
            switch(item.status) {
                case 'Hadir':
                    badgeClass = 'bg-success';
                    break;
                case 'Terlambat':
                    badgeClass = 'bg-warning text-dark';
                    break;
                default: // Izin, Sakit, Alpa
                    badgeClass = 'bg-danger';
            }
            statusCell.innerHTML = `<span class="badge ${badgeClass}">${item.status}</span>`;

            row.insertCell().textContent = item.guru_penanggung_jawab;
            row.insertCell().textContent = item.keterangan || '-';
        });
    }

    // Event Listener untuk Form Filter
    document.getElementById('filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = {};
        formData.forEach((value, key) => {
            // Hanya masukkan parameter yang memiliki nilai
            if (value) params[key] = value;
        });

        fetchData(params);
        
        // Simpan parameter filter untuk Export
        document.getElementById('export-excel-btn').dataset.filter = JSON.stringify(params);
    });
    
    // Event Listener untuk Tombol Export (Task B2)
    document.getElementById('export-excel-btn').addEventListener('click', function() {
        const filterData = this.dataset.filter;
        
        if (filterData) {
            const params = JSON.parse(filterData);
            
            // Gunakan window.location untuk memicu download GET request (Task B2)
            const queryString = new URLSearchParams(params).toString();
            window.location.href = `${API_URL_EXPORT}?${queryString}`;
        }
    });

    // Panggil fetchData pertama kali saat halaman dimuat (untuk rentang default)
    document.addEventListener('DOMContentLoaded', () => {
        const defaultParams = {
            start_date: document.getElementById('start_date').value,
            end_date: document.getElementById('end_date').value,
        };
        fetchData(defaultParams);
        document.getElementById('export-excel-btn').dataset.filter = JSON.stringify(defaultParams);
    });

</script>
<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .spin {
        animation: spin 1s linear infinite;
    }
    .bg-gradient-primary {
        background: linear-gradient(90deg, #6366f1 0%, #06b6d4 100%);
        border-radius: 0.5rem 0.5rem 0 0;
    }
</style>
@endpush