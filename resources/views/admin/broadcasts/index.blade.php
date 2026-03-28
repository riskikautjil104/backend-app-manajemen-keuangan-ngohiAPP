@extends('layouts.admin')

@section('title', 'Pengumuman / iklan — NGOHI')

@section('content')
    <div class="card">
        <h1>Pengumuman untuk aplikasi</h1>
        <p style="color:var(--muted); font-size:.9rem; margin-top:0">Teks ini bisa muncul sebagai notifikasi lokal di app mobile pengguna NGOHI.</p>
        <p><a href="{{ route('admin.broadcasts.create') }}" class="btn btn-primary">Tambah pengumuman</a></p>

        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Judul</th>
                <th>Aktif</th>
                <th>Mulai</th>
                <th>Selesai</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($items as $b)
                <tr>
                    <td>{{ $b->id }}</td>
                    <td>{{ $b->title }}</td>
                    <td>{{ $b->is_active ? 'Ya' : 'Tidak' }}</td>
                    <td>{{ $b->starts_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>{{ $b->ends_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>
                        <a href="{{ route('admin.broadcasts.edit', $b) }}" class="btn btn-secondary" style="font-size:.8rem; padding:.35rem .65rem">Edit</a>
                        <form class="inline" method="post" action="{{ route('admin.broadcasts.destroy', $b) }}" onsubmit="return confirm('Hapus?');" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn" style="background:#fee;color:#b71c1c;font-size:.8rem; padding:.35rem .65rem">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada pengumuman.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
