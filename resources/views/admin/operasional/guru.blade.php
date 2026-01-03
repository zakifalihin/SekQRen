@extends('layouts.app')

@section('title', 'Status Absensi Guru')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-gradient-primary">
        <h5 class="m-0 font-weight-bold text-white">
            <i class="bi bi-person-check me-2"></i>Monitoring Absensi Guru Harian
        </h5>
        <p class="text-white-50 mb-0">
            Lihat status kehadiran harian, jam datang/pulang, dan total jam ajar guru.
        </p>
    </div>

    <div class="card-body">

        <form id="filter-form" class="row g-3 mb-4 p-3 border rounded-3 bg-light">
            <div class="col-md-3">
                <label class="form-label fw-bold">Dari Tanggal</label>
                <input type="date" 
                       class="form-control rounded-pill" 
                       id="start_date" name="start_date" 
                       value="{{ date('Y-m-d', strtotime('-1 month')) }}"
                       required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Sampai Tanggal</label>
                <input type="date" 
                       class="form-control rounded-pill" 
                       id="end_date" name="end_date" 
                       value="{{ date('Y-m-d') }}"
                       required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Filter Guru</label>
                <select class="form-select rounded-pill" id="guru_id" name="guru_id">
                    <option value="">-- Semua Guru --</option>
                    @foreach($guru as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 rounded-pill">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
            </div>
        </form>

        <hr>
        <div class="d-flex align-items-center">
            <label class="me-2 small fw-bold">Tampilkan</label>
            <select id="entry-select" class="form-select form-select-sm rounded-pill" style="width: 80px;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <label class="ms-2 small fw-bold">entri</label>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span id="total-data-info" class="text-muted"></span>
            <button class="btn btn-success rounded-pill" id="export-excel-btn" disabled>
                <i class="bi bi-file-earmark-excel"></i> Export Laporan
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Guru</th>
                        <th>Status</th>
                        <th>Jam Datang</th>
                        <th>Jam Pulang</th>
                        <th>Total Jam Ajar</th>
                    </tr>
                </thead>
                <tbody id="absensi-data-body">
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            Silakan atur filter lalu klik "Filter"
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection


@push('scripts')
<script>

// State Global untuk Pagination
let allData = [];
let currentPage = 1;
let rowsPerPage = 10;

/* =============================
   ROUTE (WEB)
   ============================= */
const API_URL_LAPORAN_GURU = '{{ route("admin.laporan.guru.data") }}';
const API_URL_EXPORT_GURU  = '{{ route("admin.laporan.guru.export") }}';


/* =============================
   FETCH DATA (TANPA SORTING FRONTEND)
   ============================= */
async function fetchData(params) {
    const body       = document.getElementById('absensi-data-body');
    const totalInfo  = document.getElementById('total-data-info');
    const exportBtn  = document.getElementById('export-excel-btn');

    body.innerHTML = `
        <tr>
            <td colspan="7" class="text-center">
                <i class="bi bi-arrow-repeat spin me-1"></i> Mengambil data...
            </td>
        </tr>`;

    exportBtn.disabled = true;

    const url = API_URL_LAPORAN_GURU + "?" + new URLSearchParams(params);

    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        
        const result = await res.json();
        const data = result.data; // Data SUDAH difilter dan diurutkan dari Backend

        /* =============================
           TIDAK ADA LOGIC SORTING DI SINI
           ============================= */
        
        renderTable(data);

        totalInfo.textContent = `Total Data: ${data.length} entri`;
        exportBtn.disabled = data.length === 0;

    } catch (e) {
        body.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error: ${e.message}</td></tr>`;
        totalInfo.textContent = "Total Data: 0";
        exportBtn.disabled = true;
    }
}


/* =============================
   RENDER TABLE
   ============================= */
function renderTable(data) {
    const body = document.getElementById('absensi-data-body');
    body.innerHTML = "";

    if (data.length === 0) {
        body.innerHTML = `
            <tr><td colspan="7" class="text-center text-muted">
                Tidak ada data pada rentang waktu ini.
            </td></tr>`;
        return;
    }

    data.forEach(item => {
        const row = body.insertRow();
        row.insertCell().textContent = item.tanggal;
        row.insertCell().textContent = item.guru_nama;

        let color = "bg-danger";
        if (item.status === "Hadir") color = "bg-success";
        else if (item.status === "Terlambat") color = "bg-warning text-dark";

        row.insertCell().innerHTML = `<span class="badge ${color} rounded-pill">${item.status}</span>`;
        row.insertCell().textContent = item.jam_datang ?? "-";
        row.insertCell().textContent = item.jam_pulang ?? "-";
        row.insertCell().innerHTML = `<span class="badge bg-secondary text-white rounded-pill">${item.total_jam_ajar ?? 0} Jam</span>`;
    });
}


/* =============================
   AUTO FILTER REAL-TIME
   ============================= */
function autoFilter(e) {
    // Mencegah submit form bawaan HTML
    if (e) e.preventDefault(); 
    
    const params = {
        start_date: document.getElementById('start_date').value,
        end_date:   document.getElementById('end_date').value,
        guru_id:    document.getElementById('guru_id').value,
    };

    fetchData(params);

    document.getElementById('export-excel-btn')
        .dataset.filter = JSON.stringify(params);
}

// Event Listeners untuk Filter
document.getElementById('filter-form').addEventListener('submit', autoFilter);
document.getElementById('start_date').addEventListener('change', autoFilter);
document.getElementById('end_date').addEventListener('change', autoFilter);
document.getElementById('guru_id').addEventListener('change', autoFilter);


/* =============================
   EXPORT BUTTON
   ============================= */
document.getElementById('export-excel-btn').addEventListener('click', function () {
    try {
        const filterData = this.dataset.filter ? JSON.parse(this.dataset.filter) : {};
        const q = new URLSearchParams(filterData).toString();
        window.location.href = `${API_URL_EXPORT_GURU}?${q}`;
    } catch (e) {
        alert("Gagal membaca filter data untuk Export.");
    }
});


/* =============================
   LOAD DEFAULT
   ============================= */
document.addEventListener('DOMContentLoaded', autoFilter);
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
    /* Menggunakan warna yang lebih modern/flat */
    background: linear-gradient(90deg, #4f46e5 0%, #06b6d4 100%); 
}
</style>
@endpush