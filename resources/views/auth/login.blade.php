<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #1e3a8a, #0f172a, #475569);
      background-size: 200% 200%;
      animation: gradientMove 8s ease infinite;
    }

    @keyframes gradientMove {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .login-card {
      width: 400px;
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(16px);
      border-radius: 20px;
      padding: 2.5rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
      color: white;
      animation: fadeIn 1s ease forwards;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .logo {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .logo i {
      font-size: 3.2rem;
      color: #38bdf8;
      padding: 18px;
      border-radius: 50%;
      border: 2px solid rgba(255, 255, 255, 0.2);
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(12px);
    }

    .login-card h2 {
      font-weight: 700;
      font-size: 1.6rem;
      text-align: center;
      margin-bottom: .3rem;
    }

    .login-card p {
      font-size: .9rem;
      text-align: center;
      color: rgba(255,255,255,0.8);
      margin-bottom: 2rem;
    }

    .form-control {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.25);
      color: white;
      padding: 0.9rem 1rem 0.9rem 2.7rem;
      border-radius: 12px;
      transition: 0.3s;
    }
    
    .form-control::placeholder { /* Style untuk placeholder */
      color: rgba(255, 255, 255, 0.5);
      opacity: 1;
    }

    .form-control:focus {
      background: rgba(255, 255, 255, 0.15);
      border-color: #38bdf8;
      box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.4);
    }

    .input-group {
      position: relative;
    }

    .input-group i {
      position: absolute;
      top: 50%;
      left: 0.9rem;
      transform: translateY(-50%);
      color: rgba(255,255,255,0.7);
      font-size: 1.2rem;
    }

    .btn-login {
      background: linear-gradient(135deg, #2563eb, #06b6d4);
      border: none;
      border-radius: 12px;
      padding: 0.9rem;
      font-weight: 600;
      color: white;
      font-size: 1rem;
      transition: all 0.3s ease;
      margin-top: 1.5rem; /* Beri jarak lebih dari input terakhir */
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(6, 182, 212, 0.35);
    }

    .btn-login i {
      margin-right: .4rem;
    }

    .error-message {
      color: #f87171;
      font-size: 0.85rem;
      margin-top: 0.5rem; /* Jarak dari input field */
      padding-left: 0.2rem;
    }
    
    /* Style untuk error umum di atas form */
    .general-error {
        background: rgba(248, 113, 113, 0.2);
        border: 1px solid rgba(248, 113, 113, 0.5);
        color: #f87171;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        font-size: 0.9rem;
        text-align: center;
        margin-bottom: 1.5rem;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="logo">
      <i class="bi bi-mortarboard-fill"></i>
    </div>
    <h2>Portal SMA</h2>
    <p>Sistem Administrasi Sekolah</p>

    <form method="POST" action="{{ route('admin.login.post') }}">
      @csrf

      @if(session('loginError'))
          <div class="general-error">
              {{ session('loginError') }}
          </div>
      @endif


      <div class="mb-3">
        <div class="input-group">
          <i class="bi bi-person-fill"></i>
          <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="Username" required value="{{ old('username') }}">
        </div>
        @error('username')
            <div class="error-message">{{ $message }}</div>
        @enderror
      </div>

      <div class="mb-3">
        <div class="input-group">
          <i class="bi bi-lock-fill"></i>
          <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        @error('password')
          <div class="error-message">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="btn btn-login w-100">
        <i class="bi bi-box-arrow-in-right"></i> Masuk
      </button>
    </form>
  </div>
</body>
</html> 