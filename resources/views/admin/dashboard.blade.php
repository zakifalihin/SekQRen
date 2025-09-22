@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fw-bold text-primary">📊 Dashboard Admin</h1>
    </div>

    <div class="row g-4 mb-5">
        
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.guru.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm overflow-hidden dashboard-card">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center p-3 me-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-person-video2 fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted text-uppercase fw-bold mb-1">Total Guru</p>
                            <h2 class="fw-bold mb-0">{{ $totalGuru }}</h2>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.siswa.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm overflow-hidden dashboard-card">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center p-3 me-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted text-uppercase fw-bold mb-1">Total Siswa</p>
                            <h2 class="fw-bold mb-0">{{ $totalSiswa }}</h2>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.kelas.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm overflow-hidden dashboard-card">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center p-3 me-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-easel-fill fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted text-uppercase fw-bold mb-1">Total Kelas</p>
                            <h2 class="fw-bold mb-0">{{ $totalKelas }}</h2>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.absensi.hariini') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm overflow-hidden dashboard-card">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center p-3 me-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-calendar-check-fill fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted text-uppercase fw-bold mb-1">Absensi Hari Ini</p>
                            <h2 class="fw-bold mb-0">{{ $absensiHariIni }}</h2>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm dashboard-card h-100">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold mb-3">Pengaturan Absensi Guru</h5>
                    <p class="text-muted">Pilih status absensi dan Buat QR Code.</p>
                    <form action="{{ route('admin.absensi.qr') }}" method="GET" class="d-flex justify-content-center align-items-center flex-wrap gap-3 mt-4">
                        <div class="form-floating" style="width: 200px;">
                            <select class="form-select" id="status" name="status" required>
                                <option value="datang" selected>Jam Datang</option>
                                <option value="pulang">Jam Pulang</option>
                            </select>
                            <label for="status">Status</label>
                        </div>
                        <button type="submit" class="btn btn-lg btn-primary">
                            <i class="bi bi-qr-code-scan me-2"></i> Buat QR Absensi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm dashboard-card h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Aktivitas Terbaru</h5>
                    <ul class="list-group list-group-flush">
                        @forelse($aktivitas as $item)
                            <li class="list-group-item d-flex align-items-center">
                                @if($item->jam_datang && !$item->jam_pulang)
                                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                    <div>
                                        <p class="fw-bold mb-0">
                                            Guru '{{ $item->guru->nama ?? 'Unknown' }}' absen datang.
                                        </p>
                                        <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                                    </div>
                                @elseif($item->jam_pulang)
                                    <i class="bi bi-box-arrow-right text-primary me-3 fs-5"></i>
                                    <div>
                                        <p class="fw-bold mb-0">
                                            Guru '{{ $item->guru->nama ?? 'Unknown' }}' absen pulang.
                                        </p>
                                        <small class="text-muted">{{ $item->updated_at->diffForHumans() }}</small>
                                    </div>
                                @else
                                    <i class="bi bi-info-circle-fill text-info me-3 fs-5"></i>
                                    <div>
                                        <p class="fw-bold mb-0">
                                            Aktivitas tidak diketahui.
                                        </p>
                                        <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">
                                Belum ada aktivitas absensi.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dashboard-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 12px;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }
</style>
@endsection