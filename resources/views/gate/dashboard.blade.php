@extends('gate.layout', ['title' => 'Dashboard Gate'])

@section('content')
    @php
        $activeGate = $selectedGate;
        $scanResult = $scanResult ?? null;
        $scanTicket = data_get($scanResult, 'ticket');
        $scanCheckin = data_get($scanResult, 'checkin');
        $scanStatus = data_get($scanResult, 'status');
        $gateStats = $gateStats ?? [
            'total_hadir' => 0,
            'belum_scan' => 0,
            'sudah_scan' => 0,
            'ditolak' => 0,
        ];
        $recentScans = $recentScans ?? collect();
    @endphp

    <div class="card" id="gate-dashboard" data-stats-url="{{ route('gate.stats', ['gate' => $activeGate?->id]) }}">
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
                            <span id="scan-status-badge" class="badge {{ $scanResult ? ($scanStatus === 'success' ? 'badge-success' : 'badge-warning') : 'badge-warning' }}">
                                {{ $scanResult ? ($scanStatus === 'success' ? 'Berhasil' : ($scanStatus === 'already_scanned' ? 'Sudah Scan' : 'Tidak Ditemukan')) : 'Siap scan' }}
                            </span>
                        </div>
                        <div class="panel-body">
                            <div id="scan-result-banner" class="result-banner {{ $scanResult && $scanStatus === 'success' ? '' : 'is-empty' }}">
                                <div id="scan-result-icon" class="result-check">
                                    {{ $scanResult ? ($scanStatus === 'success' ? '✓' : '!') : '⌁' }}
                                </div>
                                <div>
                                    <p id="scan-result-title" class="result-title">
                                        {{ $scanResult ? ($scanStatus === 'success' ? 'VALID' : 'INFO') : 'Menunggu QR' }}
                                    </p>
                                    <p id="scan-result-message" class="result-subtitle" style="{{ $scanResult ? '' : 'margin-top: 0;' }}">
                                        {{ data_get($scanResult, 'message', 'Setelah QR terbaca, check-in dibuat otomatis dan hasilnya muncul di sini.') }}
                                    </p>
                                </div>
                            </div>

                            <div class="details-list">
                                <div class="detail-row">
                                    <div class="detail-label">Nama Peserta</div>
                                    <div class="detail-value" data-scan-field="student_name">{{ $scanTicket?->student?->name ?? '-' }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Kelas</div>
                                    <div class="detail-value" data-scan-field="student_class">{{ $scanTicket?->student?->eventClass?->name ?? '-' }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Kode Tiket</div>
                                    <div class="detail-value" data-scan-field="ticket_code">{{ $scanTicket?->ticket_code ?? '-' }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">QR Token</div>
                                    <div class="detail-value" data-scan-field="qr_token">{{ $scanTicket?->qr_token ?? '-' }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Gate</div>
                                    <div class="detail-value" data-scan-field="gate_name">{{ data_get($scanResult, 'gate_name', $activeGate->name) }}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Status</div>
                                    <div class="detail-value">
                                        @if ($scanResult)
                                            <span id="scan-result-badge" class="badge {{ $scanStatus === 'success' ? 'badge-success' : 'badge-warning' }}">
                                                {{ $scanStatus === 'success' ? 'Check-in berhasil' : ($scanStatus === 'already_scanned' ? 'Sudah check-in' : 'Tidak ditemukan') }}
                                            </span>
                                        @else
                                            <span id="scan-result-badge" class="badge badge-warning">Menunggu Scan</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Waktu Check-in</div>
                                    <div class="detail-value">
                                        <span data-scan-field="checked_in_at">{{ $scanCheckin?->checked_in_at?->format('d/m/Y H:i:s') ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Metode Scan</div>
                                    <div class="detail-value">
                                        <span data-scan-field="scan_method">{{ $scanCheckin?->scan_method ?? '-' }}</span>
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
                                <p class="stat-value" data-stat-key="total_hadir">{{ $gateStats['total_hadir'] }}</p>
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
                                <p class="stat-value" data-stat-key="belum_scan">{{ $gateStats['belum_scan'] }}</p>
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
                                <p class="stat-value" data-stat-key="sudah_scan">{{ $gateStats['sudah_scan'] }}</p>
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
                                <p class="stat-value" data-stat-key="ditolak">{{ $gateStats['ditolak'] }}</p>
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
                        <tbody id="recent-scans-body">
                            @forelse ($recentScans as $scan)
                                <tr>
                                    <td>{{ $scan->checked_in_at?->format('H:i:s') ?? '-' }}</td>
                                    <td>{{ $scan->ticket?->student?->name ?? '-' }}</td>
                                                    <td>{{ $scan->ticket?->ticket_code ?? '-' }}</td>
                                                    <td>
                                                        <span class="badge badge-success">
                                                            {{ ucfirst($scan->scan_method ?? 'qr') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" style="text-align: center; color: var(--muted); padding: 24px;">
                                                        Belum ada riwayat scan.
                                                    </td>
                                                </tr>
                                            @endforelse
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
                const dashboard = document.getElementById('gate-dashboard');
                const recentScansBody = document.getElementById('recent-scans-body');
                const scanStatusBadge = document.getElementById('scan-status-badge');
                const scanResultBadge = document.getElementById('scan-result-badge');
                const scanResultBanner = document.getElementById('scan-result-banner');
                const scanResultIcon = document.getElementById('scan-result-icon');
                const scanResultTitle = document.getElementById('scan-result-title');
                const scanResultMessage = document.getElementById('scan-result-message');
                const enterButton = document.getElementById('gate-scan-mode-enter');
                const autoButton = document.getElementById('gate-scan-mode-auto');
                const modeKey = 'gate.scan.mode';
                const recentScansUrl = '{{ route('gate.recent-scans', ['gate' => $activeGate->id]) }}';
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

                const setScanField = (key, value) => {
                    document.querySelectorAll(`[data-scan-field="${key}"]`).forEach((node) => {
                        node.textContent = value ?? '-';
                    });
                };

                const applyScanResult = (payload) => {
                    const result = payload?.scanResult || {};
                    const ticket = result.ticket || null;
                    const checkin = result.checkin || null;
                    const status = result.status || 'missing';

                    if (scanStatusBadge) {
                        scanStatusBadge.textContent = status === 'success'
                            ? 'Berhasil'
                            : status === 'already_scanned'
                                ? 'Sudah Scan'
                                : 'Tidak Ditemukan';
                        scanStatusBadge.classList.remove('badge-success', 'badge-warning');
                        scanStatusBadge.classList.add(status === 'success' ? 'badge-success' : 'badge-warning');
                    }

                    if (scanResultBadge) {
                        scanResultBadge.textContent = status === 'success'
                            ? 'Check-in berhasil'
                            : status === 'already_scanned'
                                ? 'Sudah check-in'
                                : 'Tidak ditemukan';
                        scanResultBadge.classList.remove('badge-success', 'badge-warning');
                        scanResultBadge.classList.add(status === 'success' ? 'badge-success' : 'badge-warning');
                    }

                    if (scanResultBanner) {
                        scanResultBanner.classList.toggle('is-empty', status !== 'success');
                    }

                    if (scanResultIcon) {
                        scanResultIcon.textContent = status === 'success' ? '✓' : '!';
                    }

                    if (scanResultTitle) {
                        scanResultTitle.textContent = status === 'success'
                            ? 'VALID'
                            : 'INFO';
                    }

                    if (scanResultMessage) {
                        scanResultMessage.textContent = result.message || 'Hasil scan ditampilkan di bawah.';
                    }

                    setScanField('student_name', ticket?.name ?? '-');
                    setScanField('student_class', ticket?.class ?? '-');
                    setScanField('ticket_code', ticket?.ticket_code ?? '-');
                    setScanField('qr_token', ticket?.qr_token ?? '-');
                    setScanField('gate_name', result.gate_name ?? '-');
                    setScanField('checked_in_at', checkin?.checked_in_at ?? '-');
                    setScanField('scan_method', checkin?.scan_method ?? '-');

                    if (payload?.gateStats) {
                        Object.entries(payload.gateStats).forEach(([key, value]) => {
                            const node = dashboard.querySelector(`[data-stat-key="${key}"]`);
                            if (node) {
                                node.textContent = String(value ?? 0);
                            }
                        });
                    }

                    if (Array.isArray(payload?.recentScans) && recentScansBody) {
                        if (payload.recentScans.length === 0) {
                            recentScansBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px;">Belum ada riwayat scan.</td></tr>';
                        } else {
                            recentScansBody.innerHTML = payload.recentScans.map((scan) => `
                                <tr>
                                    <td>${scan.time ?? '-'}</td>
                                    <td>${scan.student ?? '-'}</td>
                                    <td>${scan.ticket_code ?? '-'}</td>
                                    <td><span class="badge badge-success">${scan.status ?? 'Qr'}</span></td>
                                </tr>
                            `).join('');
                        }
                    }

                    input.value = '';
                    input.focus();
                    input.select();
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

                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    clearTimeout(submitTimer);

                    if (input.value.trim() === '') {
                        input.focus();
                        return;
                    }

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: new FormData(form),
                            credentials: 'same-origin',
                        });

                        if (! response.ok) {
                            return;
                        }

                        const payload = await response.json();
                        applyScanResult(payload);
                    } catch (error) {
                        // ignore polling errors
                    }
                });

                syncMode();
                input.focus();
                input.select();

                const refreshStats = async () => {
                    if (! dashboard) {
                        return;
                    }

                    const statsUrl = dashboard.getAttribute('data-stats-url');

                    if (! statsUrl) {
                        return;
                    }

                    try {
                        const response = await fetch(statsUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        if (! response.ok) {
                            return;
                        }

                        const payload = await response.json();
                        const stats = payload.stats || {};

                        Object.entries(stats).forEach(([key, value]) => {
                            const node = dashboard.querySelector(`[data-stat-key="${key}"]`);
                            if (node) {
                                node.textContent = String(value ?? 0);
                            }
                        });
                    } catch (error) {
                        // ignore polling errors
                    }
                };

                refreshStats();
                window.setInterval(refreshStats, 5000);

                const refreshRecentScans = async () => {
                    if (! recentScansBody) {
                        return;
                    }

                    try {
                        const response = await fetch(recentScansUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        if (! response.ok) {
                            return;
                        }

                        const payload = await response.json();
                        const scans = Array.isArray(payload.scans) ? payload.scans : [];

                        if (scans.length === 0) {
                            recentScansBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px;">Belum ada riwayat scan.</td></tr>';
                            return;
                        }

                        recentScansBody.innerHTML = scans.map((scan) => `
                            <tr>
                                <td>${scan.time ?? '-'}</td>
                                <td>${scan.student ?? '-'}</td>
                                <td>${scan.ticket_code ?? '-'}</td>
                                <td><span class="badge badge-success">${scan.status ?? 'Qr'}</span></td>
                            </tr>
                        `).join('');
                    } catch (error) {
                        // ignore polling errors
                    }
                };

                refreshRecentScans();
                window.setInterval(refreshRecentScans, 5000);
            })();
        </script>
    @endpush
@endsection
