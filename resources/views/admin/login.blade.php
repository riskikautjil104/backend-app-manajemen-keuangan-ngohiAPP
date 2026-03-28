@extends('layouts.admin')

@section('title', 'Masuk — NGOHI Admin')

@section('content')
<div class="card" style="max-width:420px;margin:3rem auto">
    <h1>Masuk admin</h1>
    <p style="color:var(--muted);font-size:.9rem;margin-top:0">Kelola pengguna dan tampilan aplikasi mobile.</p>
    <form method="post" action="{{ route('admin.login') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        @error('email')<div class="error">{{ $message }}</div>@enderror

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <label style="display:flex;align-items:center;gap:.5rem;font-weight:500">
            <input type="checkbox" name="remember" value="1"> Ingat saya
        </label>

        <div style="margin-top:1.25rem">
            <button type="submit" class="btn btn-primary" style="width:100%">Masuk</button>
        </div>
    </form>
</div>
@endsection
