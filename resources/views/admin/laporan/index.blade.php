@extends('layouts.app')

@section('title', 'Laporan & Export Data')

@section('content')
<!-- Header Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-lg overflow-hidden">
            <div class="card-header bg-gradient-primary-modern border-0 py-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center">
                            <div class="icon-badge me-3">
                                <i class="bi bi-file-earmark-bar-graph-fill fs-2"></i>
                            </div>
                            <div>
                                <h4 class="m-0 font-weight-bold text-white">
                                    <i class="bi bi-file-earmark-bar-graph-fill me-2"></i>Laporan & Export Absensi
                                </h4>
                                <p class="text-white-75 mb-0 mt-1">
                                    Hasilkan laporan absensi Siswa (per Mapel) dan Guru (per Hari) dalam format Excel/PDF
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div class="stats-mini">
                            <div class="d-inline-block bg-white bg-opacity-20 rounded-3 px-3 py-2 me-2">
                                <i class="bi bi-calendar-check text-white me-1"></i>
                                <small class="text-white fw-semibold">{{ date('d M Y') }}</small>
                            </div>
                            <div class="d-inline-block bg-white bg-opacity-20 rounded-3 px-3 py-2">
                                <i class="bi bi-clock text-white me-1"></i>
                                <small class="text-white fw-semibold" id="live-clock">--:--:--</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Card -->
<div class="card border-0 shadow-lg">
    <div class="card-body p-0">
        <!-- Modern Tab Navigation -->
        <div class="tab-navigation-modern">
            <ul class="nav nav-pills nav-fill p-3 gap-2" id="laporanTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="siswa-tab" data-bs-toggle="tab" data-bs-target="#siswa-tab-pane" type="button" role="tab" aria-controls="siswa-tab-pane" aria-selected="true">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="bi bi-people-fill fs-5 me-2"></i>
                            <div class="text-start">
                                <div class="fw-bold">Laporan Siswa</div>
                                <small class="opacity-75">Absensi per Mata Pelajaran</small>
                            </div>
                        </div>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="guru-tab" data-bs-toggle="tab" data-bs-target="#guru-tab-pane" type="button" role="tab" aria-controls="guru-tab-pane" aria-selected="false">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="bi bi-person-badge-fill fs-5 me-2"></i>
                            <div class="text-start">
                                <div class="fw-bold">Laporan Guru</div>
                                <small class="opacity-75">Absensi Harian & Jam Ajar</small>
                            </div>
                        </div>
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content p-4" id="laporanTabContent">
            <!-- TAB 1: LAPORAN SISWA (Menggunakan Partial View) -->
            <div class="tab-pane fade show active" id="siswa-tab-pane" role="tabpanel" aria-labelledby="siswa-tab" tabindex="0">
                @include('admin.laporan.partials.siswa_form')
            </div>

            <!-- TAB 2: LAPORAN GURU (Menggunakan Partial View) -->
            <div class="tab-pane fade" id="guru-tab-pane" role="tabpanel" aria-labelledby="guru-tab" tabindex="0">
                @include('admin.laporan.partials.guru_form')
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Dashboard -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 card-hover">
            <div class="card-body text-center">
                <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                    <i class="bi bi-people fs-3"></i>
                </div>
                <h3 class="mb-1 fw-bold text-primary" id="stat-total-siswa">-</h3>
                <p class="text-muted mb-0 small">Total Siswa Aktif</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 card-hover">
            <div class="card-body text-center">
                <div class="icon-circle bg-success bg-opacity-10 text-success mx-auto mb-3">
                    <i class="bi bi-person-badge fs-3"></i>
                </div>
                <h3 class="mb-1 fw-bold text-success" id="stat-total-guru">-</h3>
                <p class="text-muted mb-0 small">Total Guru Aktif</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 card-hover">
            <div class="card-body text-center">
                <div class="icon-circle bg-info bg-opacity-10 text-info mx-auto mb-3">
                    <i class="bi bi-file-earmark-check fs-3"></i>
                </div>
                <h3 class="mb-1 fw-bold text-info" id="stat-absensi-today">-</h3>
                <p class="text-muted mb-0 small">Absensi Siswa Hari Ini</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 card-hover">
            <div class="card-body text-center">
                <div class="icon-circle bg-warning bg-opacity-10 text-warning mx-auto mb-3">
                    <i class="bi bi-download fs-3"></i>
                </div>
                <h3 class="mb-1 fw-bold text-warning" id="stat-export-bulan-ini">-</h3>
                <p class="text-muted mb-0 small">Total Export Bulan Ini</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Live Clock
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('live-clock').textContent = `${hours}:${minutes}:${seconds}`;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // URL API (pastikan endpoint Task B3 & B4 ada di backend Anda)
    const API_URL_SISWA = '{{ url("api/laporan/absensi") }}'; 
    const API_URL_EXPORT_SISWA = '{{ url("api/laporan/export/excel") }}';
    const API_URL_GURU = '{{ url("api/laporan/guru") }}'; // ASUMSI: Endpoint untuk data guru absensi
    const API_URL_EXPORT_GURU = '{{ url("api/laporan/export/guru") }}'; // ASUMSI: Endpoint untuk export guru
    const CSRF_TOKEN = '{{ csrf_token() }}';

    // ===============================================
    // LOGIC UMUM (AJAX FETCHING)
    // ===============================================

    async function fetchData(apiUrl, params, tableBodyId, totalInfoId, exportBtnId, isSiswa) {
        const body = document.getElementById(tableBodyId);
        const totalInfo = document.getElementById(totalInfoId);
        const exportBtn = document.getElementById(exportBtnId);
        
        const numCols = isSiswa ? 9 : 7; 
        
        body.innerHTML = `<tr><td colspan="${numCols}" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Mengambil data...</p>
        </td></tr>`;
        exportBtn.disabled = true;

        const queryString = new URLSearchParams(params).toString();
        const url = `${apiUrl}?${queryString}`;

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    // Untuk Laravel, kita biasanya mengandalkan session, 
                    // atau jika menggunakan Sanctum/API Token, perlu header Authorization.
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Gagal mengambil data dari API.');
            }

            const data = await response.json();
            
            if (isSiswa) {
                renderTableSiswa(data.data, body);
            } else {
                renderTableGuru(data.data, body);
            }
            
            totalInfo.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i>Total Data: <strong>${data.data.length}</strong> entri`;
            exportBtn.disabled = data.data.length === 0;

        } catch (error) {
            body.innerHTML = `<tr><td colspan="${numCols}" class="text-center text-danger py-5">
                <i class="bi bi-exclamation-triangle-fill fs-1 d-block mb-3"></i>
                <p class="fw-bold">Error: ${error.message}</p>
            </td></tr>`;
            console.error('Fetch Error:', error);
            totalInfo.innerHTML = `<i class="bi bi-x-circle-fill text-danger me-1"></i>Total Data: <strong>0</strong> entri`;
        }
    }

    // ===============================================
    // LOGIC RENDERING SISWA
    // ===============================================

    function renderTableSiswa(data, body) {
        body.innerHTML = '';
        if (data.length === 0) {
            body.innerHTML = `<tr><td colspan="9" class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i><p class="mb-0">Tidak ada data absensi siswa yang ditemukan.</p>
            </td></tr>`;
            return;
        }

        data.forEach((item, index) => {
            const row = body.insertRow();
            row.classList.add('animate-fade-in');
            row.style.animationDelay = `${index * 0.02}s`;
            
            row.insertCell().textContent = item.tanggal;
            row.insertCell().textContent = item.waktu_scan;
            row.insertCell().textContent = item.siswa.nisn;
            row.insertCell().textContent = item.siswa.nama;
            row.insertCell().textContent = item.kelas;
            row.insertCell().textContent = item.mata_pelajaran;
            
            const statusCell = row.insertCell();
            const badgeClass = item.status === 'Hadir' ? 'bg-success' : item.status === 'Terlambat' ? 'bg-warning text-dark' : 'bg-danger';
            statusCell.innerHTML = `<span class="badge ${badgeClass} rounded-pill px-3">${item.status}</span>`;

            row.insertCell().textContent = item.guru_penanggung_jawab;
            row.insertCell().textContent = item.keterangan || '-';
        });
    }

    // ===============================================
    // LOGIC RENDERING GURU
    // ===============================================
    
    function renderTableGuru(data, body) {
        body.innerHTML = '';
        if (data.length === 0) {
            body.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i><p class="mb-0">Tidak ada data absensi guru yang ditemukan.</p>
            </td></tr>`;
            return;
        }

        data.forEach((item, index) => {
            const row = body.insertRow();
            row.classList.add('animate-fade-in');
            row.style.animationDelay = `${index * 0.02}s`;
            
            row.insertCell().textContent = item.tanggal;
            row.insertCell().textContent = item.guru_nama;
            
            const statusCell = row.insertCell();
            const badgeClass = item.status === 'Hadir' ? 'bg-success' : item.status === 'Terlambat' ? 'bg-warning text-dark' : 'bg-danger';
            statusCell.innerHTML = `<span class="badge ${badgeClass} rounded-pill px-3">${item.status}</span>`;
            
            row.insertCell().textContent = item.jam_datang || '-';
            row.insertCell().textContent = item.jam_pulang || '-';
            row.insertCell().innerHTML = `<span class="badge bg-info text-dark">${item.total_jam_ajar || '0.00'} Jam</span>`;
            row.insertCell().textContent = item.keterangan || '-';
        });
    }

    // ===============================================
    // EVENT LISTENERS
    // ===============================================

    // 1. Filter Siswa
    document.getElementById('filter-siswa-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = {};
        formData.forEach((value, key) => {
            if (value) params[key] = value;
        });

        // Panggil API Siswa
        fetchData(API_URL_SISWA, params, 'laporan-data-body-siswa', 'total-data-info-siswa', 'export-excel-btn-siswa', true);
        document.getElementById('export-excel-btn-siswa').dataset.filter = JSON.stringify(params);
    });

    // 2. Export Siswa
    document.getElementById('export-excel-btn-siswa').addEventListener('click', function() {
        const params = JSON.parse(this.dataset.filter || '{}');
        if (params && Object.keys(params).length > 0) {
            const queryString = new URLSearchParams(params).toString();
            window.location.href = `${API_URL_EXPORT_SISWA}?${queryString}`;
        }
    });

    // 3. Filter Guru
    document.getElementById('filter-guru-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = {};
        formData.forEach((value, key) => {
            if (value) params[key] = value;
        });

        // Panggil API Guru
        fetchData(API_URL_GURU, params, 'laporan-data-body-guru', 'total-data-info-guru', 'export-excel-btn-guru', false);
        document.getElementById('export-excel-btn-guru').dataset.filter = JSON.stringify(params);
    });

    // 4. Export Guru
    document.getElementById('export-excel-btn-guru').addEventListener('click', function() {
        const params = JSON.parse(this.dataset.filter || '{}');
        if (params && Object.keys(params).length > 0) {
            const queryString = new URLSearchParams(params).toString();
            window.location.href = `${API_URL_EXPORT_GURU}?${queryString}`;
        }
    });

    // Inisiasi awal (Panggil data saat halaman dimuat)
    document.addEventListener('DOMContentLoaded', () => {
        // Panggil filter siswa untuk mengisi data awal
        document.getElementById('filter-siswa-form').dispatchEvent(new Event('submit'));
        
        // Panggil filter guru untuk mengisi data awal
        document.getElementById('filter-guru-form').dispatchEvent(new Event('submit'));
    });

</script>

<style>
    /* Styles Anda */
    .bg-gradient-primary-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        position: relative;
        overflow: hidden;
    }
    .bg-gradient-primary-modern::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: pulse 15s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }
    .icon-badge {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    .tab-navigation-modern {
        background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
        border-bottom: 1px solid #e9ecef;
    }
    .nav-pills .nav-link {
        border-radius: 12px;
        padding: 1rem 1.5rem;
        background: white;
        border: 2px solid #e9ecef;
        color: #6c757d;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .nav-pills .nav-link:hover {
        background: #f8f9fa;
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    .icon-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card-hover {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease forwards;
        opacity: 0;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .spin { animation: spin 1s linear infinite; }
    .table tbody tr { transition: all 0.2s ease; }
    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
        transform: scale(1.01);
    }
    .text-white-75 { color: rgba(255, 255, 255, 0.85); }
    @media (max-width: 768px) {
        .icon-badge { width: 50px; height: 50px; }
        .nav-pills .nav-link { padding: 0.75rem 1rem; font-size: 0.875rem; }
        .stats-mini { display: block !important; }
        .stats-mini > div { display: block !important; margin-bottom: 0.5rem; }
    }
</style>
@endpush