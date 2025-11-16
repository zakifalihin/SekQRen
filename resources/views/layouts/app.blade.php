<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 280px;
            --primary: #6366f1;
            --accent: #06b6d4;
            --danger: #ef4444;
            --bg-1: #ffffff;
            --bg-2: #f8fafc;
            --bg-3: #f1f5f9;
            --text-1: #0f172a;
            --text-2: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px 0 rgba(163, 92, 92, 0.1), 0 1px 2px 0 rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
        }

        [data-theme="dark"] {
            --bg-1: #0f172a;
            --bg-2: #1e293b;
            --bg-3: #334155;
            --text-1: #f8fafc;
            --text-2: #cbd5e0;
            --border: #334155;
            --shadow: 0 1px 3px 0 rgba(0,0,0,0.3), 0 1px 2px 0 rgba(0,0,0,0.2);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.3), 0 4px 6px -2px rgba(0,0,0,0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-2);
            color: var(--text-1);
            overflow-x: hidden;
            transition: all 0.3s ease;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--bg-1);
            border-right: 1px solid var(--border);
            z-index: 1000;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            position: relative;
            overflow: hidden;
        }

        .sidebar-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .sidebar-header h4 {
            font-weight: 700;
            font-size: 1.25rem;
            position: relative;
            z-index: 2;
        }

        .sidebar-header .subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-top: 0.25rem;
            position: relative;
            z-index: 2;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
        }

        .nav-item { margin-bottom: 0.25rem; }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            color: var(--text-2);
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            transform: translateX(4px);
            box-shadow: var(--shadow-lg);
        }

        .nav-link:hover::before, .nav-link.active::before { opacity: 1; }

        .nav-link i {
            font-size: 1.125rem;
            margin-right: 0.875rem;
            position: relative;
            z-index: 2;
            min-width: 20px;
        }

        .nav-link span { position: relative; z-index: 2; }

        .dropdown-toggle-custom { justify-content: space-between !important; }

        .chevron {
            transition: transform 0.2s ease;
            position: relative;
            z-index: 2;
        }

        .dropdown-toggle-custom[aria-expanded="true"] .chevron { transform: rotate(180deg); }

        .collapse .nav-link {
            padding-left: 3rem;
            font-size: 0.8125rem;
            margin-bottom: 0.125rem;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--border);
            background: var(--bg-3);
        }

        .theme-toggle, .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .theme-toggle {
            border: none;
            background: var(--bg-1);
            color: var(--text-1);
            margin-bottom: 0.75rem;
            box-shadow: var(--shadow);
        }

        .theme-toggle:hover { transform: translateY(-1px); box-shadow: var(--shadow-lg); }

        .logout-btn {
            border: 2px solid var(--danger);
            background: transparent;
            color: var(--danger);
        }

        .logout-btn:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-1px);
        }

        .content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: var(--bg-2);
            transition: all 0.3s ease;
        }

        .content-wrapper { padding: 2rem; }

        .top-navbar {
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            background: var(--bg-1);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .menu-toggle, .mobile-theme-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 0.5rem;
            background: var(--bg-3);
            color: var(--text-1);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .menu-toggle:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.05);
        }

        .mobile-theme-toggle:hover {
            background: var(--accent);
            color: white;
            transform: scale(1.05);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--text-1);
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            display: inline-block;
            margin-right: 0.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        @media (max-width: 991.98px) {
            .sidebar { left: calc(-1 * var(--sidebar-width)); }
            .sidebar.active { left: 0; box-shadow: var(--shadow-lg); }
            .content { margin-left: 0; }
            .content-wrapper { padding: 1rem; }
            .top-navbar { display: flex; }
            
            .overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(2px);
                z-index: 900;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            body.sidebar-open .overlay {
                opacity: 1;
                visibility: visible;
            }
        }

        * { transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }
    </style>
</head>
<body data-theme="light">
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="status-indicator"></div>
            <h4>Admin Panel</h4>
            <div class="subtitle">Management System</div>
        </div>
        
    <nav class="sidebar-nav">
    <ul class="nav flex-column">
        
        <!-- DASHBOARD -->
        <li class="nav-item">
            <a class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- MODUL 1: KELOLA DATA MASTER -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle-custom {{ Route::is(['admin.guru.*', 'admin.siswa.*', 'admin.kelas.*', 'admin.mapel.*']) ? 'active' : '' }}" 
               data-bs-toggle="collapse" 
               href="#dataDropdown" 
               role="button" 
               aria-expanded="{{ Route::is(['admin.guru.*', 'admin.siswa.*', 'admin.kelas.*', 'admin.mapel.*']) ? 'true' : 'false' }}">
                <div class="d-flex align-items-center">
                    <i class="bi bi-database-fill"></i>
                    <span>Data Master</span>
                </div>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse {{ Route::is(['admin.guru.*', 'admin.siswa.*', 'admin.kelas.*', 'admin.mapel.*']) ? 'show' : '' }}" id="dataDropdown">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.guru.*') ? 'active' : '' }}" href="{{ route('admin.guru.index') }}">
                            <i class="bi bi-person-badge-fill"></i>
                            <span>Guru</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.siswa.*') ? 'active' : '' }}" href="{{ route('admin.siswa.index') }}">
                            <i class="bi bi-people-fill"></i>
                            <span>Siswa</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.kelas.*') ? 'active' : '' }}" href="{{ route('admin.kelas.index') }}">
                            <i class="bi bi-building"></i>
                            <span>Kelas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.mapel.*') ? 'active' : '' }}" href="{{ route('admin.mapel.index') }}">
                            <i class="bi bi-book-fill"></i>
                            <span>Mata Pelajaran</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- MODUL 2: OPERASIONAL & ABSENSI -->
        <!-- Menggunakan route grouping 'admin.jadwal.*', 'admin.absensi.*' -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle-custom {{ Route::is(['admin.jadwal.*', 'admin.absensi.*']) ? 'active' : '' }}" 
               data-bs-toggle="collapse" 
               href="#operasionalDropdown" 
               role="button" 
               aria-expanded="{{ Route::is(['admin.jadwal.*', 'admin.absensi.*']) ? 'true' : 'false' }}">
                <div class="d-flex align-items-center">
                    <i class="bi bi-clock-history"></i>
                    <span>Operasional Absensi</span>
                </div>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse {{ Route::is(['admin.jadwal.*', 'admin.absensi.*']) ? 'show' : '' }}" id="operasionalDropdown">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <!-- Menggunakan Route: admin.absensi.hariini -->
                        <a class="nav-link {{ Route::is('admin.absensi.guru') ? 'active' : '' }}" href="{{ route('admin.absensi.guru') }}">
                            <i class="bi bi-person-check"></i>
                            <span>Absensi Guru</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <!-- Menggunakan Route: admin.absensi.siswa -->
                        <a class="nav-link {{ Route::is('admin.absensi.siswa') ? 'active' : '' }}" href="{{ route('admin.absensi.siswa') }}">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>Absensi Siswa</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- MODUL 3: PELAPORAN & EXPORT -->
        <li class="nav-item">
            <!-- Menggunakan Route: admin.laporan.index -->
            <a class="nav-link {{ Route::is('admin.laporan.index') ? 'active' : '' }}" href="{{ route('admin.laporan.index') }}">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                <span>Laporan & Export</span>
            </a>
        </li>
        
    </ul>
</nav>
        <div class="sidebar-footer">
            <button class="theme-toggle" id="theme-toggle">
                <i class="bi bi-moon-stars-fill me-2"></i>
                <span>Toggle Theme</span>
            </button>
            <form action="{{ route('admin.logout') }}" method="POST" class="w-100">
                @csrf
                <button class="logout-btn" type="submit">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
    
    <div class="overlay" id="overlay"></div>

    <nav class="top-navbar" id="top-navbar">
        <button class="menu-toggle" type="button" id="sidebar-toggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="brand-logo">
            <i class="bi bi-shield-check me-2"></i>
            Admin Panel
        </div>
        <button class="mobile-theme-toggle" id="theme-toggle-mobile">
            <i class="bi bi-moon-stars-fill"></i>
        </button>
    </nav>

    <div class="content" id="main-content">
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const body = document.body;

        document.getElementById('sidebar-toggle').addEventListener('click', () => {
            sidebar.classList.toggle('active');
            body.classList.toggle('sidebar-open');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            body.classList.remove('sidebar-open');
        });

        function toggleTheme() {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme');
            const newTheme = current === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            document.querySelectorAll('#theme-toggle i, #theme-toggle-mobile i').forEach(icon => {
                icon.className = newTheme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
            });
        }

        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        
        if (savedTheme === 'dark') {
            document.querySelectorAll('#theme-toggle i, #theme-toggle-mobile i').forEach(icon => {
                icon.className = 'bi bi-sun-fill';
            });
        }

        document.getElementById('theme-toggle').addEventListener('click', toggleTheme);
        document.getElementById('theme-toggle-mobile').addEventListener('click', toggleTheme);

        document.querySelectorAll('.nav-link:not([data-bs-toggle])').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 991.98) {
                    sidebar.classList.remove('active');
                    body.classList.remove('sidebar-open');
                }
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 991.98) {
                sidebar.classList.remove('active');
                body.classList.remove('sidebar-open');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>