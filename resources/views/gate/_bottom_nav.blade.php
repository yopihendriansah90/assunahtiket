<nav class="gate-bottom-nav" aria-label="Navigasi Gate">
    <a href="{{ route('gate.dashboard', request()->filled('gate') ? ['gate' => request()->integer('gate')] : []) }}" class="gate-bottom-nav-item {{ $activeTab === 'scanner' ? 'is-active' : '' }}">
        <span class="gate-bottom-nav-icon-wrap">
            <span class="gate-bottom-nav-icon gate-bottom-nav-icon-qr" aria-hidden="true">
                <span class="qr-corner tl"></span>
                <span class="qr-corner tr"></span>
                <span class="qr-corner bl"></span>
                <span class="qr-corner br"></span>
                <span class="qr-dot"></span>
            </span>
        </span>
        <span class="gate-bottom-nav-label">Scanner</span>
    </a>
    <a href="{{ route('gate.history', request()->filled('gate') ? ['gate' => request()->integer('gate')] : []) }}" class="gate-bottom-nav-item {{ $activeTab === 'history' ? 'is-active' : '' }}">
        <span class="gate-bottom-nav-icon-wrap">
            <span class="gate-bottom-nav-icon" aria-hidden="true">↺</span>
        </span>
        <span class="gate-bottom-nav-label">Riwayat</span>
    </a>
</nav>
