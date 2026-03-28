@extends('layouts.admin')

@section('title', ($broadcast->exists ? 'Edit' : 'Tambah').' pengumuman — NGOHI')

@section('content')
    <div class="card">
        <h1>{{ $broadcast->exists ? 'Edit pengumuman' : 'Pengumuman baru' }}</h1>

        @if ($errors->any())
            <div class="error" style="background:#ffebee;padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem">
                <ul style="margin:0;padding-left:1.2rem">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post"
              action="{{ $broadcast->exists ? route('admin.broadcasts.update', $broadcast) : route('admin.broadcasts.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if ($broadcast->exists)
                @method('PUT')
            @endif

            <input type="hidden" name="is_active" value="0">

            <label for="title">Judul (notifikasi)</label>
            <input id="title" name="title" type="text" value="{{ old('title', $broadcast->title) }}" required>

            <label for="body">Isi pesan</label>
            <textarea id="body" name="body" rows="5" required
                      style="width:100%;max-width:520px;padding:.6rem .75rem;border:1px solid var(--border);border-radius:10px;font-size:1rem">{{ old('body', $broadcast->body) }}</textarea>

            <label>
                <input type="checkbox" name="is_active" value="1"
                    @checked((string) old('is_active', $broadcast->exists ? ($broadcast->is_active ? '1' : '0') : '1') === '1')> Aktif
            </label>

            <label for="starts_at">Mulai tampil (opsional)</label>
            <input id="starts_at" name="starts_at" type="datetime-local"
                   value="{{ old('starts_at', $broadcast->starts_at?->format('Y-m-d\TH:i')) }}">

            <label for="ends_at">Selesai (opsional)</label>
            <input id="ends_at" name="ends_at" type="datetime-local"
                   value="{{ old('ends_at', $broadcast->ends_at?->format('Y-m-d\TH:i')) }}">

            <label for="image">Gambar (opsional)</label>
            <input id="image" name="image" type="file" accept="image/*">
            @if ($broadcast->exists && $broadcast->image_path)
                <p style="font-size:.85rem;color:var(--muted)">Gambar saat ini: <a href="{{ asset('storage/'.$broadcast->image_path) }}" target="_blank">lihat</a></p>
            @endif

            <p style="margin-top:1.5rem">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.broadcasts.index') }}" class="btn btn-secondary">Batal</a>
            </p>
        </form>
    </div>
@endsection
