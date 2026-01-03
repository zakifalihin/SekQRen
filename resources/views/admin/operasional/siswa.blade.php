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
        <form id="filter-form" class="row g-2 mb-4 p-3 border rounded-3 bg-light shadow-sm align-items-end">
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

        <div class="d-flex align-items-center">
        <label class="me-2 small fw-bold">Tampilkan</label>
        <select id="entry-select" class="form-select form-select-sm rounded-pill" style="width: 80px;">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
        <label class="ms-2 small fw-bold">entri</label>
    </div>
        <!-- Aksi Export -->
        <div class="d-flex justify-content-between align-items-center mb-3">
             <span class="fs-6 text-muted" id="total-data-info"></span>
             <button type="button" class="btn btn-success rounded-pill" id="export-excel-btn" disabled><i class="bi bi-file-earmark-excel"></i> Export Laporan</button>
        </div>
       

        <!-- Tabel Laporan -->
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover align-middle" id="absensiTable">
                <thead class="table-primary">
                    <tr>
                        <th onclick="sortBy('tanggal')" style="cursor:pointer">Tanggal <i class="bi bi-arrow-down-up small"></i></th>
                        <th onclick="sortBy('waktu_absen')" style="cursor:pointer">Waktu Scan <i class="bi bi-arrow-down-up small"></i></th>
                        <th>NISN</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th onclick="sortBy('status')" style="cursor:pointer" class="text-center">Status <i class="bi bi-arrow-down-up small"></i></th>
                        <th>Guru PJ</th>
                    </tr>
                </thead>
                <tbody id="absensi-data-body">
            </tbody>
        </table>
    </div>
    <div id="pagination-container" class="d-flex justify-content-center mt-3"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const API_URL_LAPORAN = '{{ route("admin.laporan.siswa.data") }}';
    const API_URL_EXPORT = '{{ route("admin.laporan.siswa.export") }}';

    // State Global
    let allData = [];
    let currentPage = 1;
    let rowsPerPage = 10; // State untuk kontrol entry
    let currentParams = {
        sort_by: 'tanggal',
        order: 'desc'
    };

    // 1. Fungsi Fetch Data (Ajax)
    async function fetchData(params = {}) {
        const body = document.getElementById('absensi-data-body');
        const exportBtn = document.getElementById('export-excel-btn');
        const totalInfo = document.getElementById('total-data-info');
        
        const filterForm = document.getElementById('filter-form');
        const formData = new FormData(filterForm);
        const formParams = Object.fromEntries(formData.entries());
        
        currentParams = { ...currentParams, ...formParams, ...params };
        
        body.innerHTML = '<tr><td colspan="9" class="text-center py-5"><i class="bi bi-arrow-repeat spin fs-2 text-primary"></i><br>Memproses data...</td></tr>';

        try {
            const queryString = new URLSearchParams(currentParams).toString();
            const response = await fetch(`${API_URL_LAPORAN}?${queryString}`, {
                headers: { 'Accept': 'application/json' }
            });

            const json = await response.json();
            allData = json.data || []; 
            currentPage = 1; // Reset ke halaman 1 setiap ada filter/sort baru
            
            renderDisplay(); // Panggil fungsi render
            
            totalInfo.textContent = `Total Data: ${allData.length} entri`;
            exportBtn.disabled = allData.length === 0;

        } catch (error) {
            console.error(error);
            body.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Gagal memuat data.</td></tr>';
        }
    }

    // 2. Fungsi Render Tabel (Logika Entry & Slicing)
    function renderDisplay() {
        const body = document.getElementById('absensi-data-body');
        body.innerHTML = '';

        if (allData.length === 0) {
            body.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">Data tidak ditemukan.</td></tr>';
            renderPagination(0);
            return;
        }

        // --- BAGIAN INI YANG MENGATUR ENTRY DATA ---
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + parseInt(rowsPerPage);
        const paginatedData = allData.slice(start, end);

        paginatedData.forEach(item => {
            const row = body.insertRow();
            row.innerHTML = `
                <td>${item.tanggal}</td>
                <td>${item.waktu_scan}</td>
                <td><code>${item.siswa.nisn}</code></td>
                <td class="fw-bold">${item.siswa.nama}</td>
                <td>${item.kelas}</td>
                <td>${item.mata_pelajaran}</td>
                <td class="text-center">
                    <span class="badge ${item.status === 'Hadir' ? 'bg-success' : (item.status === 'Alpha' ? 'bg-danger' : 'bg-warning text-dark')} px-3 rounded-pill">
                        ${item.status}
                    </span>
                </td>
                <td>${item.guru_pj}</td>
            `;
        });

        renderPagination(allData.length);
    }

    // 3. Fungsi Pagination (Tombol Prev & Next)
    function renderPagination(totalItems) {
        const container = document.getElementById('pagination-container');
        const totalPages = Math.ceil(totalItems / rowsPerPage);
        
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        container.innerHTML = `
            <nav>
                <ul class="pagination shadow-sm">
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link px-3" href="javascript:void(0)" onclick="changePage(${currentPage - 1})">
                            <i class="bi bi-chevron-left"></i> Prev
                        </a>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link bg-light text-dark px-4">Hal ${currentPage} dari ${totalPages}</span>
                    </li>
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link px-3" href="javascript:void(0)" onclick="changePage(${currentPage + 1})">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        `;
    }

    function changePage(page) {
        currentPage = page;
        renderDisplay();
        document.getElementById('absensiTable').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // 4. Fungsi Sorting
    function sortBy(field) {
        const order = (currentParams.sort_by === field && currentParams.order === 'asc') ? 'desc' : 'asc';
        fetchData({ sort_by: field, order: order });
    }

    // 5. Event Listeners (Control Entry)
    document.getElementById('entry-select').addEventListener('change', function() {
        rowsPerPage = parseInt(this.value); // Update nilai entry
        currentPage = 1; // Kembali ke halaman awal
        renderDisplay(); // Render ulang tabel dengan jumlah entry baru
    });

    document.getElementById('filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        fetchData();
    });

    document.getElementById('export-excel-btn').addEventListener('click', function() {
        const queryString = new URLSearchParams(currentParams).toString();
        window.location.href = `${API_URL_EXPORT}?${queryString}`;
    });

    document.addEventListener('DOMContentLoaded', () => fetchData());
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

    .pagination .page-link {
    border-radius: 20px !important;
    margin: 0 5px;
    font-size: 0.85rem;
    font-weight: 600;
}
.page-item.disabled .page-link {
    background-color: #f8f9fa;
    color: #6c757d;
}
#entry-select {
    cursor: pointer;
    border-color: #6366f1;
}
/* Style untuk header yang bisa di-sort */
th[onclick] {
    user-select: none;
    transition: background 0.2s;
}
th[onclick]:hover {
    background-color: rgba(0,0,0,0.05);
}
</style>
@endpush