<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'NGOHI Admin')</title>
    <style>
        :root {
            --primary: #1565C0;
            --secondary: #F9A825;
            --accent: #FFFFFF;
            --text: #1a1a2e;
            --muted: #5c6b7a;
            --border: #e2e8f0;
            --bg: #f0f4fb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        header {
            background: linear-gradient(135deg, var(--primary) 0%, #0D47A1 100%);
            color: #fff;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 4px 14px rgba(21, 101, 192, 0.25);
        }
        header a { color: #fff; text-decoration: none; opacity: .95; }
        header nav { display: flex; gap: 1.25rem; align-items: center; flex-wrap: wrap; }
        header nav a { font-weight: 500; }
        header .badge {
            background: var(--secondary);
            color: #1a1a1a;
            padding: .25rem .6rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
        }
        main { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(26, 26, 46, .06);
            border: 1px solid var(--border);
        }
        h1 { font-size: 1.35rem; margin: 0 0 1rem; }
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th, td { text-align: left; padding: .65rem .5rem; border-bottom: 1px solid var(--border); }
        th { color: var(--muted); font-weight: 600; font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; }
        .btn {
            display: inline-block;
            padding: .5rem 1rem;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: .875rem;
            text-decoration: none;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-ghost { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,.5); }
        .btn-secondary { background: var(--secondary); color: #1a1a1a; }
        label { display: block; font-size: .85rem; font-weight: 600; margin: 1rem 0 .35rem; }
        input[type="text"], input[type="email"], input[type="password"], input[type="color"] {
            width: 100%; max-width: 420px; padding: .6rem .75rem; border: 1px solid var(--border);
            border-radius: 10px; font-size: 1rem;
        }
        .row-colors { display: flex; gap: 1rem; flex-wrap: wrap; }
        .row-colors label { width: 140px; }
        .flash { background: #e8f5e9; color: #2e7d32; padding: .75rem 1rem; border-radius: 10px; margin-bottom: 1rem; }
        .error { color: #c62828; font-size: .85rem; margin-top: .25rem; }
        form.inline { display: inline; }
    </style>
    @stack('styles')
</head>
<body>
    @auth
        <header>
            <div>
                <strong style="font-size:1.1rem">NGOHI</strong>
                <span class="badge">Admin</span>
            </div>
            <nav>
                <a href="{{ route('admin.users.index') }}">Pengguna</a>
                <a href="{{ route('admin.app-settings.edit') }}">Manajemen aplikasi</a>
                <a href="{{ route('admin.broadcasts.index') }}">Iklan &amp; pengumuman</a>
                <form class="inline" method="post" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost" style="cursor:pointer">Keluar</button>
                </form>
            </nav>
        </header>
    @endauth
    <main>
        @yield('content')
    </main>
</body>
</html>
