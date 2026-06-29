<?php
function sharedCSS() { ?>
<style>
* { box-sizing: border-box; }
body {
    font-family: 'Segoe UI', 'Inter', sans-serif;
    background: #FFFFFF;
    color: #2D2433;
    font-size: 16px; line-height: 1.55;
    min-height: 100vh; margin: 0;
}

/* SIDEBAR */
.sidebar {
    width: 252px; min-height: 100vh;
    background: linear-gradient(165deg, #1E3A5F 0%, #2E73D8 55%, #1FBCD4 100%);
    position: fixed; top: 0; left: 0;
    display: flex; flex-direction: column; z-index: 100;
    box-shadow: 4px 0 24px rgba(40,70,120,0.25);
}
.sidebar-brand { padding: 26px 22px 20px; border-bottom: 1px solid rgba(255,255,255,0.20); }
.sidebar-brand .brand-icon {
    width: 54px; height: 54px;
    background: linear-gradient(135deg, #1FBCD4 0%, #2E73D8 100%);
    border-radius: 15px;
    display: flex; align-items: center; justify-content: center;
    font-size: 27px; color: #fff; margin-bottom: 12px;
    box-shadow: 0 6px 16px rgba(31,188,212,0.5);
}
.sidebar-brand .brand-title { color: #fff; font-size: 0.98rem; font-weight: 800; line-height: 1.3; letter-spacing: 0.3px; }
.sidebar-brand .brand-sub { color: rgba(255,255,255,0.85); font-size: 0.8rem; margin-top: 4px; font-weight: 500; }
.sidebar-nav { padding: 18px 14px; flex: 1; }
.sidebar-nav .nav-label {
    color: rgba(255,255,255,0.75); font-size: 0.72rem; text-transform: uppercase;
    letter-spacing: 1.2px; padding: 0 10px; margin-bottom: 10px; margin-top: 14px; font-weight: 700;
}
.sidebar-nav .nav-item {
    display: flex; align-items: center; gap: 13px; padding: 13px 15px; border-radius: 13px;
    color: #fff; font-size: 1rem; font-weight: 600;
    text-decoration: none; transition: all 0.18s; margin-bottom: 6px;
}
.sidebar-nav .nav-item:hover { background: rgba(255,255,255,0.22); transform: translateX(4px); }
.sidebar-nav .nav-item.active {
    background: #fff; color: #234B7A; font-weight: 800;
    box-shadow: 0 6px 18px rgba(0,0,0,0.22);
}
.sidebar-nav .nav-item.active i { color: #1FBCD4; }
.sidebar-nav .nav-item i { font-size: 1.2rem; width: 22px; text-align: center; }
.sidebar-footer { padding: 16px 14px; border-top: 1px solid rgba(255,255,255,0.20); }
.user-info {
    display: flex; align-items: center; gap: 11px; padding: 11px 12px;
    background: rgba(255,255,255,0.16); border-radius: 13px; margin-bottom: 11px;
}
.user-avatar {
    width: 42px; height: 42px;
    background: linear-gradient(135deg, #2E73D8, #1FBCD4); border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 19px; color: #fff; flex-shrink: 0;
    box-shadow: 0 3px 9px rgba(0,0,0,0.2);
}
.user-name { color: #fff; font-size: 0.92rem; font-weight: 700; }
.user-role { color: rgba(255,255,255,0.82); font-size: 0.76rem; }
.btn-logout {
    display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
    background: rgba(255,255,255,0.95); border: none;
    color: #C0392B; border-radius: 11px; padding: 12px;
    font-size: 0.94rem; font-weight: 700; text-decoration: none; transition: all 0.18s;
}
.btn-logout:hover { background: #C0392B; color: #fff; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(192,57,43,0.5); }
.btn-logout:active { transform: translateY(1px) scale(0.98); }

/* MAIN */
.main-content { margin-left: 252px; padding: 34px; min-height: 100vh; }
.page-header { margin-bottom: 26px; }
.page-header h4 { font-size: 1.8rem; font-weight: 800; color: #1E3A5F; margin-bottom: 5px; }
.page-header p { color: #5C5560; font-size: 0.98rem; margin: 0; }
.page-header .breadcrumb { font-size: 0.86rem; margin-bottom: 6px; }
.page-header .breadcrumb-item a { color: #2F86DD; text-decoration: none; font-weight: 600; }
.page-header .breadcrumb-item a:hover { text-decoration: underline; }
.page-header .breadcrumb-item.active { color: #8A7E86; }

/* STAT CARDS */
.stat-card {
    border: none; border-radius: 18px; padding: 24px 26px;
    display: flex; align-items: center; gap: 18px;
    box-shadow: 0 4px 18px rgba(40,70,120,0.10); background: #fff;
    transition: transform 0.18s, box-shadow 0.18s;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(40,70,120,0.18); }
.stat-icon { width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 27px; flex-shrink: 0; }
.stat-num { font-size: 2.3rem; font-weight: 800; line-height: 1; }
.stat-lbl { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 5px; }

/* Icon colors - bright & cheerful mix */
.icon-total   { background: #E6EFFA; color: #2E73D8; }
.icon-warning { background: #FFF3D2; color: #E8920C; }
.icon-success { background: #DFF7E6; color: #18A957; }
.icon-danger  { background: #FFE2E0; color: #E23B36; }
.icon-info    { background: #E6EEFF; color: #2F6BE8; }
.icon-primary { background: #E6EFFA; color: #2F86DD; }

.num-total   { color: #2E73D8; } .num-warning { color: #E8920C; }
.num-success { color: #18A957; } .num-danger  { color: #E23B36; }
.num-info    { color: #2F6BE8; } .num-primary { color: #2F86DD; }

.lbl-total   { color: #6E6470; } .lbl-warning { color: #92580B; }
.lbl-success { color: #15803D; } .lbl-danger  { color: #B42318; }
.lbl-info    { color: #1E4FA8; } .lbl-primary { color: #234B7A; }

/* TABLE CARD */
.table-card { background: #fff; border-radius: 18px; box-shadow: 0 4px 18px rgba(40,70,120,0.10); overflow: hidden; }
.table-card-header {
    padding: 20px 24px; border-bottom: 2px solid #E6EFFA;
    display: flex; align-items: center; justify-content: space-between;
}
.table-card-header h6 { font-size: 1.12rem; font-weight: 800; color: #1E3A5F; margin: 0; }
.badge-count { background: linear-gradient(135deg, #1FBCD4, #2E73D8); color: #fff; font-size: 0.8rem; font-weight: 700; padding: 4px 13px; border-radius: 20px; }

table.data-table { width: 100%; border-collapse: collapse; }
table.data-table thead th {
    background: #EFF4FC; color: #234B7A; font-size: 0.8rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 16px;
    border-bottom: 2px solid #D7E3F3; white-space: nowrap;
}
table.data-table tbody td {
    padding: 15px 16px; border-bottom: 1px solid #F3E9DA;
    font-size: 0.95rem; color: #2D2433; vertical-align: middle;
}
table.data-table tbody tr:last-child td { border-bottom: none; }
table.data-table tbody tr:hover td { background: #EFF4FC; }

/* BADGES - status badges keep semantic colors */
.badge-status { padding: 6px 13px; border-radius: 20px; font-size: 0.76rem; font-weight: 700; letter-spacing: 0.3px; white-space: nowrap; }
.badge-warning   { background: #FFF3D2; color: #92580B; }
.badge-info      { background: #E6EEFF; color: #1E4FA8; }
.badge-success   { background: #DFF7E6; color: #15803D; }
.badge-danger    { background: #FFE2E0; color: #B42318; }
.badge-primary   { background: #EFF4FC; color: #234B7A; }
.badge-secondary { background: #EEE9E0; color: #5C5560; }

/* BUTTONS - modern shared base */
.btn-primary-dark, .btn-secondary-soft, .btn-success-soft, .btn-danger-soft {
    position: relative; overflow: hidden;
    display: inline-flex; align-items: center; justify-content: center; gap: 9px;
    border: none; cursor: pointer; text-decoration: none;
    font-weight: 700; letter-spacing: 0.2px; line-height: 1.1;
    border-radius: 12px;
    transition: transform 0.22s cubic-bezier(.2,.8,.2,1), box-shadow 0.22s ease, filter 0.22s ease, background 0.22s ease;
    -webkit-tap-highlight-color: transparent;
}
/* shine sweep on hover */
.btn-primary-dark::after, .btn-secondary-soft::after, .btn-success-soft::after, .btn-danger-soft::after {
    content: ''; position: absolute; top: 0; left: -130%; width: 65%; height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.55), transparent);
    transform: skewX(-20deg); transition: left 0.55s ease; pointer-events: none;
}
.btn-primary-dark:hover::after, .btn-secondary-soft:hover::after, .btn-success-soft:hover::after, .btn-danger-soft:hover::after { left: 130%; }
.btn-primary-dark:active, .btn-secondary-soft:active, .btn-success-soft:active, .btn-danger-soft:active { transform: translateY(1px) scale(0.98); }
.btn-primary-dark:focus-visible, .btn-secondary-soft:focus-visible, .btn-success-soft:focus-visible, .btn-danger-soft:focus-visible { outline: 3px solid rgba(46,115,216,0.45); outline-offset: 2px; }

.btn-primary-dark {
    background: linear-gradient(135deg, #2E73D8 0%, #1FBCD4 100%);
    color: #fff; padding: 13px 28px; font-size: 1rem;
    box-shadow: 0 6px 18px rgba(46,115,216,0.35);
}
.btn-primary-dark:hover { color: #fff; transform: translateY(-3px); box-shadow: 0 13px 30px rgba(31,188,212,0.48); filter: brightness(1.05); }

.btn-secondary-soft {
    background: #fff; color: #1E3A5F; padding: 12px 22px; font-size: 0.95rem;
    box-shadow: inset 0 0 0 1.5px #DDE7F4, 0 2px 8px rgba(40,70,120,0.06);
}
.btn-secondary-soft:hover { color: #1E3A5F; transform: translateY(-2px); box-shadow: inset 0 0 0 1.5px #BFD2EC, 0 8px 18px rgba(40,70,120,0.15); }

.btn-success-soft {
    background: linear-gradient(135deg, #18A957 0%, #3CC470 100%);
    color: #fff; padding: 11px 20px; font-size: 0.92rem;
    box-shadow: 0 5px 14px rgba(24,169,87,0.32);
}
.btn-success-soft:hover { color: #fff; transform: translateY(-2px); box-shadow: 0 10px 24px rgba(24,169,87,0.45); filter: brightness(1.05); }

.btn-danger-soft {
    background: linear-gradient(135deg, #E23B36 0%, #3D9AE0 100%);
    color: #fff; padding: 11px 20px; font-size: 0.92rem;
    box-shadow: 0 5px 14px rgba(226,59,54,0.30);
}
.btn-danger-soft:hover { color: #fff; transform: translateY(-2px); box-shadow: 0 10px 24px rgba(226,59,54,0.45); filter: brightness(1.05); }

/* FORM CARD */
.form-card { background: #fff; border-radius: 18px; box-shadow: 0 4px 18px rgba(40,70,120,0.10); overflow: hidden; }
.form-section-header {
    background: linear-gradient(90deg, #E6EFFA, #F4F8FD); padding: 16px 24px; border-bottom: 2px solid #D7E3F3;
    display: flex; align-items: center; gap: 10px;
}
.form-section-header .sec-label {
    background: linear-gradient(135deg, #2E73D8, #1FBCD4); color: #fff; font-size: 0.78rem; font-weight: 800;
    padding: 4px 11px; border-radius: 8px; letter-spacing: 0.5px;
}
.form-section-header .sec-title { font-size: 1rem; font-weight: 800; color: #1E3A5F; }
.form-section-body { padding: 24px; }
.field-label { font-size: 0.95rem; font-weight: 700; color: #3A2E40; margin-bottom: 7px; display: block; }
.field-label .req { color: #E23B36; margin-left: 2px; }
.form-control-custom {
    border: 1.5px solid #DCE6F2; border-radius: 12px; padding: 12px 15px;
    font-size: 0.98rem; color: #2D2433; width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s; outline: none;
    background: #fff;
}
.form-control-custom:focus { border-color: #2F86DD; box-shadow: 0 0 0 3px rgba(46,115,216,0.18); }
.form-control-custom::placeholder { color: #A89AA2; }
.field-hint { font-size: 0.82rem; color: #6E6470; margin-top: 5px; }

/* TOAST */
.toast-box {
    position: fixed; top: 24px; right: 24px; z-index: 9999; background: #fff;
    border-radius: 14px; box-shadow: 0 10px 34px rgba(40,70,120,0.22);
    padding: 15px 22px; display: flex; align-items: center; gap: 13px;
    font-size: 0.95rem; color: #2D2433; min-width: 290px;
    border-left: 5px solid #1FBCD4;
    animation: slideIn 0.3s ease;
}
.toast-box .t-icon { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.toast-success .t-icon { background: #DFF7E6; color: #18A957; }
.toast-error .t-icon { background: #FFE2E0; color: #E23B36; }
@keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

/* VIEW CARD */
.view-card { background: #fff; border-radius: 18px; box-shadow: 0 4px 18px rgba(40,70,120,0.10); margin-bottom: 20px; overflow: hidden; }
.view-card-header { padding: 17px 24px; background: linear-gradient(90deg, #E6EFFA, #F4F8FD); border-bottom: 2px solid #D7E3F3; display: flex; align-items: center; gap: 10px; }
.view-card-header h6 { font-size: 1.05rem; font-weight: 800; color: #1E3A5F; margin: 0; }
.view-card-body { padding: 24px; }
.info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 14px; }
.info-item label { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #2E73D8; display: block; margin-bottom: 4px; }
.info-item .val { font-size: 1rem; color: #2D2433; font-weight: 600; }

/* TIMELINE */
.timeline { list-style: none; padding: 0; margin: 0; }
.timeline li { display: flex; gap: 15px; margin-bottom: 22px; }
.tl-dot { width: 14px; height: 14px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.tl-dot.done    { background: #2E73D8; }
.tl-dot.pending { background: #E6EFFA; border: 2px solid #A9C6E8; }
.tl-dot.active  { background: #1FBCD4; box-shadow: 0 0 0 4px rgba(31,188,212,0.25); }
.tl-content .tl-title { font-size: 0.96rem; font-weight: 700; color: #1E3A5F; }
.tl-content .tl-date  { font-size: 0.82rem; color: #6E6470; }
.tl-content .tl-note  { font-size: 0.88rem; color: #5C5560; margin-top: 3px; }

/* EMPTY STATE */
.empty-state { text-align: center; padding: 60px 20px; color: #8A7E86; font-size: 1rem; }
.empty-state i { font-size: 3.2rem; margin-bottom: 12px; display: block; color: #A9C6E8; }

/* SISTEM TABLE */
.sistem-table { width: 100%; border-collapse: collapse; font-size: 0.92rem; }
.sistem-table th, .sistem-table td { padding: 11px 14px; border: 1px solid #D7E3F3; }
.sistem-table thead th { background: #EFF4FC; color: #234B7A; font-weight: 800; font-size: 0.78rem; text-transform: uppercase; }
.sistem-table tbody tr:hover td { background: #EFF4FC; }
.sistem-table .check-col { width: 50px; text-align: center; }
.sistem-table input[type=checkbox] { width: 18px; height: 18px; cursor: pointer; accent-color: #2F86DD; }
.sistem-table input[type=text] { border: 1.5px solid #DCE6F2; border-radius: 8px; padding: 7px 11px; font-size: 0.9rem; width: 100%; outline: none; background: #fff; color: #2D2433; }
.sistem-table input[type=text]:focus { border-color: #2F86DD; box-shadow: 0 0 0 2px rgba(46,115,216,0.15); }

/* ACTION ROW */
.action-row { display: flex; align-items: center; gap: 12px; padding: 20px 24px; border-top: 2px solid #D7E3F3; background: linear-gradient(90deg, #F4F8FD, #E6EFFA); }

/* SCROLLBAR */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: #ECE8F2; }
::-webkit-scrollbar-thumb { background: #A9C6E8; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #1FBCD4; }

/* ===================== MOBILE / RESPONSIVE ===================== */
.mobile-topbar { display: none; }
.sidebar-overlay { display: none; }

@media (max-width: 992px) {
    /* Top bar + off-canvas sidebar */
    .mobile-topbar {
        display: flex; align-items: center; gap: 12px;
        position: sticky; top: 0; z-index: 95;
        background: linear-gradient(135deg, #1E3A5F 0%, #2E73D8 100%);
        padding: 11px 15px; box-shadow: 0 2px 12px rgba(40,70,120,0.3);
    }
    .mobile-topbar .mt-burger {
        background: rgba(255,255,255,0.18); border: none; color: #fff;
        width: 44px; height: 44px; border-radius: 11px; font-size: 23px;
        display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0;
    }
    .mobile-topbar .mt-burger:active { background: rgba(255,255,255,0.32); }
    .mobile-topbar .mt-title { color: #fff; font-weight: 800; font-size: 0.96rem; letter-spacing: 0.3px; }
    .sidebar { transform: translateX(-100%); transition: transform 0.28s ease; width: 270px; }
    .sidebar.open { transform: translateX(0); }
    .sidebar-overlay {
        display: block; position: fixed; inset: 0; background: rgba(30,58,95,0.5);
        z-index: 94; opacity: 0; visibility: hidden; transition: opacity 0.28s;
    }
    .sidebar-overlay.show { opacity: 1; visibility: visible; }
    .main-content { margin-left: 0; padding: 18px 14px; }
    .page-header h4 { font-size: 1.45rem; }
    /* Jadual lain (tanpa .tbl-resp) boleh skrol mendatar, bukan terpotong */
    .table-card { overflow-x: auto; }
    /* Maklumat dwi-lajur -> satu lajur pada telefon (view_permohonan dll.) */
    .info-row { grid-template-columns: 1fr; gap: 12px; }
    /* Baris butang borang -> penuh & senang ditekan */
    .action-row { flex-wrap: wrap; padding: 16px; gap: 10px; }
    .action-row > a, .action-row > button { flex: 1 1 auto; justify-content: center; text-align: center; }
    .stat-card { padding: 18px; gap: 13px; }
    .stat-icon { width: 50px; height: 50px; font-size: 23px; }
    .stat-num { font-size: 1.9rem; }
}

/* Jadual -> kad pada telefon (guna kelas .tbl-resp + atribut data-label pada td) */
@media (max-width: 768px) {
    table.data-table.tbl-resp thead { display: none; }
    table.data-table.tbl-resp,
    table.data-table.tbl-resp tbody,
    table.data-table.tbl-resp tr,
    table.data-table.tbl-resp td { display: block; width: 100%; }
    table.data-table.tbl-resp tr {
        border: 1px solid #E2EAF5; border-radius: 14px; margin: 12px; padding: 6px 2px;
        box-shadow: 0 2px 9px rgba(40,70,120,0.08); background: #fff;
    }
    table.data-table.tbl-resp tbody tr:hover td { background: transparent; }
    table.data-table.tbl-resp td {
        border: none !important; padding: 9px 16px !important;
        display: flex; justify-content: space-between; align-items: center; gap: 14px; text-align: right;
    }
    table.data-table.tbl-resp td::before {
        content: attr(data-label); font-weight: 700; color: #234B7A; font-size: 0.76rem;
        text-transform: uppercase; letter-spacing: 0.3px; text-align: left; flex-shrink: 0; white-space: nowrap;
    }
    table.data-table.tbl-resp td.cell-act {
        justify-content: flex-end; flex-wrap: wrap; gap: 8px;
        border-top: 1px dashed #E2EAF5 !important; margin-top: 4px; padding-top: 12px !important;
    }
    table.data-table.tbl-resp td.cell-act::before { content: none; }
    table.data-table.tbl-resp td.cell-act a { padding: 9px 16px !important; font-size: 0.92rem !important; }
    table.data-table.tbl-resp td.cell-chk { justify-content: flex-start; }
    table.data-table.tbl-resp td .empty-state { text-align: center; }
    table.data-table.tbl-resp td.cell-empty::before { content: none; }
    table.data-table.tbl-resp td.cell-empty { text-align: center; }
}
</style>
<?php }

function sidebarHTML($username, $roleLabel, $navItems) { ?>
<div class="mobile-topbar">
    <button class="mt-burger" onclick="toggleSidebar()" aria-label="Buka menu"><i class="bi bi-list"></i></button>
    <div class="mt-title">Borang Capaian Sistem</div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<div class="sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-shield-lock"></i></div>
        <div class="brand-title">BORANG CAPAIAN<br>SISTEM</div>
        <div class="brand-sub"><?= htmlspecialchars($roleLabel) ?></div>
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
            <div class="user-avatar"><i class="bi bi-person"></i></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($username) ?></div>
                <div class="user-role"><?= htmlspecialchars($roleLabel) ?></div>
            </div>
        </div>
        <a href="logout.php" class="btn-logout"><i class="bi bi-box-arrow-left"></i> Log Keluar</a>
    </div>
</div>
<script>
function toggleSidebar(){
    var s = document.getElementById('appSidebar');
    var o = document.getElementById('sidebarOverlay');
    if(!s) return;
    s.classList.toggle('open');
    if(o) o.classList.toggle('show');
}
</script>
<?php }

function toastHTML($msg, $type = 'success') {
    $icon = $type === 'success' ? 'bi-check-lg' : 'bi-exclamation-triangle';
    echo "<div class='toast-box toast-{$type}' id='toast'>
        <div class='t-icon'><i class='bi {$icon}'></i></div>
        <div><div style='font-weight:600'>".($type==='success'?'Berjaya!':'Ralat')."</div>
        <div style='font-size:0.85rem;color:#5C5560'>".htmlspecialchars($msg)."</div></div></div>
        <script>setTimeout(()=>{const t=document.getElementById('toast');if(t)t.remove();},3500);</script>";
}
