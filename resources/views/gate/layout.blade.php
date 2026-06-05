<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Gate Dashboard' }}</title>
    @vite('resources/js/gate-scanner.js')
    <script>
        (() => {
            const storageKey = 'gate.theme';
            const storedTheme = window.localStorage.getItem(storageKey);
            const resolvedTheme = storedTheme === 'light' || storedTheme === 'dark'
                ? storedTheme
                : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

            document.documentElement.setAttribute('data-theme', resolvedTheme);
        })();
    </script>
    <style>
        :root {
            color-scheme: light;
            --bg: #fffbeb;
            --card: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --line: #e5e7eb;
            --page-bg-start: #fff7ed;
            --page-bg-end: #fffbeb;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --surface-soft-2: #fffaf0;
            --surface-soft-3: #fafcff;
            --border-soft: #f3e8c8;
            --desktop-sidebar-bg: color-mix(in srgb, var(--primary-soft) 38%, var(--surface) 62%);
            --text-strong: #0f172a;
            --text-heading: #0b1c30;
            --text-soft: #475569;
            --text-soft-2: #5b6472;
            --text-soft-3: #7b8190;
            --nav-bg: rgba(255, 250, 240, 0.99);
            --topbar-bg: rgba(255, 250, 240, 0.96);
            --modal-overlay: rgba(15, 23, 42, 0.52);
            --primary: #f59e0b;
            --primary-dark: #d97706;
            --primary-soft: #fef3c7;
            --primary-soft-border: #fcd34d;
            --danger: #e11d48;
            --danger-soft: #ffe4e6;
            --danger-soft-border: #fda4af;
            --success: #16a34a;
            --success-soft: #dcfce7;
            --success-soft-border: #86efac;
            --warning: #d97706;
            --warning-soft: #fef3c7;
            --warning-soft-border: #fcd34d;
        }
        :root[data-theme="dark"] {
            color-scheme: dark;
            --bg: #020617;
            --card: #0f172a;
            --text: #e5e7eb;
            --muted: #94a3b8;
            --line: #1e293b;
            --page-bg-start: #020617;
            --page-bg-end: #0f172a;
            --surface: #0f172a;
            --surface-soft: #111827;
            --surface-soft-2: #0b1220;
            --surface-soft-3: #111827;
            --border-soft: #243244;
            --desktop-sidebar-bg: #151e31;
            --text-strong: #f8fafc;
            --text-heading: #f8fafc;
            --text-soft: #cbd5e1;
            --text-soft-2: #94a3b8;
            --text-soft-3: #94a3b8;
            --nav-bg: rgba(2, 6, 23, 0.96);
            --topbar-bg: rgba(11, 18, 32, 0.94);
            --modal-overlay: rgba(2, 6, 23, 0.74);
            --primary-soft: rgba(245, 158, 11, 0.18);
            --primary-soft-border: rgba(245, 158, 11, 0.42);
            --danger-soft: rgba(225, 29, 72, 0.16);
            --danger-soft-border: rgba(251, 113, 133, 0.42);
            --success-soft: rgba(22, 163, 74, 0.16);
            --success-soft-border: rgba(74, 222, 128, 0.35);
            --warning-soft: rgba(217, 119, 6, 0.18);
            --warning-soft-border: rgba(251, 191, 36, 0.38);
        }
        :root[data-theme="dark"] .gate-mobile-settings-button,
        :root[data-theme="dark"] .gate-mobile-settings-panel,
        :root[data-theme="dark"] .gate-mobile-search-input,
        :root[data-theme="dark"] .gate-mobile-chip,
        :root[data-theme="dark"] .gate-history-filter,
        :root[data-theme="dark"] .gate-mobile-recent-card,
        :root[data-theme="dark"] .gate-mobile-empty-history,
        :root[data-theme="dark"] .scanner-setting-toggle {
            box-shadow: none;
        }
        :root[data-theme="dark"] .gate-mobile-search-input::placeholder {
            color: var(--text-soft-3);
        }
        :root[data-theme="dark"] .gate-mobile-settings-button {
            color: var(--text);
        }
        :root[data-theme="dark"] .gate-mobile-chip.is-active,
        :root[data-theme="dark"] .gate-history-filter.is-active {
            color: #020617;
            background: var(--primary);
            border-color: var(--primary);
        }
        :root[data-theme="dark"] .gate-history-stat-mini {
            border: 1px solid var(--primary-soft-border);
        }
        :root[data-theme="dark"] .gate-history-stat-mini.is-alt {
            border-color: var(--line);
            color: var(--text);
        }
        :root[data-theme="dark"] .gate-history-stat-mini.is-danger {
            border: 1px solid var(--danger-soft-border);
            color: #fb7185;
        }
        :root[data-theme="dark"] .gate-mobile-recent-icon {
            background: var(--success-soft);
            color: #4ade80;
        }
        :root[data-theme="dark"] .gate-history-status-success {
            color: #4ade80;
        }
        :root[data-theme="dark"] .gate-history-status-already_scanned {
            color: #fbbf24;
        }
        :root[data-theme="dark"] .gate-history-status-missing {
            color: #fb7185;
        }
        :root[data-theme="dark"] .gate-bottom-nav-item {
            color: var(--text-soft-2);
        }
        :root[data-theme="dark"] .gate-bottom-nav-item.is-active,
        :root[data-theme="dark"] .gate-bottom-nav-item.is-active .gate-bottom-nav-icon,
        :root[data-theme="dark"] .gate-bottom-nav-item.is-active .gate-bottom-nav-label {
            color: var(--primary);
        }
        :root[data-theme="dark"] .gate-history-card-submeta,
        :root[data-theme="dark"] .gate-mobile-recent-meta,
        :root[data-theme="dark"] .gate-mobile-subtitle {
            color: var(--text-soft-2);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(180deg, var(--page-bg-start) 0%, var(--page-bg-end) 100%);
            color: var(--text);
        }
        a { color: inherit; text-decoration: none; }
        .shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }
        .container {
            width: 100%;
            max-width: 1120px;
        }
        @media (max-width: 768px) {
            body {
                background: var(--page-bg-end);
            }
            .shell {
                align-items: stretch;
                justify-content: stretch;
                padding: 0;
            }
            .container {
                max-width: none;
                min-height: 100vh;
            }
        }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        .card-body { padding: 28px; }
        .grid {
            display: grid;
            gap: 20px;
        }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (max-width: 900px) {
            .grid-2 { grid-template-columns: 1fr; }
        }
        .muted { color: var(--muted); }
        .title {
            margin: 0 0 8px;
            font-size: 28px;
            line-height: 1.1;
        }
        .subtitle {
            margin: 0;
            color: var(--muted);
        }
        .field { display: grid; gap: 8px; }
        label { font-weight: 600; font-size: 14px; }
        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px 16px;
            font: inherit;
            background: var(--surface);
            color: var(--text);
        }
        input:focus {
            outline: 2px solid rgba(245, 158, 11, 0.25);
            border-color: var(--primary);
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 14px;
            padding: 14px 18px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        .button-primary {
            background: var(--primary);
            color: #111827;
        }
        .button-primary:hover { background: var(--primary-dark); }
        .button-ghost {
            background: var(--surface-soft);
            color: var(--text);
        }
        .alert {
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 700;
            background: #f3f4f6;
            color: #374151;
        }
        .badge-success {
            background: var(--success-soft);
            color: #166534;
            border: 1px solid var(--success-soft-border);
        }
        .badge-warning {
            background: var(--warning-soft);
            color: #9a3412;
            border: 1px solid var(--warning-soft-border);
        }
        .badge-danger {
            background: var(--danger-soft);
            color: #be123c;
            border: 1px solid var(--danger-soft-border);
        }
        .stack { display: grid; gap: 16px; }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 24px 28px;
            border-bottom: 1px solid var(--line);
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(12px);
        }
        .topbar h1 {
            margin: 0;
            font-size: 24px;
            line-height: 1.2;
        }
        .topbar p { margin: 4px 0 0; color: var(--muted); }
        .content { padding: 28px; }
        .scanner-shell {
            display: grid;
            gap: 18px;
        }
        .scanner-top {
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(0, 0.9fr) auto;
            gap: 14px;
            align-items: center;
        }
        @media (max-width: 1100px) {
            .scanner-top {
                grid-template-columns: 1fr;
                align-items: stretch;
            }
        }
        .scanner-title {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .scanner-title h1 {
            margin: 0;
            font-size: 24px;
        }
        .scanner-subtitle {
            margin: 6px 0 0;
            color: var(--muted);
        }
        .selector {
            display: grid;
            gap: 8px;
        }
        .selector select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px 16px;
            font: inherit;
            background: white;
        }
        .search-strip {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #fafcff 100%);
            padding: 18px;
        }
        .search-strip-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            grid-template-areas:
                "toolbar toolbar"
                "main actions"
                "hint hint";
            gap: 14px;
            align-items: end;
        }
        @media (max-width: 900px) {
            .search-strip-form {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "toolbar"
                    "main"
                    "actions"
                    "hint";
            }
        }
        .search-strip-toolbar-row { grid-area: toolbar; }
        .search-strip-main { grid-area: main; }
        .search-strip-actions {
            grid-area: actions;
            align-self: end;
            justify-self: end;
            padding-top: 0;
            padding-bottom: 0;
        }
        @media (max-width: 900px) {
            .search-strip-actions {
                padding-top: 0;
                justify-self: stretch;
            }
        }
        .search-strip-help { grid-area: hint; }
        .search-strip-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }
        .mode-toggle {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: var(--surface-soft);
        }
        .mode-toggle-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--muted);
            padding-left: 4px;
        }
        .scanner-setting-toggle {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--surface);
            cursor: pointer;
        }
        .scanner-setting-toggle input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .scanner-setting-toggle-indicator {
            position: relative;
            flex: 0 0 46px;
            width: 46px;
            height: 28px;
            border-radius: 999px;
            background: #cbd5e1;
            transition: background 0.18s ease;
        }
        .scanner-setting-toggle-indicator::after {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
            transition: transform 0.18s ease;
        }
        .scanner-setting-toggle input:checked + .scanner-setting-toggle-indicator {
            background: #16a34a;
        }
        .scanner-setting-toggle input:checked + .scanner-setting-toggle-indicator::after {
            transform: translateX(18px);
        }
        .scanner-setting-toggle-copy {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .scanner-setting-toggle-copy strong {
            font-size: 13px;
            color: var(--text-strong);
        }
        .scanner-setting-toggle-copy small {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.45;
        }
        .gate-mobile-shell {
            min-height: 100vh;
            background: var(--surface-soft-2);
        }
        .gate-desktop-sidebar,
        .gate-desktop-system-badge,
        .gate-desktop-panel-heading,
        .gate-desktop-history-link {
            display: none;
        }
        .gate-mobile-topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 16px 14px;
            background: var(--topbar-bg);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-soft);
        }
        .gate-mobile-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .gate-mobile-logo {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: var(--primary-soft);
            color: var(--text-heading);
            font-size: 24px;
            font-weight: 800;
            flex: 0 0 auto;
            padding: 0;
        }
        .gate-mobile-title {
            margin: 0;
            font-size: 18px;
            line-height: 1.2;
            font-weight: 700;
            color: var(--text-heading);
        }
        .gate-mobile-subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            line-height: 1.4;
            color: var(--text-soft-2);
        }
        .gate-mobile-settings {
            position: relative;
            flex: 0 0 auto;
        }
        .gate-mobile-settings-button {
            list-style: none;
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: var(--surface);
            border: 1px solid var(--border-soft);
            cursor: pointer;
            font-size: 20px;
        }
        .gate-mobile-settings-button::-webkit-details-marker {
            display: none;
        }
        .gate-mobile-settings-panel {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: min(320px, calc(100vw - 32px));
            padding: 14px;
            border-radius: 16px;
            border: 1px solid var(--border-soft);
            background: var(--surface);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
        }
        .gate-mobile-settings-head {
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 800;
            color: var(--text-heading);
        }
        .gate-mobile-settings-actions {
            margin-top: 12px;
        }
        .gate-mobile-settings-logout {
            width: 100%;
            border: 1px solid var(--primary-soft-border);
        }
        .gate-mobile-empty {
            margin: 16px;
            padding: 16px;
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            background: var(--surface);
            color: var(--text-soft);
            line-height: 1.6;
        }
        .gate-mobile-empty strong {
            display: block;
            margin-bottom: 6px;
            color: var(--text-heading);
        }
        .gate-mobile-main {
            display: grid;
            gap: 16px;
            padding: 16px 16px 100px;
        }
        .gate-desktop-controls {
            display: contents;
        }
        .gate-mobile-search,
        .gate-mobile-recent {
            display: grid;
            gap: 12px;
        }
        .gate-mobile-search-form {
            display: grid;
            gap: 12px;
        }
        .gate-mobile-search-box {
            position: relative;
        }
        .gate-mobile-search-icon,
        .gate-mobile-search-clear {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-soft-3);
            font-size: 22px;
            line-height: 1;
        }
        .gate-mobile-search-icon {
            left: 14px;
        }
        .gate-mobile-search-clear {
            right: 14px;
        }
        .gate-mobile-search-input {
            padding: 16px 46px 16px 44px;
            border-radius: 16px;
            border: 1px solid var(--primary-soft-border);
            background: var(--surface);
            font-size: 16px;
        }
        .gate-mobile-mode-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
        }
        .gate-mobile-search-submit {
            min-height: 48px;
            padding-inline: 16px;
        }
        .gate-mobile-chip-list {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 2px;
            scrollbar-width: none;
        }
        .gate-mobile-chip-list::-webkit-scrollbar {
            display: none;
        }
        .gate-mobile-chip {
            flex: 0 0 auto;
            padding: 12px 18px;
            border-radius: 18px;
            border: 1px solid var(--primary-soft-border);
            background: var(--primary-soft);
            color: var(--text-soft);
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
        }
        .gate-mobile-chip.is-active {
            background: var(--text-heading);
            border-color: var(--text-heading);
            color: #fff;
        }
        .gate-mobile-camera-section {
            display: grid;
            gap: 12px;
        }
        .gate-mobile-camera-status-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        .gate-mobile-camera-frame {
            position: relative;
            min-height: 360px;
            aspect-ratio: 4 / 5;
            border-radius: 0;
            overflow: hidden;
            background:
                radial-gradient(circle at 50% 55%, rgba(245, 158, 11, 0.18), transparent 28%),
                linear-gradient(180deg, rgba(0, 0, 0, 0.12), rgba(0, 0, 0, 0.58)),
                #020617;
            border: 0;
        }
        .gate-mobile-camera-frame::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.08) 20%, rgba(0,0,0,0.08) 80%, rgba(0,0,0,0.55) 100%);
            pointer-events: none;
        }
        .camera-scan-line {
            position: absolute;
            left: 22%;
            right: 22%;
            top: 22%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #f59e0b, transparent);
            box-shadow: 0 0 18px rgba(245, 158, 11, 0.85);
            animation: gate-scan-line 2.8s ease-in-out infinite;
            z-index: 2;
        }
        @keyframes gate-scan-line {
            0%, 100% { top: 22%; }
            50% { top: 78%; }
        }
        .gate-mobile-camera-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(0, 0, 0, 0.55);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
        }
        .gate-mobile-camera-copy {
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.82) !important;
            font-size: 14px;
            line-height: 1.5;
        }
        .gate-mobile-camera-hint {
            margin: 0;
            text-align: center;
            font-size: 14px;
            line-height: 1.5;
            color: var(--text-soft);
        }
        .gate-mobile-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .gate-mobile-section-head h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--text-heading);
        }
        .gate-mobile-section-link {
            font-size: 14px;
            font-weight: 600;
            color: var(--primary-dark);
        }
        .gate-mobile-recent-list {
            display: grid;
            gap: 12px;
        }
        .gate-mobile-recent-card {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            padding: 14px;
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            background: var(--surface);
        }
        .gate-mobile-recent-icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: #ecfdf5;
            color: #059669;
            font-size: 24px;
            font-weight: 900;
        }
        .gate-mobile-recent-copy {
            min-width: 0;
        }
        .gate-mobile-recent-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-heading);
            line-height: 1.35;
        }
        .gate-mobile-recent-meta {
            margin-top: 4px;
            font-size: 13px;
            line-height: 1.45;
            color: var(--text-soft-2);
            word-break: break-word;
        }
        .gate-mobile-recent-time {
            font-size: 14px;
            font-weight: 500;
            color: var(--muted);
            text-align: right;
        }
        .gate-mobile-empty-history {
            padding: 18px 14px;
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            background: var(--surface);
            font-size: 14px;
            color: var(--muted);
            text-align: center;
        }
        .gate-mobile-hidden-state {
            display: none;
        }
        .gate-bottom-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 25;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            padding: 10px 16px calc(10px + env(safe-area-inset-bottom));
            background: var(--nav-bg);
            backdrop-filter: blur(12px);
            border-top: 1px solid var(--border-soft);
        }
        .gate-bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            min-height: 60px;
            border-radius: 20px;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }
        .gate-bottom-nav-item.is-active {
            background: transparent;
            color: var(--primary-dark);
            box-shadow: none;
        }
        .gate-bottom-nav-icon-wrap {
            min-width: 64px;
            min-height: 32px;
            display: grid;
            place-items: center;
            border-radius: 999px;
        }
        .gate-bottom-nav-item.is-active .gate-bottom-nav-icon-wrap {
            background: transparent;
        }
        .gate-bottom-nav-item.is-active .gate-bottom-nav-icon {
            color: var(--primary-dark);
        }
        .gate-bottom-nav-item.is-active .gate-bottom-nav-label {
            color: var(--primary-dark);
        }
        .gate-bottom-nav-icon {
            font-size: 21px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .gate-bottom-nav-label {
            line-height: 1.1;
            letter-spacing: 0.01em;
        }
        .gate-bottom-nav-icon-qr {
            position: relative;
            width: 20px;
            height: 20px;
        }
        .gate-bottom-nav-icon-qr .qr-corner {
            position: absolute;
            width: 7px;
            height: 7px;
            border: 2px solid currentColor;
        }
        .gate-bottom-nav-icon-qr .qr-corner.tl {
            top: 0;
            left: 0;
            border-right: 0;
            border-bottom: 0;
            border-top-left-radius: 3px;
        }
        .gate-bottom-nav-icon-qr .qr-corner.tr {
            top: 0;
            right: 0;
            border-left: 0;
            border-bottom: 0;
            border-top-right-radius: 3px;
        }
        .gate-bottom-nav-icon-qr .qr-corner.bl {
            bottom: 0;
            left: 0;
            border-right: 0;
            border-top: 0;
            border-bottom-left-radius: 3px;
        }
        .gate-bottom-nav-icon-qr .qr-corner.br {
            right: 0;
            bottom: 0;
            border-left: 0;
            border-top: 0;
            border-bottom-right-radius: 3px;
        }
        .gate-bottom-nav-icon-qr .qr-dot {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            border-radius: 2px;
            background: currentColor;
            transform: translate(-50%, -50%);
        }
        .gate-history-main {
            gap: 20px;
        }
        .gate-history-mobile-stack {
            display: contents;
        }
        .gate-history-search {
            gap: 14px;
        }
        .gate-history-filter-list {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .gate-history-filter-list::-webkit-scrollbar {
            display: none;
        }
        .gate-history-filter {
            flex: 0 0 auto;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid var(--primary-soft-border);
            background: var(--surface);
            color: var(--text-soft);
            font-size: 13px;
            font-weight: 600;
        }
        .gate-history-filter.is-active {
            background: var(--text-heading);
            border-color: var(--text-heading);
            color: #fff;
        }
        .gate-history-stats {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
            gap: 12px;
        }
        .gate-history-stat-primary {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 112px;
            padding: 16px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: #fff;
        }
        .gate-history-stat-primary span,
        .gate-history-stat-mini span {
            font-size: 12px;
            font-weight: 600;
            line-height: 1.4;
        }
        .gate-history-stat-primary strong {
            font-size: 30px;
            font-weight: 800;
            line-height: 1;
        }
        .gate-history-stat-side {
            display: grid;
            gap: 12px;
        }
        .gate-history-stat-mini {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-radius: 16px;
            background: var(--primary-soft);
            color: var(--text-heading);
        }
        .gate-history-stat-mini.is-alt {
            background: var(--surface-soft);
        }
        .gate-history-stat-mini.is-danger {
            background: var(--danger-soft);
            color: var(--danger);
        }
        .gate-history-stat-mini strong {
            font-size: 16px;
            font-weight: 800;
        }
        .gate-history-card {
            align-items: start;
        }
        .gate-history-card-submeta {
            margin-top: 4px;
            font-size: 12px;
            line-height: 1.45;
            color: var(--text-soft-3);
        }
        .gate-history-status-success {
            background: var(--success-soft);
            color: var(--success);
        }
        .gate-history-status-already_scanned {
            background: var(--warning-soft);
            color: var(--warning);
        }
        .gate-history-status-missing {
            background: var(--danger-soft);
            color: var(--danger);
        }
        .gate-history-pagination {
            padding-top: 8px;
        }
        .gate-history-pagination nav {
            display: flex;
            justify-content: center;
        }
        .gate-history-pagination svg {
            width: 16px;
            height: 16px;
        }
        .gate-history-desktop-header,
        .gate-history-desktop-filters,
        .gate-history-desktop-table-card,
        .gate-history-desktop-stats {
            display: none;
        }
        .mode-option {
            border: 0;
            border-radius: 999px;
            padding: 10px 14px;
            background: transparent;
            color: var(--text-soft);
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        .mode-option.is-active {
            background: var(--surface);
            color: var(--text);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }
        .search-strip-label {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .search-strip-label strong {
            font-size: 15px;
        }
        .search-strip-input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 18px 20px;
            font: inherit;
            font-size: 18px;
            letter-spacing: 0.02em;
        }
        .search-strip-input:focus {
            outline: 2px solid rgba(245, 158, 11, 0.25);
            border-color: var(--primary);
        }
        .search-strip-hint {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
        .scanner-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.2fr) 320px;
            gap: 16px;
            align-items: start;
        }
        @media (max-width: 1200px) {
            .scanner-grid {
                grid-template-columns: 1fr;
            }
        }
        .panel {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--surface);
            overflow: hidden;
        }
        .panel-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .panel-header h2 {
            margin: 0;
            font-size: 14px;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .panel-body {
            padding: 16px;
        }
        .camera-frame {
            position: relative;
            aspect-ratio: 4 / 3;
            border-radius: 16px;
            background:
                radial-gradient(circle at 50% 30%, rgba(96, 165, 250, 0.12), transparent 35%),
                linear-gradient(180deg, #111827 0%, #1f2937 100%);
            border: 1px solid #f3e8c8;
            overflow: hidden;
        }
        .camera-reader {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            background: #020617;
        }
        .camera-reader.is-hidden {
            display: none;
        }
        .camera-reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            border-radius: 0 !important;
        }
        .camera-reader > div {
            width: 100%;
            height: 100%;
        }
        .camera-reader section {
            display: none !important;
        }
        .camera-frame::before,
        .camera-frame::after {
            content: "";
            position: absolute;
            inset: 18px;
            border: 4px solid rgba(74, 222, 128, 0.0);
            border-radius: 16px;
        }
        .camera-corner {
            position: absolute;
            width: 48px;
            height: 48px;
            border-color: #4ade80;
            border-style: solid;
            border-width: 0;
        }
        .camera-corner.tl { top: 28px; left: 28px; border-top-width: 5px; border-left-width: 5px; border-top-left-radius: 12px; }
        .camera-corner.tr { top: 28px; right: 28px; border-top-width: 5px; border-right-width: 5px; border-top-right-radius: 12px; }
        .camera-corner.bl { bottom: 28px; left: 28px; border-bottom-width: 5px; border-left-width: 5px; border-bottom-left-radius: 12px; }
        .camera-corner.br { bottom: 28px; right: 28px; border-bottom-width: 5px; border-right-width: 5px; border-bottom-right-radius: 12px; }
        .camera-placeholder {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            color: rgba(255, 255, 255, 0.88);
            text-align: center;
            padding: 24px;
        }
        .camera-placeholder.is-hidden {
            display: none;
        }
        .camera-placeholder strong {
            display: block;
            font-size: 18px;
            margin-top: 8px;
        }
        .camera-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .camera-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
        }
        .camera-message {
            margin-top: 12px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
        .camera-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }
        .camera-actions .button-ghost {
            border: 1px solid var(--primary-soft-border);
            background: var(--surface);
        }
        .scanner-readiness-indicator {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-top: 14px;
            padding: 12px 14px;
            border: 1px solid var(--primary-soft-border);
            border-radius: 16px;
            background: color-mix(in srgb, var(--primary-soft) 72%, var(--surface) 28%);
        }
        .scanner-readiness-indicator.is-waiting {
            border-color: var(--warning-soft-border);
            background: color-mix(in srgb, var(--warning-soft) 72%, var(--surface) 28%);
        }
        .scanner-readiness-indicator.is-paused {
            border-color: var(--danger-soft-border);
            background: color-mix(in srgb, var(--danger-soft) 72%, var(--surface) 28%);
        }
        .scanner-readiness-dot {
            width: 12px;
            height: 12px;
            flex: 0 0 12px;
            margin-top: 4px;
            border-radius: 999px;
            background: var(--primary-dark);
        }
        .scanner-readiness-indicator.is-waiting .scanner-readiness-dot {
            background: #d97706;
        }
        .scanner-readiness-indicator.is-paused .scanner-readiness-dot {
            background: #dc2626;
        }
        .scanner-readiness-copy {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .scanner-readiness-copy strong {
            font-size: 14px;
            color: var(--text-strong);
        }
        .scanner-readiness-copy small {
            font-size: 12px;
            line-height: 1.45;
            color: var(--text-soft);
        }
        .button-soft {
            background: #eef2ff;
            color: #4338ca;
        }
        .result-banner {
            border-radius: 18px;
            padding: 18px;
            min-height: 128px;
            display: flex;
            align-items: center;
            gap: 16px;
            background: linear-gradient(135deg, #0f9f6e 0%, #16a34a 100%);
            color: white;
        }
        .result-banner.is-empty {
            background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);
            color: var(--text);
            border: 1px dashed #cbd5e1;
        }
        .result-check {
            width: 72px;
            height: 72px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,0.15);
            font-size: 30px;
            font-weight: 900;
            flex: 0 0 auto;
        }
        .result-title {
            font-size: 28px;
            font-weight: 900;
            line-height: 1;
            margin: 0;
        }
        .result-subtitle {
            margin: 8px 0 0;
            font-size: 16px;
            opacity: 0.92;
        }
        .details-list {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }
        .detail-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid var(--line);
            padding-bottom: 10px;
        }
        .detail-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            color: var(--muted);
            font-size: 14px;
        }
        .detail-value {
            font-weight: 700;
            text-align: right;
        }
        .stat-grid {
            display: grid;
            gap: 12px;
        }
        .stat-card {
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 16px;
            background: #fff;
            display: flex;
            gap: 14px;
            align-items: center;
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            font-size: 22px;
            flex: 0 0 auto;
        }
        .stat-icon svg {
            width: 26px;
            height: 26px;
        }
        .stat-label {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }
        .stat-value {
            margin: 6px 0 0;
            font-size: 24px;
            font-weight: 900;
            line-height: 1;
        }
        .subgrid {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
            gap: 16px;
        }
        @media (max-width: 1200px) {
            .subgrid {
                grid-template-columns: 1fr;
            }
        }
        .table-shell {
            width: 100%;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
        }
        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
        }
        tbody tr:last-child td {
            border-bottom: 0;
        }
        .input-hint {
            margin-top: 8px;
            color: var(--muted);
            font-size: 13px;
        }
        .search-input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px 16px;
            font: inherit;
        }
        .aside-card {
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #fafcff 100%);
        }
        .aside-illustration {
            height: 180px;
            border-radius: 16px;
            border: 1px dashed #f3e8c8;
            display: grid;
            place-items: center;
            color: var(--muted);
            background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
            text-align: center;
            padding: 16px;
        }
        .gate-card {
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }
        .gate-code {
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 700;
        }
        .gate-name {
            margin: 8px 0 6px;
            font-size: 20px;
        }
        .gate-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }
        .empty {
            padding: 42px 24px;
            text-align: center;
            color: var(--muted);
            border: 1px dashed var(--line);
            border-radius: 20px;
            background: rgba(255,255,255,0.65);
        }
        .scan-modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
            background: var(--modal-overlay);
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.18s ease, visibility 0.18s ease;
        }
        .scan-modal.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        .scan-modal-card {
            width: min(100%, 420px);
            max-height: calc(100dvh - 40px);
            border-radius: 24px;
            background: var(--surface);
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.24);
            overflow: hidden;
            transform: translateY(12px) scale(0.98);
            transition: transform 0.18s ease;
        }
        .scan-modal.is-visible .scan-modal-card {
            transform: translateY(0) scale(1);
        }
        .scan-modal-head {
            display: grid;
            place-items: center;
            gap: 14px;
            padding: 28px 24px 20px;
            text-align: center;
            color: #fff;
        }
        .scan-modal-head.is-success {
            background: linear-gradient(135deg, #15803d 0%, var(--success) 100%);
        }
        .scan-modal-head.is-warning {
            background: linear-gradient(135deg, #b45309 0%, var(--primary) 100%);
        }
        .scan-modal-head.is-danger {
            background: linear-gradient(135deg, #be123c 0%, var(--danger) 100%);
        }
        .scan-modal-icon {
            width: 88px;
            height: 88px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.18);
            border: 3px solid rgba(255, 255, 255, 0.28);
            font-size: 42px;
            font-weight: 900;
            line-height: 1;
        }
        .scan-modal-title {
            margin: 0;
            font-size: 28px;
            font-weight: 900;
            line-height: 1.05;
        }
        .scan-modal-body {
            padding: 18px 18px 20px;
            max-height: calc(100dvh - 220px);
            overflow-y: auto;
        }
        .scan-modal-message {
            margin: 0;
            font-size: 15px;
            line-height: 1.55;
            color: var(--text-soft);
            text-align: center;
        }
        .scan-modal-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 16px;
        }
        .scan-modal-detail {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--surface-soft);
            text-align: left;
        }
        .scan-modal-detail-label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .scan-modal-detail-value {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.45;
            color: var(--text-strong);
            word-break: break-word;
        }
        .scan-modal-detail-value:empty::before {
            content: "-";
        }
        .scan-modal-meta {
            margin-top: 14px;
            font-size: 13px;
            color: var(--muted);
            text-align: center;
        }
        .scan-modal-actions {
            display: flex;
            justify-content: center;
            margin-top: 16px;
        }
        .scan-modal-actions .button {
            min-width: 120px;
        }
        @media (min-width: 1024px) {
            .gate-desktop-shell {
                width: calc(100vw - 32px);
                min-height: calc(100vh - 32px);
                margin-inline: calc(50% - 50vw + 16px);
                display: grid;
                grid-template-columns: 272px minmax(0, 1fr);
                background: var(--surface-soft-2);
            }
            .gate-desktop-sidebar {
                display: flex;
                flex-direction: column;
                gap: 24px;
                padding: 32px 20px 24px;
                background: var(--desktop-sidebar-bg);
                border-right: 1px solid var(--border-soft);
            }
            .gate-desktop-sidebar-head {
                display: grid;
                gap: 24px;
            }
            .gate-desktop-sidebar-title {
                margin: 0;
                font-size: 24px;
                line-height: 1.2;
                color: var(--text-heading);
            }
            .gate-desktop-gate-card {
                display: flex;
                align-items: center;
                gap: 14px;
            }
            .gate-desktop-gate-icon {
                width: 50px;
                height: 50px;
                display: grid;
                place-items: center;
                border-radius: 14px;
                background: var(--primary);
                color: #111827;
                font-size: 24px;
                font-weight: 800;
                flex: 0 0 auto;
                padding: 0;
            }
            .gate-desktop-gate-icon-svg {
                width: 18px;
                height: 18px;
                color: currentColor;
                display: block;
                flex: 0 0 auto;
                transform: translateX(0.5px);
            }
            .gate-desktop-gate-name {
                font-size: 20px;
                font-weight: 700;
                color: var(--text-heading);
                line-height: 1.25;
            }
            .gate-desktop-gate-event {
                margin-top: 4px;
                font-size: 14px;
                line-height: 1.45;
                color: var(--text-soft-2);
            }
            .gate-desktop-sidebar-nav {
                display: grid;
                gap: 8px;
            }
            .gate-desktop-sidebar-link,
            .gate-desktop-logout-button {
                display: flex;
                align-items: center;
                gap: 14px;
                width: 100%;
                min-height: 52px;
                padding: 0 18px;
                border: 1px solid transparent;
                border-radius: 16px;
                background: transparent;
                color: var(--text-soft);
                font: inherit;
                font-size: 18px;
                font-weight: 600;
                cursor: pointer;
                text-align: left;
                transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
            }
            .gate-desktop-sidebar-link:hover,
            .gate-desktop-logout-button:hover {
                background: var(--surface-soft);
                border-color: var(--line);
            }
            .gate-desktop-sidebar-link.is-active {
                background: var(--primary-soft);
                border-color: var(--primary-soft-border);
                color: var(--primary-dark);
            }
            .gate-desktop-sidebar-link.is-active .gate-bottom-nav-icon {
                color: var(--primary-dark);
            }
            .gate-desktop-sidebar-link.is-active .gate-desktop-sidebar-symbol {
                color: var(--primary-dark);
            }
            .gate-desktop-sidebar-symbol {
                width: 20px;
                text-align: center;
                font-size: 22px;
                line-height: 1;
                flex: 0 0 20px;
            }
            .gate-desktop-sidebar-footer {
                margin-top: auto;
            }
            .gate-desktop-content {
                min-width: 0;
                display: flex;
                flex-direction: column;
            }
            .gate-mobile-topbar {
                position: sticky;
                top: 0;
                padding: 24px 30px;
                background: var(--surface);
                border-bottom: 1px solid var(--border-soft);
            }
            .gate-mobile-logo {
                display: none;
            }
            .gate-mobile-title {
                font-size: 24px;
            }
            .gate-mobile-subtitle {
                font-size: 14px;
            }
            .gate-desktop-topbar-actions {
                display: flex;
                align-items: center;
                gap: 16px;
            }
            .gate-desktop-system-badge {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                min-height: 40px;
                padding: 0 16px;
                border: 1px solid var(--border-soft);
                border-radius: 999px;
                background: var(--surface-soft);
                color: var(--text-soft);
                font-size: 14px;
                font-weight: 600;
            }
            .gate-desktop-system-dot {
                width: 10px;
                height: 10px;
                border-radius: 999px;
                background: var(--success);
                box-shadow: 0 0 0 6px color-mix(in srgb, var(--success-soft) 80%, transparent 20%);
            }
            .gate-desktop-settings .gate-mobile-settings-panel {
                top: calc(100% + 14px);
            }
            .gate-mobile-main.gate-desktop-main {
                grid-template-columns: minmax(0, 1fr) minmax(320px, 360px);
                grid-template-areas:
                    "camera recent"
                    "controls recent";
                align-items: start;
                gap: 24px 28px;
                padding: 30px;
            }
            .gate-desktop-camera-panel {
                grid-area: camera;
                min-width: 0;
            }
            .gate-desktop-controls {
                grid-area: controls;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 24px;
                min-width: 0;
            }
            .gate-desktop-recent-panel {
                grid-area: recent;
                min-width: 0;
                align-self: stretch;
                grid-template-rows: auto minmax(0, 1fr) auto;
                min-height: calc(100vh - 170px);
                padding: 24px;
                border: 1px solid var(--border-soft);
                border-radius: 24px;
                background: var(--surface);
            }
            .gate-desktop-panel {
                padding: 24px;
                border: 1px solid var(--border-soft);
                border-radius: 20px;
                background: var(--surface);
            }
            .gate-desktop-panel-heading {
                display: block;
                margin-bottom: 18px;
                font-size: 16px;
                font-weight: 700;
                color: var(--text-heading);
            }
            .gate-mobile-camera-frame {
                width: 100%;
                max-width: 100%;
                aspect-ratio: 16 / 9;
                min-height: 460px;
                border-radius: 20px;
                border: 1px solid var(--border-soft);
            }
            .gate-mobile-camera-status-row {
                justify-content: space-between;
            }
            .camera-actions {
                width: fit-content;
                margin-left: auto;
            }
            .gate-mobile-chip-list {
                flex-wrap: wrap;
                overflow: visible;
            }
            .gate-mobile-search-input {
                min-height: 56px;
                font-size: 18px;
            }
            .mode-toggle {
                min-height: 56px;
                padding-inline: 12px;
            }
            .gate-mobile-search-submit {
                min-height: 56px;
                min-width: 104px;
            }
            .gate-mobile-section-link {
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }
            .gate-mobile-recent-list {
                align-content: start;
                max-height: none;
                overflow: auto;
                padding-right: 4px;
            }
            .gate-desktop-history-link {
                display: inline-flex;
                justify-content: center;
                margin-top: 8px;
            }
            .gate-history-desktop-main {
                display: grid;
                gap: 24px;
                padding: 30px;
            }
            .gate-history-mobile-stack {
                display: none;
            }
            .gate-history-desktop-header,
            .gate-history-desktop-filters,
            .gate-history-desktop-table-card,
            .gate-history-desktop-stats {
                display: block;
            }
            .gate-history-desktop-header {
                display: flex;
                align-items: end;
                justify-content: space-between;
                gap: 20px;
            }
            .gate-history-desktop-title {
                margin: 0;
                font-size: 46px;
                line-height: 1.05;
                font-weight: 800;
                color: var(--text-heading);
            }
            .gate-history-desktop-copy {
                margin: 10px 0 0;
                font-size: 18px;
                line-height: 1.5;
                color: var(--text-soft-2);
            }
            .gate-history-desktop-actions {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
            }
            .gate-history-desktop-refresh {
                border: 1px solid var(--primary-soft-border);
                background: var(--surface);
            }
            .gate-history-desktop-filters {
                padding: 20px;
                border: 1px solid var(--border-soft);
                border-radius: 20px;
                background: color-mix(in srgb, var(--surface-soft) 42%, var(--surface) 58%);
            }
            .gate-history-desktop-filter-form {
                display: grid;
                gap: 18px;
            }
            .gate-history-desktop-search {
                position: relative;
            }
            .gate-history-desktop-search .gate-mobile-search-icon {
                left: 16px;
            }
            .gate-history-desktop-search-input {
                min-height: 56px;
                padding-left: 48px;
                font-size: 17px;
            }
            .gate-history-desktop-filter-grid {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
                gap: 16px;
                align-items: end;
            }
            .gate-history-desktop-filter-field {
                display: grid;
                gap: 8px;
            }
            .gate-history-desktop-filter-field span {
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: var(--text-soft);
            }
            .gate-history-desktop-select {
                width: 100%;
                min-height: 52px;
                padding: 0 14px;
                border: 1px solid var(--line);
                border-radius: 14px;
                background: var(--surface);
                color: var(--text);
                font: inherit;
                font-size: 15px;
            }
            .gate-history-desktop-filter-actions {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .gate-history-desktop-clear {
                border: 1px solid var(--primary-soft-border);
                background: var(--surface);
            }
            .gate-history-desktop-table-card {
                border: 1px solid var(--border-soft);
                border-radius: 22px;
                overflow: hidden;
                background: var(--surface);
            }
            .gate-history-desktop-table-wrap {
                overflow-x: auto;
            }
            .gate-history-desktop-table {
                width: 100%;
                border-collapse: collapse;
            }
            .gate-history-desktop-table thead th {
                padding: 22px 24px;
                border-bottom: 1px solid var(--border-soft);
                background: color-mix(in srgb, var(--surface-soft) 68%, var(--surface) 32%);
                color: var(--text-soft);
                font-size: 13px;
                font-weight: 800;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                text-align: left;
                white-space: nowrap;
            }
            .gate-history-desktop-table tbody td {
                padding: 22px 24px;
                border-bottom: 1px solid var(--border-soft);
                font-size: 16px;
                line-height: 1.5;
                color: var(--text);
                vertical-align: middle;
            }
            .gate-history-desktop-rownum {
                width: 72px;
                font-weight: 700;
                color: var(--text-soft);
                white-space: nowrap;
            }
            .gate-history-desktop-table tbody tr:last-child td {
                border-bottom: 0;
            }
            .gate-history-desktop-person {
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 220px;
            }
            .gate-history-desktop-avatar {
                width: 40px;
                height: 40px;
                display: grid;
                place-items: center;
                border-radius: 999px;
                font-size: 13px;
                font-weight: 800;
                flex: 0 0 auto;
            }
            .gate-history-desktop-person-name {
                font-size: 16px;
                font-weight: 700;
                color: var(--text-heading);
            }
            .gate-history-desktop-person-meta {
                margin-top: 2px;
                font-size: 13px;
                color: var(--text-soft-2);
            }
            .gate-history-desktop-mono {
                font-size: 14px;
                font-weight: 600;
                letter-spacing: 0.02em;
                color: var(--text-soft);
            }
            .gate-history-desktop-status {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 15px;
                font-weight: 700;
            }
            .gate-history-desktop-status-dot {
                width: 10px;
                height: 10px;
                border-radius: 999px;
                background: currentColor;
            }
            .gate-history-desktop-table-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 20px 24px;
                border-top: 1px solid var(--border-soft);
                background: color-mix(in srgb, var(--surface-soft) 68%, var(--surface) 32%);
            }
            .gate-history-desktop-table-count {
                font-size: 15px;
                color: var(--text-soft);
            }
            .gate-history-desktop-pagination nav {
                justify-content: end;
            }
            .gate-history-desktop-stats {
                display: grid;
                grid-template-columns: minmax(0, 1.35fr) repeat(3, minmax(0, 1fr));
                gap: 20px;
            }
            .gate-history-desktop-stat-card {
                display: grid;
                gap: 10px;
                min-height: 164px;
                padding: 24px;
                border: 1px solid var(--border-soft);
                border-radius: 22px;
                background: color-mix(in srgb, var(--surface-soft) 68%, var(--surface) 32%);
            }
            .gate-history-desktop-stat-card span {
                font-size: 13px;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: var(--text-soft);
            }
            .gate-history-desktop-stat-card strong {
                font-size: 48px;
                line-height: 1;
                font-weight: 800;
                color: var(--text-heading);
                align-self: end;
            }
            .gate-history-desktop-stat-card.is-primary {
                background: linear-gradient(135deg, color-mix(in srgb, var(--desktop-sidebar-bg) 92%, #000 8%) 0%, color-mix(in srgb, var(--desktop-sidebar-bg) 72%, var(--primary) 28%) 100%);
                border-color: color-mix(in srgb, var(--desktop-sidebar-bg) 80%, var(--primary) 20%);
            }
            .gate-history-desktop-stat-card.is-primary span,
            .gate-history-desktop-stat-card.is-primary strong {
                color: #fff;
            }
            .gate-history-desktop-stat-card.is-danger {
                background: color-mix(in srgb, var(--danger-soft) 72%, var(--surface) 28%);
                border-color: var(--danger-soft-border);
            }
            .gate-history-desktop-stat-card.is-danger strong {
                color: var(--danger);
            }
            .gate-bottom-nav {
                display: none;
            }
        }
        @media (min-width: 640px) {
            .gate-mobile-shell:not(.gate-desktop-shell) {
                max-width: 720px;
                margin: 0 auto;
                box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
            }
            .scan-modal-card {
                width: min(100%, 560px);
                max-height: calc(100dvh - 48px);
            }
            .scan-modal-body {
                padding: 20px 24px 24px;
                max-height: calc(100dvh - 240px);
            }
            .scan-modal-details {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }
            .gate-mobile-camera-frame {
                min-height: 420px;
            }
        }
        @media (max-width: 768px) {
            .gate-mobile-shell {
                width: 100vw;
                min-height: 100vh;
                margin-inline: calc(50% - 50vw);
                border-radius: 0;
                box-shadow: none;
            }
            .gate-mobile-topbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 40;
                padding-top: max(20px, env(safe-area-inset-top));
            }
            .gate-mobile-main {
                padding-top: calc(92px + env(safe-area-inset-top));
                padding-bottom: max(100px, calc(88px + env(safe-area-inset-bottom)));
            }
            .gate-bottom-nav {
                z-index: 40;
            }
        }
        .form-hint {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }
    </style>
</head>
<body>
    <main class="shell">
        <div class="container">
            @yield('content')
        </div>
    </main>
    <script>
        (() => {
            const storageKey = 'gate.theme';
            const themeToggles = Array.from(document.querySelectorAll('[data-theme-toggle]'));
            const themeHelpNodes = Array.from(document.querySelectorAll('[data-theme-help]'));
            const settingsPanels = Array.from(document.querySelectorAll('.gate-mobile-settings'));
            const getResolvedTheme = () => {
                const currentTheme = document.documentElement.getAttribute('data-theme');

                if (currentTheme === 'light' || currentTheme === 'dark') {
                    return currentTheme;
                }

                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            };

            const applyTheme = (theme, persist = true) => {
                const normalizedTheme = theme === 'dark' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', normalizedTheme);

                themeToggles.forEach((toggle) => {
                    toggle.checked = normalizedTheme === 'dark';
                });

                themeHelpNodes.forEach((node) => {
                    node.textContent = normalizedTheme === 'dark'
                        ? 'Aktif: gunakan tampilan gelap.'
                        : 'Nonaktif: gunakan tampilan terang.';
                });

                if (persist) {
                    window.localStorage.setItem(storageKey, normalizedTheme);
                }
            };

            applyTheme(getResolvedTheme(), false);

            themeToggles.forEach((toggle) => {
                toggle.addEventListener('change', (event) => {
                    applyTheme(event.currentTarget?.checked ? 'dark' : 'light');
                });
            });

            const closeAllSettingsPanels = (except = null) => {
                settingsPanels.forEach((panel) => {
                    if (panel !== except) {
                        panel.removeAttribute('open');
                    }
                });
            };

            if (settingsPanels.length > 0) {
                document.addEventListener('click', (event) => {
                    const target = event.target;
                    const clickedPanel = target instanceof Element
                        ? target.closest('.gate-mobile-settings')
                        : null;

                    if (clickedPanel) {
                        closeAllSettingsPanels(clickedPanel);
                        return;
                    }

                    closeAllSettingsPanels();
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeAllSettingsPanels();
                    }
                });
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
