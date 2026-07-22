<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Documentation')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-navy: #1e3a5f;
            --primary-red: #e63946;
            --primary-light-blue: #a8dadc;
            --bg-soft: #f8f9fa;
            --card-bg: #ffffff;
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --border-soft: #e2e8f0;
            --code-bg: #1e1e1e;
            --shadow-soft: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-medium: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-large: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
            color: var(--text-primary);
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            box-shadow: var(--shadow-soft);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            min-height: 100vh;
        }

        /* Common Components */
        .page-header {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .page-header:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-2px);
        }

        .header-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-navy);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-title i {
            background: linear-gradient(135deg, var(--primary-navy), var(--primary-light-blue));
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .header-subtitle {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-navy) 0%, #2c5282 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-soft);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            color: white;
        }

        .btn-secondary-custom {
            background: var(--card-bg);
            color: var(--text-primary);
            border: 1px solid var(--border-soft);
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-secondary-custom:hover {
            background: var(--bg-soft);
            border-color: var(--primary-navy);
        }

        .form-label-custom {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary-navy);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control-custom,
        .form-select-custom {
            border: 1px solid var(--border-soft);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            width: 100%;
            background-color: white;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--primary-navy);
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
            outline: none;
        }

        .modal-content-custom {
            border: none;
            border-radius: 20px;
            box-shadow: var(--shadow-large);
            overflow: hidden;
        }

        .modal-header-custom {
            background: linear-gradient(135deg, var(--primary-navy) 0%, #2c5282 100%);
            color: white;
            padding: 1.5rem 2rem;
            border: none;
        }

        .modal-title-custom {
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-body-custom {
            padding: 2rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer-custom {
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--border-soft);
            background: #f8f9fa;
        }

        .dynamic-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .dynamic-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .dynamic-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-navy);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-add {
            background: var(--primary-navy);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-add:hover {
            background: #2c5282;
            transform: translateY(-1px);
        }

        .btn-remove {
            background: var(--primary-red);
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-remove:hover {
            background: #c53030;
            transform: scale(1.1);
        }

        .code-block-form {
            background: white;
            border: 1px solid var(--border-soft);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>

    <script>
        $(document).ready(function() {
            mermaid.initialize({
                startOnLoad: true
            });
            if (typeof Prism !== 'undefined') Prism.highlightAll();
        });
    </script>
    @stack('scripts')
</body>

</html>
