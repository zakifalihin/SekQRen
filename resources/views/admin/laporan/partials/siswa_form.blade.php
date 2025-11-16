<form id="filter-siswa-form" class="row g-3 mb-4 p-3 border rounded-3 bg-light">
    <!-- Kontrol Filter Siswa -->
    <div class="col-md-3">
        <label for="start_date_siswa" class="form-label fw-bold">Dari Tanggal</label>
        <input type="date" class="form-control rounded-pill" id="start_date_siswa" name="start_date" value="{{ date('Y-m-d', strtotime('-1 month')) }}" required>
    </div>
    <div class="col-md-3">
        <label for="end_date_siswa" class="form-label fw-bold">Sampai Tanggal</label>
        <input type="date" class="form-control rounded-pill" id="end_date_siswa" name="end_date" value="{{ date('Y-m-d') }}" required>
    </div>
    <div class="col-md-2">
        <label for="kelas_id_laporan" class="form-label fw-bold">Filter Kelas</label>
        <select class="form-select rounded-pill" id="kelas_id_laporan" name="kelas_id">
            <option value="">-- Semua Kelas --</option>
            @foreach($kelas as $k)
                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label for="mapel_id_laporan" class="form-label fw-bold">Filter Mapel</label>
        <select class="form-select rounded-pill" id="mapel_id_laporan" name="mapel_id">
            <option value="">-- Semua Mapel --</option>
            @foreach($mapel as $m)
                <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100 me-2 rounded-pill"><i class="bi bi-search"></i> Lihat Data</button>
    </div>
</form>

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="fs-6 text-muted" id="total-data-info-siswa"></span>
    <button type="button" class="btn btn-success rounded-pill" id="export-excel-btn-siswa" disabled><i class="bi bi-file-earmark-excel"></i> Export Siswa</button>
</div>

<!-- Tabel Siswa -->
<div class="table-responsive">
    <table class="table table-striped table-hover" id="laporanSiswaTable" width="100%" cellspacing="0">
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
        <tbody id="laporan-data-body-siswa">
            <tr><td colspan="9" class="text-center text-muted">Silakan atur filter dan klik 'Lihat Data'.</td></tr>
        </tbody>
    </table>
</div>