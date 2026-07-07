<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('pemohon');

$db   = getDB();
$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$_SESSION['user_id']]);
$u = $user->fetch();

// Sumber rasmi data kakitangan: Sistem Gaji (rujuk ikut No. Kakitangan pengguna)
$gs = $db->prepare("SELECT * FROM gaji WHERE no_kakitangan = ?");
$gs->execute([$u['no_kakitangan'] ?? '']);
$g = $gs->fetch();

// Sistem yang permohonannya MASIH DALAM PROSES untuk pengguna ini.
// (belum sampai status muktamad — tidak boleh dimohon semula sehingga selesai)
$inProc = [];
$ip = $db->prepare("
    SELECT DISTINCT ps.bil, p.status
    FROM permohonan_sistem ps
    JOIN permohonan p ON p.id = ps.permohonan_id
    WHERE p.user_id = ?
      AND p.status IN ('MENUNGGU_PENGARAH_JAB','MENUNGGU_JTIK','DILULUSKAN')
");
$ip->execute([$_SESSION['user_id']]);
foreach ($ip->fetchAll() as $r) { $inProc[(int)$r['bil']] = $r['status']; }

// Admin IT (pemberi akses) yang bertanggungjawab bagi setiap sistem (peta id_sistem -> nama admin)
$adminBySistem = [];
foreach ($db->query("SELECT id_sistem, nama_admin FROM sistem_admin WHERE status = 1 AND nama_admin IS NOT NULL AND nama_admin <> '' ORDER BY nama_admin")->fetchAll() as $row) {
    $adminBySistem[(int)$row['id_sistem']][] = $row['nama_admin'];
}

// Mohon semula: prefill dari permohonan lama (mesti milik pemohon). Guna untuk permohonan yang ditolak.
$resubmit = null;
$rid = (int)($_GET['resubmit'] ?? 0);
if ($rid && $g) {
    $rq = $db->prepare("SELECT * FROM permohonan WHERE id=? AND user_id=?");
    $rq->execute([$rid, $_SESSION['user_id']]);
    if ($rp = $rq->fetch()) {
        $resubmit = ['id'=>$rp['id'], 'no_rujukan'=>$rp['no_rujukan'], 'tujuan'=>$rp['tujuan'], 'sistem'=>[]];
        $rs = $db->prepare("SELECT * FROM permohonan_sistem WHERE permohonan_id=? ORDER BY bil");
        $rs->execute([$rid]);
        foreach ($rs->fetchAll() as $srow) {
            $hk = [];
            foreach (SENARAI_FUNGSI as $f) $hk[$f] = (int)($srow[$f] ?? 0);
            $resubmit['sistem'][(int)$srow['bil']] = ['peranan'=>$srow['peranan_sistem'], 'catatan'=>$srow['catatan'] ?? '', 'hk'=>$hk];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Borang Permohonan Capaian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .radio-card{position:relative;border:2px solid #DCE6F2;border-radius:14px;padding:18px 14px;cursor:pointer;transition:all 0.18s;display:flex;flex-direction:column;align-items:center;gap:9px;text-align:center;background:#fff;}
        .radio-card:hover{border-color:#3A86D0;background:#F4F8FD;transform:translateY(-2px);box-shadow:0 5px 14px rgba(46,115,216,0.12);}
        .radio-card.selected{border-color:#2E73D8;background:#E6EFFA;box-shadow:0 6px 16px rgba(46,115,216,0.20);}
        .radio-card input{position:absolute;opacity:0;pointer-events:none;}
        .radio-card .rc-ic{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:21px;background:#EEF3FA;color:#2E73D8;transition:all 0.18s;}
        .radio-card.selected .rc-ic{background:linear-gradient(135deg,#2E73D8,#1FBCD4);color:#fff;}
        .radio-card .rc-txt{font-size:0.92rem;font-weight:700;color:#3A4658;}
        .radio-card .rc-check{position:absolute;top:9px;right:10px;color:#2E73D8;font-size:1.15rem;opacity:0;transition:opacity 0.18s;}
        .radio-card.selected .rc-check{opacity:1;}
        .form-control-custom[readonly]{background:#EEF3FA;color:#3A4658;cursor:not-allowed;border-style:dashed;}
        .locked-note{font-size:0.84rem;color:#2862C0;background:#E6EFFA;border:1px solid #BFD2EC;border-radius:8px;padding:9px 13px;display:flex;align-items:center;gap:8px;margin-bottom:16px;font-weight:600;}
        .block-card{text-align:center;padding:46px 30px;}
        .block-icon{width:84px;height:84px;border-radius:50%;background:#FFE2E0;color:#E23B36;display:flex;align-items:center;justify-content:center;font-size:42px;margin:0 auto 18px;}
        /* Baris sistem yang masih dalam proses */
        tr.row-inproc{background:#FFF8EC !important;}
        tr.row-inproc td{color:#9A8254;}
        .btn-inproc{border:none;background:#FCE9C7;color:#B9791C;width:30px;height:30px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;cursor:pointer;transition:all .15s;}
        .btn-inproc:hover{background:#F6D79A;color:#8A5A12;}
        .badge-inproc{display:inline-flex;align-items:center;gap:5px;font-size:0.74rem;font-weight:700;color:#B9791C;background:#FCE9C7;border:1px solid #EFD3A0;border-radius:20px;padding:2px 10px;margin-left:8px;white-space:nowrap;vertical-align:middle;}
        /* Pop up dalam proses */
        .ip-overlay{position:fixed;inset:0;background:rgba(30,58,95,0.55);display:none;align-items:center;justify-content:center;z-index:1080;padding:18px;backdrop-filter:blur(2px);}
        .ip-overlay.show{display:flex;}
        .ip-box{background:#fff;border-radius:18px;max-width:430px;width:100%;padding:34px 30px 28px;text-align:center;box-shadow:0 20px 60px rgba(30,58,95,0.35);animation:ipPop .2s ease;}
        @keyframes ipPop{from{transform:scale(.92);opacity:0}to{transform:scale(1);opacity:1}}
        .ip-ic{width:74px;height:74px;border-radius:50%;background:linear-gradient(135deg,#F6C25B,#E89A2B);color:#fff;display:flex;align-items:center;justify-content:center;font-size:36px;margin:0 auto 16px;box-shadow:0 8px 20px rgba(232,154,43,0.4);}
        .ip-box h5{font-weight:800;color:#1E3A5F;margin-bottom:10px;}
        .ip-box p{color:#5B6675;line-height:1.6;font-size:0.94rem;margin-bottom:22px;}
        .ip-box .ip-sys{color:#B9791C;font-weight:700;}
        .ip-box button{background:linear-gradient(135deg,#1E3A5F,#2C5488);color:#fff;border:none;border-radius:10px;padding:11px 30px;font-weight:700;cursor:pointer;transition:filter .15s;}
        .ip-box button:hover{filter:brightness(1.12);}
        /* ===== JADUAL SISTEM -> KAD PADA TELEFON ===== */
        @media (max-width:768px){
            #sistemSection > div[style*="overflow-x"]{overflow-x:visible;}
            .sistem-table thead{display:none;}
            .sistem-table, .sistem-table tbody, .sistem-table tr, .sistem-table td{display:block;width:100%;}
            .sistem-table tr{border:1.5px solid #DCE6F2;border-radius:14px;margin-bottom:12px;padding:6px 4px;background:#fff;box-shadow:0 2px 8px rgba(40,70,120,0.07);}
            .sistem-table tr.row-inproc{background:#FFF8EC;border-color:#EFD3A0;}
            .sistem-table td{border:none !important;padding:9px 14px !important;display:flex;flex-direction:column;gap:6px;}
            .sistem-table td::before{content:attr(data-label);font-weight:700;color:#234B7A;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.3px;}
            .sistem-table td.check-col{flex-direction:row;align-items:center;gap:10px;}
            .sistem-table td.check-col::before{order:2;font-size:0.9rem;color:#3A4658;text-transform:none;letter-spacing:0;font-weight:600;}
            .sistem-table td.check-col input[type=checkbox]{order:1;}
            .sistem-table td.cell-bil{display:none;}
            .sistem-table td.cell-nama{font-weight:800;color:#1E3A5F;font-size:1.02rem;border-top:1px dashed #E2EAF5 !important;padding-top:11px !important;}
            .sistem-table td.cell-nama::before{content:none;}
            /* Sembunyi butiran sehingga sistem ditanda (jimat skrol utk 26 sistem) */
            .sistem-table td.cell-peranan, .sistem-table td.cell-hk, .sistem-table td.cell-catatan{display:none;}
            .sistem-table tr.picked td.cell-peranan, .sistem-table tr.picked td.cell-hk, .sistem-table tr.picked td.cell-catatan{display:flex;}
            .sistem-table select{width:100% !important;}
            .sistem-table input[type=text]{width:100%;}
        }
    </style>
</head>
<body>
<?php sidebarHTML($_SESSION['nama'] ?? $_SESSION['username'], 'Pemohon', [
    ['href'=>'dashboard_pemohon.php', 'icon'=>'bi-grid-1x2',   'label'=>'Dashboard',       'active'=>false],
    ['href'=>'borang_permohonan.php', 'icon'=>'bi-plus-circle', 'label'=>'Buat Permohonan', 'active'=>true],
]); ?>
<div class="main-content">
    <div class="page-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard_pemohon.php"><i class="bi bi-house me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Borang Permohonan</li>
            </ol>
        </nav>
        <h4>Borang Capaian Sistem</h4>
        <p>KOD BORANG: 119/D35 &nbsp;|&nbsp; Isi semua maklumat dengan tepat dan lengkap</p>
    </div>

    <?php if (!$g): ?>
    <!-- SEKATAN: No. Kakitangan tiada dalam Sistem Gaji -->
    <div class="form-card mb-4">
        <div class="form-section-body block-card">
            <div class="block-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <h4 style="font-weight:800;color:#1E3A5F;margin-bottom:10px">Data Belum Dalam Sistem Gaji</h4>
            <p style="color:#5B6675;max-width:540px;margin:0 auto 22px;line-height:1.65">
                No. Kakitangan anda (<b><?= htmlspecialchars($u['no_kakitangan'] ?? '-') ?></b>) belum didaftarkan dalam <b>Sistem Gaji</b>.
                Sila maklum <b>Unit Gaji</b> untuk mendaftar data anda terlebih dahulu sebelum boleh memohon capaian sistem.
            </p>
            <a href="dashboard_pemohon.php" class="btn-secondary-soft"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </div>
    <?php else: ?>
    <?php if (($_GET['error'] ?? '') === 'inproc'): ?>
    <div class="locked-note" style="color:#B9791C;background:#FFF8EC;border-color:#EFD3A0;margin-bottom:18px">
        <i class="bi bi-hourglass-split"></i> Sistem yang dipohon masih dalam proses dan telah ditapis. Tiada permohonan baharu direkodkan.
    </div>
    <?php endif; ?>
    <?php if (($_GET['error'] ?? '') === 'simpan'): ?>
    <div class="locked-note" style="color:#B42318;background:#FFE2E0;border-color:#F3B4B0;margin-bottom:18px">
        <i class="bi bi-exclamation-triangle"></i> Permohonan gagal disimpan. Sila cuba semula. Tiada data separa direkodkan.
    </div>
    <?php endif; ?>
    <?php if ($resubmit): ?>
    <div class="locked-note" style="color:#2862C0;background:#E6EFFA;border-color:#BFD2EC;margin-bottom:18px">
        <i class="bi bi-arrow-repeat"></i> Anda sedang <b>memohon semula</b> berdasarkan permohonan <b><?= htmlspecialchars($resubmit['no_rujukan'] ?? ('#'.$resubmit['id'])) ?></b>. Semak &amp; kemas kini maklumat sebelum hantar.
    </div>
    <?php endif; ?>
    <form method="POST" action="submit_permohonan.php" id="mainForm">

    <!-- SECTION A -->
    <div class="form-card mb-4">
        <div class="form-section-header">
            <span class="sec-label">A</span>
            <span class="sec-title">Maklumat Kakitangan</span>
        </div>
        <div class="form-section-body">
            <div class="locked-note"><i class="bi bi-shield-lock"></i> Maklumat diambil automatik dari <b>Sistem Gaji</b> dan tidak boleh diubah.</div>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="field-label">Nama</label>
                    <input type="text" name="nama" class="form-control-custom" readonly value="<?= htmlspecialchars($g['nama'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="field-label">No. Kakitangan</label>
                    <input type="text" name="no_kakitangan" class="form-control-custom" readonly value="<?= htmlspecialchars($g['no_kakitangan'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="field-label">Jawatan</label>
                    <input type="text" name="jawatan" class="form-control-custom" readonly value="<?= htmlspecialchars($g['jawatan'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="field-label">Gred Jawatan</label>
                    <input type="text" name="gred_jawatan" class="form-control-custom" readonly value="<?= htmlspecialchars($g['gred_jawatan'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="field-label">Jabatan</label>
                    <input type="text" name="jabatan" class="form-control-custom" readonly value="<?= htmlspecialchars($g['jabatan'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="field-label">Telefon</label>
                    <input type="text" name="telefon" class="form-control-custom" readonly value="<?= htmlspecialchars($g['telefon'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION B -->
    <div class="form-card mb-4">
        <div class="form-section-header">
            <span class="sec-label">B</span>
            <span class="sec-title">Maklumat Sistem</span>
        </div>
        <div class="form-section-body">
            <label class="field-label mb-3">Tujuan Permohonan <span class="req">*</span> <span style="font-size:0.8rem;font-weight:500;color:#6B6675">(pilih satu)</span></label>
            <div class="row g-3 mb-4">
                <?php foreach([
                    'baru'       => ['Permohonan Baru',     'bi-plus-circle'],
                    'kemaskini'  => ['Kemaskini Capaian',   'bi-pencil-square'],
                    'pembatalan' => ['Pembatalan Capaian',  'bi-x-circle'],
                ] as $val=>$info): ?>
                <div class="col-md-4">
                    <label class="radio-card" id="rc_<?=$val?>">
                        <input type="radio" name="tujuan" value="<?=$val?>" required onchange="toggleSistem(this)">
                        <i class="bi bi-check-circle-fill rc-check"></i>
                        <span class="rc-ic"><i class="bi <?=$info[1]?>"></i></span>
                        <span class="rc-txt"><?=$info[0]?></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="sistemSection">
                <p style="font-size:0.92rem;color:#6b7280;margin-bottom:12px">
                    <i class="bi bi-info-circle me-1 text-primary"></i>
                    Tandakan (✓) sistem yang dipohon dan nyatakan catatan jika perlu.
                    <span id="pembatalanNote" style="display:none;color:#dc2626"> (Tidak perlu diisi bagi permohonan pembatalan capaian)</span>
                </p>
                <div style="overflow-x:auto">
                <table class="sistem-table">
                    <thead>
                        <tr>
                            <th class="check-col">Pilih</th>
                            <th style="width:40px">Bil</th>
                            <th>Senarai Sistem</th>
                            <th style="min-width:160px">Peranan <span style="color:#dc2626;font-size:0.7rem">(pilih 1)</span></th>
                            <th style="min-width:300px">Had Kuasa</th>
                            <th style="min-width:180px">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach (getSenaraiSistem(true) as $bil => $nama): $proc = isset($inProc[$bil]); ?>
                        <tr id="row-<?=$bil?>" class="<?= $proc ? 'row-inproc' : '' ?>">
                            <td class="check-col" data-label="<?= $proc ? '' : 'Pilih sistem ini' ?>">
                                <?php if ($proc): ?>
                                <button type="button" class="btn-inproc" title="Masih dalam proses"
                                        onclick="showInProc(<?= htmlspecialchars(json_encode($nama), ENT_QUOTES) ?>)">
                                    <i class="bi bi-hourglass-split"></i>
                                </button>
                                <?php else: ?>
                                <input type="checkbox" name="sistem[]" value="<?=$bil?>"
                                       onchange="toggleRow(this,<?=$bil?>)">
                                <?php endif; ?>
                            </td>
                            <td class="cell-bil" style="color:#6E6470;font-size:0.9rem;text-align:center"><?=$bil?></td>
                            <td class="cell-nama"><?= htmlspecialchars($nama) ?>
                                <?php if ($proc): ?><span class="badge-inproc"><i class="bi bi-hourglass-split"></i> Dalam Proses</span><?php endif; ?>
                                <div style="font-size:0.78rem;color:#6b7280;font-weight:500;margin-top:3px">
                                    <i class="bi bi-person-gear" style="color:#2E73D8"></i>
                                    Pemberi Akses:
                                    <?php if (!empty($adminBySistem[$bil])): ?>
                                        <span style="color:#234B7A;font-weight:600"><?= htmlspecialchars(implode(', ', $adminBySistem[$bil])) ?></span>
                                    <?php else: ?>
                                        <span style="color:#b0b8c4">— belum ditetapkan —</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="cell-peranan" data-label="Peranan">
                                <select name="peranan_sistem_<?=$bil?>" id="ps-<?=$bil?>"
                                        disabled onchange="updateHadKuasa(<?=$bil?>)"
                                        style="border:1px solid #E6EFFA;border-radius:6px;padding:5px 8px;font-size:0.9rem;width:100%;outline:none;background:#f9f9f9;color:#6E6470;cursor:not-allowed">
                                    <option value="">-- Pilih Peranan --</option>
                                    <?php foreach(getSenaraiPeranan(true) as $key=>$label): ?>
                                    <option value="<?=$key?>"><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td id="hk-cell-<?=$bil?>" class="cell-hk" data-label="Had Kuasa">
                                <div style="display:flex;flex-wrap:wrap;gap:4px">
                                <?php foreach(SENARAI_FUNGSI as $f): ?>
                                    <label id="hklbl-<?=$bil?>-<?=$f?>"
                                           style="display:inline-flex;align-items:center;gap:3px;font-size:0.82rem;padding:2px 6px;border-radius:12px;border:1px solid #e5e7eb;background:#f9f9f9;color:#6E6470;cursor:not-allowed;white-space:nowrap">
                                        <input type="checkbox" name="had_kuasa_<?=$bil?>[<?=$f?>]" value="1"
                                               id="hk-<?=$bil?>-<?=$f?>" disabled style="accent-color:#3A86D0">
                                        <?= fungsiLabel($f) ?>
                                    </label>
                                <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="cell-catatan" data-label="Catatan"><input type="text" name="catatan_<?=$bil?>" id="cat-<?=$bil?>"
                                       placeholder="Catatan (jika ada)" disabled
                                       style="background:#f9f9f9;color:#6E6470;cursor:not-allowed"></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION C -->
    <div class="form-card mb-4">
        <div class="form-section-header">
            <span class="sec-label">C</span>
            <span class="sec-title">Pengesahan Pemohon</span>
        </div>
        <div class="form-section-body">
            <div style="background:#FFFFFF;border:1px solid #E6EFFA;border-radius:10px;padding:16px;margin-bottom:20px;font-size:0.875rem;color:#374151">
                Saya dengan ini mengesahkan maklumat yang diberikan di atas adalah benar dan mengaku akan bertanggungjawab terhadap permohonan ini.
            </div>
        </div>
        <div class="action-row">
            <button type="submit" class="btn-primary-dark"><i class="bi bi-send"></i> Hantar Permohonan</button>
            <a href="dashboard_pemohon.php" class="btn-secondary-soft"><i class="bi bi-x-lg"></i> Batal</a>
        </div>
    </div>

    </form>

    <!-- Pop up: sistem masih dalam proses -->
    <div class="ip-overlay" id="inProcModal" onclick="if(event.target===this)closeInProc()">
        <div class="ip-box">
            <div class="ip-ic"><i class="bi bi-hourglass-split"></i></div>
            <h5>Permohonan Masih Dalam Proses</h5>
            <p>Permohonan capaian bagi sistem <span class="ip-sys" id="ipSysName"></span> masih <b>dalam proses</b> untuk diberi akses. Anda tidak boleh memohon semula sistem ini sehingga proses permohonan terdahulu selesai.</p>
            <button type="button" onclick="closeInProc()"><i class="bi bi-check-lg me-1"></i> Faham</button>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($g): ?>
<script>
// Had kuasa preset (dari jadual peranan / tetapan_peranan.php)
const hadKuasa = <?= json_encode(getHadKuasa()) ?>;
const senaraiFungsi = <?= json_encode(SENARAI_FUNGSI) ?>;

function enableHadKuasa(bil) {
    senaraiFungsi.forEach(f => {
        const cb  = document.getElementById('hk-' + bil + '-' + f);
        const lbl = document.getElementById('hklbl-' + bil + '-' + f);
        if (!cb) return;
        cb.disabled = false;
        lbl.style.cssText = 'display:inline-flex;align-items:center;gap:3px;font-size:0.82rem;padding:2px 6px;border-radius:12px;border:1px solid #9DBCE0;background:#fff;color:#374151;cursor:default;white-space:nowrap';
    });
    updateHadKuasa(bil);
}

function disableHadKuasa(bil) {
    senaraiFungsi.forEach(f => {
        const cb  = document.getElementById('hk-' + bil + '-' + f);
        const lbl = document.getElementById('hklbl-' + bil + '-' + f);
        if (!cb) return;
        cb.disabled = true;
        cb.checked  = false;
        lbl.style.cssText = 'display:inline-flex;align-items:center;gap:3px;font-size:0.82rem;padding:2px 6px;border-radius:12px;border:1px solid #e5e7eb;background:#f9f9f9;color:#6E6470;cursor:not-allowed;white-space:nowrap';
    });
}

function updateHadKuasa(bil) {
    const sel = document.getElementById('ps-' + bil);
    if (!sel || !sel.value) return;
    const kuasa = hadKuasa[sel.value] || {};
    senaraiFungsi.forEach(f => {
        const cb = document.getElementById('hk-' + bil + '-' + f);
        if (cb) cb.checked = kuasa[f] === 1;
    });
}
function toggleSistem(el) {
    document.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
    el.closest('.radio-card').classList.add('selected');
    const note = document.getElementById('pembatalanNote');
    if (el.value === 'pembatalan') note.style.display = 'inline';
    else note.style.display = 'none';
}
const _ct = document.querySelector('input[name="tujuan"]:checked');
if (_ct) _ct.closest('.radio-card').classList.add('selected');

function showInProc(nama) {
    document.getElementById('ipSysName').textContent = nama;
    document.getElementById('inProcModal').classList.add('show');
}
function closeInProc() {
    document.getElementById('inProcModal').classList.remove('show');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeInProc(); });

function toggleRow(chk, bil) {
    const sel = document.getElementById('ps-' + bil);
    const cat = document.getElementById('cat-' + bil);
    const row = document.getElementById('row-' + bil);
    row.classList.toggle('picked', chk.checked);
    if (chk.checked) {
        sel.disabled = false;
        sel.required = true;
        sel.style.cssText = 'border:1px solid #9DBCE0;border-radius:6px;padding:5px 8px;font-size:0.9rem;width:100%;outline:none;background:#fff;color:#374151;cursor:default';
        cat.disabled = false;
        cat.style.cssText = 'background:#fff;color:#374151;cursor:default';
        row.style.background = '#FFFFFF';
        enableHadKuasa(bil);
    } else {
        sel.disabled = true;
        sel.required = false;
        sel.value = '';
        sel.style.cssText = 'border:1px solid #E6EFFA;border-radius:6px;padding:5px 8px;font-size:0.9rem;width:100%;outline:none;background:#f9f9f9;color:#6E6470;cursor:not-allowed';
        cat.value = '';
        cat.disabled = true;
        cat.style.cssText = 'background:#f9f9f9;color:#6E6470;cursor:not-allowed';
        row.style.background = '';
        disableHadKuasa(bil);
    }
}

// Mohon semula: prefill borang dari permohonan lama
const RESUBMIT = <?= $resubmit ? json_encode($resubmit) : 'null' ?>;
if (RESUBMIT) {
    const tj = document.querySelector('input[name="tujuan"][value="' + RESUBMIT.tujuan + '"]');
    if (tj) { tj.checked = true; toggleSistem(tj); }
    Object.keys(RESUBMIT.sistem).forEach(function(bil){
        const info = RESUBMIT.sistem[bil];
        const cb = document.querySelector('input[name="sistem[]"][value="' + bil + '"]');
        if (!cb) return; // sistem tidak aktif atau sedang dalam proses — langkau
        cb.checked = true;
        toggleRow(cb, bil);
        const sel = document.getElementById('ps-' + bil);
        if (sel && info.peranan) sel.value = info.peranan;
        const cat = document.getElementById('cat-' + bil);
        if (cat) cat.value = info.catatan || '';
        senaraiFungsi.forEach(function(f){
            const hkcb = document.getElementById('hk-' + bil + '-' + f);
            if (hkcb) hkcb.checked = !!(info.hk && info.hk[f]);
        });
    });
}
</script>
<?php endif; ?>
</body></html>
