<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ThinkQA</title>
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ asset('js/select.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo-mini.png') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <style>
        :root {
            --primary-color: #1D3341;
            --secondary-color: #1CB2A2;
            --hover-color: #8B9DAB;
            --text-muted: #6c757d;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }

        .container-scroller {
            min-height: 100vh;
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar .navbar-brand {
            color: #fff;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .sidebar {
            background-color: #fff;
            border-right: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .sidebar .nav .nav-item .nav-link {
            color: var(--primary-color);
            border-radius: 6px;
            margin: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .sidebar .nav .nav-item.active>.nav-link {
            background-color: var(--secondary-color);
            color: #fff;
        }

        .sidebar .nav .nav-item .nav-link:hover {
            background-color: var(--hover-color);
            color: #fff;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-body {
            padding: 1.5rem;
        }

        .content-wrapper {
            padding: 2rem;
        }

        .footer {
            background-color: #fff;
            border-top: 1px solid #e9ecef;
            padding: 1rem 2rem;
            color: var(--text-muted);
        }

        .btn {
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-primary:hover {
            background-color: #17a092;
            border-color: #17a092;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 1rem;
            }

            .navbar {
                padding: 0.75rem 1rem;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="container-scroller">
        <!-- Navbar -->
        @include('partials.navbar')

        <div class="container-fluid page-body-wrapper">
            <!-- Sidebar -->
            @include('partials.sidebar')

            <div class="main-panel">
                <div class="content-wrapper">
                    @yield('content')
                </div>
                <!-- Footer -->
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between align-items-center">
                        <span class="text-muted">Copyright © {{ date('Y') }}. All rights reserved.</span>
                        <span class="text-muted">Hand-crafted & made with <i
                                class="bi bi-heart-fill text-danger ms-1"></i></span>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('vendors/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ asset('js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('js/off-canvas.js') }}"></script>
    <script src="{{ asset('js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('js/template.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
    <script src="{{ asset('js/todolist.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="{{ asset('js/custom/custom.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    @stack('scripts')
</body>

</html>
