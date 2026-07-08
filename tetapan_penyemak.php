<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('admin_it');

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    if ($act === 'add' || $act === 'edit') {
        $nama    = trim($_POST['nama']       ?? '');
        $jawatan = trim($_POST['jawatan']    ?? '');
        $noPek   = trim($_POST['no_pekerja'] ?? '');
        if ($act === 'add' && $nama !== '') {
            // Had: sistem hanya membenarkan SEORANG penyemak sahaja
            $existing = (int)$db->query("SELECT COUNT(*) c FROM penyemak")->fetch()['c'];
            if ($existing > 0) { header('Location: tetapan_penyemak.php?msg=limit'); exit; }
            $st = $db->prepare("INSERT INTO penyemak (nama,jawatan,no_pekerja,status) VALUES (?,?,?,1)");
            $st->execute([$nama, $jawatan, $noPek]);
        }
        if ($act === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id && $nama !== '') {
                $st = $db->prepare("UPDATE penyemak SET nama=?, jawatan=?, no_pekerja=?, tkh_kemaskini=datetime('now','+8 hours') WHERE id=?");
                $st->execute([$nama, $jawatan, $noPek, $id]);
            }
        }
        header('Location: tetapan_penyemak.php?msg=' . $act); exit;
    }
    if ($act === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $db->prepare("UPDATE penyemak SET status = 1 - status, tkh_kemaskini=datetime('now','+8 hours') WHERE id=?")->execute([$id]);
        header('Location: tetapan_penyemak.php?msg=toggle'); exit;
    }
}

$list  = $db->query("SELECT * FROM penyemak ORDER BY nama")->fetchAll();
$aktif = count(array_filter($list, fn($r) => $r['status'] == 1));
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tetapan Penyemak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .modal-overlay{position:fixed;inset:0;background:rgba(20,35,60,0.55);-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:9999;padding:20px;}
        .modal-overlay.show{display:flex;}
        .modal-box{background:#fff;border-radius:18px;width:100%;max-width:480px;padding:28px;box-shadow:0 24px 60px rgba(20,35,60,0.4);animation:pop 0.22s ease;}
        @keyframes pop{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
        .modal-box h3{font-size:1.2rem;font-weight:800;color:#1E3A5F;margin-bottom:16px;display:flex;align-items:center;gap:9px;}
        .m-actions{display:flex;gap:10px;margin-top:20px;}
        .m-actions .btn-primary-dark,.m-actions .btn-secondary-soft{flex:1;}
    </style>
</head>
<body>
<?php
$msg = $_GET['msg'] ?? '';
if ($msg === 'add')    toastHTML('Penyemak berjaya ditambah.');
if ($msg === 'edit')   toastHTML('Maklumat penyemak berjaya dikemas kini.');
if ($msg === 'toggle') toastHTML('Status penyemak berjaya dikemas kini.');
if ($msg === 'limit')  toastHTML('Sistem hanya membenarkan seorang penyemak sahaja.', 'error');
?>
<?php sidebarHTML($_SESSION['nama'] ?? $_SESSION['username'], 'Admin IT', [
    ['href'=>'dashboard_admin_it.php',  'icon'=>'bi-grid-1x2',     'label'=>'Dashboard',            'active'=>false],
    ['href'=>'laporan.php',             'icon'=>'bi-bar-chart-line','label'=>'Laporan & Statistik',  'active'=>false],
    ['href'=>'tetapan_sistem.php',      'icon'=>'bi-hdd-stack',    'label'=>'Tetapan Sistem',       'active'=>false],
    ['href'=>'tetapan_peranan.php',     'icon'=>'bi-diagram-3',    'label'=>'Tetapan Peranan',      'active'=>false],
    ['href'=>'tetapan_pengarah.php',    'icon'=>'bi-person-badge', 'label'=>'Tetapan Pengarah',     'active'=>false],
    ['href'=>'tetapan_admin_sistem.php','icon'=>'bi-person-gear',  'label'=>'Tetapan Admin Sistem', 'active'=>false],
    ['href'=>'tetapan_penyemak.php',    'icon'=>'bi-person-check', 'label'=>'Tetapan Penyemak',     'active'=>true],
]); ?>
<div class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <h4>Tetapan Penyemak</h4>
            <p>Sistem membenarkan <b>seorang penyemak sahaja</b>. Penyemak ini menyemak kerja Admin IT melalui akaun log masuk sendiri.</p>
        </div>
        <?php if (count($list) === 0): ?>
        <button class="btn-primary-dark" onclick="openAdd()"><i class="bi bi-plus-lg"></i> Tambah Penyemak</button>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-total"><i class="bi bi-person-check"></i></div><div><div class="stat-num num-total"><?=count($list)?></div><div class="stat-lbl lbl-total">Jumlah</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-success"><i class="bi bi-check-circle"></i></div><div><div class="stat-num num-success"><?=$aktif?></div><div class="stat-lbl lbl-success">Aktif</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-danger"><i class="bi bi-slash-circle"></i></div><div><div class="stat-num num-danger"><?=count($list)-$aktif?></div><div class="stat-lbl lbl-danger">Tidak Aktif</div></div></div></div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <h6>Senarai Penyemak</h6>
            <span class="badge-count"><?=count($list)?> rekod</span>
        </div>
        <table class="data-table tbl-resp">
            <thead><tr>
                <th style="padding-left:24px">Nama</th>
                <th>Cop Jawatan</th>
                <th style="width:140px">No. Pekerja</th>
                <th style="width:120px">Status</th>
                <th style="width:220px">Tindakan</th>
            </tr></thead>
            <tbody>
            <?php if (!$list): ?>
                <tr><td colspan="5" class="cell-empty"><div class="empty-state"><i class="bi bi-person-x"></i>Tiada penyemak ditetapkan lagi.</div></td></tr>
            <?php else: foreach ($list as $r): ?>
                <tr>
                    <td data-label="Nama" style="padding-left:24px;font-weight:600;color:#234B7A"><?= htmlspecialchars($r['nama']) ?></td>
                    <td data-label="Cop Jawatan"><?= htmlspecialchars($r['jawatan'] ?? '-') ?></td>
                    <td data-label="No. Pekerja" style="color:#2862C0;font-weight:600"><?= htmlspecialchars($r['no_pekerja'] ?? '-') ?></td>
                    <td data-label="Status">
                        <?php if ($r['status'] == 1): ?><span class="badge-status badge-success">Aktif</span>
                        <?php else: ?><span class="badge-status badge-danger">Tidak Aktif</span><?php endif; ?>
                    </td>
                    <td class="cell-act">
                        <div style="display:flex;gap:8px;align-items:center">
                            <button class="btn-secondary-soft" style="padding:8px 14px;font-size:0.85rem"
                                onclick='openEdit(<?= (int)$r["id"] ?>, <?= htmlspecialchars(json_encode($r["nama"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["jawatan"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["no_pekerja"]), ENT_QUOTES) ?>)'>
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form method="POST" style="margin:0" data-confirm="Tukar status penyemak ini?">
                                <input type="hidden" name="act" value="toggle">
                                <input type="hidden" name="id" value="<?=$r['id']?>">
                                <?php if ($r['status'] == 1): ?>
                                    <button type="submit" class="btn-danger-soft" style="padding:8px 14px;font-size:0.85rem"><i class="bi bi-slash-circle"></i> Nyahaktif</button>
                                <?php else: ?>
                                    <button type="submit" class="btn-success-soft" style="padding:8px 14px;font-size:0.85rem"><i class="bi bi-check-circle"></i> Aktifkan</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="pModal">
    <div class="modal-box">
        <h3 id="modalTitle"><i class="bi bi-person-check"></i> Tambah Penyemak</h3>
        <form method="POST" id="pForm">
            <input type="hidden" name="act" id="f_act" value="add">
            <input type="hidden" name="id" id="f_id" value="">
            <div class="mb-3">
                <label class="field-label">Nama Penyemak <span class="req">*</span></label>
                <input type="text" name="nama" id="f_nama" class="form-control-custom" required placeholder="Nama penuh penyemak">
            </div>
            <div class="mb-3">
                <label class="field-label">Cop Jawatan</label>
                <input type="text" name="jawatan" id="f_jawatan" class="form-control-custom" placeholder="Cth: Ketua Unit Teknologi Maklumat">
            </div>
            <div class="mb-1">
                <label class="field-label">No. Pekerja</label>
                <input type="text" name="no_pekerja" id="f_nopek" class="form-control-custom" placeholder="Cth: MB000201">
            </div>
            <div class="m-actions">
                <button type="button" class="btn-secondary-soft" onclick="closeModal()"><i class="bi bi-x-lg"></i> Batal</button>
                <button type="submit" class="btn-primary-dark"><i class="bi bi-check-lg"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdd(){
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Tambah Penyemak';
    document.getElementById('f_act').value='add'; document.getElementById('f_id').value='';
    document.getElementById('f_nama').value=''; document.getElementById('f_jawatan').value=''; document.getElementById('f_nopek').value='';
    document.getElementById('pModal').classList.add('show');
    setTimeout(()=>document.getElementById('f_nama').focus(),50);
}
function openEdit(id,nama,jawatan,nopek){
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square"></i> Kemas Kini Penyemak';
    document.getElementById('f_act').value='edit'; document.getElementById('f_id').value=id;
    document.getElementById('f_nama').value=nama||''; document.getElementById('f_jawatan').value=jawatan||''; document.getElementById('f_nopek').value=nopek||'';
    document.getElementById('pModal').classList.add('show');
    setTimeout(()=>document.getElementById('f_nama').focus(),50);
}
function closeModal(){ document.getElementById('pModal').classList.remove('show'); }
document.getElementById('pModal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
</script>
</body>
</html>
