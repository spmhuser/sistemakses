<?php
require_once __DIR__ . '/_chatbox.php';

function sharedCSS() { ?>
<style>
* { box-sizing: border-box; }
body { font-family: 'Segoe UI', sans-serif; background: #f0f4ff; min-height: 100vh; margin: 0; }

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
    width: 240px; min-height: 100vh;
    background: linear-gradient(180deg, #0a1628 0%, #003087 60%, #1565c0 100%);
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
.main-content { margin-left: 240px; padding: 32px; min-height: 100vh; }
.page-header { margin-bottom: 28px; }
.page-header h4 { font-size: 1.4rem; font-weight: 700; color: #003087; margin-bottom: 4px; }
.page-header p { color: #6c757d; font-size: 0.85rem; margin: 0; }
.page-header .breadcrumb { font-size: 0.8rem; margin-bottom: 6px; }
.page-header .breadcrumb-item a { color: #1976d2; text-decoration: none; font-weight: 500; }
.page-header .breadcrumb-item a:hover { text-decoration: underline; }
.page-header .breadcrumb-item.active { color: #9ca3af; }

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
    box-shadow: 0 2px 16px rgba(0,48,135,0.08); background: #fff;
    transition: transform 0.15s, box-shadow 0.15s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,48,135,0.14); }
.stat-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
.stat-num { font-size: 1.9rem; font-weight: 800; line-height: 1; }
.stat-lbl { font-size: 0.78rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 3px; }

/* Icon colors - keep status colors semantic, rebrand total/primary to pink */
.icon-total   { background: #e8f0fe; color: #003087; }
.icon-warning { background: #fff8e1; color: #f59e0b; }
.icon-success { background: #e6f9f0; color: #16a34a; }
.icon-danger  { background: #fef0f0; color: #dc2626; }
.icon-info    { background: #e8f0fe; color: #1976d2; }
.icon-primary { background: #eff6ff; color: #0d47a1; }

.num-total   { color: #003087; } .num-warning { color: #f59e0b; }
.num-success { color: #16a34a; } .num-danger  { color: #dc2626; }
.num-info    { color: #1976d2; } .num-primary { color: #0d47a1; }

.lbl-total   { color: #9ca3af; } .lbl-warning { color: #1e3a5f; }
.lbl-success { color: #166534; } .lbl-danger  { color: #991b1b; }
.lbl-info    { color: #0d47a1; } .lbl-primary { color: #003087; }

/* TABLE CARD */
.table-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,48,135,0.08); overflow: hidden; }
.table-card-header {
    padding: 20px 24px; border-bottom: 1px solid #e8f0fe;
    display: flex; align-items: center; justify-content: space-between;
    background: transparent;
}
.table-card-header h6 { font-size: 1.05rem; font-weight: 700; color: var(--blue-900); margin: 0; letter-spacing: -0.02em; }
.badge-count {
    background: rgba(250, 204, 21, 0.2); color: var(--blue-800);
    font-size: 0.75rem; font-weight: 700; padding: 4px 11px; border-radius: 20px;
}
.table-card-header h6 { font-size: 1rem; font-weight: 700; color: #003087; margin: 0; }
.badge-count { background: #e8f0fe; color: #003087; font-size: 0.75rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; }

table.data-table { width: 100%; border-collapse: collapse; }
table.data-table thead th {
    background: #eff6ff; color: #0d47a1; font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.6px; padding: 12px 16px;
    border-bottom: 1px solid #e8f0fe; white-space: nowrap;
}
table.data-table tbody td {
    padding: 14px 16px; border-bottom: 1px solid #eff6ff;
    font-size: 0.875rem; color: #374151; vertical-align: middle;
}
table.data-table tbody tr:last-child td { border-bottom: none; }
table.data-table tbody tr:hover td { background: #eff6ff; }

/* BADGES - status badges keep semantic colors */
.badge-status { padding: 5px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.4px; white-space: nowrap; }
.badge-warning   { background: #fff8e1; color: #1e3a5f; }
.badge-info      { background: #e8f0fe; color: #0d47a1; }
.badge-success   { background: #e6f9f0; color: #166534; }
.badge-danger    { background: #fef0f0; color: #991b1b; }
.badge-primary   { background: #eff6ff; color: #003087; }
.badge-secondary { background: #f3f4f6; color: #6b7280; }

/* BUTTONS */
.btn-primary-dark {
    display: inline-flex; align-items: center; gap: 8px; background: #003087;
    color: #fff; border: none; border-radius: 10px; padding: 10px 20px;
    font-size: 0.875rem; font-weight: 600; text-decoration: none; cursor: pointer;
    transition: background 0.15s, transform 0.15s, box-shadow 0.15s;
}
.btn-primary-dark:hover { background: #0d47a1; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,48,135,0.35); }

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

/* FORM CARD */
.form-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,48,135,0.08); overflow: hidden; }
.form-section-header {
    background: #eff6ff; padding: 14px 24px; border-bottom: 1px solid #e8f0fe;
    display: flex; align-items: center; gap: 10px;
}
.form-section-header .sec-label {
    background: #003087; color: #fff; font-size: 0.72rem; font-weight: 700;
    padding: 3px 9px; border-radius: 6px; letter-spacing: 0.5px;
}
.form-section-header .sec-title { font-size: 0.9rem; font-weight: 700; color: #003087; }
.form-section-body { padding: 24px; }
.field-label { font-size: 0.83rem; font-weight: 600; color: #374151; margin-bottom: 6px; display: block; }
.field-label .req { color: #dc2626; margin-left: 2px; }
.form-control-custom {
    border: 1px solid #bfdbfe; border-radius: 10px; padding: 10px 14px;
    font-size: 0.875rem; color: #374151; width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s; outline: none;
    background: #fff;
}
.form-control-custom:focus { border-color: #1976d2; box-shadow: 0 0 0 3px rgba(0,48,135,0.15); }
.form-control-custom::placeholder { color: #bfdbfe; }
.field-hint { font-size: 0.76rem; color: #bfdbfe; margin-top: 5px; }

/* TOAST */
.toast-box {
    position: fixed; top: 24px; right: 24px; z-index: 9999; background: #fff;
    border-radius: 12px; box-shadow: 0 8px 30px rgba(0,48,135,0.15);
    padding: 14px 20px; display: flex; align-items: center; gap: 12px;
    font-size: 0.875rem; color: #374151; min-width: 280px;
    border-left: 4px solid #1565c0;
    animation: slideIn 0.3s ease;
}
.toast-box .t-icon { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.toast-success .t-icon { background: #e8f0fe; color: #1976d2; }
.toast-error .t-icon { background: #fef0f0; color: #dc2626; }
@keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

/* VIEW CARD */
.view-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,48,135,0.08); margin-bottom: 20px; overflow: hidden; }
.view-card-header { padding: 16px 24px; background: #eff6ff; border-bottom: 1px solid #e8f0fe; display: flex; align-items: center; gap: 10px; }
.view-card-header h6 { font-size: 0.95rem; font-weight: 700; color: #003087; margin: 0; }
.view-card-body { padding: 24px; }
.info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px; }
.info-item label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #bfdbfe; display: block; margin-bottom: 3px; }
.info-item .val { font-size: 0.9rem; color: #374151; font-weight: 500; }

/* TIMELINE */
.timeline { list-style: none; padding: 0; margin: 0; }
.timeline li { display: flex; gap: 14px; margin-bottom: 20px; }
.tl-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.tl-dot.done    { background: #1565c0; }
.tl-dot.pending { background: #e8f0fe; border: 2px solid #bfdbfe; }
.tl-dot.active  { background: #f59e0b; }
.tl-content .tl-title { font-size: 0.875rem; font-weight: 600; color: #003087; }
.tl-content .tl-date  { font-size: 0.78rem; color: #bfdbfe; }
.tl-content .tl-note  { font-size: 0.82rem; color: #6b7280; margin-top: 3px; }

/* EMPTY STATE */
.empty-state { text-align: center; padding: 60px 20px; color: #bfdbfe; }
.empty-state i { font-size: 3rem; margin-bottom: 12px; display: block; color: #bfdbfe; }

/* SISTEM TABLE */
.sistem-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.sistem-table th, .sistem-table td { padding: 10px 14px; border: 1px solid #e8f0fe; }
.sistem-table thead th { background: #eff6ff; color: #003087; font-weight: 700; font-size: 0.72rem; text-transform: uppercase; }
.sistem-table tbody tr:hover td { background: #eff6ff; }
.sistem-table .check-col { width: 50px; text-align: center; }
.sistem-table input[type=checkbox] { width: 16px; height: 16px; cursor: pointer; accent-color: #1976d2; }
.sistem-table input[type=text] { border: 1px solid #e8f0fe; border-radius: 6px; padding: 5px 10px; font-size: 0.82rem; width: 100%; outline: none; background: #fff; }
.sistem-table input[type=text]:focus { border-color: #1976d2; box-shadow: 0 0 0 2px rgba(0,48,135,0.10); }

/* ACTION ROW */
.action-row { display: flex; align-items: center; gap: 10px; padding: 20px 24px; border-top: 1px solid #e8f0fe; background: #eff6ff; }

/* SCROLLBAR */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: #eff6ff; }
::-webkit-scrollbar-thumb { background: #bfdbfe; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #1565c0; }
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
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-rocket-takeoff-fill"></i></div>
        <div class="brand-title">SISTEM CAPAIAN SISTEM</div>
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
