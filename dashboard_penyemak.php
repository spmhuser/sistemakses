<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('penyemak');

$db      = getDB();
$all     = $db->query("SELECT p.*,u.username FROM permohonan p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC")->fetchAll();
$perlu   = array_filter($all, fn($r)=>$r['status']==='MENUNGGU_PENYEMAK');
$selesai = array_filter($all, fn($r)=>$r['status']!=='MENUNGGU_PENYEMAK');
$lulus   = array_filter($selesai, fn($r)=>in_array($r['status'],['MENUNGGU_PENGARAH_JAB','MENUNGGU_JTIK','DILULUSKAN','AKSES_DIBERIKAN']));
$tolak   = array_filter($selesai, fn($r)=>$r['status']==='TIDAK_DILULUSKAN');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Penyemak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .dash-tabs{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
        .dash-tab{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;font-size:0.9rem;font-weight:700;border-radius:50px;border:2px solid #ddd6fe;background:#fff;color:#9ca3af;cursor:pointer;transition:all 0.2s;letter-spacing:0.01em;box-shadow:0 1px 4px rgba(0,0,0,0.06)}
        .dash-tab:hover{border-color:#c4b5fd;color:#6d28d9;background:#f5f3ff;transform:translateY(-2px);box-shadow:0 4px 12px rgba(109,40,217,0.12)}
        .dash-tab.active{background:#6d28d9;color:#fff;border-color:#6d28d9;box-shadow:0 4px 16px rgba(109,40,217,0.4);transform:translateY(-2px)}
        .dash-tab.active .tab-badge{background:rgba(255,255,255,0.25);color:#fff}
        .dash-tab-pane{display:none}
        .dash-tab-pane.active{display:block}
        .tab-badge{font-size:0.72rem;font-weight:700;padding:2px 9px;border-radius:20px;margin-left:2px;background:#f3f4f6;color:#374151}
        .chk-col{width:42px;text-align:center}
        .row-chk{width:16px;height:16px;cursor:pointer;accent-color:#6d28d9}
        .bulk-bar{display:none;position:fixed;bottom:28px;left:50%;transform:translateX(-50%);
            background:#6d28d9;color:#fff;border-radius:50px;padding:14px 28px;
            align-items:center;gap:16px;box-shadow:0 8px 30px rgba(109,40,217,0.5);z-index:999;white-space:nowrap}
        .bulk-bar.show{display:flex}
        .bulk-bar-btn{background:#fff;color:#6d28d9;border:none;border-radius:50px;
            padding:8px 22px;font-weight:700;font-size:0.88rem;cursor:pointer;
            display:inline-flex;align-items:center;gap:6px;transition:opacity 0.15s}
        .bulk-bar-btn:hover{opacity:0.85}
    </style>
</head>
<body>
<?php if(isset($_GET['success'])) toastHTML('Tindakan berjaya direkodkan.'); ?>
<?php sidebarHTML($_SESSION['nama']??$_SESSION['username'],'Penyemak',[
    ['href'=>'dashboard_penyemak.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>true],
]); ?>
<div class="main-content">
    <div class="page-header"><h4>Dashboard Penyemak</h4><p>Semak dan hantar permohonan capaian sistem kepada Pengarah Jabatan</p></div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-total"><i class="bi bi-collection"></i></div><div><div class="stat-num num-total"><?=count($all)?></div><div class="stat-lbl lbl-total">Jumlah</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-warning"><i class="bi bi-hourglass-split"></i></div><div><div class="stat-num num-warning"><?=count($perlu)?></div><div class="stat-lbl lbl-warning">Perlu Semak</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-success"><i class="bi bi-check-circle"></i></div><div><div class="stat-num num-success"><?=count($lulus)?></div><div class="stat-lbl lbl-success">Telah Disemak</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-danger"><i class="bi bi-x-circle"></i></div><div><div class="stat-num num-danger"><?=count($tolak)?></div><div class="stat-lbl lbl-danger">Tidak Lulus</div></div></div></div>
    </div>

    <div class="dash-tabs">
        <button class="dash-tab active" onclick="switchTab(this,'tab-proses')">
            <i class="bi bi-hourglass-split me-1"></i>Masih Dalam Proses
            <?php if(count($perlu)>0): ?><span class="tab-badge" style="background:#fbbf24;color:#78350f"><?=count($perlu)?></span><?php endif; ?>
        </button>
        <button class="dash-tab" onclick="switchTab(this,'tab-selesai')">
            <i class="bi bi-check2-all me-1"></i>Dah Selesai
            <span class="tab-badge" style="background:#e5e7eb;color:#374151"><?=count($selesai)?></span>
        </button>
    </div>

    <div id="tab-proses" class="dash-tab-pane active">
        <form id="bulk-form" method="POST" action="bulk_approve_penyemak.php">
        <div class="table-card">
            <table class="data-table">
                <thead><tr>
                    <th class="chk-col"><input type="checkbox" id="chk-all" class="row-chk" title="Pilih Semua"></th>
                    <th style="padding-left:8px">#</th>
                    <th>No. Rujukan</th><th>Pemohon</th><th>Jabatan</th><th>Tujuan</th><th>Tarikh Mohon</th><th>Tindakan</th>
                </tr></thead>
                <tbody>
                <?php if(empty($perlu)): ?>
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-check2-circle"></i>Tiada permohonan menunggu semakan.</div></td></tr>
                <?php else: foreach(array_values($perlu) as $i=>$r): ?>
                <tr>
                    <td class="chk-col"><input type="checkbox" name="ids[]" value="<?=$r['id']?>" class="row-chk item-chk"></td>
                    <td style="padding-left:8px;color:#9ca3af;font-size:0.8rem"><?=$i+1?></td>
                    <td style="font-weight:600;color:#6d28d9;font-size:0.82rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                    <td style="font-size:0.82rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td><span class="badge-status badge-info" style="font-size:0.72rem"><?= tujuanLabel($r['tujuan']) ?></span></td>
                    <td style="color:#9ca3af;font-size:0.8rem"><?=$r['created_at']?></td>
                    <td style="display:flex;gap:6px">
                        <a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 10px;font-size:0.78rem"><i class="bi bi-eye"></i></a>
                        <a href="tindakan_penyemak.php?id=<?=$r['id']?>" class="btn-primary-dark" style="padding:5px 12px;font-size:0.78rem"><i class="bi bi-pen"></i> Semak</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        </form>
    </div>

    <div id="tab-selesai" class="dash-tab-pane">
        <div class="table-card">
            <table class="data-table">
                <thead><tr><th style="padding-left:24px">#</th><th>No. Rujukan</th><th>Pemohon</th><th>Jabatan</th><th>Tujuan</th><th>Status</th><th>Tarikh Semak</th><th>Lihat</th></tr></thead>
                <tbody>
                <?php if(empty($selesai)): ?>
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-inbox"></i>Tiada rekod lagi.</div></td></tr>
                <?php else: foreach(array_values($selesai) as $i=>$r): ?>
                <tr>
                    <td style="padding-left:24px;color:#9ca3af;font-size:0.8rem"><?=$i+1?></td>
                    <td style="font-weight:600;color:#6d28d9;font-size:0.82rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                    <td style="font-size:0.82rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td style="font-size:0.82rem"><?= tujuanLabel($r['tujuan']) ?></td>
                    <td><span class="badge-status <?= statusClass($r['status']) ?>"><?= statusLabel($r['status']) ?></span></td>
                    <td style="color:#9ca3af;font-size:0.8rem"><?= $r['tarikh_penyemak'] ?? '-' ?></td>
                    <td><a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 12px;font-size:0.78rem"><i class="bi bi-eye"></i> Lihat</a></td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Floating bulk action bar -->
<div class="bulk-bar" id="bulk-bar">
    <i class="bi bi-check2-square" style="font-size:1.1rem"></i>
    <span id="bulk-count" style="font-weight:700">0 dipilih</span>
    <button type="button" class="bulk-bar-btn" onclick="submitBulk()">
        <i class="bi bi-check2-all"></i> Lulus Semua
    </button>
    <button type="button" onclick="clearAll()" style="background:rgba(255,255,255,0.15);color:#fff;border:none;border-radius:50px;padding:8px 16px;cursor:pointer;font-size:0.82rem">Batal</button>
</div>

<script>
function switchTab(btn, id) {
    document.querySelectorAll('.dash-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.dash-tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(id).classList.add('active');
}
function updateBulkBar() {
    const checked = document.querySelectorAll('.item-chk:checked').length;
    const bar = document.getElementById('bulk-bar');
    document.getElementById('bulk-count').textContent = checked + ' dipilih';
    bar.classList.toggle('show', checked > 0);
}
function submitBulk() {
    if (document.querySelectorAll('.item-chk:checked').length === 0) return;
    if (confirm('Lulus semua permohonan yang dipilih?')) {
        document.getElementById('bulk-form').submit();
    }
}
function clearAll() {
    document.querySelectorAll('.item-chk').forEach(c => c.checked = false);
    document.getElementById('chk-all').checked = false;
    updateBulkBar();
}
document.getElementById('chk-all').addEventListener('change', function() {
    document.querySelectorAll('.item-chk').forEach(c => c.checked = this.checked);
    updateBulkBar();
});
document.querySelectorAll('.item-chk').forEach(c => c.addEventListener('change', updateBulkBar));
</script>
</body></html>
