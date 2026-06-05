@extends('gate.layout', ['title' => 'Riwayat Pemindaian'])

@section('content')
    <div class="gate-mobile-shell gate-history-shell">
        <header class="gate-mobile-topbar gate-history-topbar">
            <div class="gate-mobile-title-wrap">
                <div class="gate-mobile-logo">⌁</div>
                <div>
                    <h1 class="gate-mobile-title">Riwayat Pemindaian</h1>
                    <p class="gate-mobile-subtitle">Lihat riwayat scan dari gate yang bisa Anda akses.</p>
                </div>
            </div>
            <details class="gate-mobile-settings">
                <summary class="gate-mobile-settings-button" aria-label="Pengaturan riwayat">⚙</summary>
                <div class="gate-mobile-settings-panel">
                    <div class="gate-mobile-settings-head">Akses Riwayat</div>
                    <div class="form-hint">Data yang tampil saat ini berasal dari check-in yang berhasil tersimpan di sistem.</div>
                    <div class="gate-mobile-settings-actions">
                        <a href="{{ route('gate.dashboard', request()->filled('gate') ? ['gate' => request()->integer('gate')] : []) }}" class="button button-ghost gate-mobile-settings-logout">Kembali ke Scanner</a>
                    </div>
                </div>
            </details>
        </header>

        <main class="gate-mobile-main gate-history-main">
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
                            <div class="gate-mobile-recent-time">{{ $scan->scanned_at?->format('H:i') ?? '-' }}</div>
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
        </main>

        @include('gate._bottom_nav', ['activeTab' => 'history'])
    </div>
@endsection
