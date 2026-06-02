@extends('gate.layout', ['title' => 'Login Gate'])

@section('content')
    <div class="card">
        <div class="grid grid-2">
            <div class="card-body" style="background: linear-gradient(180deg, #fffbeb 0%, #fff 100%);">
                <span class="badge badge-warning">Gate Access</span>
                <h1 class="title" style="margin-top: 18px;">Login ke dashboard gate</h1>
                <p class="subtitle">
                    Akses ini khusus untuk petugas gate dan super admin. Setelah login, kamu masuk ke dashboard ringan untuk persiapan scan QR.
                </p>
                <div class="stack" style="margin-top: 28px;">
                    <div class="gate-card">
                        <div class="gate-code">Fokus Operasional</div>
                        <div class="gate-name">Login cepat, dashboard sederhana, siap scan.</div>
                        <div class="form-hint">Tidak ada UI admin tambahan. Satu login, satu flow, satu dashboard gate.</div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <h2 style="margin: 0 0 8px; font-size: 22px;">Masuk</h2>
                <p class="subtitle">Gunakan email dan password akun yang sudah ditugaskan.</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('gate.login.store') }}" class="stack" style="margin-top: 20px;">
                    @csrf

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>

                    <label class="actions" style="align-items: center;">
                        <input type="checkbox" name="remember" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span class="muted">Ingat saya</span>
                    </label>

                    <button type="submit" class="button button-primary">Login Gate</button>
                </form>
            </div>
        </div>
    </div>
@endsection
