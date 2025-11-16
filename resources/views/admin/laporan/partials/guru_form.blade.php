<!-- resources/views/admin/laporan/partials/guru_form.blade.php -->

<div class="row">
    <!-- Filter Form Section -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-gradient-success text-white border-0">
                <h6 class="mb-0"><i class="bi bi-funnel-fill me-2"></i>Filter Laporan</h6>
            </div>
            <div class="card-body">
                <form id="filter-guru-form">
                    <!-- Periode Tanggal -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">
                            <i class="bi bi-calendar-range me-1"></i>Periode
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" class="form-control form-control-sm" name="tanggal_mulai" placeholder="Dari">
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control form-control-sm" name="tanggal_akhir" placeholder="Sampai">
                            </div>
                        </div>
                    </div>

                    <!-- Nama Guru -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">
                            <i class="bi bi-person-badge me-1"></i>Nama Guru
                        </label>
                        <input type="text" class="form-control form-control-sm" name="guru_nama" placeholder="Cari nama guru...">
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">
                            <i class="bi bi-check-circle me-1"></i>Status Kehadiran
                        </label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">Semua Status</option>
                            <option value="Hadir">Hadir</option>
                            <option value="Terlambat">Terlambat</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpha">Alpha</option>
                        </select>
                    </div>

                    <!-- Jam Ajar Minimum -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">
                            <i class="bi bi-clock-history me-1"></i>Min. Jam Ajar
                        </label>
                        <input type="number" class="form-control form-control-sm" name="min_jam_ajar" placeholder="0" step="0.5" min="0">
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-search me-1"></i>Tampilkan Data
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mt-3 g-2">
            <div class="col-6">
                <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                    <div class="card-body p-3 text-center">
                        <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                        <h3 class="mb-0 fw-bold text-success" id="stat-hadir-guru">-</h3>
                        <small class="text-muted">Hadir</small>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                    <div class="card-body p-3 text-center">
                        <i class="bi bi-x-circle-fill text-danger fs-3 d-block mb-2"></i>
                        <h3 class="mb-0 fw-bold text-danger" id="stat-tidak-hadir-guru">-</h3>
                        <small class="text-muted">Tidak Hadir</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card border-0 shadow-sm mt-3 bg-light">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="icon-box bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="bi bi-lightbulb-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 fw-bold">Info</h6>
                        <small class="text-muted">Data absensi guru dihitung per hari dengan total jam mengajar.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-1 fw-bold text-dark"><i class="bi bi-table me-2"></i>Data Absensi Guru</h6>
                    <small class="text-muted" id="total-data-info-guru">Gunakan filter untuk menampilkan data</small>
                </div>
                <button id="export-excel-btn-guru" class="btn btn-success btn-sm" disabled data-filter="{}">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px;">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="text-center small fw-semibold">Tanggal</th>
                                <th class="small fw-semibold">Nama Guru</th>
                                <th class="text-center small fw-semibold">Status</th>
                                <th class="text-center small fw-semibold">Jam Datang</th>
                                <th class="text-center small fw-semibold">Jam Pulang</th>
                                <th class="text-center small fw-semibold">Total Jam Ajar</th>
                                <th class="small fw-semibold">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="laporan-data-body-guru">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                    <p class="mb-0">Silakan pilih filter dan klik "Tampilkan Data"</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.icon-box {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
}

.table thead.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

.table-hover tbody tr:hover {
    background-color: rgba(17, 153, 142, 0.05);
    cursor: pointer;
}

.form-label {
    margin-bottom: 0.5rem;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
}
</style>