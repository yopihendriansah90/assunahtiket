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
        $hasAnyGate = $gates->isNotEmpty();
        $isSuperAdmin = $user?->hasRole('super_admin') ?? false;
        $initialScanPayload = $scanResult
            ? [
                'scanResult' => [
                    'status' => $scanStatus,
                    'message' => data_get($scanResult, 'message'),
                    'gate_name' => data_get($scanResult, 'gate_name', $activeGate?->name),
                    'gate_code' => data_get($scanResult, 'gate_code'),
                    'query' => data_get($scanResult, 'query'),
                    'ticket' => $scanTicket
                        ? [
                            'name' => $scanTicket?->student?->name ?? '-',
                            'class' => $scanTicket?->student?->eventClass?->name ?? '-',
                            'mother_name' => $scanTicket?->student?->mother_name ?? '-',
                            'mother_whatsapp' => $scanTicket?->student?->mother_whatsapp ?? '-',
                            'ticket_code' => $scanTicket?->ticket_code,
                            'qr_token' => $scanTicket?->qr_token,
                            'event_name' => $scanTicket?->event?->name ?? '-',
                        ]
                        : null,
                    'checkin' => $scanCheckin
                        ? [
                            'checked_in_at' => $scanCheckin?->checked_in_at?->format('d/m/Y H:i:s'),
                            'scan_method' => $scanCheckin?->scan_method,
                        ]
                        : null,
                ],
            ]
            : null;
    @endphp

    <div class="gate-mobile-shell" id="gate-dashboard" data-stats-url="{{ route('gate.stats', ['gate' => $activeGate?->id]) }}">
        <header class="gate-mobile-topbar">
            <div class="gate-mobile-title-wrap">
                <div class="gate-mobile-logo">⌁</div>
                <div>
                    <h1 class="gate-mobile-title">{{ $activeGate?->name ?? 'Dashboard Gate' }}</h1>
                    <p class="gate-mobile-subtitle">{{ $activeGate?->event?->name ?? 'Pilih gate dan siapkan proses scan QR.' }}</p>
                </div>
            </div>
            <details class="gate-mobile-settings">
                <summary class="gate-mobile-settings-button" aria-label="Pengaturan scanner">⚙</summary>
                <div class="gate-mobile-settings-panel">
                    <div class="gate-mobile-settings-head">Pengaturan Scanner</div>
                    <label class="scanner-setting-toggle" for="gate-auto-close-toggle">
                        <input id="gate-auto-close-toggle" type="checkbox" checked>
                        <span class="scanner-setting-toggle-indicator"></span>
                        <span class="scanner-setting-toggle-copy">
                            <strong>Auto-close Notifikasi</strong>
                            <small id="gate-auto-close-help">Aktif: modal tertutup otomatis 5 detik, scanner menunggu 5 detik.</small>
                        </span>
                    </label>
                    <div class="gate-mobile-settings-actions">
                        <form method="POST" action="{{ route('gate.logout') }}">
                            @csrf
                            <button type="submit" class="button button-ghost gate-mobile-settings-logout">Logout</button>
                        </form>
                    </div>
                </div>
            </details>
        </header>

        @if (! $activeGate)
            <div class="gate-mobile-empty">
                @if (! $hasAnyGate && $isSuperAdmin)
                    <strong>Belum ada gate yang dibuat.</strong>
                    <div>Silakan buat gate dulu dari panel admin agar dashboard gate bisa digunakan.</div>
                @else
                    <strong>Tidak ada gate yang ditugaskan.</strong>
                    <div>Hubungkan akun ini ke gate terlebih dulu dari menu Pintu Masuk di panel admin.</div>
                @endif
            </div>
        @else
            <main class="gate-mobile-main">
                <section class="gate-mobile-search">
                    <form method="POST" action="{{ route('gate.scan') }}" id="gate-scan-form" class="gate-mobile-search-form">
                        @csrf
                        <input type="hidden" name="gate_id" value="{{ $activeGate->id }}">
                        <div class="gate-mobile-search-box">
                            <span class="gate-mobile-search-icon">⌕</span>
                            <input
                                id="q"
                                class="gate-mobile-search-input"
                                type="text"
                                name="q"
                                value="{{ old('q') }}"
                                placeholder="Enter Ticket ID manually..."
                                enterkeyhint="done"
                                autocomplete="off"
                                autofocus
                            >
                            <a class="gate-mobile-search-clear" href="{{ route('gate.dashboard', ['gate' => $activeGate->id]) }}" aria-label="Bersihkan input">✕</a>
                        </div>
                        <div class="gate-mobile-mode-row">
                            <div class="mode-toggle" role="group" aria-label="Mode input scanner">
                                <span class="mode-toggle-label">Mode</span>
                                <button type="button" class="mode-option is-active" id="gate-scan-mode-enter" data-mode="enter">Enter</button>
                                <button type="button" class="mode-option" id="gate-scan-mode-auto" data-mode="auto">Auto</button>
                            </div>
                            <button type="submit" class="button button-primary gate-mobile-search-submit">Scan</button>
                        </div>
                    </form>
                </section>

                <section class="gate-mobile-gates">
                    <div class="gate-mobile-chip-list">
                        @foreach ($gates as $gate)
                            <a href="{{ route('gate.dashboard', ['gate' => $gate->id]) }}" class="gate-mobile-chip {{ $activeGate?->id === $gate->id ? 'is-active' : '' }}">
                                {{ $gate->name }}
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="gate-mobile-camera-section">
                    <div class="gate-mobile-camera-status-row">
                        <span id="scan-status-badge" class="badge {{ $scanResult ? ($scanStatus === 'success' ? 'badge-success' : ($scanStatus === 'already_scanned' ? 'badge-warning' : 'badge-danger')) : 'badge-warning' }}">
                            {{ $scanResult ? ($scanStatus === 'success' ? 'Berhasil' : ($scanStatus === 'already_scanned' ? 'Sudah Scan' : 'Tidak Ditemukan')) : 'Siap scan' }}
                        </span>
                        <span id="camera-status-badge" class="badge badge-warning camera-status">
                            <span class="camera-status-dot"></span>
                            <span id="camera-status-text">Menyiapkan Kamera</span>
                        </span>
                    </div>
                    <div class="gate-mobile-camera-frame" id="camera-frame">
                        <div id="camera-reader" class="camera-reader is-hidden" aria-hidden="true"></div>
                        <span class="camera-corner tl"></span>
                        <span class="camera-corner tr"></span>
                        <span class="camera-corner bl"></span>
                        <span class="camera-corner br"></span>
                        <div class="camera-scan-line"></div>
                        <div class="camera-placeholder" id="camera-placeholder">
                            <div>
                                <div class="gate-mobile-camera-pill" id="camera-placeholder-badge">Preview Kamera</div>
                                <strong id="camera-placeholder-title">Siapkan scanner QR</strong>
                                <div class="gate-mobile-camera-copy" id="camera-placeholder-message">Area ini akan dipakai untuk kamera scan dan input barcode USB.</div>
                            </div>
                        </div>
                    </div>
                    <p class="gate-mobile-camera-hint" id="camera-message">Position ticket QR code within frame</p>
                    <div class="gate-mobile-camera-selector">
                        <label class="gate-mobile-camera-selector-label" for="camera-device-select">Pilih Kamera</label>
                        <select id="camera-device-select" class="gate-mobile-camera-selector-input">
                            <option value="">Mendeteksi kamera...</option>
                        </select>
                    </div>
                    <div class="camera-actions">
                        <button type="button" class="button button-primary" id="camera-toggle-button">Hentikan Kamera</button>
                        <button type="button" class="button button-ghost" id="camera-switch-button">Ganti Kamera</button>
                    </div>
                    <div id="scanner-readiness-indicator" class="scanner-readiness-indicator is-ready">
                        <span id="scanner-readiness-dot" class="scanner-readiness-dot"></span>
                        <div class="scanner-readiness-copy">
                            <strong id="scanner-readiness-title">Scanner siap</strong>
                            <small id="scanner-readiness-message">Scanner siap menerima QR atau kode tiket berikutnya.</small>
                        </div>
                    </div>
                </section>

                <section class="gate-mobile-recent">
                    <div class="gate-mobile-section-head">
                        <h2>Recent Scans</h2>
                        <span class="gate-mobile-section-link">Live</span>
                    </div>
                    <div id="recent-scans-body" class="gate-mobile-recent-list">
                        @forelse ($recentScans as $scan)
                            <article class="gate-mobile-recent-card">
                                <div class="gate-mobile-recent-icon gate-history-status-{{ $scan['status'] === 'Invalid' ? 'missing' : ($scan['status'] === 'Sudah Digunakan' ? 'already_scanned' : 'success') }}">
                                    {{ $scan['status'] === 'Invalid' ? '✕' : ($scan['status'] === 'Sudah Digunakan' ? '!' : '✓') }}
                                </div>
                                <div class="gate-mobile-recent-copy">
                                    <div class="gate-mobile-recent-name">{{ $scan['student'] ?? '-' }}</div>
                                    <div class="gate-mobile-recent-meta">{{ $scan['ticket_code'] ?? '-' }} | {{ $scan['status'] ?? '-' }}</div>
                                </div>
                                <div class="gate-mobile-recent-time">{{ isset($scan['time']) ? \Illuminate\Support\Str::of($scan['time'])->substr(0, 5) : '-' }}</div>
                            </article>
                        @empty
                            <div class="gate-mobile-empty-history">Belum ada riwayat scan.</div>
                        @endforelse
                    </div>
                </section>

                <div class="gate-mobile-hidden-state" aria-hidden="true">
                    <span id="scan-result-badge" class="badge {{ $scanStatus === 'success' ? 'badge-success' : ($scanStatus === 'already_scanned' ? 'badge-warning' : 'badge-danger') }}">
                        {{ $scanStatus === 'success' ? 'Check-in berhasil' : ($scanStatus === 'already_scanned' ? 'Sudah check-in' : 'Tidak ditemukan') }}
                    </span>
                    <div id="scan-result-banner" class="{{ $scanResult && $scanStatus === 'success' ? '' : 'is-empty' }}"></div>
                    <div id="scan-result-icon">{{ $scanResult ? ($scanStatus === 'success' ? '✓' : '!') : '⌁' }}</div>
                    <div id="scan-result-title">{{ $scanResult ? ($scanStatus === 'success' ? 'VALID' : 'INFO') : 'Menunggu QR' }}</div>
                    <div id="scan-result-message">{{ data_get($scanResult, 'message', 'Setelah QR terbaca, check-in dibuat otomatis dan hasilnya muncul di sini.') }}</div>
                    <div data-scan-field="student_name">{{ $scanTicket?->student?->name ?? '-' }}</div>
                    <div data-scan-field="student_class">{{ $scanTicket?->student?->eventClass?->name ?? '-' }}</div>
                    <div data-scan-field="ticket_code">{{ $scanTicket?->ticket_code ?? '-' }}</div>
                    <div data-scan-field="qr_token">{{ $scanTicket?->qr_token ?? '-' }}</div>
                    <div data-scan-field="gate_name">{{ data_get($scanResult, 'gate_name', $activeGate->name) }}</div>
                    <div data-scan-field="checked_in_at">{{ $scanCheckin?->checked_in_at?->format('d/m/Y H:i:s') ?? '-' }}</div>
                    <div data-scan-field="scan_method">{{ $scanCheckin?->scan_method ?? '-' }}</div>
                    <div data-stat-key="total_hadir">{{ $gateStats['total_hadir'] }}</div>
                    <div data-stat-key="belum_scan">{{ $gateStats['belum_scan'] }}</div>
                    <div data-stat-key="sudah_scan">{{ $gateStats['sudah_scan'] }}</div>
                    <div data-stat-key="ditolak">{{ $gateStats['ditolak'] }}</div>
                </div>
            </main>
            @include('gate._bottom_nav', ['activeTab' => 'scanner'])
        @endif
    </div>

    <div
        id="scan-feedback-modal"
        class="scan-modal"
        aria-hidden="true"
        role="dialog"
        aria-modal="true"
        aria-labelledby="scan-feedback-title"
    >
        <div class="scan-modal-card">
            <div id="scan-feedback-head" class="scan-modal-head is-success">
                <div id="scan-feedback-icon" class="scan-modal-icon">✓</div>
                <p id="scan-feedback-title" class="scan-modal-title">Scan Berhasil</p>
            </div>
            <div class="scan-modal-body">
                <p id="scan-feedback-message" class="scan-modal-message">
                    Tiket valid dan check-in berhasil diproses.
                </p>
                <div class="scan-modal-details">
                    <div class="scan-modal-detail">
                        <span class="scan-modal-detail-label">Nama Peserta</span>
                        <span id="scan-feedback-student-name" class="scan-modal-detail-value">-</span>
                    </div>
                    <div class="scan-modal-detail">
                        <span class="scan-modal-detail-label">Kelas</span>
                        <span id="scan-feedback-student-class" class="scan-modal-detail-value">-</span>
                    </div>
                    <div class="scan-modal-detail">
                        <span class="scan-modal-detail-label">Nama Ibu</span>
                        <span id="scan-feedback-mother-name" class="scan-modal-detail-value">-</span>
                    </div>
                    <div class="scan-modal-detail">
                        <span class="scan-modal-detail-label">Kode Tiket</span>
                        <span id="scan-feedback-ticket-code" class="scan-modal-detail-value">-</span>
                    </div>
                </div>
                <div id="scan-feedback-meta" class="scan-modal-meta">
                    Notifikasi ini akan tertutup otomatis dalam 5 detik.
                </div>
                <div class="scan-modal-actions">
                    <button type="button" id="scan-feedback-ok-button" class="button button-primary">OK</button>
                </div>
            </div>
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
                const scanFeedbackModal = document.getElementById('scan-feedback-modal');
                const scanFeedbackHead = document.getElementById('scan-feedback-head');
                const scanFeedbackIcon = document.getElementById('scan-feedback-icon');
                const scanFeedbackTitle = document.getElementById('scan-feedback-title');
                const scanFeedbackMessage = document.getElementById('scan-feedback-message');
                const scanFeedbackMeta = document.getElementById('scan-feedback-meta');
                const scanFeedbackStudentName = document.getElementById('scan-feedback-student-name');
                const scanFeedbackStudentClass = document.getElementById('scan-feedback-student-class');
                const scanFeedbackMotherName = document.getElementById('scan-feedback-mother-name');
                const scanFeedbackTicketCode = document.getElementById('scan-feedback-ticket-code');
                const scanFeedbackOkButton = document.getElementById('scan-feedback-ok-button');
                const autoCloseToggle = document.getElementById('gate-auto-close-toggle');
                const autoCloseHelp = document.getElementById('gate-auto-close-help');
                const scannerReadinessIndicator = document.getElementById('scanner-readiness-indicator');
                const scannerReadinessTitle = document.getElementById('scanner-readiness-title');
                const scannerReadinessMessage = document.getElementById('scanner-readiness-message');
                const enterButton = document.getElementById('gate-scan-mode-enter');
                const autoButton = document.getElementById('gate-scan-mode-auto');
                const cameraReader = document.getElementById('camera-reader');
                const cameraPlaceholder = document.getElementById('camera-placeholder');
                const cameraStatusBadge = document.getElementById('camera-status-badge');
                const cameraStatusText = document.getElementById('camera-status-text');
                const cameraMessage = document.getElementById('camera-message');
                const cameraToggleButton = document.getElementById('camera-toggle-button');
                const cameraSwitchButton = document.getElementById('camera-switch-button');
                const cameraDeviceSelect = document.getElementById('camera-device-select');
                const cameraPlaceholderBadge = document.getElementById('camera-placeholder-badge');
                const cameraPlaceholderTitle = document.getElementById('camera-placeholder-title');
                const cameraPlaceholderMessage = document.getElementById('camera-placeholder-message');
                const modeKey = 'gate.scan.mode';
                const autoCloseKey = 'gate.scan.autoClose';
                const recentScansUrl = '{{ route('gate.recent-scans', ['gate' => $activeGate?->id]) }}';
                let submitTimer = null;
                let mode = localStorage.getItem(modeKey) || 'enter';
                let autoCloseEnabled = localStorage.getItem(autoCloseKey) !== '0';
                let html5QrCode = null;
                let cameraDevices = [];
                let activeCameraIndex = 0;
                let isSubmittingScan = false;
                let cameraEnabled = true;
                let isCameraRunning = false;
                let feedbackModalTimer = null;
                let feedbackModalCountdownTimer = null;
                let scanCooldownUntil = 0;

                if (! input || ! form) {
                    return;
                }

                const isFeedbackModalVisible = () => {
                    return scanFeedbackModal?.classList.contains('is-visible') ?? false;
                };

                const isScanBlocked = () => {
                    return isSubmittingScan || isFeedbackModalVisible() || Date.now() < scanCooldownUntil;
                };

                const syncMode = () => {
                    const isAuto = mode === 'auto';
                    enterButton?.classList.toggle('is-active', ! isAuto);
                    autoButton?.classList.toggle('is-active', isAuto);
                    input.setAttribute('data-scan-mode', mode);
                    localStorage.setItem(modeKey, mode);
                };

                const syncAutoCloseSetting = () => {
                    if (autoCloseToggle) {
                        autoCloseToggle.checked = autoCloseEnabled;
                    }

                    if (autoCloseHelp) {
                        autoCloseHelp.textContent = autoCloseEnabled
                            ? 'Aktif: modal tertutup otomatis 5 detik, scanner menunggu 5 detik.'
                            : 'Nonaktif: tutup modal dengan tombol OK, scanner menunggu sampai modal ditutup.';
                    }

                    localStorage.setItem(autoCloseKey, autoCloseEnabled ? '1' : '0');
                };

                const submitScan = () => {
                    if (input.value.trim() === '' || isScanBlocked()) {
                        return;
                    }

                    form.requestSubmit();
                };

                const setCameraStatus = (type, text, message = null) => {
                    if (cameraStatusBadge) {
                        cameraStatusBadge.classList.remove('badge-success', 'badge-warning');
                        cameraStatusBadge.classList.add(type === 'success' ? 'badge-success' : 'badge-warning');
                    }

                    if (cameraStatusText) {
                        cameraStatusText.textContent = text;
                    }

                    if (cameraMessage && message !== null) {
                        cameraMessage.textContent = message;
                    }
                };

                const setCameraPlaceholder = (title, message, badge = 'Preview Kamera') => {
                    if (cameraPlaceholderTitle) {
                        cameraPlaceholderTitle.textContent = title;
                    }

                    if (cameraPlaceholderMessage) {
                        cameraPlaceholderMessage.textContent = message;
                    }

                    if (cameraPlaceholderBadge) {
                        cameraPlaceholderBadge.textContent = badge;
                    }
                };

                const showCameraPreview = (show) => {
                    cameraReader?.classList.toggle('is-hidden', ! show);
                    cameraPlaceholder?.classList.toggle('is-hidden', show);
                };

                const isMobileDevice = () => {
                    return /android|iphone|ipad|ipod/i.test(window.navigator.userAgent || '');
                };

                const getPreferredCameraIndex = (devices) => {
                    if (! Array.isArray(devices) || devices.length === 0) {
                        return 0;
                    }

                    const rearCameraIndex = devices.findIndex((device) => {
                        const label = String(device?.label || '').toLowerCase();

                        return label.includes('back')
                            || label.includes('rear')
                            || label.includes('environment')
                            || label.includes('belakang');
                    });

                    if (rearCameraIndex !== -1) {
                        return rearCameraIndex;
                    }

                    return isMobileDevice() ? Math.min(1, devices.length - 1) : 0;
                };

                const getCameraLabel = (device, index) => {
                    const fallback = `Kamera ${index + 1}`;
                    const rawLabel = String(device?.label || '').trim();

                    if (rawLabel === '') {
                        return fallback;
                    }

                    return rawLabel;
                };

                const syncCameraDeviceOptions = () => {
                    if (! cameraDeviceSelect) {
                        return;
                    }

                    if (! Array.isArray(cameraDevices) || cameraDevices.length === 0) {
                        cameraDeviceSelect.innerHTML = '<option value="">Tidak ada kamera</option>';
                        cameraDeviceSelect.setAttribute('disabled', 'disabled');
                        return;
                    }

                    cameraDeviceSelect.innerHTML = cameraDevices.map((device, index) => `
                        <option value="${device.id ?? ''}" ${index === activeCameraIndex ? 'selected' : ''}>
                            ${getCameraLabel(device, index)}
                        </option>
                    `).join('');

                    if (cameraDevices.length <= 1) {
                        cameraDeviceSelect.setAttribute('disabled', 'disabled');
                    } else {
                        cameraDeviceSelect.removeAttribute('disabled');
                    }
                };

                const getQrboxConfig = () => {
                    const frameWidth = cameraReader?.clientWidth || 320;
                    const qrboxSize = Math.max(180, Math.min(280, Math.floor(frameWidth * 0.62)));

                    return {
                        width: qrboxSize,
                        height: qrboxSize,
                    };
                };

                const stopCameraStream = async () => {
                    if (! html5QrCode || ! isCameraRunning) {
                        return;
                    }

                    try {
                        await html5QrCode.stop();
                    } catch (error) {
                    }

                    try {
                        html5QrCode.clear();
                    } catch (error) {
                    }

                    isCameraRunning = false;
                };

                const startCameraStream = async () => {
                    if (! cameraReader) {
                        return;
                    }

                    const scannerLib = window.GateScannerLib;

                    if (! scannerLib?.Html5Qrcode) {
                        setCameraStatus('warning', 'Scanner Tidak Tersedia', 'Library scanner QR belum termuat dengan benar.');
                        setCameraPlaceholder('Scanner tidak tersedia', 'Muat ulang halaman lalu coba lagi. Jika masih gagal, periksa asset frontend aplikasi.', 'Scanner Tidak Tersedia');
                        return;
                    }

                    if (! ('mediaDevices' in navigator) || ! navigator.mediaDevices.getUserMedia) {
                        setCameraStatus('warning', 'Kamera Tidak Didukung', 'Browser ini tidak mendukung akses webcam untuk scan QR.');
                        setCameraPlaceholder('Browser tidak mendukung kamera', 'Gunakan browser modern yang mendukung akses webcam.', 'Kamera Tidak Tersedia');
                        cameraToggleButton?.setAttribute('disabled', 'disabled');
                        cameraSwitchButton?.setAttribute('disabled', 'disabled');
                        return;
                    }

                    if (! html5QrCode) {
                        html5QrCode = new scannerLib.Html5Qrcode('camera-reader', {
                            formatsToSupport: [scannerLib.Html5QrcodeSupportedFormats.QR_CODE],
                            useBarCodeDetectorIfSupported: false,
                            verbose: false,
                        });
                    }

                    try {
                        cameraDevices = await scannerLib.Html5Qrcode.getCameras();

                        cameraSwitchButton?.toggleAttribute('disabled', cameraDevices.length <= 1);
                        if (activeCameraIndex >= cameraDevices.length) {
                            activeCameraIndex = 0;
                        }

                        if (cameraDevices.length > 0 && activeCameraIndex === 0) {
                            activeCameraIndex = getPreferredCameraIndex(cameraDevices);
                        }

                        syncCameraDeviceOptions();

                        const selectedDevice = cameraDevices[activeCameraIndex] ?? cameraDevices[0] ?? null;

                        await stopCameraStream();
                        setCameraStatus('warning', 'Menyalakan Kamera', 'Meminta izin akses webcam untuk scanner QR.');

                        if (! selectedDevice) {
                            throw new Error('Tidak ada kamera yang tersedia.');
                        }

                        await html5QrCode.start(
                            selectedDevice.id ?? { facingMode: 'environment' },
                            {
                                fps: isMobileDevice() ? 12 : 10,
                                qrbox: getQrboxConfig(),
                                aspectRatio: isMobileDevice() ? 1 : 4 / 3,
                                disableFlip: false,
                            },
                            (decodedText) => {
                                if (isScanBlocked() || input.value.trim() !== '') {
                                    return;
                                }

                                input.value = decodedText.trim();
                                submitScan();
                            },
                            () => {
                            },
                        );

                        showCameraPreview(true);
                        setCameraPlaceholder('Kamera aktif', 'Arahkan QR code ke area kamera untuk scan otomatis.', 'Kamera Aktif');
                        setCameraStatus(
                            'success',
                            'Kamera Aktif',
                            selectedDevice?.label
                                ? `Kamera aktif: ${selectedDevice.label}`
                                : (isMobileDevice()
                                    ? 'Kamera belakang diutamakan dan siap untuk scan QR.'
                                    : 'Kamera aktif dan siap untuk scan QR.'),
                        );
                        cameraToggleButton?.removeAttribute('disabled');
                        cameraToggleButton && (cameraToggleButton.textContent = 'Hentikan Kamera');
                        cameraEnabled = true;
                        isCameraRunning = true;
                    } catch (error) {
                        showCameraPreview(false);
                        setCameraStatus('warning', 'Kamera Tidak Aktif', 'Izin kamera ditolak, webcam tidak bisa diakses, atau scanner gagal dijalankan.');
                        setCameraPlaceholder('Kamera tidak dapat diakses', 'Periksa izin browser, koneksi webcam USB, lalu coba lagi. Jika webcam test berjalan, kemungkinan izin kamera untuk situs ini belum diberikan.', 'Kamera Tidak Aktif');
                    }
                };

                const toggleCamera = async () => {
                    cameraEnabled = ! cameraEnabled;

                    if (cameraEnabled) {
                        await startCameraStream();
                        return;
                    }

                    await stopCameraStream();
                    showCameraPreview(false);
                    setCameraStatus('warning', 'Kamera Dimatikan', 'Kamera dimatikan. Anda masih bisa scan dengan USB scanner atau input manual.');
                    setCameraPlaceholder('Kamera dimatikan', 'Klik tombol nyalakan kembali untuk memakai webcam sebagai scanner QR.', 'Kamera Nonaktif');
                    if (cameraToggleButton) {
                        cameraToggleButton.textContent = 'Nyalakan Kamera';
                    }
                };

                const switchCamera = async () => {
                    if (cameraDevices.length <= 1) {
                        return;
                    }

                    activeCameraIndex = (activeCameraIndex + 1) % cameraDevices.length;
                    cameraEnabled = true;
                    await startCameraStream();
                };

                const setScanField = (key, value) => {
                    document.querySelectorAll(`[data-scan-field="${key}"]`).forEach((node) => {
                        node.textContent = value ?? '-';
                    });
                };

                const setFeedbackDetail = (node, value) => {
                    if (node) {
                        node.textContent = value && String(value).trim() !== '' ? value : '-';
                    }
                };

                const setScannerReadiness = (state, message = null) => {
                    if (! scannerReadinessIndicator || ! scannerReadinessTitle || ! scannerReadinessMessage) {
                        return;
                    }

                    scannerReadinessIndicator.classList.remove('is-ready', 'is-waiting', 'is-paused');

                    if (state === 'waiting') {
                        scannerReadinessIndicator.classList.add('is-waiting');
                        scannerReadinessTitle.textContent = 'Scanner menunggu';
                        scannerReadinessMessage.textContent = message || 'Tunggu hingga jeda scan selesai sebelum memindai tiket berikutnya.';
                        return;
                    }

                    if (state === 'paused') {
                        scannerReadinessIndicator.classList.add('is-paused');
                        scannerReadinessTitle.textContent = 'Scanner ditahan';
                        scannerReadinessMessage.textContent = message || 'Tutup notifikasi dengan tombol OK untuk melanjutkan proses scan.';
                        return;
                    }

                    scannerReadinessIndicator.classList.add('is-ready');
                    scannerReadinessTitle.textContent = 'Scanner siap';
                    scannerReadinessMessage.textContent = message || 'Scanner siap menerima QR atau kode tiket berikutnya.';
                };

                const hideFeedbackModal = () => {
                    if (! scanFeedbackModal) {
                        return;
                    }

                    clearTimeout(feedbackModalTimer);
                    clearInterval(feedbackModalCountdownTimer);
                    scanFeedbackModal.classList.remove('is-visible');
                    scanFeedbackModal.setAttribute('aria-hidden', 'true');
                    if (! autoCloseEnabled) {
                        scanCooldownUntil = 0;
                        setScannerReadiness('ready');
                    }
                    input.focus();
                    input.select();
                };

                const showFeedbackModal = (status, message, result = {}) => {
                    if (! scanFeedbackModal || ! scanFeedbackHead || ! scanFeedbackIcon || ! scanFeedbackTitle || ! scanFeedbackMessage) {
                        return;
                    }

                    const variant = status === 'success'
                        ? {
                            headClass: 'is-success',
                            icon: '✓',
                            title: 'Scan Berhasil',
                            meta: 'Tiket valid dan check-in berhasil diproses.',
                        }
                        : status === 'already_scanned'
                            ? {
                                headClass: 'is-warning',
                                icon: '!',
                                title: 'Tiket Sudah Pernah Masuk',
                                meta: 'Tiket ini sudah pernah digunakan untuk check-in.',
                            }
                            : {
                                headClass: 'is-danger',
                                icon: '✕',
                                title: 'Tiket Tidak Valid',
                                meta: 'QR code atau kode tiket tidak ditemukan pada pintu masuk aktif.',
                            };

                    clearTimeout(feedbackModalTimer);
                    clearInterval(feedbackModalCountdownTimer);
                    scanFeedbackHead.classList.remove('is-success', 'is-warning', 'is-danger');
                    scanFeedbackHead.classList.add(variant.headClass);
                    scanFeedbackIcon.textContent = variant.icon;
                    scanFeedbackTitle.textContent = variant.title;
                    scanFeedbackMessage.textContent = message || variant.meta;
                    setFeedbackDetail(scanFeedbackStudentName, result?.ticket?.name);
                    setFeedbackDetail(scanFeedbackStudentClass, result?.ticket?.class);
                    setFeedbackDetail(scanFeedbackMotherName, result?.ticket?.mother_name);
                    setFeedbackDetail(scanFeedbackTicketCode, result?.ticket?.ticket_code);

                    let countdown = 5;

                    if (scanFeedbackMeta) {
                        scanFeedbackMeta.textContent = autoCloseEnabled
                            ? `Notifikasi ini akan tertutup otomatis dalam ${countdown} detik.`
                            : 'Scanner ditahan sampai Anda menutup notifikasi ini dengan tombol OK.';
                    }

                    scanFeedbackModal.classList.add('is-visible');
                    scanFeedbackModal.setAttribute('aria-hidden', 'false');

                    if (! autoCloseEnabled) {
                        setScannerReadiness('paused');
                        return;
                    }

                    feedbackModalCountdownTimer = window.setInterval(() => {
                        countdown -= 1;

                        if (countdown <= 0) {
                            clearInterval(feedbackModalCountdownTimer);
                            return;
                        }

                        if (scanFeedbackMeta) {
                            scanFeedbackMeta.textContent = `Notifikasi ini akan tertutup otomatis dalam ${countdown} detik.`;
                        }

                        if (countdown > 0) {
                            setScannerReadiness('waiting', `Scanner siap lagi dalam ${countdown} detik.`);
                        }
                    }, 1000);

                    feedbackModalTimer = window.setTimeout(() => {
                        hideFeedbackModal();
                        setScannerReadiness('ready');
                    }, 5000);
                };

                const renderRecentScans = (scans) => {
                    if (! recentScansBody) {
                        return;
                    }

                    if (! Array.isArray(scans) || scans.length === 0) {
                        recentScansBody.innerHTML = '<div class="gate-mobile-empty-history">Belum ada riwayat scan.</div>';
                        return;
                    }

                    recentScansBody.innerHTML = scans.map((scan) => `
                        <article class="gate-mobile-recent-card">
                            <div class="gate-mobile-recent-icon">✓</div>
                            <div class="gate-mobile-recent-copy">
                                <div class="gate-mobile-recent-name">${scan.student ?? '-'}</div>
                                <div class="gate-mobile-recent-meta">${scan.ticket_code ?? '-'} | ${scan.status ?? 'Qr'}</div>
                            </div>
                            <div class="gate-mobile-recent-time">${scan.time ?? '-'}</div>
                        </article>
                    `).join('');
                };

                const applyScanResult = (payload) => {
                    const result = payload?.scanResult || {};
                    const ticket = result.ticket || null;
                    const checkin = result.checkin || null;
                    const status = result.status || 'missing';
                    const isAlreadyScanned = status === 'already_scanned';
                    const isSuccess = status === 'success';

                    if (scanStatusBadge) {
                        scanStatusBadge.textContent = isSuccess
                            ? 'Berhasil'
                            : isAlreadyScanned
                                ? 'Sudah Scan'
                                : 'Tidak Ditemukan';
                        scanStatusBadge.classList.remove('badge-success', 'badge-warning', 'badge-danger');
                        scanStatusBadge.classList.add(isSuccess ? 'badge-success' : isAlreadyScanned ? 'badge-warning' : 'badge-danger');
                    }

                    if (scanResultBadge) {
                        scanResultBadge.textContent = isSuccess
                            ? 'Check-in berhasil'
                            : isAlreadyScanned
                                ? 'Sudah check-in'
                                : 'Tidak ditemukan';
                        scanResultBadge.classList.remove('badge-success', 'badge-warning', 'badge-danger');
                        scanResultBadge.classList.add(isSuccess ? 'badge-success' : isAlreadyScanned ? 'badge-warning' : 'badge-danger');
                    }

                    if (scanResultBanner) {
                        scanResultBanner.classList.toggle('is-empty', ! isSuccess);
                    }

                    if (scanResultIcon) {
                        scanResultIcon.textContent = isSuccess ? '✓' : isAlreadyScanned ? '!' : '✕';
                    }

                    if (scanResultTitle) {
                        scanResultTitle.textContent = isSuccess
                            ? 'VALID'
                            : isAlreadyScanned
                                ? 'WARNING'
                                : 'INVALID';
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

                    renderRecentScans(payload?.recentScans ?? []);

                    scanCooldownUntil = autoCloseEnabled ? Date.now() + 5000 : 0;
                    setScannerReadiness(
                        autoCloseEnabled ? 'waiting' : 'paused',
                        autoCloseEnabled
                            ? 'Scanner siap lagi dalam 5 detik.'
                            : 'Tutup notifikasi dengan tombol OK untuk melanjutkan proses scan.',
                    );
                    showFeedbackModal(status, result.message || null, result);
                    input.value = '';
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
                        isSubmittingScan = true;
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
                    } finally {
                        isSubmittingScan = false;
                    }
                });

                syncMode();
                syncAutoCloseSetting();
                setScannerReadiness('ready');
                input.focus();
                input.select();
                autoCloseToggle?.addEventListener('change', (event) => {
                    autoCloseEnabled = Boolean(event.currentTarget?.checked);
                    syncAutoCloseSetting();
                });
                cameraToggleButton?.addEventListener('click', () => {
                    toggleCamera();
                });
                cameraSwitchButton?.addEventListener('click', () => {
                    switchCamera();
                });
                cameraDeviceSelect?.addEventListener('change', async (event) => {
                    const selectedId = String(event.currentTarget?.value || '');
                    const selectedIndex = cameraDevices.findIndex((device) => String(device?.id || '') === selectedId);

                    if (selectedIndex === -1) {
                        return;
                    }

                    activeCameraIndex = selectedIndex;
                    cameraEnabled = true;
                    await startCameraStream();
                });
                window.addEventListener('beforeunload', () => {
                    stopCameraStream();
                });
                scanFeedbackOkButton?.addEventListener('click', hideFeedbackModal);
                startCameraStream();

                @if ($initialScanPayload)
                    applyScanResult(@json($initialScanPayload));
                @endif

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
                        renderRecentScans(Array.isArray(payload.scans) ? payload.scans : []);
                    } catch (error) {
                    }
                };

                refreshRecentScans();
                window.setInterval(refreshRecentScans, 5000);
            })();
        </script>
    @endpush
@endsection
