<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$db = getDB();
$perm = $db->prepare("SELECT * FROM permohonan WHERE id=?");
$perm->execute([$id]);
$r = $perm->fetch();
if (!$r) { header('Location: login.php'); exit; }

// Kawalan capaian: pemohon hanya boleh cetak permohonan sendiri
if ($_SESSION['role'] === 'pemohon' && $r['user_id'] != $_SESSION['user_id']) { header('Location: dashboard_pemohon.php'); exit; }

$ss = $db->prepare("SELECT * FROM permohonan_sistem WHERE permohonan_id=? ORDER BY bil");
$ss->execute([$id]);
$sistems = $ss->fetchAll();

// Nama Pengarah JTIK (untuk ruang tandatangan)
$namaJtik = '';
if (!empty($r['pengarah_jtik_id'])) {
    $jn = $db->prepare("SELECT nama FROM users WHERE id=?");
    $jn->execute([$r['pengarah_jtik_id']]);
    $namaJtik = ($jn->fetch()['nama'] ?? '');
}
function fmtTkh($t) { return $t ? date('d/m/Y', strtotime($t)) : '.......................'; }

// Blok tandatangan: jika sudah disahkan dalam sistem (ada tarikh) -> papar nama, jawatan, tarikh & masa.
// Jika belum -> ruang tandatangan manual kosong.
function sahBox($nama, $jawatan, $tarikh, $labelKosong = 'Tandatangan') {
    if ($tarikh) {
        $masa = date('d/m/Y  H:i', strtotime($tarikh));
        $h  = '<div class="sah">';
        $h .= '<div class="sah-badge"><i class="bi bi-patch-check-fill"></i> DISAHKAN DALAM SISTEM</div>';
        $h .= '<div class="sah-nama">' . htmlspecialchars($nama ?: '-') . '</div>';
        if ($jawatan) $h .= '<div class="sah-jaw">' . htmlspecialchars($jawatan) . '</div>';
        $h .= '<div class="sah-masa"><i class="bi bi-clock"></i> Tarikh &amp; Masa: <b>' . $masa . '</b></div>';
        $h .= '</div>';
        return $h;
    }
    return '<div class="sign-line">' . htmlspecialchars($labelKosong) . '<br><span style="color:#888">( menunggu tindakan )</span></div>';
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Borang Capaian Sistem 119/D35 - <?= htmlspecialchars($r['no_rujukan'] ?? $r['id']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
    @page { size: A4; margin: 14mm 14mm 12mm 14mm; }
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI','Calibri',sans-serif; color: #000; font-size: 10.5pt; margin: 0; background: #E9EDF2; }
    .sheet { background: #fff; width: 210mm; min-height: 297mm; margin: 16px auto; padding: 16mm 14mm; box-shadow: 0 4px 24px rgba(0,0,0,0.15); }
    .doc-head { display: flex; align-items: center; gap: 14px; border-bottom: 2.5px solid #000; padding-bottom: 10px; margin-bottom: 4px; }
    .doc-head .logo { width: 58px; height: 58px; border-radius: 10px; background: linear-gradient(135deg,#1E3A5F,#2E73D8 55%,#1FBCD4); color:#fff; display:flex; align-items:center; justify-content:center; font-size:28px; flex-shrink:0; }
    .doc-head h1 { font-size: 15pt; margin: 0; color:#000; }
    .doc-head .sub { font-size: 9.5pt; color:#333; }
    .doc-meta { display:flex; justify-content:space-between; font-size:9pt; color:#333; margin-bottom:12px; }
    .sec { border: 1px solid #000; margin-bottom: 10px; }
    .sec-h { background: #E6EFFA; border-bottom: 1px solid #000; padding: 5px 10px; font-weight: 700; font-size: 10pt; display:flex; align-items:center; gap:8px; }
    .sec-h .lbl { background:#1E3A5F; color:#fff; border-radius:4px; padding:1px 8px; font-size:9pt; }
    .sec-b { padding: 9px 11px; }
    table.info { width:100%; border-collapse:collapse; }
    table.info td { padding: 3px 6px; font-size: 10pt; vertical-align: top; }
    table.info td.k { width: 30%; color:#333; }
    table.info td.k::after { content:":"; float:right; }
    table.sys { width:100%; border-collapse:collapse; font-size:9pt; }
    table.sys th, table.sys td { border:1px solid #555; padding:5px 7px; text-align:left; vertical-align:top; }
    table.sys th { background:#EEF3FA; }
    .tick { font-size:9pt; }
    .decl { font-size:9.5pt; line-height:1.5; }
    .sign-row { display:flex; gap:26px; margin-top:16px; }
    .sign-box { flex:1; }
    .sign-line { border-top:1px solid #000; margin-top:34px; padding-top:4px; font-size:9pt; }
    .sah { border:1px solid #18A957; background:#F0FBF4; border-radius:8px; padding:8px 11px; margin-top:6px; }
    .sah-badge { display:inline-block; background:#18A957; color:#fff; font-size:7.5pt; font-weight:700; padding:2px 9px; border-radius:20px; letter-spacing:0.3px; margin-bottom:5px; }
    .sah-nama { font-weight:700; font-size:10.5pt; color:#000; }
    .sah-jaw { font-size:9pt; color:#333; }
    .sah-masa { font-size:8.5pt; color:#155e34; margin-top:4px; }
    .kv { font-size:9.5pt; margin:3px 0; }
    .kv b { display:inline-block; min-width:130px; }
    .status-line { font-size:9.5pt; margin-bottom:10px; }
    .status-line b { color:#000; }
    .toolbar { max-width:210mm; margin:14px auto 0; display:flex; gap:10px; justify-content:flex-end; }
    .btn { border:none; border-radius:9px; padding:10px 18px; font-size:0.92rem; font-weight:700; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:7px; }
    .btn-print { background:linear-gradient(135deg,#1E3A5F,#2C5488); color:#fff; }
    .btn-back { background:#E6EFFA; color:#234B7A; }
    @media print {
        body { background:#fff; }
        .sheet { box-shadow:none; margin:0; width:auto; min-height:auto; padding:0; }
        .toolbar { display:none !important; }
    }
</style>
</head>
<body>

<div class="toolbar">
    <a href="javascript:history.back()" class="btn btn-back"><i class="bi bi-arrow-left"></i> Kembali</a>
    <button class="btn btn-print" onclick="window.print()"><i class="bi bi-printer"></i> Cetak / Simpan PDF</button>
</div>

<div class="sheet">
    <div class="doc-head">
        <div class="logo"><i class="bi bi-shield-lock"></i></div>
        <div style="flex:1">
            <h1>MAJLIS BANDARAYA SEBERANG PERAI</h1>
            <div class="sub">Borang Permohonan Capaian Sistem &mdash; <b>Kod Borang: 119/D35</b></div>
        </div>
    </div>
    <div class="doc-meta">
        <div>No. Rujukan: <b><?= htmlspecialchars($r['no_rujukan'] ?? '-') ?></b></div>
        <div>Tarikh Permohonan: <b><?= fmtTkh($r['tkh_keyin']) ?></b></div>
        <div>Tujuan: <b><?= strtoupper(tujuanLabel($r['tujuan'])) ?></b></div>
    </div>

    <div class="status-line">Status Semasa: <b><?= statusLabel($r['status']) ?></b></div>

    <!-- A: Maklumat Kakitangan -->
    <div class="sec">
        <div class="sec-h"><span class="lbl">A</span> MAKLUMAT KAKITANGAN</div>
        <div class="sec-b">
            <table class="info">
                <tr><td class="k">Nama</td><td><b><?= htmlspecialchars($r['nama']) ?></b></td><td class="k">No. Kakitangan</td><td><b><?= htmlspecialchars($r['no_kakitangan']) ?></b></td></tr>
                <tr><td class="k">Jawatan</td><td><?= htmlspecialchars($r['jawatan']) ?></td><td class="k">Gred</td><td><?= htmlspecialchars($r['gred_jawatan']) ?></td></tr>
                <tr><td class="k">Jabatan</td><td><?= htmlspecialchars($r['jabatan']) ?></td><td class="k">No. Telefon</td><td><?= htmlspecialchars($r['telefon']) ?></td></tr>
            </table>
        </div>
    </div>

    <!-- B: Maklumat Sistem -->
    <div class="sec">
        <div class="sec-h"><span class="lbl">B</span> MAKLUMAT SISTEM DIPOHON</div>
        <div class="sec-b">
            <?php if ($sistems): ?>
            <table class="sys">
                <thead><tr><th style="width:28px">Bil</th><th>Nama Sistem</th><th style="width:120px">Peranan</th><th>Had Kuasa</th><th style="width:120px">Catatan</th></tr></thead>
                <tbody>
                <?php $n=1; foreach ($sistems as $s): ?>
                <tr>
                    <td style="text-align:center"><?= $n++ ?></td>
                    <td><?= htmlspecialchars($s['nama_sistem']) ?></td>
                    <td><?= $s['peranan_sistem'] ? htmlspecialchars(perananLabel($s['peranan_sistem'])) : '-' ?></td>
                    <td class="tick">
                        <?php $hk=[]; foreach (SENARAI_FUNGSI as $f) if ((int)($s[$f]??0)===1) $hk[]=fungsiLabel($f); echo $hk ? htmlspecialchars(implode(', ', $hk)) : '-'; ?>
                    </td>
                    <td><?= htmlspecialchars($s['catatan'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="font-size:9.5pt;color:#333">Tiada sistem dipilih (permohonan pembatalan).</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- C: Pengesahan Pemohon -->
    <div class="sec">
        <div class="sec-h"><span class="lbl">C</span> PENGESAHAN PEMOHON</div>
        <div class="sec-b">
            <div class="decl">Saya dengan ini mengesahkan maklumat yang diberikan di atas adalah benar dan mengaku akan bertanggungjawab terhadap capaian yang dipohon.</div>
            <div class="sign-row">
                <div class="sign-box"><div style="font-size:9pt;font-weight:700;margin-bottom:2px">Pemohon</div><?= sahBox($r['nama'], $r['jawatan'], $r['tkh_keyin'], 'Tandatangan Pemohon') ?></div>
                <div class="sign-box"></div>
            </div>
        </div>
    </div>

    <!-- E: Perakuan Pengarah Jabatan -->
    <div class="sec">
        <div class="sec-h"><span class="lbl">E</span> PERAKUAN PENGARAH JABATAN</div>
        <div class="sec-b">
            <?php if ($r['tarikh_pengarah_jab']): ?>
                <div class="kv"><b>Keputusan:</b> <?= (($r['kelulusan_jtik']??'')!=='' || $r['status']!=='TIDAK_DILULUSKAN') ? 'DIPERAKUKAN' : 'TIDAK DIPERAKUKAN' ?></div>
                <?php if ($r['status']==='TIDAK_DILULUSKAN' && ($r['kelulusan_jtik']??'')===''): ?>
                <div class="kv"><b>Alasan:</b> <?= htmlspecialchars($r['alasan_pengarah_jab'] ?: '-') ?></div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="sign-row">
                <div class="sign-box"><?= sahBox($r['nama_pengarah_jab'], 'Pengarah Jabatan', $r['tarikh_pengarah_jab'], 'Tandatangan & Cop Pengarah Jabatan') ?></div>
                <div class="sign-box"></div>
            </div>
        </div>
    </div>

    <!-- F: Kelulusan Pengarah JTIK -->
    <div class="sec">
        <div class="sec-h"><span class="lbl">F</span> KELULUSAN PENGARAH JTIK</div>
        <div class="sec-b">
            <?php if ($r['tarikh_jtik']): ?>
                <div class="kv"><b>Keputusan:</b> <?= ($r['kelulusan_jtik']==='DILULUSKAN') ? 'DILULUSKAN' : 'TIDAK DILULUSKAN' ?></div>
                <?php if (!empty($r['alasan_jtik'])): ?><div class="kv"><b>Alasan:</b> <?= htmlspecialchars($r['alasan_jtik']) ?></div><?php endif; ?>
            <?php endif; ?>
            <div class="sign-row">
                <div class="sign-box"><?= sahBox($namaJtik, 'Pengarah JTIK', $r['tarikh_jtik'], 'Tandatangan & Cop Pengarah JTIK') ?></div>
                <div class="sign-box"></div>
            </div>
        </div>
    </div>

    <!-- G: Kegunaan IT -->
    <div class="sec">
        <div class="sec-h"><span class="lbl">G</span> KEGUNAAN JABATAN IT (JTIK)</div>
        <div class="sec-b">
            <div class="sign-row">
                <div class="sign-box">
                    <div style="font-weight:700;font-size:9.5pt;margin-bottom:2px">Pemberi Akses</div>
                    <?= sahBox($r['it_pemberi_nama'], $r['it_pemberi_cop'] ?: 'Admin IT (JTIK)', $r['tarikh_it'], 'Tandatangan & Cop Pemberi Akses') ?>
                </div>
                <div class="sign-box">
                    <div style="font-weight:700;font-size:9.5pt;margin-bottom:2px">Penyemak</div>
                    <?= sahBox($r['it_penyemak_nama'], $r['it_penyemak_cop'] ?: 'Penyemak IT', $r['tarikh_semakan'], 'Tandatangan & Cop Penyemak') ?>
                </div>
            </div>
        </div>
    </div>

    <div style="text-align:center;font-size:8pt;color:#555;margin-top:8px">Dokumen ini dijana oleh Sistem Permohonan Capaian MBSP &mdash; <?= date('d/m/Y H:i') ?></div>
</div>

<script>
    // Auto-buka dialog cetak jika dibuka dengan ?print=1
    <?php if (($_GET['print'] ?? '') === '1'): ?>window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 350); });<?php endif; ?>
</script>
</body>
</html>
