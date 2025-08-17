<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #0d6efd;
            --dark-color: #1a202c;
            --text-light-color: #cbd5e0;
            --bg-light: #f8f9fa;
        }
        body {
            background-color: var(--bg-light);
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        /* Style Sidebar yang Diperbarui */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark-color);
            background: linear-gradient(180deg, #1a202c 0%, #2d3748 100%);
            color: var(--text-light-color);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 1.5rem 1rem;
            transition: all 0.3s ease-in-out;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            text-align: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #4a5568;
            margin-bottom: 1.5rem;
        }
        .sidebar-header h4 {
            color: white;
            font-weight: 600;
        }
        .sidebar-nav .nav-item {
            margin-bottom: 0.5rem;
        }
        .sidebar-nav .nav-link {
            color: var(--text-light-color);
            text-decoration: none;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
        }
        .sidebar-nav .nav-link:hover, .sidebar-nav .nav-link.active {
            background-color: #2d3748;
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .sidebar-nav .nav-link i {
            font-size: 1.25rem;
            margin-right: 12px;
        }
        .sidebar-footer {
            margin-top: auto;
            padding-top: 1.5rem;
        }
        .content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: margin-left 0.3s ease-in-out;
        }
        .top-navbar {
            display: none;
            background-color: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        @media (max-width: 991.98px) {
            .sidebar {
                left: -250px;
            }
            .sidebar.active {
                left: 0;
            }
            .content {
                margin-left: 0;
            }
            .top-navbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: sticky;
                top: 0;
                z-index: 999;
            }
            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 900;
                display: none;
            }
            body.sidebar-open .overlay {
                display: block;
            }
        }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4>Admin Panel</h4>
        </div>
        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.guru.index') ? 'active' : '' }}" href="{{ route('admin.guru.index') }}">
                    <i class="bi bi-person-video2"></i> Kelola Guru
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.siswa.index') ? 'active' : '' }}" href="{{ route('admin.siswa.index') }}">
                    <i class="bi bi-person-gear"></i> Kelola Siswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.kelas.index') ? 'active' : '' }}" href="{{ route('admin.kelas.index') }}">
                    <i class="bi bi-house-door"></i> Kelas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.absensi.qr') ? 'active' : '' }}" href="{{ route('admin.absensi.qr') }}">
                    <i class="bi bi-qr-code-scan"></i> QrCode
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button class="btn btn-outline-danger w-100 py-2">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </button>
            </form>
        </div>
    </div>
    
    <div class="overlay" id="overlay"></div>

    <nav class="top-navbar" id="top-navbar">
        <button class="navbar-toggler btn" type="button" id="sidebar-toggle">
            <span class="navbar-toggler-icon"></span>
        </button>
        <span class="h5 mb-0 fw-bold">Admin Panel</span>
    </nav>

    <div class="content" id="main-content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
        });
        document.getElementById('overlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('active');
            document.body.classList.remove('sidebar-open');
        });
    </script>
    @stack('scripts')
</body>
</html>