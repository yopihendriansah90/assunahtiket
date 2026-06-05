<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Gate Dashboard' }}</title>
    @vite('resources/js/gate-scanner.js')
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --line: #e5e7eb;
            --primary: #f59e0b;
            --primary-dark: #d97706;
            --danger: #dc2626;
            --success: #059669;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(180deg, #eff6ff 0%, var(--bg) 100%);
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
                background: #f8f9ff;
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
            background: white;
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
            background: #f3f4f6;
            color: #111827;
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
        .badge-success { background: #d1fae5; color: #047857; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
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
            background: #f8fafc;
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
            background: #fff;
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
            color: #0f172a;
        }
        .scanner-setting-toggle-copy small {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.45;
        }
        .gate-mobile-shell {
            min-height: 100vh;
            background: #f8f9ff;
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
            background: rgba(248, 249, 255, 0.96);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #dbe3ef;
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
            background: #e5eeff;
            color: #131b2e;
            font-size: 24px;
            font-weight: 800;
            flex: 0 0 auto;
        }
        .gate-mobile-title {
            margin: 0;
            font-size: 18px;
            line-height: 1.2;
            font-weight: 700;
            color: #0b1c30;
        }
        .gate-mobile-subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            line-height: 1.4;
            color: #5b6472;
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
            background: #ffffff;
            border: 1px solid #dbe3ef;
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
            border: 1px solid #dbe3ef;
            background: #fff;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
        }
        .gate-mobile-settings-head {
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 800;
            color: #0b1c30;
        }
        .gate-mobile-settings-actions {
            margin-top: 12px;
        }
        .gate-mobile-settings-logout {
            width: 100%;
        }
        .gate-mobile-empty {
            margin: 16px;
            padding: 16px;
            border: 1px solid #dbe3ef;
            border-radius: 16px;
            background: #fff;
            color: #475569;
            line-height: 1.6;
        }
        .gate-mobile-empty strong {
            display: block;
            margin-bottom: 6px;
            color: #0b1c30;
        }
        .gate-mobile-main {
            display: grid;
            gap: 16px;
            padding: 16px 16px 32px;
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
            color: #7b8190;
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
            border: 1px solid #c6d2e4;
            background: #fff;
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
            border: 1px solid #c6d2e4;
            background: #e5eeff;
            color: #253045;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
        }
        .gate-mobile-chip.is-active {
            background: #000;
            border-color: #000;
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
                radial-gradient(circle at 50% 55%, rgba(0, 170, 255, 0.18), transparent 28%),
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
            background: linear-gradient(90deg, transparent, #2f7df6, transparent);
            box-shadow: 0 0 18px rgba(47, 125, 246, 0.85);
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
            color: #334155;
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
            color: #0b1c30;
        }
        .gate-mobile-section-link {
            font-size: 14px;
            font-weight: 600;
            color: #2f66ec;
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
            border: 1px solid #dbe3ef;
            border-radius: 16px;
            background: #fff;
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
            color: #0b1c30;
            line-height: 1.35;
        }
        .gate-mobile-recent-meta {
            margin-top: 4px;
            font-size: 13px;
            line-height: 1.45;
            color: #5b6472;
            word-break: break-word;
        }
        .gate-mobile-recent-time {
            font-size: 14px;
            font-weight: 500;
            color: #6b7280;
            text-align: right;
        }
        .gate-mobile-empty-history {
            padding: 18px 14px;
            border: 1px solid #dbe3ef;
            border-radius: 16px;
            background: #fff;
            font-size: 14px;
            color: #64748b;
            text-align: center;
        }
        .gate-mobile-hidden-state {
            display: none;
        }
        .mode-option {
            border: 0;
            border-radius: 999px;
            padding: 10px 14px;
            background: transparent;
            color: #374151;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        .mode-option.is-active {
            background: #fff;
            color: #111827;
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
            background: #fff;
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
            border: 1px solid #dbe3ef;
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
        .scanner-readiness-indicator {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-top: 14px;
            padding: 12px 14px;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            background: #eff6ff;
        }
        .scanner-readiness-indicator.is-waiting {
            border-color: #fde68a;
            background: #fffbeb;
        }
        .scanner-readiness-indicator.is-paused {
            border-color: #fecaca;
            background: #fef2f2;
        }
        .scanner-readiness-dot {
            width: 12px;
            height: 12px;
            flex: 0 0 12px;
            margin-top: 4px;
            border-radius: 999px;
            background: #2563eb;
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
            color: #0f172a;
        }
        .scanner-readiness-copy small {
            font-size: 12px;
            line-height: 1.45;
            color: #475569;
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
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
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
            border: 1px dashed #dbe3ef;
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
            background: rgba(15, 23, 42, 0.52);
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
            background: #fff;
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
            background: linear-gradient(135deg, #059669 0%, #16a34a 100%);
        }
        .scan-modal-head.is-warning {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        }
        .scan-modal-head.is-danger {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
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
            color: #334155;
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
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #f8fafc;
            text-align: left;
        }
        .scan-modal-detail-label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
        }
        .scan-modal-detail-value {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.45;
            color: #0f172a;
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
        @media (min-width: 640px) {
            .gate-mobile-shell {
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
                padding-top: max(20px, env(safe-area-inset-top));
            }
            .gate-mobile-main {
                padding-bottom: max(32px, env(safe-area-inset-bottom));
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
    @stack('scripts')
</body>
</html>
