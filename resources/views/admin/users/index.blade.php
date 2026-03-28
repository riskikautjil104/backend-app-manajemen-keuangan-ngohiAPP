@extends('layouts.admin')

@section('title', 'Pengguna — NGOHI Admin')

@section('content')
<div class="card">
    <h1>Daftar pengguna</h1>
    <p style="color:var(--muted);margin-top:0">Akun aplikasi mobile (bukan admin).</p>

    <form method="get" action="{{ route('admin.users.index') }}" style="margin-bottom:1rem;display:flex;gap:.5rem;flex-wrap:wrap">
        <input type="text" name="search" placeholder="Cari nama atau email…" value="{{ request('search') }}" style="max-width:280px">
        <button type="submit" class="btn btn-primary">Cari</button>
    </form>

    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Level</th>
                    <th>Terdaftar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td>{{ $u->id }}</td>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->level }}</td>
                        <td>{{ $u->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1rem">
        {{ $users->links() }}
    </div>
</div>
@endsection
