<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Panel Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts: Poppins untuk font yang lebih modern -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-gradient: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            --text-light: #e9ecef;
            --card-shadow: 0 0.75rem 1.5rem rgba(0,0,0,.15);
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e9ecef;
            overflow-x: hidden;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-gradient);
            color: var(--text-light);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 1.5rem 1rem;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            z-index: 1050;
        }
        .sidebar a {
            color: var(--text-light);
            text-decoration: none;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
            margin-bottom: 8px;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .sidebar .logo {
            text-align: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 1.5rem;
        }
        .sidebar .logo h4 {
            color: white;
            font-weight: 600;
        }
        .content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .card-elev {
            box-shadow: var(--card-shadow);
            border-radius: 1rem;
            border: none;
        }
        .card-title {
            font-weight: 600;
        }
        .btn {
            font-weight: 500;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            transition: background-color 0.3s;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .btn-toggler {
            background-color: #2c5364;
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.3s ease-in-out;
        }
        .btn-toggler:hover {
            transform: scale(1.1);
        }
        /* Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            .sidebar.active {
                left: 0;
            }
            .content {
                margin-left: 0;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" id="sidebar">
        <div class="logo">
            <h4>Panel Admin</h4>
        </div>
        <ul class="nav flex-column mb-auto">
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
        <form action="{{ route('admin.logout') }}" method="POST" class="mt-3">
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

    <!-- Tombol Toggler untuk mobile -->
    <button class="btn btn-toggler d-md-none position-fixed top-0 start-0 m-3" id="sidebar-toggler">
        <i class="bi bi-list fs-4"></i>
    </button>

    <!-- Bootstrap JS dan dependensi -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebar-toggler').addEventListener('click', () => {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        });
    </script>
    @stack('scripts')
</body>
</html>
