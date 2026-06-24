<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('pengarah_jtik');

$db  = getDB();
$all     = $db->query("SELECT p.*,u.username FROM permohonan p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC")->fetchAll();
$perlu   = array_filter($all, fn($r)=>$r['status']==='MENUNGGU_JTIK');
$selesai = array_filter($all, fn($r)=>in_array($r['status'],['DILULUSKAN','TIDAK_DILULUSKAN','AKSES_DIBERIKAN']));
$lulus   = array_filter($selesai, fn($r)=>in_array($r['status'],['DILULUSKAN','AKSES_DIBERIKAN']));
$tolak   = array_filter($selesai, fn($r)=>$r['status']==='TIDAK_DILULUSKAN');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Pengarah JTIK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .dash-tabs{display:inline-flex;gap:4px;margin-bottom:22px;background:#EAF1FB;padding:6px;border-radius:50px;box-shadow:inset 0 1px 4px rgba(40,70,120,0.16)}
        .dash-tab{position:relative;display:inline-flex;align-items:center;gap:9px;padding:11px 26px;border:none;background:transparent;cursor:pointer;border-radius:50px;transition:all 0.25s cubic-bezier(.2,.8,.2,1)}
        .dash-tab .tab-ic{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;background:transparent;color:#2E73D8;transition:all 0.25s}
        .dash-tab .tab-txt{font-size:0.95rem;font-weight:700;color:#6E7787;transition:color 0.2s;letter-spacing:0.2px}
        .dash-tab:hover .tab-ic{color:#1E3A5F}
        .dash-tab:hover .tab-txt{color:#1E3A5F}
        .dash-tab.active{background:linear-gradient(135deg,#2E73D8,#1FBCD4);box-shadow:0 6px 16px rgba(46,115,216,0.42)}
        .dash-tab.active .tab-ic{background:rgba(255,255,255,0.22);color:#fff}
        .dash-tab.active .tab-txt{color:#fff;font-weight:800}
        .dash-tab .tab-badge{min-width:22px;height:22px;padding:0 7px;display:inline-flex;align-items:center;justify-content:center;font-size:0.74rem;font-weight:800;border-radius:20px;background:#1FBCD4;color:#fff}
        .dash-tab.active .tab-badge{background:#fff;color:#234B7A}
        .dash-tab-pane{display:none}
        .dash-tab-pane.active{display:block}
        .bulk-bar{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #DCE6F2;border-radius:14px;padding:12px 18px;margin-bottom:14px;box-shadow:0 2px 10px rgba(40,70,120,0.06);flex-wrap:wrap}
        .bulk-chk{display:flex;align-items:center;gap:8px;font-weight:700;color:#234B7A;font-size:0.92rem;cursor:pointer}
        .bulk-chk input,.rowchk,.row-allhead{width:17px;height:17px;cursor:pointer;accent-color:#2E73D8}
        .bulk-count{font-size:0.86rem;color:#6E7787;font-weight:600}
        .bulk-select{border:1.5px solid #DCE6F2;border-radius:10px;padding:9px 12px;font-size:0.9rem;color:#2D2433;background:#fff;font-weight:600}
    </style>
</head>
<body>
<?php if(isset($_GET['success'])) toastHTML('Kelulusan berjaya direkodkan.'); ?>
<?php if(isset($_GET['bulk'])) toastHTML(((int)$_GET['bulk']).' permohonan berjaya diproses secara pukal.'); ?>
<?php sidebarHTML($_SESSION['nama']??$_SESSION['username'],'Pengarah JTIK',[
    ['href'=>'dashboard_pengarah_jtik.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>true],
]); ?>
<div class="main-content">
    <div class="page-header"><h4>Dashboard Pengarah JTIK</h4><p>Semak dan luluskan permohonan capaian sistem</p></div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-total"><i class="bi bi-collection"></i></div><div><div class="stat-num num-total"><?=count($all)?></div><div class="stat-lbl lbl-total">Jumlah</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-warning"><i class="bi bi-hourglass-split"></i></div><div><div class="stat-num num-warning"><?=count($perlu)?></div><div class="stat-lbl lbl-warning">Perlu Kelulusan</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-success"><i class="bi bi-check-circle"></i></div><div><div class="stat-num num-success"><?=count($lulus)?></div><div class="stat-lbl lbl-success">Diluluskan</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-danger"><i class="bi bi-x-circle"></i></div><div><div class="stat-num num-danger"><?=count($tolak)?></div><div class="stat-lbl lbl-danger">Tidak Lulus</div></div></div></div>
    </div>

    <div class="dash-tabs">
        <button class="dash-tab active" onclick="switchTab(this,'tab-proses')">
            <span class="tab-ic"><i class="bi bi-hourglass-split"></i></span><span class="tab-txt">Masih Dalam Proses</span>
            <?php if(count($perlu)>0): ?><span class="tab-badge"><?=count($perlu)?></span><?php endif; ?>
        </button>
        <button class="dash-tab" onclick="switchTab(this,'tab-selesai')">
            <span class="tab-ic"><i class="bi bi-check2-all"></i></span><span class="tab-txt">Dah Selesai</span>
            <span class="tab-badge"><?=count($selesai)?></span>
        </button>
    </div>

    <div id="tab-proses" class="dash-tab-pane active">
        <form method="POST" action="bulk_action.php" id="bulkForm" onsubmit="return confirmBulk()">
        <div class="bulk-bar">
            <label class="bulk-chk"><input type="checkbox" id="selAll" onclick="toggleAll(this)"> Pilih Semua</label>
            <span id="selCount" class="bulk-count">0 dipilih</span>
            <div style="flex:1"></div>
            <select name="bulk_action" id="bulkAct" class="bulk-select">
                <option value="">— Tindakan Pukal —</option>
                <option value="lulus">Luluskan</option>
                <option value="tolak">Tolak</option>
            </select>
            <button type="submit" class="btn-primary-dark" style="padding:9px 18px"><i class="bi bi-lightning-charge"></i> Laksana</button>
        </div>
        <div class="table-card">
            <table class="data-table">
                <thead><tr><th style="padding-left:24px;width:46px"><input type="checkbox" class="row-allhead" onclick="toggleAll(this)"></th><th>No. Rujukan</th><th>Pemohon</th><th>Jabatan</th><th>Tujuan</th><th>Disahkan Pengarah Jab</th><th>Tindakan</th></tr></thead>
                <tbody>
                <?php if(empty($perlu)): ?>
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-check2-circle"></i>Tiada permohonan menunggu kelulusan.</div></td></tr>
                <?php else: foreach(array_values($perlu) as $i=>$r): ?>
                <tr>
                    <td style="padding-left:24px"><input type="checkbox" class="rowchk" name="ids[]" value="<?=$r['id']?>" onclick="updCount()"></td>
                    <td style="font-weight:600;color:#2C5488;font-size:0.92rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                    <td style="font-size:0.92rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td><span class="badge-status badge-info" style="font-size:0.82rem"><?= tujuanLabel($r['tujuan']) ?></span></td>
                    <td style="font-size:0.92rem;color:#6b7280"><?= $r['tarikh_pengarah_jab']??'-' ?></td>
                    <td style="display:flex;gap:6px">
                        <a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 10px;font-size:0.88rem"><i class="bi bi-eye"></i></a>
                        <a href="tindakan_jtik.php?id=<?=$r['id']?>" class="btn-primary-dark" style="padding:5px 12px;font-size:0.88rem"><i class="bi bi-check2-square"></i> Luluskan</a>
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
                <thead><tr><th style="padding-left:24px">#</th><th>No. Rujukan</th><th>Pemohon</th><th>Jabatan</th><th>Tujuan</th><th>Status</th><th>Tarikh Kelulusan</th><th>Lihat</th></tr></thead>
                <tbody>
                <?php if(empty($selesai)): ?>
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-inbox"></i>Tiada rekod lagi.</div></td></tr>
                <?php else: foreach(array_values($selesai) as $i=>$r): ?>
                <tr>
                    <td style="padding-left:24px;color:#6E6470;font-size:0.9rem"><?=$i+1?></td>
                    <td style="font-weight:600;color:#2C5488;font-size:0.92rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                    <td style="font-size:0.92rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td style="font-size:0.92rem"><?= tujuanLabel($r['tujuan']) ?></td>
                    <td><span class="badge-status <?= statusClass($r['status']) ?>"><?= statusLabel($r['status']) ?></span></td>
                    <td style="color:#6E6470;font-size:0.9rem"><?= $r['tarikh_jtik'] ?? '-' ?></td>
                    <td><a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 12px;font-size:0.88rem"><i class="bi bi-eye"></i> Lihat</a></td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
function switchTab(btn, id) {
    document.querySelectorAll('.dash-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.dash-tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(id).classList.add('active');
}
function toggleAll(src){
    const on = src.checked;
    document.querySelectorAll('#bulkForm .rowchk').forEach(c => c.checked = on);
    const a = document.getElementById('selAll'); if(a) a.checked = on;
    document.querySelectorAll('#bulkForm .row-allhead').forEach(c => c.checked = on);
    updCount();
}
function updCount(){
    const n = document.querySelectorAll('#bulkForm .rowchk:checked').length;
    document.getElementById('selCount').textContent = n + ' dipilih';
}
function confirmBulk(){
    const n = document.querySelectorAll('#bulkForm .rowchk:checked').length;
    const act = document.getElementById('bulkAct').value;
    if(n === 0){ alert('Sila pilih sekurang-kurangnya satu permohonan.'); return false; }
    if(!act){ alert('Sila pilih tindakan pukal.'); return false; }
    return confirm('Laksana tindakan "' + act.toUpperCase() + '" untuk ' + n + ' permohonan?');
}
</script>
</body></html>
