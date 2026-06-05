@extends('gate.layout', ['title' => 'Login Gate'])

@section('content')
    <style>
        .gate-login-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, var(--page-bg-start) 0%, var(--page-bg-end) 100%);
        }
        .gate-login-topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 64px;
            padding: max(16px, env(safe-area-inset-top)) 16px 16px;
            background: var(--topbar-bg);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-soft);
        }
        .gate-login-topbar-icon {
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            flex: 0 0 auto;
        }
        .gate-login-topbar-title {
            margin: 0;
            font-size: 20px;
            line-height: 1.2;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.02em;
        }
        .gate-login-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
            padding: 32px 16px 24px;
        }
        .gate-login-header {
            margin-bottom: 28px;
            text-align: center;
        }
        .gate-login-heading {
            margin: 0 0 8px;
            font-size: 28px;
            line-height: 1.15;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.02em;
        }
        .gate-login-copy {
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
            color: var(--text-soft);
        }
        .gate-login-panel {
            display: grid;
            gap: 20px;
            padding: 24px 20px;
            border: 1px solid var(--border-soft);
            border-radius: 24px;
            background: var(--surface);
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
        }
        .gate-login-form {
            display: grid;
            gap: 16px;
        }
        .gate-login-field {
            display: grid;
            gap: 8px;
        }
        .gate-login-label {
            margin: 0 0 0 4px;
            font-size: 14px;
            line-height: 1.3;
            font-weight: 700;
            color: var(--text-soft);
        }
        .gate-login-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 54px;
            padding: 0 16px;
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            background: var(--surface-soft-2);
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }
        .gate-login-input-wrap:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--primary) 18%, transparent 82%);
            background: var(--surface);
        }
        .gate-login-input-icon {
            color: var(--text-soft-3);
            font-size: 18px;
            line-height: 1;
            flex: 0 0 auto;
        }
        .gate-login-input {
            width: 100%;
            min-width: 0;
            padding: 0;
            border: 0;
            box-shadow: none;
            background: transparent;
            color: var(--text);
            font: inherit;
            font-size: 16px;
            outline: none;
        }
        .gate-login-input:focus,
        .gate-login-input:focus-visible {
            border: 0;
            outline: none;
            box-shadow: none;
        }
        .gate-login-input::placeholder {
            color: var(--text-soft-3);
        }
        .gate-login-password-toggle {
            border: 0;
            background: transparent;
            color: var(--text-soft-3);
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            flex: 0 0 auto;
        }
        .gate-login-remember {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 2px;
            color: var(--text-soft);
            font-size: 14px;
        }
        .gate-login-remember input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }
        .gate-login-submit {
            width: 100%;
            min-height: 52px;
            border-radius: 16px;
            font-size: 15px;
        }
        .gate-login-note {
            padding: 14px 16px;
            border: 1px solid var(--primary-soft-border);
            border-radius: 16px;
            background: color-mix(in srgb, var(--primary-soft) 55%, var(--surface) 45%);
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.55;
        }
        .gate-login-note strong {
            display: block;
            margin-bottom: 4px;
            color: var(--text-heading);
            font-size: 13px;
        }
        .gate-login-footer {
            padding: 0 16px calc(24px + env(safe-area-inset-bottom));
            text-align: center;
            color: var(--text-soft-3);
            font-size: 12px;
            line-height: 1.5;
        }
        @media (min-width: 768px) {
            .gate-login-shell {
                width: min(100%, 960px);
                min-height: auto;
                margin: 32px auto;
                border: 1px solid var(--border-soft);
                border-radius: 28px;
                overflow: hidden;
                box-shadow: 0 24px 80px rgba(15, 23, 42, 0.1);
            }
            .gate-login-main {
                max-width: none;
                padding: 40px 32px 32px;
            }
            .gate-login-layout {
                display: grid;
                grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
                gap: 28px;
                align-items: center;
            }
            .gate-login-header {
                margin-bottom: 0;
                text-align: left;
            }
            .gate-login-heading {
                font-size: 34px;
            }
            .gate-login-copy {
                font-size: 16px;
            }
        }
        @media (max-width: 767px) {
            .shell {
                padding: 0;
            }
            .container {
                max-width: none;
                min-height: 100vh;
            }
        }
    </style>

    <div class="gate-login-shell">
        <header class="gate-login-topbar">
            <div class="gate-login-topbar-icon" aria-hidden="true">
                @svg('carbon-ibm-engineering-requirements-doors-next', 'gate-desktop-gate-icon-svg')
            </div>
            <h1 class="gate-login-topbar-title">Gate Manager</h1>
        </header>

        <main class="gate-login-main">
            <div class="gate-login-layout">
                <section class="gate-login-header">
                    <h2 class="gate-login-heading">Masuk ke dashboard gate</h2>
                    <p class="gate-login-copy">
                        Login ini khusus untuk petugas pintu masuk dan super admin. Setelah masuk, Anda langsung menuju alur scan QR yang ringan dan fokus.
                    </p>
                </section>

                <section class="gate-login-panel">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('gate.login.store') }}" class="gate-login-form">
                        @csrf

                        <div class="gate-login-field">
                            <label for="email" class="gate-login-label">Email</label>
                            <div class="gate-login-input-wrap">
                                <span class="gate-login-input-icon" aria-hidden="true">✉</span>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    class="gate-login-input"
                                    value="{{ old('email') }}"
                                    placeholder="Masukkan email akun"
                                    autocomplete="email"
                                    required
                                    autofocus
                                >
                            </div>
                        </div>

                        <div class="gate-login-field">
                            <label for="password" class="gate-login-label">Password</label>
                            <div class="gate-login-input-wrap">
                                <span class="gate-login-input-icon" aria-hidden="true">🔒</span>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    class="gate-login-input"
                                    placeholder="Masukkan password"
                                    autocomplete="current-password"
                                    required
                                >
                                <button type="button" class="gate-login-password-toggle" id="gate-login-password-toggle" aria-label="Tampilkan atau sembunyikan password">👁</button>
                            </div>
                        </div>

                        <label class="gate-login-remember">
                            <input type="checkbox" name="remember" value="1">
                            <span>Ingat saya di perangkat ini</span>
                        </label>

                        <button type="submit" class="button button-primary gate-login-submit">Masuk ke Scanner</button>
                    </form>

                    <div class="gate-login-note">
                        <strong>Catatan operasional</strong>
                        Gunakan akun yang sudah ditugaskan ke gate. Setelah berhasil login, sistem akan langsung menampilkan halaman scanner.
                    </div>
                </section>
            </div>
        </main>

        <footer class="gate-login-footer">
            Sistem scanner gate sekolah. Gunakan akun yang telah diberi akses.
            <br>
            Supported by Aksesin Digital
        </footer>
    </div>

    <script>
        (() => {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('gate-login-password-toggle');

            passwordToggle?.addEventListener('click', () => {
                const isPassword = passwordInput?.getAttribute('type') === 'password';

                passwordInput?.setAttribute('type', isPassword ? 'text' : 'password');
                passwordToggle.textContent = isPassword ? '🙈' : '👁';
            });
        })();
    </script>
@endsection
