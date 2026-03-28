@extends('layouts.admin')

@section('title', 'Manajemen aplikasi — NGOHI Admin')

@section('content')
<div class="card">
    <h1>Manajemen aplikasi</h1>
    <p style="color:var(--muted);margin-top:0">Nama dan warna tema yang dibaca aplikasi mobile lewat API <code>/api/v1/app/branding</code>.</p>

    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <form method="post" action="{{ route('admin.app-settings.update') }}">
        @csrf
        @method('PUT')

        <label for="display_name">Nama aplikasi (di app)</label>
        <input id="display_name" type="text" name="display_name" required
            value="{{ old('display_name', $settings->display_name ?? 'NGOHI') }}">
        @error('display_name')<div class="error">{{ $message }}</div>@enderror

        <label for="tagline">Tagline</label>
        <input id="tagline" type="text" name="tagline"
            value="{{ old('tagline', $settings->tagline ?? 'Wujudkan mimpi finansialmu') }}">
        @error('tagline')<div class="error">{{ $message }}</div>@enderror

        <p style="margin-top:1.25rem;font-weight:600;font-size:.9rem">Warna (nuansa biru, kuning, putih — gaya e-wallet)</p>
        <div class="row-colors">
            <div>
                <label for="primary_color">Primer (biru)</label>
                <input type="color" id="primary_color" name="primary_color"
                    value="{{ old('primary_color', $settings->primary_color ?? '#1565C0') }}">
                @error('primary_color')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="secondary_color">Sekunder (kuning)</label>
                <input type="color" id="secondary_color" name="secondary_color"
                    value="{{ old('secondary_color', $settings->secondary_color ?? '#F9A825') }}">
                @error('secondary_color')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="accent_color">Aksen (putih / highlight)</label>
                <input type="color" id="accent_color" name="accent_color"
                    value="{{ old('accent_color', $settings->accent_color ?? '#FFFFFF') }}">
                @error('accent_color')<div class="error">{{ $message }}</div>@enderror
            </div>
        </div>
        <p style="font-size:.8rem;color:var(--muted)">Color picker menghasilkan format hex; server memvalidasi <code>#RRGGBB</code>.</p>

        <div style="margin-top:1.5rem">
            <button type="submit" class="btn btn-secondary">Simpan</button>
        </div>
    </form>
</div>
@endsection
