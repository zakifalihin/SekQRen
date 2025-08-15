<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Panel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
        }
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: #212529;
            color: #adb5bd;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 1rem;
            transition: all 0.3s;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            transition: all 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #343a40;
            color: white;
        }
        .sidebar .logo {
            text-align: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid #495057;
            margin-bottom: 1rem;
        }
        .sidebar .logo h4 {
            color: white;
        }
        .content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: all 0.3s;
        }
        .card-elev {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
            border-radius: 1rem;
        }
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            .sidebar.active {
                left: 0;
            }
            .content {
                margin-left: 0;
                padding: 1rem;
            }
            .navbar-toggler {
                display: block;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <h4>Admin Panel</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.guru.index') ? 'active' : '' }}" href="{{ route('admin.guru.index') }}">
                    <i class="bi bi-person-video me-2"></i> Kelola Guru
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.siswa.index') ? 'active' : '' }}" href="{{ route('admin.siswa.index') }}">
                    <i class="bi bi-person-fill-gear me-2"></i> Kelola Siswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.kelas.index') ? 'active' : '' }}" href="{{ route('admin.kelas.index') }}">
                    <i class="bi bi-house-door me-2"></i> Kelas
                </a>
            </li>
        </ul>
        <form action="{{ route('admin.logout') }}" method="POST" class="mt-auto p-3">
            @csrf
            <button class="btn btn-danger w-100">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </button>
        </form>
    </div>

    <!-- Main Content -->
    <div class="content" id="main-content">
        @yield('content')
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>