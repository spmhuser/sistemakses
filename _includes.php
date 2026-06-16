<?php
function sharedCSS() { ?>
<style>
* { box-sizing: border-box; }
body { font-family: 'Segoe UI', sans-serif; background: #f0f4ff; min-height: 100vh; margin: 0; }

/* SIDEBAR */
.sidebar {
    width: 240px; min-height: 100vh;
    background: linear-gradient(180deg, #0a1628 0%, #003087 60%, #1565c0 100%);
    position: fixed; top: 0; left: 0;
    display: flex; flex-direction: column; z-index: 100;
}
.sidebar-brand { padding: 28px 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.12); }
.sidebar-brand .brand-icon {
    width: 44px; height: 44px; background: rgba(255,255,255,0.15); border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 10px;
}
.sidebar-brand .brand-title { color: #fff; font-size: 0.82rem; font-weight: 700; line-height: 1.3; }
.sidebar-brand .brand-sub { color: rgba(255,255,255,0.55); font-size: 0.72rem; margin-top: 2px; }
.sidebar-nav { padding: 20px 14px; flex: 1; }
.sidebar-nav .nav-label {
    color: rgba(255,255,255,0.4); font-size: 0.68rem; text-transform: uppercase;
    letter-spacing: 1px; padding: 0 10px; margin-bottom: 8px; margin-top: 16px;
}
.sidebar-nav .nav-item {
    display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px;
    color: rgba(255,255,255,0.8); font-size: 0.875rem; font-weight: 500;
    text-decoration: none; transition: all 0.15s; margin-bottom: 2px;
}
.sidebar-nav .nav-item:hover, .sidebar-nav .nav-item.active {
    background: rgba(255,255,255,0.18); color: #fff;
}
.sidebar-nav .nav-item i { font-size: 1rem; width: 18px; }
.sidebar-footer { padding: 16px 14px; border-top: 1px solid rgba(255,255,255,0.12); }
.user-info {
    display: flex; align-items: center; gap: 10px; padding: 10px 12px;
    background: rgba(255,255,255,0.12); border-radius: 10px; margin-bottom: 10px;
}
.user-avatar {
    width: 34px; height: 34px; background: rgba(255,255,255,0.2); border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 14px; color: #fff; flex-shrink: 0;
}
.user-name { color: #fff; font-size: 0.82rem; font-weight: 600; }
.user-role { color: rgba(255,255,255,0.55); font-size: 0.7rem; }
.btn-logout {
    display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
    background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
    color: rgba(255,255,255,0.85); border-radius: 8px; padding: 8px;
    font-size: 0.82rem; font-weight: 500; text-decoration: none; transition: all 0.15s;
}
.btn-logout:hover { background: rgba(220,53,69,0.55); border-color: transparent; color: #fff; }

/* MAIN */
.main-content { margin-left: 240px; padding: 32px; min-height: 100vh; }
.page-header { margin-bottom: 28px; }
.page-header h4 { font-size: 1.4rem; font-weight: 700; color: #003087; margin-bottom: 4px; }
.page-header p { color: #6c757d; font-size: 0.85rem; margin: 0; }
.page-header .breadcrumb { font-size: 0.8rem; margin-bottom: 6px; }
.page-header .breadcrumb-item a { color: #1976d2; text-decoration: none; font-weight: 500; }
.page-header .breadcrumb-item a:hover { text-decoration: underline; }
.page-header .breadcrumb-item.active { color: #9ca3af; }

/* STAT CARDS */
.stat-card {
    border: none; border-radius: 16px; padding: 22px 24px;
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
    display: inline-flex; align-items: center; gap: 8px; background: #f3f4f6;
    color: #6b7280; border: none; border-radius: 10px; padding: 10px 18px;
    font-size: 0.875rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: background 0.15s;
}
.btn-secondary-soft:hover { background: #e5e7eb; color: #374151; }

.btn-success-soft {
    display: inline-flex; align-items: center; gap: 8px; background: #e6f9f0;
    color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; padding: 8px 16px;
    font-size: 0.82rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.15s;
}
.btn-success-soft:hover { background: #dcfce7; color: #15803d; }

.btn-danger-soft {
    display: inline-flex; align-items: center; gap: 8px; background: #fef0f0;
    color: #991b1b; border: 1px solid #fecaca; border-radius: 10px; padding: 8px 16px;
    font-size: 0.82rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.15s;
}
.btn-danger-soft:hover { background: #fee2e2; color: #b91c1c; }

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

function sidebarHTML($username, $roleLabel, $navItems) { ?>
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-rocket-takeoff-fill"></i></div>
        <div class="brand-title">SISTEM CAPAIAN SISTEM</div>
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
<?php }

function toastHTML($msg, $type = 'success') {
    $icon = $type === 'success' ? 'bi-check-lg' : 'bi-exclamation-triangle';
    echo "<div class='toast-box toast-{$type}' id='toast'>
        <div class='t-icon'><i class='bi {$icon}'></i></div>
        <div><div style='font-weight:600'>".($type==='success'?'Berjaya!':'Ralat')."</div>
        <div style='font-size:0.8rem;color:#6b7280'>".htmlspecialchars($msg)."</div></div></div>
        <script>setTimeout(()=>{const t=document.getElementById('toast');if(t)t.remove();},3500);</script>";
}
