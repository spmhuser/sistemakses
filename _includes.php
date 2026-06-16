<?php
require_once __DIR__ . '/_chatbox.php';

function sharedCSS() { ?>
<style>
:root {
    --blue-950: #0a1628;
    --blue-900: #0d2137;
    --blue-800: #1a3a5c;
    --blue-700: #1e4976;
    --blue-600: #2563eb;
    --blue-500: #3b82f6;
    --blue-100: #dbeafe;
    --blue-50: #eff6ff;
    --yellow-500: #eab308;
    --yellow-400: #facc15;
    --yellow-100: #fef9c3;
    --yellow-50: #fffbeb;
    --ios-bg: #0b1120;
    --ios-card: #151d2e;
    --ios-elevated: #1c2640;
    --ios-separator: rgba(255, 255, 255, 0.08);
    --ios-label: #e2e8f0;
    --ios-secondary: #94a3b8;
    --grey-muted: #64748b;
    --grey-border: #334155;
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3);
    --shadow-md: 0 4px 24px rgba(0, 0, 0, 0.35), 0 1px 4px rgba(0, 0, 0, 0.2);
    --shadow-lg: 0 12px 48px rgba(0, 0, 0, 0.45), 0 4px 16px rgba(0, 0, 0, 0.3);
    --radius-sm: 10px;
    --radius-md: 14px;
    --radius-lg: 20px;
    --radius-xl: 24px;
    --ease-ios: cubic-bezier(0.25, 0.1, 0.25, 1);
    --spring: cubic-bezier(0.34, 1.25, 0.64, 1);
    --spring-soft: cubic-bezier(0.22, 1, 0.36, 1);
    --dur-fast: 0.2s;
    --dur-normal: 0.35s;
    --dur-slow: 0.55s;
}

@keyframes iosFadeUp {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes iosSlideIn {
    from { opacity: 0; transform: translateX(24px) scale(0.96); }
    to { opacity: 1; transform: translateX(0) scale(1); }
}
@keyframes iosScaleIn {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
}
@keyframes iosShimmer {
    0% { background-position: -200% center; }
    100% { background-position: 200% center; }
}

* { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

html {
    scroll-behavior: smooth;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Segoe UI', system-ui, sans-serif;
    background: var(--ios-bg);
    min-height: 100vh;
    margin: 0;
    color: var(--ios-label);
    letter-spacing: -0.01em;
    animation: iosFadeUp var(--dur-slow) var(--spring-soft) both;
}

/* iOS INTERACTIVE — smooth press & lift, no infinite glow */
.ios-press,
button, .btn, input[type="submit"], input[type="button"],
a.btn-primary-dark, a.btn-secondary-soft, a.btn-success-soft, a.btn-danger-soft, a.btn-logout,
.btn-primary-dark, .btn-secondary-soft, .btn-success-soft, .btn-danger-soft, .btn-logout {
    transition:
        transform var(--dur-normal) var(--spring),
        box-shadow var(--dur-normal) var(--ease-ios),
        background var(--dur-fast) var(--ease-ios),
        border-color var(--dur-fast) var(--ease-ios),
        color var(--dur-fast) var(--ease-ios),
        opacity var(--dur-fast) var(--ease-ios) !important;
    will-change: transform;
}
.ios-press:hover,
button:not(:disabled):hover, .btn:hover,
a.btn-primary-dark:hover, a.btn-secondary-soft:hover, a.btn-success-soft:hover, a.btn-danger-soft:hover, a.btn-logout:hover,
.btn-primary-dark:hover, .btn-secondary-soft:hover, .btn-success-soft:hover, .btn-danger-soft:hover, .btn-logout:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: var(--shadow-lg), 0 0 0 1px rgba(250, 204, 21, 0.15);
}
.ios-press:active,
button:active, .btn:active,
a.btn-primary-dark:active, a.btn-secondary-soft:active, a.btn-success-soft:active, a.btn-danger-soft:active, a.btn-logout:active,
.btn-primary-dark:active, .btn-secondary-soft:active, .btn-success-soft:active, .btn-danger-soft:active, .btn-logout:active {
    transform: scale(0.96) !important;
    box-shadow: var(--shadow-sm) !important;
    transition-duration: 0.12s !important;
}

/* STAGGER ANIMATIONS */
.animate-in { animation: iosFadeUp var(--dur-slow) var(--spring-soft) both; }
.stat-card:nth-child(1) { animation-delay: 0.04s; }
.stat-card:nth-child(2) { animation-delay: 0.08s; }
.stat-card:nth-child(3) { animation-delay: 0.12s; }
.stat-card:nth-child(4) { animation-delay: 0.16s; }
.view-card:nth-child(1) { animation-delay: 0.06s; }
.view-card:nth-child(2) { animation-delay: 0.12s; }
.view-card:nth-child(3) { animation-delay: 0.18s; }
.view-card:nth-child(4) { animation-delay: 0.24s; }
.view-card:nth-child(5) { animation-delay: 0.30s; }
.data-table tbody tr { animation: iosFadeUp var(--dur-normal) var(--spring-soft) both; }
.data-table tbody tr:nth-child(1) { animation-delay: 0.03s; }
.data-table tbody tr:nth-child(2) { animation-delay: 0.06s; }
.data-table tbody tr:nth-child(3) { animation-delay: 0.09s; }
.data-table tbody tr:nth-child(4) { animation-delay: 0.12s; }
.data-table tbody tr:nth-child(5) { animation-delay: 0.15s; }
.data-table tbody tr:nth-child(n+6) { animation-delay: 0.18s; }

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* SIDEBAR — frosted glass iOS style */
.sidebar {
    width: 260px; min-height: 100vh;
    background: linear-gradient(165deg, rgba(10, 22, 40, 0.97) 0%, rgba(13, 33, 55, 0.95) 50%, rgba(26, 58, 92, 0.93) 100%);
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    position: fixed; top: 0; left: 0;
    display: flex; flex-direction: column; z-index: 100;
    box-shadow: 4px 0 32px rgba(10, 22, 40, 0.15);
    border-right: 1px solid rgba(255, 255, 255, 0.06);
}
.sidebar-brand { padding: 32px 24px 24px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
.sidebar-brand .brand-icon {
    width: 48px; height: 48px;
    background: linear-gradient(145deg, var(--yellow-400), var(--yellow-500));
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; margin-bottom: 14px;
    box-shadow: 0 4px 16px rgba(250, 204, 21, 0.3);
    transition: transform var(--dur-normal) var(--spring);
}
.sidebar-brand .brand-icon:hover { transform: scale(1.08) rotate(-3deg); }
.sidebar-brand .brand-title { color: #fff; font-size: 0.85rem; font-weight: 700; line-height: 1.35; letter-spacing: -0.02em; }
.sidebar-brand .brand-sub { color: var(--yellow-400); font-size: 0.75rem; margin-top: 4px; font-weight: 600; opacity: 0.9; }
.sidebar-nav { padding: 16px 12px; flex: 1; }
.sidebar-nav .nav-label {
    color: rgba(255, 255, 255, 0.35); font-size: 0.65rem; text-transform: uppercase;
    letter-spacing: 0.08em; padding: 0 14px; margin-bottom: 8px; margin-top: 12px; font-weight: 600;
}
.sidebar-nav .nav-item {
    display: flex; align-items: center; gap: 12px; padding: 12px 14px;
    border-radius: var(--radius-md); color: rgba(255, 255, 255, 0.75);
    font-size: 0.9rem; font-weight: 500; text-decoration: none; margin-bottom: 4px;
    transition: transform var(--dur-normal) var(--spring), background var(--dur-fast), color var(--dur-fast), box-shadow var(--dur-normal);
}
.sidebar-nav .nav-item:hover {
    background: rgba(255, 255, 255, 0.1); color: #fff;
    transform: translateX(4px) scale(1.01);
}
.sidebar-nav .nav-item.active {
    background: rgba(250, 204, 21, 0.18); color: var(--yellow-400);
    box-shadow: inset 0 0 0 1px rgba(250, 204, 21, 0.2);
    font-weight: 600;
}
.sidebar-nav .nav-item:active { transform: scale(0.97); }
.sidebar-nav .nav-item i { font-size: 1.05rem; width: 20px; opacity: 0.9; }
.sidebar-footer { padding: 16px 12px 24px; border-top: 1px solid rgba(255, 255, 255, 0.08); }
.user-info {
    display: flex; align-items: center; gap: 12px; padding: 12px 14px;
    background: rgba(255, 255, 255, 0.06); border-radius: var(--radius-md); margin-bottom: 10px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
}
.user-avatar {
    width: 38px; height: 38px;
    background: linear-gradient(145deg, var(--blue-700), var(--blue-600));
    border: 2px solid rgba(250, 204, 21, 0.5);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; color: var(--yellow-400); flex-shrink: 0;
}
.user-name { color: #fff; font-size: 0.85rem; font-weight: 600; }
.user-role { color: rgba(250, 204, 21, 0.8); font-size: 0.72rem; font-weight: 500; }
.btn-logout {
    display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
    background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 0.85); border-radius: var(--radius-md); padding: 11px;
    font-size: 0.85rem; font-weight: 500; text-decoration: none;
}
.btn-logout:hover { background: rgba(255, 59, 48, 0.25); border-color: rgba(255, 59, 48, 0.3); color: #fff; }

/* MAIN */
.main-content { margin-left: 260px; padding: 28px 32px 48px; min-height: 100vh; }
.page-header { margin-bottom: 24px; animation: iosFadeUp var(--dur-slow) var(--spring-soft) both; }
.page-header h4 {
    font-size: 1.75rem; font-weight: 700; color: var(--blue-900);
    margin-bottom: 6px; letter-spacing: -0.03em;
}
.page-header p { color: var(--ios-secondary); font-size: 0.9rem; margin: 0; font-weight: 400; }
.page-header .breadcrumb { font-size: 0.8rem; margin-bottom: 8px; }
.page-header .breadcrumb-item a {
    color: var(--blue-600); text-decoration: none; font-weight: 500;
    transition: color var(--dur-fast);
}
.page-header .breadcrumb-item a:hover { color: var(--yellow-500); }
.page-header .breadcrumb-item.active { color: var(--ios-secondary); }

/* iOS SEGMENTED CONTROL TABS */
.dash-tabs {
    display: inline-flex; gap: 0;
    background: rgba(118, 118, 128, 0.1);
    border-radius: var(--radius-sm); padding: 3px;
    margin-bottom: 20px;
    backdrop-filter: blur(8px);
}
.dash-tab {
    background: transparent; border: none; border-radius: 8px;
    padding: 9px 20px; font-size: 0.85rem; font-weight: 600;
    color: var(--ios-secondary); cursor: pointer;
    transition: all var(--dur-normal) var(--spring);
    position: relative; white-space: nowrap;
}
.dash-tab:hover { color: var(--blue-800); }
.dash-tab.active {
    background: var(--ios-card); color: var(--blue-900);
    box-shadow: var(--shadow-sm), 0 1px 4px rgba(0, 0, 0, 0.06);
}
.dash-tab:active { transform: scale(0.96); }
.dash-tab-pane { display: none; }
.dash-tab-pane.active { display: block; animation: iosTabIn var(--dur-normal) var(--spring-soft) both; }
@keyframes iosTabIn {
    from { opacity: 0; transform: translateY(10px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.tab-badge {
    font-size: 0.68rem; font-weight: 700; padding: 2px 7px;
    border-radius: 20px; margin-left: 6px; vertical-align: middle;
}

/* KEPUTUSAN / RADIO CARDS — iOS selection tiles */
.keputusan-card, .radio-card {
    border: 1.5px solid var(--ios-separator);
    border-radius: var(--radius-md); padding: 16px 18px;
    cursor: pointer; display: flex; align-items: center; gap: 12px;
    background: var(--ios-card);
    transition: transform var(--dur-normal) var(--spring), box-shadow var(--dur-normal), border-color var(--dur-fast), background var(--dur-fast);
}
.keputusan-card:hover, .radio-card:hover {
    border-color: rgba(250, 204, 21, 0.5);
    transform: translateY(-2px) scale(1.01);
    box-shadow: var(--shadow-md);
}
.keputusan-card:active, .radio-card:active { transform: scale(0.98); }
.keputusan-card.lulus { border-color: #34c759; background: rgba(52, 199, 89, 0.08); }
.keputusan-card.tolak { border-color: #ff3b30; background: rgba(255, 59, 48, 0.06); }
.radio-card.selected {
    border-color: var(--yellow-400);
    background: rgba(250, 204, 21, 0.1);
    box-shadow: var(--shadow-md), inset 0 0 0 1px rgba(250, 204, 21, 0.2);
}
.keputusan-card input, .radio-card input { accent-color: var(--blue-600); width: 18px; height: 18px; }

/* STAT CARDS — iOS widget style */
.stat-card {
    border: none; border-radius: var(--radius-lg); padding: 22px 24px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: var(--shadow-md); background: var(--ios-card);
    border: 1px solid rgba(0, 0, 0, 0.04);
    transition: transform var(--dur-normal) var(--spring), box-shadow var(--dur-normal);
    animation: iosFadeUp var(--dur-slow) var(--spring-soft) both;
}
.stat-card:hover {
    transform: translateY(-4px) scale(1.015);
    box-shadow: var(--shadow-lg);
}
.stat-card:active { transform: scale(0.98); }
.stat-icon {
    width: 52px; height: 52px; border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
    transition: transform var(--dur-normal) var(--spring);
}
.stat-card:hover .stat-icon { transform: scale(1.08); }
.stat-num { font-size: 2rem; font-weight: 700; line-height: 1; letter-spacing: -0.03em; }
.stat-lbl { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 4px; opacity: 0.7; }

.icon-total   { background: var(--blue-50); color: var(--blue-700); }
.icon-warning { background: var(--yellow-50); color: var(--yellow-500); }
.icon-success { background: rgba(52, 199, 89, 0.12); color: #34c759; }
.icon-danger  { background: rgba(255, 59, 48, 0.1); color: #ff3b30; }
.icon-info    { background: var(--blue-50); color: var(--blue-600); }
.icon-primary { background: var(--yellow-50); color: var(--blue-800); }

.num-total   { color: var(--blue-700); } .num-warning { color: var(--yellow-500); }
.num-success { color: #34c759; } .num-danger  { color: #ff3b30; }
.num-info    { color: var(--blue-600); } .num-primary { color: var(--blue-800); }
.lbl-total, .lbl-warning, .lbl-success, .lbl-danger, .lbl-info, .lbl-primary { color: var(--ios-secondary); }

/* TABLE CARD — iOS grouped list */
.table-card {
    background: var(--ios-card); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid rgba(0, 0, 0, 0.04);
    overflow: hidden;
    animation: iosFadeUp var(--dur-slow) var(--spring-soft) both;
    animation-delay: 0.1s;
}
.table-card-header {
    padding: 18px 22px; border-bottom: 1px solid var(--ios-separator);
    display: flex; align-items: center; justify-content: space-between;
    background: transparent;
}
.table-card-header h6 { font-size: 1.05rem; font-weight: 700; color: var(--blue-900); margin: 0; letter-spacing: -0.02em; }
.badge-count {
    background: rgba(250, 204, 21, 0.2); color: var(--blue-800);
    font-size: 0.75rem; font-weight: 700; padding: 4px 11px; border-radius: 20px;
}

table.data-table { width: 100%; border-collapse: collapse; }
table.data-table thead th {
    background: transparent; color: var(--ios-secondary);
    font-size: 0.7rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.06em; padding: 12px 18px;
    border-bottom: 1px solid var(--ios-separator); white-space: nowrap;
}
table.data-table tbody td {
    padding: 15px 18px; border-bottom: 1px solid var(--ios-separator);
    font-size: 0.9rem; color: var(--ios-label); vertical-align: middle;
    transition: background var(--dur-fast);
}
table.data-table tbody tr:last-child td { border-bottom: none; }
table.data-table tbody tr {
    transition: background var(--dur-fast), transform var(--dur-normal) var(--spring);
}
table.data-table tbody tr:hover td { background: rgba(250, 204, 21, 0.06); }
table.data-table tbody tr:active td { background: rgba(0, 0, 0, 0.04); }

/* BADGES — iOS pill tags */
.badge-status {
    padding: 5px 12px; border-radius: 20px; font-size: 0.72rem;
    font-weight: 600; letter-spacing: 0.02em; white-space: nowrap;
    transition: transform var(--dur-fast) var(--spring);
}
.badge-status:hover { transform: scale(1.05); }
.badge-warning   { background: rgba(255, 149, 0, 0.15); color: #c93400; }
.badge-info      { background: rgba(0, 122, 255, 0.12); color: var(--blue-700); }
.badge-success   { background: rgba(52, 199, 89, 0.15); color: #248a3d; }
.badge-danger    { background: rgba(255, 59, 48, 0.12); color: #d70015; }
.badge-primary   { background: rgba(250, 204, 21, 0.2); color: var(--blue-800); }
.badge-secondary { background: rgba(120, 120, 128, 0.12); color: var(--ios-secondary); }

/* BUTTONS — iOS filled & tinted */
.btn-primary-dark {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    background: linear-gradient(180deg, var(--blue-700) 0%, var(--blue-800) 100%);
    color: #fff; border: none;
    border-radius: var(--radius-md); padding: 12px 22px;
    font-size: 0.9rem; font-weight: 600; text-decoration: none; cursor: pointer;
    box-shadow: var(--shadow-sm), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    letter-spacing: -0.01em;
}
.btn-primary-dark:hover {
    background: linear-gradient(180deg, var(--blue-600) 0%, var(--blue-700) 100%);
    color: #fff;
}

.btn-secondary-soft {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(120, 120, 128, 0.12); color: var(--ios-label);
    border: none; border-radius: var(--radius-md); padding: 11px 20px;
    font-size: 0.875rem; font-weight: 600; text-decoration: none; cursor: pointer;
}
.btn-secondary-soft:hover { background: rgba(120, 120, 128, 0.18); }

.btn-success-soft {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(52, 199, 89, 0.12); color: #248a3d; border: none;
    border-radius: var(--radius-md); padding: 9px 16px;
    font-size: 0.85rem; font-weight: 600; text-decoration: none; cursor: pointer;
}
.btn-success-soft:hover { background: rgba(52, 199, 89, 0.2); }

.btn-danger-soft {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255, 59, 48, 0.1); color: #d70015; border: none;
    border-radius: var(--radius-md); padding: 9px 16px;
    font-size: 0.85rem; font-weight: 600; text-decoration: none; cursor: pointer;
}
.btn-danger-soft:hover { background: rgba(255, 59, 48, 0.18); }

/* FORM CARD — iOS settings sections */
.form-card {
    background: var(--ios-card); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md); border: 1px solid rgba(0, 0, 0, 0.04);
    overflow: hidden; animation: iosFadeUp var(--dur-slow) var(--spring-soft) both;
}
.form-section-header {
    background: rgba(242, 242, 247, 0.8);
    padding: 14px 22px; border-bottom: 1px solid var(--ios-separator);
    display: flex; align-items: center; gap: 10px;
}
.form-section-header .sec-label {
    background: linear-gradient(145deg, var(--blue-700), var(--blue-800));
    color: var(--yellow-400); font-size: 0.7rem; font-weight: 700;
    padding: 4px 10px; border-radius: 8px; letter-spacing: 0.04em;
}
.form-section-header .sec-title { font-size: 0.95rem; font-weight: 700; color: var(--blue-900); letter-spacing: -0.02em; }
.form-section-body { padding: 22px; }
.field-label { font-size: 0.85rem; font-weight: 600; color: var(--ios-label); margin-bottom: 8px; display: block; }
.field-label .req { color: #ff3b30; margin-left: 2px; }
.form-control-custom {
    border: none; border-radius: var(--radius-md); padding: 13px 16px;
    font-size: 0.95rem; color: var(--ios-label); width: 100%;
    transition: box-shadow var(--dur-normal), background var(--dur-fast), transform var(--dur-normal) var(--spring);
    outline: none; background: rgba(120, 120, 128, 0.08);
    -webkit-appearance: none; appearance: none;
}
.form-control-custom:focus {
    background: var(--ios-card);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25), var(--shadow-sm);
    transform: scale(1.005);
}
.form-control-custom::placeholder { color: var(--ios-secondary); }
.field-hint { font-size: 0.78rem; color: var(--ios-secondary); margin-top: 6px; }

select.form-control-custom {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2386868b' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center;
    padding-right: 36px;
}

/* TOAST — iOS notification banner */
.toast-box {
    position: fixed; top: 20px; right: 20px; left: auto; z-index: 9999;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    padding: 16px 20px; display: flex; align-items: center; gap: 14px;
    font-size: 0.9rem; color: var(--ios-label); min-width: 300px; max-width: 400px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    animation: iosSlideIn var(--dur-normal) var(--spring) both;
}
.toast-box .t-icon {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.toast-success .t-icon { background: rgba(52, 199, 89, 0.15); color: #34c759; }
.toast-error .t-icon { background: rgba(255, 59, 48, 0.12); color: #ff3b30; }

/* VIEW CARD */
.view-card {
    background: var(--ios-card); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md); border: 1px solid rgba(0, 0, 0, 0.04);
    margin-bottom: 16px; overflow: hidden;
    animation: iosFadeUp var(--dur-slow) var(--spring-soft) both;
    transition: transform var(--dur-normal) var(--spring), box-shadow var(--dur-normal);
}
.view-card:hover { box-shadow: var(--shadow-lg); }
.view-card-header {
    padding: 16px 22px; background: rgba(242, 242, 247, 0.6);
    border-bottom: 1px solid var(--ios-separator);
    display: flex; align-items: center; gap: 10px;
}
.view-card-header h6 { font-size: 1rem; font-weight: 700; color: var(--blue-900); margin: 0; letter-spacing: -0.02em; }
.view-card-body { padding: 22px; }
.info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 14px; }
.info-item label {
    font-size: 0.72rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.05em; color: var(--ios-secondary); display: block; margin-bottom: 4px;
}
.info-item .val { font-size: 0.95rem; color: var(--ios-label); font-weight: 500; }

/* TIMELINE */
.timeline { list-style: none; padding: 0; margin: 0; }
.timeline li { display: flex; gap: 14px; margin-bottom: 22px; animation: iosFadeUp var(--dur-normal) var(--spring-soft) both; }
.timeline li:nth-child(2) { animation-delay: 0.08s; }
.timeline li:nth-child(3) { animation-delay: 0.16s; }
.timeline li:nth-child(4) { animation-delay: 0.24s; }
.tl-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; transition: transform var(--dur-normal) var(--spring); }
.tl-dot.done { background: var(--yellow-400); box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.25); }
.tl-dot.pending { background: var(--ios-bg); border: 2px solid var(--ios-separator); }
.tl-dot.active { background: var(--blue-600); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2); animation: iosScaleIn 1.2s var(--spring) infinite alternate; }
.tl-content .tl-title { font-size: 0.9rem; font-weight: 600; color: var(--blue-900); }
.tl-content .tl-date  { font-size: 0.78rem; color: var(--ios-secondary); }
.tl-content .tl-note  { font-size: 0.85rem; color: var(--ios-secondary); margin-top: 4px; }

/* EMPTY STATE */
.empty-state { text-align: center; padding: 64px 24px; color: var(--ios-secondary); animation: iosFadeUp var(--dur-slow) var(--spring-soft) both; }
.empty-state i { font-size: 3.2rem; margin-bottom: 16px; display: block; color: rgba(120, 120, 128, 0.3); }

/* SISTEM TABLE */
.sistem-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.875rem; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--ios-separator); }
.sistem-table th, .sistem-table td { padding: 12px 16px; border-bottom: 1px solid var(--ios-separator); }
.sistem-table thead th {
    background: rgba(242, 242, 247, 0.8); color: var(--ios-secondary);
    font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;
}
.sistem-table tbody tr { transition: background var(--dur-fast); }
.sistem-table tbody tr:last-child td { border-bottom: none; }
.sistem-table tbody tr:hover td { background: rgba(250, 204, 21, 0.05); }
.sistem-table .check-col { width: 50px; text-align: center; }
.sistem-table input[type=checkbox] { width: 20px; height: 20px; cursor: pointer; accent-color: var(--blue-600); border-radius: 6px; }
.sistem-table input[type=text] {
    border: none; border-radius: 8px; padding: 8px 12px;
    font-size: 0.85rem; width: 100%; outline: none;
    background: rgba(120, 120, 128, 0.08);
    transition: box-shadow var(--dur-normal), background var(--dur-fast);
}
.sistem-table input[type=text]:focus { background: var(--ios-card); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2); }

/* ACTION ROW */
.action-row {
    display: flex; align-items: center; gap: 12px; padding: 20px 22px;
    border-top: 1px solid var(--ios-separator);
    background: rgba(242, 242, 247, 0.5);
    backdrop-filter: blur(8px);
}

/* SCROLLBAR — minimal iOS-like */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(120, 120, 128, 0.3); border-radius: 10px; border: 2px solid transparent; background-clip: padding-box; }
::-webkit-scrollbar-thumb:hover { background: rgba(120, 120, 128, 0.5); background-clip: padding-box; }

/* HAMBURGER */
.hamburger {
    display: none; position: fixed; top: 16px; left: 16px; z-index: 201;
    background: var(--ios-elevated); color: #f8fafc; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;
    width: 42px; height: 42px; font-size: 1.2rem;
    align-items: center; justify-content: center; cursor: pointer;
    box-shadow: var(--shadow-md); transition: background var(--dur-fast) var(--ease-ios), transform var(--dur-normal) var(--spring);
}
.hamburger:hover { background: var(--blue-800); }

/* SIDEBAR OVERLAY */
.sidebar-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 99;
}
.sidebar-overlay.open { display: block; }

/* TABLE SCROLL HINT — hidden on desktop */
.table-scroll-hint {
    display: none; font-size: 0.75rem; color: var(--yellow-400); margin-bottom: 6px;
}

/* HELPERS */
.text-primary-theme { color: var(--blue-700) !important; }
.bg-sec-label {
    background: linear-gradient(145deg, var(--blue-700), var(--blue-800));
    color: var(--yellow-400); font-size: 0.72rem; font-weight: 700;
    padding: 4px 10px; border-radius: 8px;
}
.tag-fungsi {
    display: inline-block; font-size: 0.72rem; padding: 3px 8px;
    border-radius: 20px; background: rgba(250, 204, 21, 0.2);
    color: var(--blue-800); font-weight: 600;
    transition: transform var(--dur-fast) var(--spring);
}
.tag-fungsi:hover { transform: scale(1.06); }

/* ── DARK THEME OVERRIDES ── */
.page-header h4 { color: #f1f5f9; }
.page-header p { color: var(--ios-secondary); }
.main-content { background: var(--ios-bg); }

.dash-tabs { background: rgba(255, 255, 255, 0.06); }
.dash-tab { color: var(--ios-secondary); }
.dash-tab:hover { color: #facc15; }
.dash-tab.active { background: var(--ios-elevated); color: #facc15; box-shadow: var(--shadow-sm); }

.stat-card {
    background: var(--ios-card);
    border-color: var(--grey-border);
    box-shadow: var(--shadow-md);
}
.stat-card:hover { box-shadow: var(--shadow-lg), 0 0 0 1px rgba(250, 204, 21, 0.1); }

.icon-total   { background: rgba(37, 99, 235, 0.15); color: #60a5fa; }
.icon-warning { background: rgba(250, 204, 21, 0.12); color: #facc15; }
.icon-success { background: rgba(52, 211, 153, 0.12); color: #34d399; }
.icon-danger  { background: rgba(248, 113, 113, 0.12); color: #f87171; }
.icon-info    { background: rgba(96, 165, 250, 0.12); color: #93c5fd; }
.icon-primary { background: rgba(250, 204, 21, 0.12); color: #fde047; }

.num-total { color: #60a5fa; } .num-warning { color: #facc15; }
.num-success { color: #34d399; } .num-danger { color: #f87171; }
.num-info { color: #93c5fd; } .num-primary { color: #fde047; }

.table-card { background: var(--ios-card); border-color: var(--grey-border); }
.table-card-header { background: var(--ios-elevated); border-color: var(--ios-separator); }
.table-card-header h6 { color: #f1f5f9; }
.badge-count { background: rgba(250, 204, 21, 0.15); color: #facc15; }

table.data-table thead th { background: var(--ios-elevated); color: var(--ios-secondary); border-color: var(--ios-separator); }
table.data-table tbody td { color: var(--ios-label); border-color: var(--ios-separator); }
table.data-table tbody tr:hover td { background: rgba(250, 204, 21, 0.04); }

.form-card { background: var(--ios-card); border-color: var(--grey-border); }
.form-section-header { background: var(--ios-elevated); border-color: var(--ios-separator); }
.form-section-header .sec-title { color: #f1f5f9; }
.form-control-custom { background: rgba(0, 0, 0, 0.25); color: var(--ios-label); border: 1px solid var(--ios-separator); }
.form-control-custom:focus { background: var(--ios-elevated); box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.2); }

.view-card { background: var(--ios-card); border-color: var(--grey-border); }
.view-card-header { background: var(--ios-elevated); border-color: var(--ios-separator); }
.view-card-header h6 { color: #f1f5f9; }
.info-item .val { color: var(--ios-label); }

.toast-box { background: rgba(21, 29, 46, 0.95); border-color: var(--ios-separator); color: var(--ios-label); backdrop-filter: blur(20px); }

.btn-secondary-soft { background: rgba(255,255,255,0.06); color: var(--ios-secondary); border: 1px solid var(--ios-separator); }
.btn-secondary-soft:hover { background: rgba(255,255,255,0.1); color: #f1f5f9; }

.keputusan-card, .radio-card { background: var(--ios-card); border-color: var(--ios-separator); }
.keputusan-card.lulus { background: rgba(52, 211, 153, 0.08); }
.keputusan-card.tolak { background: rgba(248, 113, 113, 0.08); }
.radio-card.selected { background: rgba(250, 204, 21, 0.08); }

.sistem-table { border-color: var(--ios-separator); }
.sistem-table th, .sistem-table td { border-color: var(--ios-separator); }
.sistem-table thead th { background: var(--ios-elevated); color: var(--ios-secondary); }
.sistem-table input[type=text] { background: rgba(0,0,0,0.25); color: var(--ios-label); border-color: var(--ios-separator); }

.action-row { background: var(--ios-elevated); border-color: var(--ios-separator); }
.empty-state { color: var(--ios-secondary); }
.empty-state i { color: var(--grey-border); }

.badge-warning   { background: rgba(251, 191, 36, 0.15); color: #fbbf24; }
.badge-info      { background: rgba(96, 165, 250, 0.12); color: #93c5fd; }
.badge-success   { background: rgba(52, 211, 153, 0.12); color: #34d399; }
.badge-danger    { background: rgba(248, 113, 113, 0.12); color: #f87171; }
.badge-primary   { background: rgba(250, 204, 21, 0.12); color: #fde047; }
.badge-secondary { background: rgba(148, 163, 184, 0.12); color: #94a3b8; }

.tag-fungsi { background: rgba(250, 204, 21, 0.12); color: #fde047; }
.bg-sec-label { background: linear-gradient(145deg, #1e4976, #0d2137); color: #facc15; }

.sidebar-brand .brand-icon {
    background: linear-gradient(145deg, #0d2137, #1a3a5c);
    box-shadow: 0 4px 16px rgba(250, 204, 21, 0.2), inset 0 0 0 1px rgba(250, 204, 21, 0.3);
    animation: none;
}

/* MOBILE */
@media (max-width: 768px) {
    .hamburger { display: flex; }
    .sidebar {
        width: 100vw;
        min-height: 100vh;
        height: 100vh;
        position: fixed;
        transform: translateX(-100vw);
        transition: transform 0.28s cubic-bezier(0.4,0,0.2,1);
        z-index: 200;
    }
    .sidebar.open { transform: translateX(0); }
    .main-content { margin-left: 0; padding: 72px 16px 24px; }
    .page-header h4 { font-size: 1.15rem; }
    .info-row { grid-template-columns: 1fr; }
    .dash-tabs { width: 100%; overflow-x: auto; }
    .action-row { flex-wrap: wrap; }
    .stat-num { font-size: 1.5rem; }
    .stat-card { padding: 16px 18px; }
    .toast-box { left: 16px; right: 16px; min-width: auto; top: 16px; }
    .table-scroll-hint { display: block; }
    .radio-card { padding: 10px 12px; }
    .form-section-body { padding: 16px; }
    .view-card-body { padding: 16px; }
}

<?php chatboxCSS(); ?>
</style>
<?php }

function sharedJS() { ?>
<script>
function switchTab(btn, id) {
    const panes = document.querySelectorAll('.dash-tab-pane');
    const tabs = document.querySelectorAll('.dash-tab');
    const target = document.getElementById(id);
    if (!target || btn.classList.contains('active')) return;

    tabs.forEach(b => b.classList.remove('active'));
    panes.forEach(p => {
        if (p.classList.contains('active')) {
            p.style.animation = 'none';
            p.offsetHeight;
            p.classList.remove('active');
        }
    });

    btn.classList.add('active');
    target.classList.add('active');
    target.style.animation = '';
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.form-control-custom, .form-control').forEach(el => {
        el.addEventListener('focus', () => el.closest('.mb-3, .info-item, td')?.classList.add('focused'));
        el.addEventListener('blur', () => el.closest('.mb-3, .info-item, td')?.classList.remove('focused'));
    });

    const toast = document.getElementById('toast');
    if (toast) {
        toast.style.transition = 'opacity 0.45s cubic-bezier(0.25,0.1,0.25,1), transform 0.45s cubic-bezier(0.34,1.25,0.64,1)';
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0) scale(1)';
        });
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px) scale(0.95)';
            setTimeout(() => toast.remove(), 450);
        }, 3200);
    }
});
</script>
<?php chatboxJS(); ?>
<?php }

function sidebarHTML($username, $roleLabel, $navItems) { ?>
<button class="hamburger" id="hamburger" aria-label="Buka menu">
    <i class="bi bi-list"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="sidebar" id="mainSidebar">
    <div class="sidebar-brand" style="position:relative">
        <div class="brand-icon">
            <svg width="26" height="26" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 2L4 8v8c0 7.2 5.1 13.9 12 15 6.9-1.1 12-7.8 12-15V8L16 2z" fill="#0d2137" stroke="#facc15" stroke-width="2"/>
                <path d="M16 10v8M12 14h8" stroke="#facc15" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
        </div>
        <div class="brand-title">BORANG CAPAIAN<br>SISTEM</div>
        <div class="brand-sub"><?= htmlspecialchars($roleLabel) ?></div>
        <button id="sidebarClose"
            style="display:none;position:absolute;top:12px;right:12px;background:rgba(255,255,255,0.15);border:none;border-radius:8px;color:#fff;width:34px;height:34px;font-size:1.1rem;align-items:center;justify-content:center;cursor:pointer">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="sidebar-nav">
        <div class="nav-label">Menu</div>
        <?php foreach ($navItems as $item): ?>
        <a href="<?= $item['href'] ?>" class="nav-item <?= $item['active'] ? 'active' : '' ?>">
            <i class="bi <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><i class="bi bi-person-fill"></i></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($username) ?></div>
                <div class="user-role"><?= htmlspecialchars($roleLabel) ?></div>
            </div>
        </div>
        <a href="logout.php" class="btn-logout"><i class="bi bi-box-arrow-left"></i> Log Keluar</a>
    </div>
</div>
<?php renderChatbox(); ?>
<script>
(function(){
    var ham     = document.getElementById('hamburger');
    var closeBtn= document.getElementById('sidebarClose');
    var overlay = document.getElementById('sidebarOverlay');
    var sidebar = document.getElementById('mainSidebar');
    function openSidebar()  {
        sidebar.classList.add('open'); overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        ham.style.display = 'none';
        closeBtn.style.display = 'flex';
    }
    function closeSidebar() {
        sidebar.classList.remove('open'); overlay.classList.remove('open');
        document.body.style.overflow = '';
        ham.style.display = '';
        closeBtn.style.display = 'none';
    }
    ham.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);
})();
</script>
<?php renderChatbox(); ?>
<?php }

function toastHTML($msg, $type = 'success') {
    $icon = $type === 'success' ? 'bi-check-lg' : 'bi-exclamation-triangle';
    echo "<div class='toast-box toast-{$type}' id='toast' style='opacity:0'>
        <div class='t-icon'><i class='bi {$icon}'></i></div>
        <div><div style='font-weight:600;font-size:0.95rem'>".($type==='success'?'Berjaya!':'Ralat')."</div>
        <div style='font-size:0.82rem;color:var(--ios-secondary,#86868b);margin-top:2px'>".htmlspecialchars($msg)."</div></div></div>";
}
