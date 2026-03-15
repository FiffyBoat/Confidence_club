<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel - Confidence Club Members</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --ccm-primary: #b00020;
            --ccm-primary-dark: #8f0019;
            --ccm-ink: #1c1c1c;
            --ccm-muted: #6f6f6f;
            --ccm-surface: #ffffff;
            --ccm-surface-alt: #fff2f5;
            --ccm-border: #ead7dc;
            --ccm-success: #2d6a4f;
            --ccm-warning: #c17d00;
            --ccm-accent: #f4b63d;
            --ccm-shadow: 0 20px 50px rgba(20, 4, 8, 0.08);
            --ccm-surface-soft: #fff9fb;

            --bs-primary: var(--ccm-primary);
            --bs-primary-rgb: 176, 0, 32;
            --bs-secondary: var(--ccm-ink);
            --bs-secondary-rgb: 28, 28, 28;
            --bs-success: var(--ccm-success);
            --bs-danger: var(--ccm-primary);
            --bs-link-color: #9b001c;
            --bs-link-hover-color: #7f0017;
        }

        body {
            font-family: 'Sora', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background:
                radial-gradient(1200px circle at 90% -10%, rgba(176, 0, 32, 0.18), transparent 45%),
                radial-gradient(900px circle at 10% 0%, rgba(244, 182, 61, 0.18), transparent 40%),
                linear-gradient(180deg, rgba(255, 250, 251, 0.96) 0%, rgba(255, 246, 248, 0.96) 45%, #fff6f8 100%);
            color: #161616;
            background-attachment: fixed;
        }

        .admin-shell {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .admin-sidebar {
            width: 260px;
            min-height: 100vh;
            height: 100vh;
            background: linear-gradient(160deg, #b00020 0%, #8a0018 60%, #630012 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 4px 0 30px rgba(0, 0, 0, 0.12);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            overflow: hidden;
        }

        .admin-nav {
            flex: 1;
            overflow-y: auto;
            padding-right: 0.25rem;
            margin-right: -0.25rem;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.35) transparent;
        }

        .admin-nav::-webkit-scrollbar {
            width: 6px;
        }

        .admin-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.35);
            border-radius: 999px;
        }

        .admin-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-badge {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            padding: 0.35rem 0.6rem;
            border-radius: 999px;
            font-size: 0.7rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .admin-link {
            display: block;
            color: #ececec;
            text-decoration: none;
            padding: 0.6rem 0.8rem;
            border-radius: 0.5rem;
            margin-bottom: 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.2s ease;
            position: relative;
        }

        .admin-link:hover {
            color: #fff;
            background: rgba(0, 0, 0, 0.22);
            transform: translateX(2px);
        }

        .admin-link:focus-visible {
            outline: 2px solid rgba(255, 255, 255, 0.7);
            outline-offset: 2px;
        }

        .admin-link.active {
            color: #fff;
            background: #1c1c1c;
            font-weight: 600;
        }

        .admin-link.active::before {
            content: '';
            position: absolute;
            left: -0.55rem;
            top: 0.55rem;
            bottom: 0.55rem;
            width: 0.25rem;
            background: var(--ccm-accent);
            border-radius: 999px;
        }

        .admin-link i {
            font-size: 1rem;
            opacity: 0.85;
        }

        .content-shell {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-meta {
            color: var(--ccm-muted);
        }

        .page-content > * {
            animation: fadeUp 0.4s ease both;
        }

        .page-content > *:nth-child(2) { animation-delay: 0.05s; }
        .page-content > *:nth-child(3) { animation-delay: 0.1s; }

        .card {
            border-radius: 1rem;
            border: 1px solid var(--ccm-border);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.06);
            background: var(--ccm-surface);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-header {
            background: var(--ccm-surface);
            border-bottom: 1px solid var(--ccm-border);
        }

        .stat-card {
            background: linear-gradient(140deg, #ffffff 0%, var(--ccm-surface-soft) 100%);
            border-radius: 1rem;
            border: 1px solid var(--ccm-border);
            padding: 1.25rem;
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.06);
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            width: 120px;
            height: 120px;
            right: -50px;
            top: -50px;
            background: radial-gradient(circle, rgba(244, 182, 61, 0.25), transparent 70%);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 22px 40px rgba(0, 0, 0, 0.12);
        }

        .stat-card .stat-label {
            color: var(--ccm-muted);
            font-size: 0.85rem;
        }

        .stat-card .stat-value {
            font-size: 1.4rem;
            font-weight: 600;
        }

        .stat-card .badge {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .btn-primary {
            --bs-btn-bg: var(--ccm-primary);
            --bs-btn-border-color: var(--ccm-primary);
            --bs-btn-hover-bg: #93001a;
            --bs-btn-hover-border-color: #93001a;
            --bs-btn-active-bg: #7d0016;
            --bs-btn-active-border-color: #7d0016;
            border-radius: 999px;
            font-weight: 600;
            box-shadow: 0 12px 24px rgba(176, 0, 32, 0.25);
        }

        .btn-outline-primary {
            --bs-btn-color: var(--ccm-primary);
            --bs-btn-border-color: var(--ccm-primary);
            --bs-btn-hover-bg: var(--ccm-primary);
            --bs-btn-hover-border-color: var(--ccm-primary);
            --bs-btn-active-bg: #93001a;
            --bs-btn-active-border-color: #93001a;
            border-radius: 999px;
        }

        .btn {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-success,
        .btn-info,
        .btn-warning,
        .btn-outline-secondary,
        .btn-outline-danger,
        .btn-danger {
            border-radius: 999px;
        }

        .page-hero {
            background: linear-gradient(120deg, #ffffff 0%, #fff1f4 55%, #fff8e6 100%);
            border: 1px solid var(--ccm-border);
            border-radius: 1.5rem;
            padding: 1.6rem 1.8rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--ccm-shadow);
        }

        .page-hero::after {
            content: '';
            position: absolute;
            width: 220px;
            height: 220px;
            right: -90px;
            top: -90px;
            background: radial-gradient(circle, rgba(176, 0, 32, 0.18), transparent 70%);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            background: rgba(176, 0, 32, 0.12);
            color: var(--ccm-primary-dark);
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .page-title {
            font-size: 1.9rem;
            font-weight: 700;
        }

        .page-subtitle {
            color: var(--ccm-muted);
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            border: 1px solid rgba(176, 0, 32, 0.2);
            background: #ffffff;
            color: var(--ccm-ink);
            font-size: 0.8rem;
        }

        .hero-icon {
            width: 72px;
            height: 72px;
            border-radius: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(176, 0, 32, 0.12);
            color: var(--ccm-primary);
            font-size: 1.9rem;
            box-shadow: 0 12px 25px rgba(176, 0, 32, 0.18);
            animation: floatUp 3s ease-in-out infinite;
        }

        @keyframes floatUp {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .form-control,
        .form-select {
            border-radius: 0.75rem;
            border-color: var(--ccm-border);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(176, 0, 32, 0.5);
            box-shadow: 0 0 0 0.2rem rgba(176, 0, 32, 0.15);
        }

        .badge.text-bg-secondary {
            background-color: #1c1c1c !important;
            color: #fff !important;
        }

        .badge.text-bg-success {
            background-color: var(--ccm-success) !important;
        }

        .badge.text-bg-danger {
            background-color: var(--ccm-primary) !important;
        }

        .badge.text-bg-warning {
            background-color: var(--ccm-warning) !important;
            color: #fff !important;
        }

        .alert-success {
            --bs-alert-bg: #edf7f1;
            --bs-alert-border-color: #b7d8c8;
            --bs-alert-color: #164a35;
        }

        .alert-danger {
            --bs-alert-bg: #fdecee;
            --bs-alert-border-color: #f4b7c0;
            --bs-alert-color: #6d0014;
        }

        .alert-info {
            --bs-alert-bg: #f2f2f2;
            --bs-alert-border-color: #d0d0d0;
            --bs-alert-color: #1f1f1f;
        }

        .table thead th {
            background-color: var(--ccm-surface-alt);
            color: #700013;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-size: 0.72rem;
            border-bottom: 1px solid var(--ccm-border);
        }

        .table td,
        .table th {
            border-color: #f0e0e4;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(176, 0, 32, 0.03);
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 991px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 260px;
                min-height: 100vh;
                border-right: none;
                border-bottom: none;
                box-shadow: 4px 0 30px rgba(0, 0, 0, 0.2);
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                z-index: 1035;
            }

            .admin-sidebar.is-open {
                transform: translateX(0);
            }

            .admin-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(8, 8, 8, 0.45);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease;
                z-index: 1030;
            }

            .admin-backdrop.show {
                opacity: 1;
                pointer-events: auto;
            }

            body.admin-sidebar-open {
                overflow: hidden;
            }
        }

        @media (min-width: 992px) {
            .admin-shell {
                padding-left: 260px;
            }

            .admin-sidebar {
                position: fixed;
                top: 0;
                left: 0;
            }
        }
    </style>
</head>
<body>
<div class="admin-shell d-flex flex-column flex-lg-row">
    <aside class="admin-sidebar text-white p-3" id="admin-sidebar">
        <div class="admin-brand mb-3">
            <div>
                <div class="fw-semibold">Admin Panel</div>
                <div class="small text-white-50">Confidence Club Members</div>
            </div>
            <span class="brand-badge">Admin</span>
        </div>
        <hr>
        <nav class="admin-nav" aria-label="Admin">
            <a href="{{ route('admin.dashboard') }}" class="admin-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i>Overview</a>
            <a href="{{ route('admin.users.index') }}" class="admin-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-people"></i>User Management</a>
            <a href="{{ route('admin.activity-logs.index') }}" class="admin-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}"><i class="bi bi-clock-history"></i>Activity Logs</a>
            <a href="{{ route('admin.settings') }}" class="admin-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}"><i class="bi bi-sliders2"></i>Settings</a>
            <a href="{{ route('admin.announcements.index') }}" class="admin-link {{ request()->routeIs('admin.announcements*') ? 'active' : '' }}"><i class="bi bi-megaphone"></i>Announcements</a>
            <a href="{{ route('admin.meetings.index') }}" class="admin-link {{ request()->routeIs('admin.meetings*') ? 'active' : '' }}"><i class="bi bi-calendar-event"></i>Meetings</a>
            <hr>
            <a href="{{ route('dashboard') }}" class="admin-link"><i class="bi bi-grid-1x2"></i>Main App</a>
        </nav>
    </aside>
    <div class="admin-backdrop d-lg-none"></div>

    <main class="flex-fill">
        <div class="content-shell p-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary d-lg-none" id="adminSidebarToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="admin-sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="page-meta small">Administrator Access</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-bg-light border"><i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">Logout</button>
                    </form>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('status'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="page-content">
                @yield('content')
            </div>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.querySelector('.admin-sidebar');
        const toggle = document.getElementById('adminSidebarToggle');
        const backdrop = document.querySelector('.admin-backdrop');

        if (!sidebar || !toggle || !backdrop) {
            return;
        }

        const closeSidebar = () => {
            sidebar.classList.remove('is-open');
            backdrop.classList.remove('show');
            document.body.classList.remove('admin-sidebar-open');
            toggle.setAttribute('aria-expanded', 'false');
        };

        const openSidebar = () => {
            sidebar.classList.add('is-open');
            backdrop.classList.add('show');
            document.body.classList.add('admin-sidebar-open');
            toggle.setAttribute('aria-expanded', 'true');
        };

        toggle.addEventListener('click', () => {
            if (sidebar.classList.contains('is-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        backdrop.addEventListener('click', closeSidebar);

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) {
                closeSidebar();
            }
        });
    });
</script>
</body>
</html>
