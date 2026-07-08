<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('admin_it');

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    if ($act === 'add' || $act === 'edit') {
        $kod   = trim($_POST['kod_jabatan']   ?? '');
        $nama  = trim($_POST['nama_pengarah'] ?? '');
        $noPek = trim($_POST['no_pekerja']    ?? '');
        if ($act === 'add' && $kod !== '' && $nama !== '') {
            $st = $db->prepare("INSERT INTO jabatan_pengarah (kod_jabatan,nama_pengarah,no_pekerja,status) VALUES (?,?,?,1)");
            $st->execute([$kod, $nama, $noPek]);
        }
        if ($act === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id && $kod !== '' && $nama !== '') {
                $st = $db->prepare("UPDATE jabatan_pengarah SET kod_jabatan=?, nama_pengarah=?, no_pekerja=?, tkh_kemaskini=datetime('now','+8 hours') WHERE id=?");
                $st->execute([$kod, $nama, $noPek, $id]);
            }
        }
        header('Location: tetapan_pengarah.php?msg=' . $act); exit;
    }
    if ($act === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $db->prepare("UPDATE jabatan_pengarah SET status = 1 - status, tkh_kemaskini=datetime('now','+8 hours') WHERE id=?")->execute([$id]);
        header('Location: tetapan_pengarah.php?msg=toggle'); exit;
    }
}

$list      = $db->query("SELECT * FROM jabatan_pengarah ORDER BY kod_jabatan")->fetchAll();
$aktif     = count(array_filter($list, fn($r) => $r['status'] == 1));
$jabatanLs = getJabatanList();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tetapan Pengarah Jabatan</title>
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
if ($msg === 'add')    toastHTML('Pengarah jabatan berjaya ditambah.');
if ($msg === 'edit')   toastHTML('Maklumat pengarah berjaya dikemas kini.');
if ($msg === 'toggle') toastHTML('Status pengarah berjaya dikemas kini.');
?>
<?php sidebarHTML($_SESSION['nama'] ?? $_SESSION['username'], 'Admin IT', [
    ['href'=>'dashboard_admin_it.php', 'icon'=>'bi-grid-1x2',  'label'=>'Dashboard',         'active'=>false],
    ['href'=>'laporan.php',            'icon'=>'bi-bar-chart-line','label'=>'Laporan & Statistik','active'=>false],
    ['href'=>'tetapan_sistem.php',     'icon'=>'bi-hdd-stack', 'label'=>'Tetapan Sistem',    'active'=>false],
    ['href'=>'tetapan_peranan.php',    'icon'=>'bi-diagram-3', 'label'=>'Tetapan Peranan',   'active'=>false],
    ['href'=>'tetapan_pengarah.php',   'icon'=>'bi-person-badge','label'=>'Tetapan Pengarah','active'=>true],
    ['href'=>'tetapan_admin_sistem.php','icon'=>'bi-person-gear','label'=>'Tetapan Admin Sistem','active'=>false],
    ['href'=>'tetapan_penyemak.php','icon'=>'bi-person-check','label'=>'Tetapan Penyemak','active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <h4>Tetapan Pengarah Jabatan</h4>
            <p>Tetapkan pengarah yang bertanggungjawab bagi setiap jabatan. Permohonan akan dihalakan secara automatik.</p>
        </div>
        <button class="btn-primary-dark" onclick="openAdd()"><i class="bi bi-plus-lg"></i> Tambah Pengarah</button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-total"><i class="bi bi-people"></i></div><div><div class="stat-num num-total"><?=count($list)?></div><div class="stat-lbl lbl-total">Jumlah</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-success"><i class="bi bi-check-circle"></i></div><div><div class="stat-num num-success"><?=$aktif?></div><div class="stat-lbl lbl-success">Aktif</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-danger"><i class="bi bi-slash-circle"></i></div><div><div class="stat-num num-danger"><?=count($list)-$aktif?></div><div class="stat-lbl lbl-danger">Tidak Aktif</div></div></div></div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <h6>Senarai Pengarah Jabatan</h6>
            <span class="badge-count"><?=count($list)?> rekod</span>
        </div>
        <div style="overflow-x:auto">
        <table class="data-table">
            <thead><tr>
                <th style="padding-left:24px">Jabatan</th>
                <th>Nama Pengarah</th>
                <th style="width:140px">No. Pekerja</th>
                <th style="width:120px">Status</th>
                <th style="width:220px">Tindakan</th>
            </tr></thead>
            <tbody>
            <?php if (!$list): ?>
                <tr><td colspan="5"><div class="empty-state"><i class="bi bi-person-x"></i>Tiada pengarah ditetapkan lagi.</div></td></tr>
            <?php else: foreach ($list as $r): ?>
                <tr>
                    <td style="padding-left:24px;font-weight:600;color:#234B7A"><?= htmlspecialchars($r['kod_jabatan']) ?></td>
                    <td><?= htmlspecialchars($r['nama_pengarah']) ?></td>
                    <td style="color:#2862C0;font-weight:600"><?= htmlspecialchars($r['no_pekerja'] ?? '-') ?></td>
                    <td>
                        <?php if ($r['status'] == 1): ?><span class="badge-status badge-success">Aktif</span>
                        <?php else: ?><span class="badge-status badge-danger">Tidak Aktif</span><?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:8px;align-items:center">
                            <button class="btn-secondary-soft" style="padding:8px 14px;font-size:0.85rem"
                                onclick='openEdit(<?= (int)$r["id"] ?>, <?= htmlspecialchars(json_encode($r["kod_jabatan"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["nama_pengarah"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["no_pekerja"]), ENT_QUOTES) ?>)'>
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form method="POST" style="margin:0" data-confirm="Tukar status pengarah ini?">
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
</div>

<datalist id="jabatanOpts">
    <?php foreach ($jabatanLs as $j): ?><option value="<?= htmlspecialchars($j) ?>"><?php endforeach; ?>
</datalist>

<!-- MODAL -->
<div class="modal-overlay" id="pModal">
    <div class="modal-box">
        <h3 id="modalTitle"><i class="bi bi-person-badge"></i> Tambah Pengarah</h3>
        <form method="POST" id="pForm">
            <input type="hidden" name="act" id="f_act" value="add">
            <input type="hidden" name="id" id="f_id" value="">
            <div class="mb-3">
                <label class="field-label">Jabatan <span class="req">*</span></label>
                <input type="text" name="kod_jabatan" id="f_kod" class="form-control-custom" list="jabatanOpts" required placeholder="Cth: Jabatan Perbendaharaan">
            </div>
            <div class="mb-3">
                <label class="field-label">Nama Pengarah <span class="req">*</span></label>
                <input type="text" name="nama_pengarah" id="f_nama" class="form-control-custom" required placeholder="Nama penuh pengarah">
            </div>
            <div class="mb-1">
                <label class="field-label">No. Pekerja</label>
                <input type="text" name="no_pekerja" id="f_nopek" class="form-control-custom" placeholder="Cth: MB000100">
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
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Tambah Pengarah';
    document.getElementById('f_act').value='add'; document.getElementById('f_id').value='';
    document.getElementById('f_kod').value=''; document.getElementById('f_nama').value=''; document.getElementById('f_nopek').value='';
    document.getElementById('pModal').classList.add('show');
    setTimeout(()=>document.getElementById('f_kod').focus(),50);
}
function openEdit(id,kod,nama,nopek){
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square"></i> Kemas Kini Pengarah';
    document.getElementById('f_act').value='edit'; document.getElementById('f_id').value=id;
    document.getElementById('f_kod').value=kod||''; document.getElementById('f_nama').value=nama||''; document.getElementById('f_nopek').value=nopek||'';
    document.getElementById('pModal').classList.add('show');
    setTimeout(()=>document.getElementById('f_nama').focus(),50);
}
function closeModal(){ document.getElementById('pModal').classList.remove('show'); }
document.getElementById('pModal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
</script>
</body>
</html>
