@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-primary">QR Code Absensi</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Kembali
        </a>
    </div>

    @if(isset($qr_html))
    <div class="card border-0 shadow-sm text-center p-4">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-3">Scan QR Code Ini untuk Absen {{ $status ?? 'Datang' }}</h5>
            
            <p class="text-muted">QR code ini akan kedaluwarsa dalam <span id="countdown" class="fw-bold text-danger"></span>.</p>

            <div class="d-flex justify-content-center my-4">
                <img src="{{ $qr_html }}" alt="QR Code Absensi" class="img-fluid" style="width: 250px; height: 250px;">
            </div>

            <p class="fw-bold text-uppercase mb-1">Token:</p>
            <p class="small text-muted">{{ $token }}</p>

            <button class="btn btn-sm btn-outline-secondary" onclick="copyToken()">
                <i class="bi bi-clipboard"></i> Salin Token
            </button>
        </div>
    </div>
    @else
    <div class="alert alert-danger text-center">
        <i class="bi bi-exclamation-triangle me-2"></i> Gagal memuat QR Code. Pastikan Anda mengakses halaman ini dengan benar.
    </div>
    @endif
</div>

<script>
    const expiredAt = new Date("{{ $expiredAt->toISOString() }}");
    const countdownElement = document.getElementById('countdown');
    const currentStatus = "{{ $status }}"; // Ambil status dari PHP

    function updateCountdown() {
        const now = new Date();
        const timeLeft = expiredAt.getTime() - now.getTime();
        
        if (timeLeft <= 0) {
            countdownElement.textContent = "QR Code sudah kedaluwarsa.";
            clearInterval(timerInterval);
            
            // Logika untuk mengarahkan ulang secara otomatis
            setTimeout(() => {
                // Buat URL redirect yang baru dengan status dan durasi yang sama
                const redirectUrl = "{{ route('admin.absensi.qr', ['status' => ':status']) }}".replace(':status', currentStatus);
                window.location.href = redirectUrl;
            }, 3000); // Tunggu 3 detik sebelum mengarahkan ulang
            
        } else {
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
            countdownElement.textContent = `${minutes}m ${seconds}s`;
        }
    }

    updateCountdown();
    const timerInterval = setInterval(updateCountdown, 1000);

    function copyToken() {
        const tokenText = "{{ $token }}";
        navigator.clipboard.writeText(tokenText).then(() => {
            alert('Token berhasil disalin!');
        });
    }
</script>
@endsection