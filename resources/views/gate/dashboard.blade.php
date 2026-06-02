@extends('gate.layout', ['title' => 'Dashboard Gate'])

@section('content')
    @php
        $activeGate = $selectedGate;
        $scanResult = $scanResult ?? null;
        $scanTicket = data_get($scanResult, 'ticket');
        $scanCheckin = data_get($scanResult, 'checkin');
        $scanStatus = data_get($scanResult, 'status');
    @endphp

    <div class="card">
        <div class="topbar">
            <div>
                <h1>Dashboard Gate</h1>
                <p>Selamat datang, {{ $user->name }}. Pilih gate dan siapkan proses scan QR.</p>
            </div>
            <form method="POST" action="{{ route('gate.logout') }}">
                @csrf
                <button type="submit" class="button button-ghost">Logout</button>
            </form>
        </div>

        <div class="content scanner-shell">
            <div class="scanner-top">
                <div class="actions">
                    <span class="badge badge-success">Terverifikasi</span>
                    <span class="badge">{{ $user->getRoleNames()->join(', ') }}</span>
                    <span class="badge">{{ $gates->count() }} Gate</span>
                </div>

                <form method="GET" action="{{ route('gate.dashboard') }}" class="selector">
                    <label for="gate">Gate aktif</label>
                    <select id="gate" name="gate" onchange="this.form.submit()">
                        @foreach ($gates as $gate)
                            <option value="{{ $gate->id }}" @selected($activeGate?->id === $gate->id)>
                                {{ $gate->code }} — {{ $gate->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <div style="display: flex; justify-content: flex-end;">
                    <span class="badge {{ $activeGate?->is_active ? 'badge-success' : 'badge-warning' }}">
                        {{ $activeGate?->is_active ? 'Gate Aktif' : 'Gate Nonaktif' }}
                    </span>
                </div>
            </div>

            @if (! $activeGate)
                <div class="empty">
                    <strong>Tidak ada gate yang ditugaskan.</strong>
                    <div style="margin-top: 8px;">Hubungkan akun ini ke gate terlebih dulu dari menu Gerbang.</div>
                </div>
            @else
                <section class="search-strip">
                    <form method="POST" action="{{ route('gate.scan') }}" class="search-strip-form" id="gate-scan-form">
                        @csrf
                        <input type="hidden" name="gate_id" value="{{ $activeGate->id }}">

                        <div class="search-strip-toolbar-row">
                            <div class="search-strip-toolbar">
                                <strong>Scan QR / Cari Tiket</strong>
                                <div class="mode-toggle" role="group" aria-label="Mode input scanner">
                                    <span class="mode-toggle-label">Mode</span>
                                    <button type="button" class="mode-option is-active" id="gate-scan-mode-enter" data-mode="enter">
                                        Enter
                                    </button>
                                    <button type="button" class="mode-option" id="gate-scan-mode-auto" data-mode="auto">
                                        Auto
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="search-strip-main">
                            <label class="search-strip-label" for="q">
                                <input
                                    id="q"
                                    class="search-strip-input"
                                    type="text"
                                    name="q"
                                    value="{{ old('q') }}"
                                    placeholder="Tempel barcode scanner di sini, atau ketik kode tiket / QR token"
                                    enterkeyhint="done"
                                    autocomplete="off"
                                    autofocus
                                >
                            </label>
                        </div>

                        <div class="actions search-strip-actions" style="justify-content: flex-end;">
                            <a class="button button-ghost" href="{{ route('gate.dashboard', ['gate' => $activeGate->id]) }}">Bersihkan</a>
                            <button type="submit" class="button button-primary">Scan & Check-in</button>
                        </div>

                    </form>
                </section>

                <div class="scanner-grid">
                    <section class="panel">
                        <div class="panel-header">
                            <h2>Scan QR Code (Kamera)</h2>
                            <span class="badge badge-success">Kamera Aktif</span>
                        </div>
                        <div class="panel-body">
                            <div class="camera-frame">
                                <span class="camera-corner tl"></span>
                                <span class="camera-corner tr"></span>
                                <span class="camera-corner bl"></span>
                                <span class="camera-corner br"></span>
                                <div class="camera-placeholder">
                                    <div>
                                        <div class="badge" style="background: rgba(255,255,255,0.12); color: #fff;">Preview Kamera</div>
                                        <strong>Siapkan scanner QR</strong>
                                        <div style="margin-top: 8px; color: rgba(255,255,255,0.78);">
                                            Area ini akan dipakai untuk kamera scan dan input barcode USB.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="camera-actions">
                                <button type="button" class="button button-primary">Hentikan Kamera</button>
                                <button type="button" class="button button-ghost">Ganti Kamera</button>
                            </div>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-header">
                            <h2>Hasil Scan</h2>
                            <span class="badge {{ $scanResult ? ($scanStatus === 'success' ? 'badge-success' : 'badge-warning') : 'badge-warning' }}">
                                {{ $scanResult ? ($scanStatus === 'success' ? 'Berhasil' : ($scanStatus === 'already_scanned' ? 'Sudah Scan' : 'Tidak Ditemukan')) : 'Siap scan' }}
                            </span>
                        </div>
                        <div class="panel-body">
                            @if ($scanResult)
                                <div class="result-banner {{ $scanStatus === 'success' ? '' : 'is-empty' }}">
                                    <div class="result-check">
                                        {{ $scanStatus === 'success' ? '✓' : '!' }}
                                    </div>
                                    <div>
                                        <p class="result-title">
                                            {{ $scanStatus === 'success' ? 'VALID' : 'INFO' }}
                                        </p>
                                        <p class="result-subtitle">
                                            {{ data_get($scanResult, 'message', 'Hasil scan ditampilkan di bawah.') }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="result-banner is-empty">
                                    <div class="result-check" style="background: rgba(15, 23, 42, 0.06); color: #334155;">⌁</div>
                                    <div>
                                        <p class="result-title" style="font-size: 24px; margin-bottom: 4px;">Menunggu QR</p>
                                        <p class="result-subtitle" style="margin-top: 0;">
                                            Setelah QR terbaca, check-in dibuat otomatis dan hasilnya muncul di sini.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div class="details-list">
                                <div class="detail-row">
                                    <div class="detail-label">Nama Peserta</div>
                                    <div class="detail-value">{{ $scanTicket?->student?->name ?? '-' }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Kelas</div>
                                    <div class="detail-value">{{ $scanTicket?->student?->eventClass?->name ?? '-' }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Kode Tiket</div>
                                    <div class="detail-value">{{ $scanTicket?->ticket_code ?? '-' }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">QR Token</div>
                                    <div class="detail-value">{{ $scanTicket?->qr_token ?? '-' }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Gate</div>
                                    <div class="detail-value">{{ data_get($scanResult, 'gate_name', $activeGate->name) }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Status</div>
                                    <div class="detail-value">
                                        @if ($scanResult)
                                            <span class="badge {{ $scanStatus === 'success' ? 'badge-success' : 'badge-warning' }}">
                                                {{ $scanStatus === 'success' ? 'Check-in berhasil' : ($scanStatus === 'already_scanned' ? 'Sudah check-in' : 'Tidak ditemukan') }}
                                            </span>
                                        @else
                                            <span class="badge badge-warning">Menunggu Scan</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Waktu Check-in</div>
                                    <div class="detail-value">
                                        {{ $scanCheckin?->checked_in_at?->format('d/m/Y H:i:s') ?? '-' }}
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Metode Scan</div>
                                    <div class="detail-value">
                                        {{ $scanCheckin?->scan_method ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <aside class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <circle cx="8" cy="8" r="2.75" />
                                    <circle cx="16" cy="8" r="2.75" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19v-1.25A4.25 4.25 0 0 1 8.75 13.5h0A4.25 4.25 0 0 1 13 17.75V19M12.75 19v-1.5A4.25 4.25 0 0 1 17 13.25h0A4.25 4.25 0 0 1 21.25 17.5V19" />
                                </svg>
                            </div>
                            <div>
                                <p class="stat-label">Total Hadir</p>
                                <p class="stat-value">0</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <circle cx="12" cy="12" r="8.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5v4.75l3 1.75" />
                                </svg>
                            </div>
                            <div>
                                <p class="stat-label">Belum Scan</p>
                                <p class="stat-value">0</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #ede9fe; color: #7c3aed;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <circle cx="12" cy="12" r="8.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.25 12.25 1.9 1.9 3.9-4.4" />
                                </svg>
                            </div>
                            <div>
                                <p class="stat-label">Sudah Scan</p>
                                <p class="stat-value">0</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #fee2e2; color: #ef4444;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <circle cx="12" cy="12" r="8.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l6 6M15 9l-6 6" />
                                </svg>
                            </div>
                            <div>
                                <p class="stat-label">Ditolak</p>
                                <p class="stat-value">0</p>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="subgrid">
                    <section>
                        <div class="panel">
                            <div class="panel-header">
                                <h2>Riwayat Scan Terakhir</h2>
                            </div>
                            <div class="panel-body" style="padding: 0;">
                                <div class="table-shell" style="border: 0; border-radius: 0;">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Waktu</th>
                                                <th>Nama Peserta</th>
                                                <th>Kode Tiket</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="4" style="text-align: center; color: var(--muted); padding: 24px;">
                                                    Belum ada riwayat scan.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>

                    <aside class="aside-card">
                        <div class="gate-code">Pintu Masuk</div>
                        <h3 style="margin: 8px 0 10px; font-size: 20px;">{{ $activeGate->name }}</h3>
                        <div class="form-hint">{{ $activeGate->event?->name ?? '-' }}</div>
                        <div style="margin-top: 16px;" class="aside-illustration">
                            <div>
                                <strong style="display: block; font-size: 18px; color: #334155;">Siapkan alur scan</strong>
                                <div style="margin-top: 8px;">Dashboard ini sudah disusun untuk mode scan cepat, input USB, dan pencarian peserta.</div>
                            </div>
                        </div>
                        <div class="gate-meta" style="margin-top: 16px;">
                            <span class="badge {{ $activeGate->is_active ? 'badge-success' : 'badge-warning' }}">
                                {{ $activeGate->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <span class="badge">Scan officer: {{ $activeGate->assignedUsers->count() }}</span>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const input = document.getElementById('q');
                const form = document.getElementById('gate-scan-form');
                const enterButton = document.getElementById('gate-scan-mode-enter');
                const autoButton = document.getElementById('gate-scan-mode-auto');
                const modeKey = 'gate.scan.mode';
                let submitTimer = null;
                let mode = localStorage.getItem(modeKey) || 'enter';

                if (! input || ! form) {
                    return;
                }

                const syncMode = () => {
                    const isAuto = mode === 'auto';

                    enterButton?.classList.toggle('is-active', ! isAuto);
                    autoButton?.classList.toggle('is-active', isAuto);
                    input.setAttribute('data-scan-mode', mode);
                    localStorage.setItem(modeKey, mode);
                };

                const submitScan = () => {
                    if (input.value.trim() === '') {
                        return;
                    }

                    form.requestSubmit();
                };

                enterButton?.addEventListener('click', () => {
                    mode = 'enter';
                    syncMode();
                    input.focus();
                });

                autoButton?.addEventListener('click', () => {
                    mode = 'auto';
                    syncMode();
                    input.focus();
                });

                input.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();
                    clearTimeout(submitTimer);
                    submitScan();
                });

                input.addEventListener('input', () => {
                    if (mode !== 'auto') {
                        return;
                    }

                    clearTimeout(submitTimer);
                    submitTimer = window.setTimeout(() => {
                        submitScan();
                    }, 350);
                });

                form.addEventListener('submit', () => {
                    clearTimeout(submitTimer);
                });

                syncMode();
                input.focus();
                input.select();
            })();
        </script>
    @endpush
@endsection
