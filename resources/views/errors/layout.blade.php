<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Lỗi') | {{ config('app.name', 'Giltech Solutions') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: url('/assets/images/backgrounds/building.jpg') center/cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        nav {
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(0,0,0,.18);
            backdrop-filter: blur(6px);
        }
        .brand {
            display: flex; align-items: center; gap: .6rem;
            text-decoration: none; color: #fff;
        }
        .brand img { width: 32px; height: 32px; object-fit: contain; }
        .brand-name { font-weight: 700; font-size: 1rem; letter-spacing: -.01em; }
        .brand-pill {
            font-size: .65rem; font-weight: 800; text-transform: uppercase;
            padding: .15rem .5rem; border-radius: 999px;
            background: rgba(255,255,255,.18); color: #fff; letter-spacing: .06em;
        }
        nav a { color: rgba(255,255,255,.85); text-decoration: none; font-size: .9rem; }
        main {
            flex: 1; display: flex; align-items: center;
            justify-content: center; padding: 2rem;
        }
        .card {
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(12px);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 480px; width: 100%;
            box-shadow: 0 24px 60px rgba(0,0,0,.18);
        }
        .card img { width: 48px; height: 48px; object-fit: contain; margin-bottom: 1rem; }
        .badge {
            display: inline-block;
            font-size: .75rem; font-weight: 700; text-transform: uppercase;
            color: #0ea5e9; letter-spacing: .06em; margin-bottom: .5rem;
        }
        h1 { font-size: 1.5rem; color: #0f172a; margin-bottom: .75rem; font-weight: 700; }
        p { color: #64748b; line-height: 1.6; margin-bottom: 1.5rem; font-size: .95rem; }
        .actions { display: flex; flex-wrap: wrap; gap: .75rem; justify-content: center; }
        .btn {
            padding: .6rem 1.4rem; border-radius: 999px; font-size: .9rem;
            font-weight: 600; text-decoration: none; display: inline-block;
            transition: opacity .15s;
        }
        .btn:hover { opacity: .85; }
        .btn-primary { background: #0ea5e9; color: #fff; }
        .btn-light { background: #f1f5f9; color: #334155; }
    </style>
</head>
<body>
<nav>
    <a href="/" class="brand">
        <img src="/assets/images/logo-transparent.png" alt="Giltech Solutions">
        <span class="brand-name">Giltech Solutions</span>
        <span class="brand-pill">Portal</span>
    </a>
    <a href="/login">Đăng nhập</a>
</nav>
<main>
    <div class="card">
        <img src="/assets/images/logo-transparent.png" alt="Giltech Solutions">
        @yield('content')
    </div>
</main>
</body>
</html>
