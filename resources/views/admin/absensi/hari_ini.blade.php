@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="fw-bold text-primary mb-4">📅 Absensi Guru Hari Ini</h1>
    <p class="text-muted">Tanggal: {{ \Carbon\Carbon::parse($today)->translatedFormat('d F Y') }}</p>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Guru</th>
                        <th>Status</th>
                        <th>Jam Datang</th>
                        <th>Jam Pulang</th>
                        <th>Diperbarui</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['guru']->nama }}</td>
                            <td>
                                @if($item['status'] === 'Hadir')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i> Hadir
                                    </span>
                                @elseif($item['status'] === 'Pulang')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-box-arrow-right me-1"></i> Pulang
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i> Alpa
                                    </span>
                                @endif
                            </td>
                            <td>{{ $item['jam_datang'] ?? '-' }}</td>
                            <td>{{ $item['jam_pulang'] ?? '-' }}</td>
                            <td>{{ $item['updated_at'] ? $item['updated_at']->diffForHumans() : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada absensi hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
