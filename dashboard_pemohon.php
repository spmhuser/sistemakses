<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('pemohon');

$db   = getDB();
$stmt = $db->prepare("SELECT * FROM permohonan WHERE user_id = ? ORDER BY tkh_keyin DESC");
$stmt->execute([$_SESSION['user_id']]);
$list = $stmt->fetchAll();

// Senarai nama sistem bagi setiap permohonan (dikumpul ikut permohonan_id)
$sysByPerm = getSistemNamaByPermohonan(array_column($list, 'id'));

$total    = count($list);
// Proses dianggap SELESAI hanya selepas Admin IT beri akses (AKSES_DIBERIKAN) atau ditolak (TIDAK_DILULUSKAN).
// DILULUSKAN (lulus JTIK tetapi belum diberi akses oleh IT) masih dikira DALAM PROSES.
$proses   = array_filter($list, fn($r)=>in_array($r['status'],['MENUNGGU_PENGARAH_JAB','MENUNGGU_JTIK','DILULUSKAN']));
$selesai  = array_filter($list, fn($r)=>in_array($r['status'],['AKSES_DIBERIKAN','TIDAK_DILULUSKAN']));
$defaultTab = count($proses) > 0 ? 'tab-proses' : 'tab-selesai';
$lulus    = count(array_filter($list, fn($r)=>in_array($r['status'],['DILULUSKAN','AKSES_DIBERIKAN'])));
$tolak    = count(array_filter($list, fn($r)=>$r['status']==='TIDAK_DILULUSKAN'));
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Pemohon</title>
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
        @media (max-width:768px){
            .dash-tabs{display:flex;width:100%;gap:4px}
            .dash-tab{flex:1;justify-content:center;padding:10px 8px}
            .dash-tab .tab-txt{font-size:0.82rem}
            .dash-tab .tab-ic{width:24px;height:24px;font-size:14px}
        }
    </style>
</head>
<body>
<?php if (isset($_GET['success'])) toastHTML('Permohonan berjaya dihantar.'); ?>
<?php sidebarHTML($_SESSION['nama'] ?? $_SESSION['username'], 'Pemohon', [
    ['href'=>'dashboard_pemohon.php',  'icon'=>'bi-grid-1x2',    'label'=>'Dashboard',         'active'=>true],
    ['href'=>'borang_permohonan.php',  'icon'=>'bi-plus-circle',  'label'=>'Buat Permohonan',   'active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <h4>Dashboard Pemohon</h4>
            <p>Semak status permohonan capaian sistem anda</p>
        </div>
        <a href="borang_permohonan.php" class="btn-primary-dark"><i class="bi bi-plus-lg"></i> Buat Permohonan</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl"><div class="stat-card"><div class="stat-icon icon-total"><i class="bi bi-collection"></i></div><div><div class="stat-num num-total"><?=$total?></div><div class="stat-lbl lbl-total">Jumlah</div></div></div></div>
        <div class="col-6 col-xl"><div class="stat-card"><div class="stat-icon icon-warning"><i class="bi bi-hourglass-split"></i></div><div><div class="stat-num num-warning"><?=count($proses)?></div><div class="stat-lbl lbl-warning">Dalam Proses</div></div></div></div>
        <div class="col-6 col-xl"><div class="stat-card"><div class="stat-icon icon-success"><i class="bi bi-check-circle"></i></div><div><div class="stat-num num-success"><?=$lulus?></div><div class="stat-lbl lbl-success">Diluluskan</div></div></div></div>
        <div class="col-6 col-xl"><div class="stat-card"><div class="stat-icon icon-primary"><i class="bi bi-key"></i></div><div><div class="stat-num num-primary"><?=count(array_filter($list,fn($r)=>$r['status']==='AKSES_DIBERIKAN'))?></div><div class="stat-lbl lbl-primary">Akses Diberikan</div></div></div></div>
        <div class="col-6 col-xl"><div class="stat-card"><div class="stat-icon icon-danger"><i class="bi bi-x-circle"></i></div><div><div class="stat-num num-danger"><?=$tolak?></div><div class="stat-lbl lbl-danger">Tidak Lulus</div></div></div></div>
    </div>

    <div class="filter-bar" data-target=".data-table">
        <div class="flt-search-wrap"><i class="bi bi-search"></i>
            <input type="text" class="flt-q" placeholder="Cari No. Rujukan, sistem, jabatan, tujuan...">
        </div>
        <select class="flt-sel" title="Tapis ikut status">
            <option value="">Semua Status</option>
            <option value="Menunggu Pengarah Jabatan">Menunggu Pengarah Jabatan</option>
            <option value="Menunggu Kelulusan Pengarah JTIK">Menunggu Kelulusan JTIK</option>
            <option value="Menunggu Akses JTIK">Menunggu Akses JTIK</option>
            <option value="Akses Diberikan">Akses Diberikan</option>
            <option value="Tidak Diluluskan">Tidak Diluluskan</option>
        </select>
    </div>

    <div class="dash-tabs">
        <button class="dash-tab <?= $defaultTab==='tab-proses'?'active':'' ?>" onclick="switchTab(this,'tab-proses')">
            <span class="tab-ic"><i class="bi bi-hourglass-split"></i></span><span class="tab-txt">Dalam Proses</span>
            <?php if(count($proses)>0): ?><span class="tab-badge"><?=count($proses)?></span><?php endif; ?>
        </button>
        <button class="dash-tab <?= $defaultTab==='tab-selesai'?'active':'' ?>" onclick="switchTab(this,'tab-selesai')">
            <span class="tab-ic"><i class="bi bi-check2-all"></i></span><span class="tab-txt">Selesai</span>
            <span class="tab-badge"><?=count($selesai)?></span>
        </button>
    </div>

    <div id="tab-proses" class="dash-tab-pane <?= $defaultTab==='tab-proses'?'active':'' ?>">
        <div class="table-card">
            <table class="data-table tbl-resp">
                <thead><tr>
                    <th style="padding-left:24px">#</th>
                    <th>No. Rujukan</th><th>Tujuan</th><th>Sistem</th><th>Jabatan</th><th>Status</th><th>Tarikh Hantar</th><th>Tindakan</th>
                </tr></thead>
                <tbody>
                <?php if(empty($proses)): ?>
                <tr><td colspan="8" class="cell-empty"><div class="empty-state"><i class="bi bi-check2-circle"></i>Tiada permohonan dalam proses. <a href="borang_permohonan.php" style="color:#2C5488;font-weight:600">Buat sekarang</a>.</div></td></tr>
                <?php else: foreach(array_values($proses) as $i=>$r): ?>
                <tr>
                    <td data-label="#" style="padding-left:24px;color:#6E6470;font-size:0.9rem"><?=$i+1?></td>
                    <td data-label="No. Rujukan" style="font-weight:600;color:#2C5488;font-size:0.92rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td data-label="Tujuan" style="font-size:0.92rem"><?= tujuanLabel($r['tujuan']) ?></td>
                    <td class="cell-stack" data-label="Sistem" style="max-width:300px"><?= renderSistemBadges($sysByPerm[$r['id']] ?? []) ?></td>
                    <td data-label="Jabatan" style="font-size:0.92rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td data-label="Status"><span class="badge-status <?= statusClass($r['status']) ?>"><?= statusLabel($r['status']) ?></span></td>
                    <td data-label="Tarikh Hantar" style="color:#6E6470;font-size:0.9rem"><?= $r['tkh_keyin'] ?></td>
                    <td class="cell-act"><a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 12px;font-size:0.88rem"><i class="bi bi-eye"></i> Lihat</a></td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="tab-selesai" class="dash-tab-pane <?= $defaultTab==='tab-selesai'?'active':'' ?>">
        <div class="table-card">
            <table class="data-table tbl-resp">
                <thead><tr>
                    <th style="padding-left:24px">#</th>
                    <th>No. Rujukan</th><th>Tujuan</th><th>Sistem</th><th>Jabatan</th><th>Status</th><th>Tarikh Hantar</th><th>Tindakan</th>
                </tr></thead>
                <tbody>
                <?php if(empty($selesai)): ?>
                <tr><td colspan="8" class="cell-empty"><div class="empty-state"><i class="bi bi-inbox"></i>Tiada permohonan selesai lagi.</div></td></tr>
                <?php else: foreach(array_values($selesai) as $i=>$r): ?>
                <tr>
                    <td data-label="#" style="padding-left:24px;color:#6E6470;font-size:0.9rem"><?=$i+1?></td>
                    <td data-label="No. Rujukan" style="font-weight:600;color:#2C5488;font-size:0.92rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td data-label="Tujuan" style="font-size:0.92rem"><?= tujuanLabel($r['tujuan']) ?></td>
                    <td class="cell-stack" data-label="Sistem" style="max-width:300px"><?= renderSistemBadges($sysByPerm[$r['id']] ?? []) ?></td>
                    <td data-label="Jabatan" style="font-size:0.92rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td data-label="Status"><span class="badge-status <?= statusClass($r['status']) ?>"><?= statusLabel($r['status']) ?></span></td>
                    <td data-label="Tarikh Hantar" style="color:#6E6470;font-size:0.9rem"><?= $r['tkh_keyin'] ?></td>
                    <td class="cell-act"><a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 12px;font-size:0.88rem"><i class="bi bi-eye"></i> Lihat</a></td>
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
</script>
</body></html>
