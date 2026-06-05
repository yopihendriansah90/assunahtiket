@extends('gate.layout', ['title' => 'Riwayat Pemindaian'])

@section('content')
    <div class="gate-mobile-shell gate-desktop-shell gate-history-desktop-shell">
        <aside class="gate-desktop-sidebar">
            <div class="gate-desktop-sidebar-head">
                <h1 class="gate-desktop-sidebar-title">Gate Manager</h1>
                <div class="gate-desktop-gate-card">
                    <div class="gate-desktop-gate-icon" aria-hidden="true">
                        <x-carbon-ibm-engineering-requirements-doors-next class="gate-desktop-gate-icon-svg" />
                    </div>
                    <div>
                        <div class="gate-desktop-gate-name">{{ $selectedGateId > 0 ? ($gates->firstWhere('id', $selectedGateId)?->name ?? 'Gate Aktif') : 'Semua Gate' }}</div>
                        <div class="gate-desktop-gate-event">Riwayat scan seluruh gate yang dapat Anda akses</div>
                    </div>
                </div>
            </div>
            <nav class="gate-desktop-sidebar-nav" aria-label="Navigasi Gate Desktop">
                <a href="{{ route('gate.dashboard', request()->filled('gate') ? ['gate' => request()->integer('gate')] : []) }}" class="gate-desktop-sidebar-link">
                    <span class="gate-bottom-nav-icon gate-bottom-nav-icon-qr" aria-hidden="true">
                        <span class="qr-corner tl"></span>
                        <span class="qr-corner tr"></span>
                        <span class="qr-corner bl"></span>
                        <span class="qr-corner br"></span>
                        <span class="qr-dot"></span>
                    </span>
                    <span>Scanner</span>
                </a>
                <a href="{{ route('gate.history', request()->query()) }}" class="gate-desktop-sidebar-link is-active">
                    <span class="gate-desktop-sidebar-symbol" aria-hidden="true">↺</span>
                    <span>Riwayat</span>
                </a>
            </nav>
            <div class="gate-desktop-sidebar-footer">
                <form method="POST" action="{{ route('gate.logout') }}">
                    @csrf
                    <button type="submit" class="gate-desktop-logout-button">
                        <span class="gate-desktop-sidebar-symbol" aria-hidden="true">↪</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="gate-desktop-content">
            <header class="gate-mobile-topbar gate-history-topbar">
                <div class="gate-mobile-title-wrap gate-desktop-title-wrap">
                    <div class="gate-mobile-logo" aria-hidden="true">
                        <x-carbon-ibm-engineering-requirements-doors-next class="gate-desktop-gate-icon-svg" />
                    </div>
                    <div>
                        <h1 class="gate-mobile-title">Riwayat Pemindaian</h1>
                        <p class="gate-mobile-subtitle">Lihat riwayat scan dari gate yang bisa Anda akses.</p>
                    </div>
                </div>
                <div class="gate-desktop-topbar-actions">
                    <div class="gate-desktop-system-badge">
                        <span class="gate-desktop-system-dot"></span>
                        <span>Sistem: Online</span>
                    </div>
                    <details class="gate-mobile-settings gate-desktop-settings">
                        <summary class="gate-mobile-settings-button" aria-label="Pengaturan riwayat">⚙</summary>
                        <div class="gate-mobile-settings-panel">
                            <div class="gate-mobile-settings-head">Akses Riwayat</div>
                            <label class="scanner-setting-toggle" for="gate-theme-toggle-history">
                                <input id="gate-theme-toggle-history" type="checkbox" data-theme-toggle>
                                <span class="scanner-setting-toggle-indicator"></span>
                                <span class="scanner-setting-toggle-copy">
                                    <strong>Dark Mode</strong>
                                    <small data-theme-help>Nonaktif: gunakan tampilan terang.</small>
                                </span>
                            </label>
                            <div class="form-hint">Data yang tampil berasal dari log scan tersimpan di sistem.</div>
                            <div class="gate-mobile-settings-actions">
                                <a href="{{ route('gate.dashboard', request()->filled('gate') ? ['gate' => request()->integer('gate')] : []) }}" class="button button-ghost gate-mobile-settings-logout">Kembali ke Scanner</a>
                            </div>
                        </div>
                    </details>
                </div>
            </header>

            <main class="gate-mobile-main gate-history-main gate-history-desktop-main">
                <section class="gate-history-desktop-header">
                    <div>
                        <h2 class="gate-history-desktop-title">History Log</h2>
                        <p class="gate-history-desktop-copy">Rekaman validasi tiket real-time untuk seluruh gate aktif yang Anda kelola.</p>
                    </div>
                    <div class="gate-history-desktop-actions">
                        <a href="{{ route('gate.dashboard', request()->filled('gate') ? ['gate' => request()->integer('gate')] : []) }}" class="button button-primary">Buka Scanner</a>
                        <a href="{{ route('gate.history', request()->query()) }}" class="button button-ghost gate-history-desktop-refresh">Refresh Data</a>
                    </div>
                </section>

                <section class="gate-history-desktop-filters">
                    <form method="GET" action="{{ route('gate.history') }}" class="gate-history-desktop-filter-form">
                        <div class="gate-history-desktop-search">
                            <span class="gate-mobile-search-icon">⌕</span>
                            <input
                                class="gate-mobile-search-input gate-history-desktop-search-input"
                                type="text"
                                name="q"
                                value="{{ $search }}"
                                placeholder="Cari nama peserta atau kode tiket..."
                                autocomplete="off"
                            >
                        </div>
                        <div class="gate-history-desktop-filter-grid">
                            <label class="gate-history-desktop-filter-field">
                                <span>Status</span>
                                <select name="method" class="gate-history-desktop-select">
                                    <option value="all" @selected($scanMethod === 'all')>Semua Status</option>
                                    <option value="success" @selected($scanMethod === 'success')>Valid</option>
                                    <option value="already_scanned" @selected($scanMethod === 'already_scanned')>Sudah Digunakan</option>
                                    <option value="missing" @selected($scanMethod === 'missing')>Invalid</option>
                                </select>
                            </label>
                            <label class="gate-history-desktop-filter-field">
                                <span>Gate</span>
                                <select name="gate" class="gate-history-desktop-select">
                                    <option value="0" @selected($selectedGateId <= 0)>Semua Gate</option>
                                    @foreach ($gates as $gate)
                                        <option value="{{ $gate->id }}" @selected($selectedGateId === $gate->id)>{{ $gate->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <div class="gate-history-desktop-filter-actions">
                                <button type="submit" class="button button-primary">Terapkan</button>
                                <a href="{{ route('gate.history') }}" class="button button-ghost gate-history-desktop-clear">Reset</a>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="gate-history-desktop-table-card">
                    <div class="gate-history-desktop-table-wrap">
                        <table class="gate-history-desktop-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Peserta</th>
                                    <th>Kode Tiket</th>
                                    <th>Gate</th>
                                    <th>Kelas</th>
                                    <th>Waktu Scan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($historyScans as $scan)
                                    @php
                                        $initials = collect(explode(' ', trim((string) ($scan->student_name ?: 'Peserta'))))
                                            ->filter()
                                            ->take(2)
                                            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                            ->implode('');
                                        $statusLabel = $scan->status === 'success' ? 'Valid' : ($scan->status === 'already_scanned' ? 'Sudah Digunakan' : 'Invalid');
                                    @endphp
                                    <tr>
                                        <td class="gate-history-desktop-rownum">{{ ($historyScans->firstItem() ?? 1) + $loop->index }}</td>
                                        <td>
                                            <div class="gate-history-desktop-person">
                                                <div class="gate-history-desktop-avatar gate-history-status-{{ $scan->status }}">{{ $initials !== '' ? $initials : 'PS' }}</div>
                                                <div>
                                                    <div class="gate-history-desktop-person-name">{{ $scan->student_name ?: 'Peserta tidak dikenal' }}</div>
                                                    <div class="gate-history-desktop-person-meta">{{ $scan->query ?: '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="gate-history-desktop-mono">{{ $scan->ticket_code ?: '-' }}</td>
                                        <td>{{ $scan->gate?->name ?? '-' }}</td>
                                        <td>{{ $scan->class_name ?: '-' }}</td>
                                        <td>{{ $scan->scanned_at?->timezone('Asia/Jakarta')->format('d/m/Y • H:i:s \W\I\B') ?? '-' }}</td>
                                        <td>
                                            <span class="gate-history-desktop-status gate-history-status-{{ $scan->status }}">
                                                <span class="gate-history-desktop-status-dot"></span>
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="gate-mobile-empty-history">Belum ada data riwayat scan untuk filter ini.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="gate-history-desktop-table-footer">
                        <div class="gate-history-desktop-table-count">
                            Menampilkan {{ $historyScans->count() }} dari {{ $historyScans->total() }} data
                        </div>
                        @if ($historyScans->hasPages())
                            <div class="gate-history-pagination gate-history-desktop-pagination">
                                {{ $historyScans->links() }}
                            </div>
                        @endif
                    </div>
                </section>

                <section class="gate-history-desktop-stats">
                    <article class="gate-history-desktop-stat-card is-primary">
                        <span>Total Scan Hari Ini</span>
                        <strong>{{ number_format($todayTotal) }}</strong>
                    </article>
                    <article class="gate-history-desktop-stat-card">
                        <span>Valid</span>
                        <strong>{{ number_format($todaySuccess) }}</strong>
                    </article>
                    <article class="gate-history-desktop-stat-card">
                        <span>Sudah Digunakan</span>
                        <strong>{{ number_format($todayAlreadyScanned) }}</strong>
                    </article>
                    <article class="gate-history-desktop-stat-card is-danger">
                        <span>Invalid</span>
                        <strong>{{ number_format($todayMissing) }}</strong>
                    </article>
                </section>

                <section class="gate-history-mobile-stack">
                    <section class="gate-mobile-search gate-history-search">
                        <form method="GET" action="{{ route('gate.history') }}" class="gate-mobile-search-form">
                            @if ($selectedGateId > 0)
                                <input type="hidden" name="gate" value="{{ $selectedGateId }}">
                            @endif
                            @if ($scanMethod !== 'all')
                                <input type="hidden" name="method" value="{{ $scanMethod }}">
                            @endif
                            <div class="gate-mobile-search-box">
                                <span class="gate-mobile-search-icon">⌕</span>
                                <input
                                    class="gate-mobile-search-input"
                                    type="text"
                                    name="q"
                                    value="{{ $search }}"
                                    placeholder="Cari nama atau nomor tiket..."
                                    autocomplete="off"
                                >
                                @if ($search !== '')
                                    <a class="gate-mobile-search-clear" href="{{ route('gate.history', array_filter(['gate' => $selectedGateId ?: null, 'method' => $scanMethod !== 'all' ? $scanMethod : null])) }}" aria-label="Bersihkan pencarian">✕</a>
                                @endif
                            </div>
                        </form>
                        <div class="gate-mobile-chip-list">
                            <a href="{{ route('gate.history', array_filter(['q' => $search !== '' ? $search : null, 'method' => $scanMethod !== 'all' ? $scanMethod : null])) }}" class="gate-mobile-chip {{ $selectedGateId <= 0 ? 'is-active' : '' }}">Semua Gate</a>
                            @foreach ($gates as $gate)
                                <a href="{{ route('gate.history', array_filter(['gate' => $gate->id, 'q' => $search !== '' ? $search : null, 'method' => $scanMethod !== 'all' ? $scanMethod : null])) }}" class="gate-mobile-chip {{ $selectedGateId === $gate->id ? 'is-active' : '' }}">{{ $gate->name }}</a>
                            @endforeach
                        </div>
                        <div class="gate-history-filter-list">
                            <a href="{{ route('gate.history', array_filter(['gate' => $selectedGateId ?: null, 'q' => $search !== '' ? $search : null])) }}" class="gate-history-filter {{ $scanMethod === 'all' ? 'is-active' : '' }}">Semua</a>
                            <a href="{{ route('gate.history', array_filter(['gate' => $selectedGateId ?: null, 'q' => $search !== '' ? $search : null, 'method' => 'success'])) }}" class="gate-history-filter {{ $scanMethod === 'success' ? 'is-active' : '' }}">Valid</a>
                            <a href="{{ route('gate.history', array_filter(['gate' => $selectedGateId ?: null, 'q' => $search !== '' ? $search : null, 'method' => 'already_scanned'])) }}" class="gate-history-filter {{ $scanMethod === 'already_scanned' ? 'is-active' : '' }}">Sudah Digunakan</a>
                            <a href="{{ route('gate.history', array_filter(['gate' => $selectedGateId ?: null, 'q' => $search !== '' ? $search : null, 'method' => 'missing'])) }}" class="gate-history-filter {{ $scanMethod === 'missing' ? 'is-active' : '' }}">Invalid</a>
                        </div>
                    </section>

                    <section class="gate-history-stats">
                        <div class="gate-history-stat-primary">
                            <span>Total Scan Hari Ini</span>
                            <strong>{{ number_format($todayTotal) }}</strong>
                        </div>
                        <div class="gate-history-stat-side">
                            <div class="gate-history-stat-mini">
                                <span>Valid</span>
                                <strong>{{ number_format($todaySuccess) }}</strong>
                            </div>
                            <div class="gate-history-stat-mini is-alt">
                                <span>Sudah Digunakan</span>
                                <strong>{{ number_format($todayAlreadyScanned) }}</strong>
                            </div>
                            <div class="gate-history-stat-mini is-danger">
                                <span>Invalid</span>
                                <strong>{{ number_format($todayMissing) }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="gate-mobile-recent gate-history-list-shell">
                        <div class="gate-mobile-section-head">
                            <h2>Aktivitas Terkini</h2>
                            <span class="gate-mobile-section-link">{{ $historyScans->total() }} data</span>
                        </div>

                        <div class="gate-mobile-recent-list">
                            @forelse ($historyScans as $scan)
                                <article class="gate-mobile-recent-card gate-history-card">
                                    <div class="gate-mobile-recent-icon gate-history-status-{{ $scan->status }}">
                                        {{ $scan->status === 'success' ? '✓' : ($scan->status === 'already_scanned' ? '!' : '✕') }}
                                    </div>
                                    <div class="gate-mobile-recent-copy">
                                        <div class="gate-mobile-recent-name">{{ $scan->student_name ?: 'Peserta tidak dikenal' }}</div>
                                        <div class="gate-mobile-recent-meta">{{ $scan->ticket_code ?: ($scan->query ?: '-') }} | {{ $scan->class_name ?: '-' }}</div>
                                        <div class="gate-history-card-submeta">{{ $scan->gate?->name ?? '-' }} · {{ $scan->status === 'success' ? 'Valid' : ($scan->status === 'already_scanned' ? 'Sudah Digunakan' : 'Invalid') }}</div>
                                    </div>
                                    <div class="gate-mobile-recent-time">{{ $scan->scanned_at?->timezone('Asia/Jakarta')->format('H:i:s \W\I\B') ?? '-' }}</div>
                                </article>
                            @empty
                                <div class="gate-mobile-empty-history">Belum ada data riwayat scan untuk filter ini.</div>
                            @endforelse
                        </div>

                        @if ($historyScans->hasPages())
                            <div class="gate-history-pagination">
                                {{ $historyScans->links() }}
                            </div>
                        @endif
                    </section>
                </section>
            </main>

            @include('gate._bottom_nav', ['activeTab' => 'history'])
        </div>
    </div>
@endsection
