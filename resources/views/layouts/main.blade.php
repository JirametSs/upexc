<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ระบบอัปโหลดไฟล์ Excel">
    <meta name="author" content="Your Company">

    <title>@yield('title', 'ระบบอัปโหลดไฟล์ Excel')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Google Font: Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);

        }

        main {
            flex: 50vh;
        }

        footer {
            background-color: #ffffff;
            border-top: 1px solid #dee2e6;
            padding: 1rem 0;
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: auto;
        }

        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
    </style>

    @yield('head')
</head>

<body>

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="d-flex align-items-center ps-4" style="height: 60px;">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ url('/') }}">
                <i class="fas fa-file-excel text-success"></i>
                ระบบอัปโหลด Excel
            </a>
        </div>
    </nav>


    {{-- Main Content --}}
    <main class="py-5">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="text-center small">
        &copy; {{ date('Y') }} Your Company. All rights reserved.
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')

    @stack('scripts')
</body>

</html>