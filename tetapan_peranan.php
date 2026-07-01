<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('admin_it');

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    if ($act === 'add' || $act === 'edit') {
        $kod  = trim($_POST['kod']  ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $hk   = $_POST['hk'] ?? [];
        $vals = [];
        foreach (SENARAI_FUNGSI as $f) $vals[$f] = isset($hk[$f]) ? 1 : 0;

        if ($act === 'add' && $kod !== '' && $nama !== '') {
            $kod = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $kod));
            $chk = $db->prepare("SELECT COUNT(*) c FROM peranan WHERE kod = ?");
            $chk->execute([$kod]);
            if ((int)$chk->fetch()['c'] > 0) { header('Location: tetapan_peranan.php?msg=dup'); exit; }
            $st = $db->prepare("INSERT INTO peranan (kod,nama,penyedia,pengemaskini,penyemak,pelapor,pengesah,pelulus,penghapus,status) VALUES (?,?,?,?,?,?,?,?,?,1)");
            $st->execute([$kod, $nama, $vals['penyedia'], $vals['pengemaskini'], $vals['penyemak'], $vals['pelapor'], $vals['pengesah'], $vals['pelulus'], $vals['penghapus']]);
        }
        if ($act === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id && $nama !== '') {
                // kod tidak diubah (kunci rujukan) — hanya nama & had kuasa
                $st = $db->prepare("UPDATE peranan SET nama=?, penyedia=?, pengemaskini=?, penyemak=?, pelapor=?, pengesah=?, pelulus=?, penghapus=?, updated_at=datetime('now','+8 hours') WHERE id=?");
                $st->execute([$nama, $vals['penyedia'], $vals['pengemaskini'], $vals['penyemak'], $vals['pelapor'], $vals['pengesah'], $vals['pelulus'], $vals['penghapus'], $id]);
            }
        }
        header('Location: tetapan_peranan.php?msg=' . $act); exit;
    }
    if ($act === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $db->prepare("UPDATE peranan SET status = 1 - status, updated_at=datetime('now','+8 hours') WHERE id=?")->execute([$id]);
        header('Location: tetapan_peranan.php?msg=toggle'); exit;
    }
}

$list  = $db->query("SELECT * FROM peranan ORDER BY id")->fetchAll();
$aktif = count(array_filter($list, fn($r) => $r['status'] == 1));
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tetapan Peranan &amp; Had Kuasa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .modal-overlay{position:fixed;inset:0;background:rgba(20,35,60,0.55);-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:9999;padding:20px;}
        .modal-overlay.show{display:flex;}
        .modal-box{background:#fff;border-radius:18px;width:100%;max-width:540px;padding:28px;box-shadow:0 24px 60px rgba(20,35,60,0.4);animation:pop 0.22s ease;max-height:92vh;overflow-y:auto;}
        @keyframes pop{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
        .modal-box h3{font-size:1.2rem;font-weight:800;color:#1E3A5F;margin-bottom:16px;display:flex;align-items:center;gap:9px;}
        .m-actions{display:flex;gap:10px;margin-top:20px;}
        .m-actions .btn-primary-dark,.m-actions .btn-secondary-soft{flex:1;}
        .hk-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px;}
        .hk-item{display:flex;align-items:center;gap:9px;border:1.5px solid #DCE6F2;border-radius:10px;padding:10px 12px;cursor:pointer;font-weight:600;color:#3A4658;font-size:0.9rem;transition:all .15s;}
        .hk-item:hover{border-color:#3A86D0;background:#F4F8FD;}
        .hk-item input{width:17px;height:17px;accent-color:#2E73D8;cursor:pointer;}
        .hk-item.on{border-color:#2E73D8;background:#E6EFFA;}
        .hk-badge{display:inline-block;font-size:0.7rem;padding:2px 8px;border-radius:10px;background:#E6EFFA;color:#2C5488;font-weight:600;margin:2px;}
    </style>
</head>
<body>
<?php
$msg = $_GET['msg'] ?? '';
if ($msg === 'add')    toastHTML('Peranan berjaya ditambah.');
if ($msg === 'edit')   toastHTML('Peranan & had kuasa berjaya dikemas kini.');
if ($msg === 'toggle') toastHTML('Status peranan berjaya dikemas kini.');
if ($msg === 'dup')    toastHTML('Kod peranan sudah wujud.', 'error');
?>
<?php sidebarHTML($_SESSION['nama'] ?? $_SESSION['username'], 'Admin IT', [
    ['href'=>'dashboard_admin_it.php',  'icon'=>'bi-grid-1x2',     'label'=>'Dashboard',            'active'=>false],
    ['href'=>'tetapan_sistem.php',      'icon'=>'bi-hdd-stack',    'label'=>'Tetapan Sistem',       'active'=>false],
    ['href'=>'tetapan_peranan.php',     'icon'=>'bi-diagram-3',    'label'=>'Tetapan Peranan',      'active'=>true],
    ['href'=>'tetapan_pengarah.php',    'icon'=>'bi-person-badge', 'label'=>'Tetapan Pengarah',     'active'=>false],
    ['href'=>'tetapan_admin_sistem.php','icon'=>'bi-person-gear',  'label'=>'Tetapan Admin Sistem', 'active'=>false],
    ['href'=>'tetapan_penyemak.php',    'icon'=>'bi-person-check', 'label'=>'Tetapan Penyemak',     'active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <h4>Tetapan Peranan &amp; Had Kuasa</h4>
            <p>Urus senarai peranan pengguna sistem dan had kuasa (fungsi) yang dibenarkan bagi setiap peranan. Ia digunakan dalam Borang Permohonan.</p>
        </div>
        <button class="btn-primary-dark" onclick="openAdd()"><i class="bi bi-plus-lg"></i> Tambah Peranan</button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-total"><i class="bi bi-diagram-3"></i></div><div><div class="stat-num num-total"><?=count($list)?></div><div class="stat-lbl lbl-total">Jumlah</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-success"><i class="bi bi-check-circle"></i></div><div><div class="stat-num num-success"><?=$aktif?></div><div class="stat-lbl lbl-success">Aktif</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-danger"><i class="bi bi-slash-circle"></i></div><div><div class="stat-num num-danger"><?=count($list)-$aktif?></div><div class="stat-lbl lbl-danger">Tidak Aktif</div></div></div></div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <h6>Senarai Peranan</h6>
            <span class="badge-count"><?=count($list)?> rekod</span>
        </div>
        <table class="data-table tbl-resp">
            <thead><tr>
                <th style="padding-left:24px">Kod</th>
                <th>Nama Peranan</th>
                <th>Had Kuasa</th>
                <th style="width:110px">Status</th>
                <th style="width:220px">Tindakan</th>
            </tr></thead>
            <tbody>
            <?php if (!$list): ?>
                <tr><td colspan="5" class="cell-empty"><div class="empty-state"><i class="bi bi-diagram-3"></i>Tiada peranan ditetapkan lagi.</div></td></tr>
            <?php else: foreach ($list as $r): ?>
                <tr>
                    <td data-label="Kod" style="padding-left:24px;font-weight:600;color:#2862C0"><?= htmlspecialchars($r['kod']) ?></td>
                    <td data-label="Nama Peranan" style="font-weight:600;color:#234B7A"><?= htmlspecialchars($r['nama']) ?></td>
                    <td class="cell-stack" data-label="Had Kuasa" style="max-width:340px">
                        <div>
                        <?php $any=false; foreach (SENARAI_FUNGSI as $f): if ((int)$r[$f] === 1): $any=true; ?>
                            <span class="hk-badge"><?= fungsiLabel($f) ?></span>
                        <?php endif; endforeach; if(!$any): ?><span style="color:#c0c8d4;font-size:0.88rem">— tiada —</span><?php endif; ?>
                        </div>
                    </td>
                    <td data-label="Status">
                        <?php if ($r['status'] == 1): ?><span class="badge-status badge-success">Aktif</span>
                        <?php else: ?><span class="badge-status badge-danger">Tidak Aktif</span><?php endif; ?>
                    </td>
                    <td class="cell-act">
                        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                            <button class="btn-secondary-soft" style="padding:8px 14px;font-size:0.85rem"
                                onclick='openEdit(<?= (int)$r["id"] ?>, <?= htmlspecialchars(json_encode($r["kod"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["nama"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(["penyedia"=>(int)$r["penyedia"],"pengemaskini"=>(int)$r["pengemaskini"],"penyemak"=>(int)$r["penyemak"],"pelapor"=>(int)$r["pelapor"],"pengesah"=>(int)$r["pengesah"],"pelulus"=>(int)$r["pelulus"],"penghapus"=>(int)$r["penghapus"]]), ENT_QUOTES) ?>)'>
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form method="POST" style="margin:0" onsubmit="return confirm('Tukar status peranan ini?')">
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
        <h3 id="modalTitle"><i class="bi bi-diagram-3"></i> Tambah Peranan</h3>
        <form method="POST" id="pForm">
            <input type="hidden" name="act" id="f_act" value="add">
            <input type="hidden" name="id" id="f_id" value="">
            <div class="mb-3">
                <label class="field-label">Kod Peranan <span class="req">*</span> <span style="font-weight:500;color:#8A7E86">(huruf kecil, tiada ruang)</span></label>
                <input type="text" name="kod" id="f_kod" class="form-control-custom" required placeholder="Cth: penyelaras_sistem">
                <div id="kodNote" style="font-size:0.8rem;color:#8A7E86;margin-top:4px;display:none"><i class="bi bi-lock"></i> Kod tidak boleh diubah selepas dicipta.</div>
            </div>
            <div class="mb-3">
                <label class="field-label">Nama Peranan <span class="req">*</span></label>
                <input type="text" name="nama" id="f_nama" class="form-control-custom" required placeholder="Cth: PENYELARAS SISTEM">
            </div>
            <div class="mb-1">
                <label class="field-label">Had Kuasa (fungsi dibenarkan)</label>
                <div class="hk-grid">
                    <?php foreach (SENARAI_FUNGSI as $f): ?>
                    <label class="hk-item" id="hkl_<?=$f?>">
                        <input type="checkbox" name="hk[<?=$f?>]" value="1" id="hk_<?=$f?>" onchange="syncHk('<?=$f?>')">
                        <?= fungsiLabel($f) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="m-actions">
                <button type="button" class="btn-secondary-soft" onclick="closeModal()"><i class="bi bi-x-lg"></i> Batal</button>
                <button type="submit" class="btn-primary-dark"><i class="bi bi-check-lg"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
const FUNGSI = <?= json_encode(SENARAI_FUNGSI) ?>;
function syncHk(f){
    const cb = document.getElementById('hk_'+f);
    document.getElementById('hkl_'+f).classList.toggle('on', cb.checked);
}
function setHk(obj){
    FUNGSI.forEach(f => {
        const cb = document.getElementById('hk_'+f);
        cb.checked = !!(obj && obj[f]);
        syncHk(f);
    });
}
function openAdd(){
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Tambah Peranan';
    document.getElementById('f_act').value='add'; document.getElementById('f_id').value='';
    document.getElementById('f_kod').value=''; document.getElementById('f_kod').readOnly=false;
    document.getElementById('kodNote').style.display='none';
    document.getElementById('f_nama').value='';
    setHk({});
    document.getElementById('pModal').classList.add('show');
    setTimeout(()=>document.getElementById('f_kod').focus(),50);
}
function openEdit(id,kod,nama,hk){
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square"></i> Kemas Kini Peranan';
    document.getElementById('f_act').value='edit'; document.getElementById('f_id').value=id;
    document.getElementById('f_kod').value=kod||''; document.getElementById('f_kod').readOnly=true;
    document.getElementById('kodNote').style.display='block';
    document.getElementById('f_nama').value=nama||'';
    setHk(hk||{});
    document.getElementById('pModal').classList.add('show');
    setTimeout(()=>document.getElementById('f_nama').focus(),50);
}
function closeModal(){ document.getElementById('pModal').classList.remove('show'); }
document.getElementById('pModal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
</script>
</body>
</html>
