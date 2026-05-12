<?php

include 'backend/config/database.php';

$id = (int)($_GET['id'] ?? 0);

// Tambah anggota - nama bebas, tidak perlu user terdaftar
if(isset($_POST['add_member'])){
    $member_name = mysqli_real_escape_string($conn, trim($_POST['member_name'] ?? ''));
    if($member_name !== ''){
        mysqli_query($conn,
            "INSERT INTO goal_members(goal_id, member_name) VALUES('$id', '$member_name')"
        );
    }
    header("Location: detail_goal.php?id=$id");
    exit;
}

// Hapus anggota
if(isset($_POST['delete_member'])){
    $member_id = (int)$_POST['member_id'];
    mysqli_query($conn,
        "DELETE FROM goal_members WHERE id='$member_id' AND goal_id='$id'"
    );
    header("Location: detail_goal.php?id=$id");
    exit;
}

// Data goal
$query = mysqli_query($conn, "SELECT * FROM goals WHERE id='$id' LIMIT 1");
$goal  = mysqli_fetch_assoc($query);
if(!$goal) die("Goals tidak ditemukan");

$target   = $goal['target_amount'];
$current  = $goal['current_amount'];
$progress = ($target > 0) ? min(100, round(($current / $target) * 100)) : 0;

// Daftar anggota
$member_query = mysqli_query($conn,
    "SELECT id, member_name FROM goal_members WHERE goal_id='$id' ORDER BY id ASC"
);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Goals</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
body { background:#f4f8f5; font-family:'Poppins',sans-serif; }
.detail-card { border:none; border-radius:28px; overflow:hidden; box-shadow:0 15px 40px rgba(0,0,0,0.06); }
.top-banner { background:linear-gradient(135deg,#0b6b36,#22c76f); padding:35px; color:white; position:relative; }
.top-banner::after { content:''; position:absolute; width:180px; height:180px; background:rgba(255,255,255,0.08); border-radius:50%; right:-50px; bottom:-70px; }
.goal-title { font-size:34px; font-weight:800; margin-bottom:12px; }
.goal-info  { display:flex; gap:30px; flex-wrap:wrap; margin-top:20px; }
.info-box   { background:rgba(255,255,255,0.12); padding:16px 20px; border-radius:18px; backdrop-filter:blur(8px); }
.info-label { font-size:13px; opacity:.9; }
.info-value { font-size:20px; font-weight:700; margin-top:5px; }
.content    { padding:35px; }
.progress   { height:20px; border-radius:20px; background:#edf3ef; }
.progress-bar { border-radius:20px; font-weight:600; }
.section-title { font-size:22px; font-weight:700; margin-bottom:16px; }
.member-list { list-style:none; padding:0; margin:0; }
.member-item {
    background:#f7fbf8; padding:14px 18px; border-radius:16px;
    margin-bottom:10px; border:1px solid #e1eee5;
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    animation:fadeIn .25s ease;
}
@keyframes fadeIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
.member-left { display:flex; align-items:center; gap:12px; }
.avatar {
    width:42px; height:42px; border-radius:50%;
    background:#22c76f; color:white;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:18px; flex-shrink:0;
}
.member-name-text { font-weight:600; font-size:15px; }
.empty-state { text-align:center; padding:28px 0; color:#9ca3af; font-size:14px; }

/* Input tambah */
.add-row { display:flex; gap:10px; margin-top:16px; }
.add-row input {
    flex:1; height:52px; border-radius:16px;
    border:1.5px solid #dce9df; padding:0 18px;
    font-family:'Poppins',sans-serif; font-size:14px;
    outline:none; transition:border-color .2s;
}
.add-row input:focus { border-color:#22c76f; }

.btn-tambah {
    height:52px; padding:0 24px; border-radius:16px;
    background:#16a34a; color:white; border:none;
    font-family:'Poppins',sans-serif; font-weight:600;
    font-size:14px; cursor:pointer; white-space:nowrap;
    transition:background .2s;
}
.btn-tambah:hover   { background:#15803d; }
.btn-tambah:disabled { background:#86efac; cursor:not-allowed; }

.btn-hapus {
    height:34px; padding:0 14px; border-radius:10px;
    font-size:12px; font-weight:600; border:none;
    background:#fee2e2; color:#dc2626;
    cursor:pointer; transition:background .2s; flex-shrink:0;
}
.btn-hapus:hover { background:#fca5a5; }

.btn-main {
    display:block; width:100%; margin-top:30px; height:58px;
    font-size:17px; border-radius:18px; background:#16a34a;
    color:white; border:none; font-family:'Poppins',sans-serif;
    font-weight:700; cursor:pointer; text-decoration:none;
    text-align:center; line-height:58px; transition:background .2s;
}
.btn-main:hover { background:#15803d; color:white; }

.toast-notif {
    position:fixed; bottom:30px; right:30px;
    background:#16a34a; color:white; padding:14px 22px;
    border-radius:14px; font-weight:600; font-size:14px;
    box-shadow:0 8px 24px rgba(0,0,0,0.15);
    opacity:0; pointer-events:none;
    transition:opacity .3s; z-index:9999;
}
.toast-notif.show { opacity:1; }
</style>
</head>
<body>

<div class="container py-5">
<div class="card detail-card">

    <div class="top-banner">
        <div class="goal-title"><?= htmlspecialchars($goal['title']) ?></div>
        <div><?= htmlspecialchars($goal['description']) ?></div>
        <div class="goal-info">
            <div class="info-box">
                <div class="info-label">Target Tabungan</div>
                <div class="info-value">Rp<?= number_format($target,0,',','.') ?></div>
            </div>
            <div class="info-box">
                <div class="info-label">Dana Terkumpul</div>
                <div class="info-value">Rp<?= number_format($current,0,',','.') ?></div>
            </div>
            <div class="info-box">
                <div class="info-label">Progress</div>
                <div class="info-value"><?= $progress ?>%</div>
            </div>
        </div>
    </div>

    <div class="content">

        <div class="mb-4">
            <div class="d-flex justify-content-between mb-2">
                <span class="fw-semibold">Progress Goals</span>
                <span class="fw-bold text-success"><?= $progress ?>%</span>
            </div>
            <div class="progress">
                <div class="progress-bar bg-success" style="width:<?= $progress ?>%"></div>
            </div>
        </div>

        <div class="section-title">👥 Anggota Goals</div>

        <ul class="member-list" id="memberList">

            <?php if(mysqli_num_rows($member_query) === 0): ?>
            <li class="empty-state" id="emptyState">Belum ada anggota. Tambahkan di bawah.</li>
            <?php endif; ?>

            <?php while($m = mysqli_fetch_assoc($member_query)): ?>
            <li class="member-item" id="member-<?= $m['id'] ?>">
                <div class="member-left">
                    <div class="avatar"><?= strtoupper(substr($m['member_name'],0,1)) ?></div>
                    <div class="member-name-text"><?= htmlspecialchars($m['member_name']) ?></div>
                </div>
                <button class="btn-hapus"
                    onclick="hapusMember(<?= $m['id'] ?>, '<?= htmlspecialchars($m['member_name'], ENT_QUOTES) ?>')">
                    🗑 Hapus
                </button>
            </li>
            <?php endwhile; ?>

        </ul>

        <!-- Input nama bebas -->
        <div class="add-row">
            <input
                type="text"
                id="inputNama"
                placeholder="Ketik nama anggota, lalu tekan Tambah atau Enter"
                maxlength="100"
                autocomplete="off"
            >
            <button class="btn-tambah" id="btnTambah" onclick="tambahMember()">➕ Tambah</button>
        </div>

        <a href="transaksi.php" class="btn-main">💸 Setor Sekarang</a>

    </div>
</div>
</div>

<div class="toast-notif" id="toast"></div>

<script>
const GOAL_ID = <?= $id ?>;

function showToast(msg, color = '#16a34a'){
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = color;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2800);
}

function refreshList(){
    return fetch(`detail_goal.php?id=${GOAL_ID}`)
        .then(r => r.text())
        .then(html => {
            const doc     = new DOMParser().parseFromString(html, 'text/html');
            const newList = doc.getElementById('memberList');
            if(newList) document.getElementById('memberList').innerHTML = newList.innerHTML;
        });
}

function tambahMember(){
    const input = document.getElementById('inputNama');
    const nama  = input.value.trim();

    if(!nama){
        input.focus();
        showToast('⚠️ Nama tidak boleh kosong', '#d97706');
        return;
    }

    const btn = document.getElementById('btnTambah');
    btn.disabled    = true;
    btn.textContent = 'Menyimpan...';

    const fd = new FormData();
    fd.append('add_member', '1');
    fd.append('member_name', nama);

    fetch(`detail_goal.php?id=${GOAL_ID}`, { method:'POST', body:fd })
        .then(() => refreshList())
        .then(() => {
            input.value = '';
            input.focus();
            showToast(`✅ ${nama} berhasil ditambahkan`);
        })
        .catch(() => showToast('❌ Gagal menambahkan', '#dc2626'))
        .finally(() => {
            btn.disabled    = false;
            btn.textContent = '➕ Tambah';
        });
}

function hapusMember(id, nama){
    if(!confirm(`Hapus ${nama} dari goals ini?`)) return;

    const fd = new FormData();
    fd.append('delete_member', '1');
    fd.append('member_id', id);

    fetch(`detail_goal.php?id=${GOAL_ID}`, { method:'POST', body:fd })
        .then(() => refreshList())
        .then(() => showToast(`🗑 ${nama} dihapus`, '#dc2626'))
        .catch(() => showToast('❌ Gagal menghapus', '#dc2626'));
}

document.getElementById('inputNama').addEventListener('keydown', e => {
    if(e.key === 'Enter') tambahMember();
});
</script>

</body>
</html>