<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$nama    = $_SESSION['name'] ?? ($_SESSION['nama'] ?? 'Pengguna');

function callBackend($endpoint, $postData = null) {
    $url = 'http://backend/api/' . $endpoint;
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    $result   = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($result === false || $httpCode !== 200) return false;
    return json_decode($result, true);
}

$error   = '';
$success = '';
if (isset($_POST['save'])) {
    $data = [
        'user_id'       => $user_id,
        'title'         => $_POST['title'] ?? '',
        'description'   => $_POST['description'] ?? '',
        'target_amount' => $_POST['target_amount'] ?? '',
        'deadline'      => $_POST['deadline'] ?? ''
    ];
    $response = callBackend('goals/create_goal.php', $data);
    if ($response === false) {
        $error = '❌ Gagal konek ke backend. Pastikan backend sedang berjalan.';
    } elseif ($response && $response['status'] === true) {
        $success = '✅ Goals berhasil dibuat!';
    } else {
        $error = '❌ Gagal membuat goals: ' . htmlspecialchars($response['message'] ?? 'Unknown error');
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Goals – Nabung Bareng</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --green-d: #0a5c36; --green-m: #1a8c56; --green-l: #22c573; --green-x: #6effc2; --ink: #0d1a13; --muted: #6b8f7a; --border: #d8ede3; --cream: #f7faf8; --card: #ffffff; --danger: #e53e3e; --sidebar: 240px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--ink); min-height: 100vh; display: flex; }

        .sidebar { width: var(--sidebar); min-height: 100vh; background: var(--green-d); display: flex; flex-direction: column; padding: 28px 0; position: fixed; top: 0; left: 0; z-index: 100; }
        .sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 0 24px 32px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 24px; }
        .sidebar-logo-icon { width: 40px; height: 40px; background: linear-gradient(135deg, var(--green-l), var(--green-x)); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .sidebar-logo-text { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 800; color: #fff; line-height: 1.2; }
        .sidebar-logo-text span { display: block; font-size: 11px; font-weight: 400; color: rgba(255,255,255,0.5); }
        .sidebar-nav { flex: 1; padding: 0 12px; }
        .nav-section-label { font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 1px; padding: 0 12px; margin-bottom: 8px; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 11px 12px; border-radius: 12px; text-decoration: none; color: rgba(255,255,255,0.65); font-size: 14px; font-weight: 500; margin-bottom: 4px; transition: all 0.15s; }
        .nav-link:hover  { background: rgba(255,255,255,0.08); color: #fff; }
        .nav-link.active { background: rgba(255,255,255,0.15); color: #fff; font-weight: 600; }
        .nav-icon { font-size: 18px; width: 22px; text-align: center; }
        .sidebar-footer { padding: 16px 12px 0; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-user { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px; text-decoration: none; transition: background 0.15s; }
        .sidebar-user:hover { background: rgba(255,255,255,0.08); }
        .sidebar-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--green-l), var(--green-x)); display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .sidebar-user-name { font-size: 13px; font-weight: 600; color: #fff; }
        .sidebar-user-role { font-size: 11px; color: rgba(255,255,255,0.5); }

        .main { margin-left: var(--sidebar); flex: 1; display: flex; flex-direction: column; }
        .topbar { background: var(--card); border-bottom: 1px solid var(--border); padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .topbar-title { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; }

        .page { padding: 32px; flex: 1; display: flex; justify-content: center; }
        .form-card { background: var(--card); border-radius: 20px; border: 1px solid var(--border); padding: 40px; width: 100%; max-width: 560px; height: fit-content; animation: fadeUp 0.4s ease both; }
        .form-card-title { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800; margin-bottom: 8px; }
        .form-card-desc  { font-size: 14px; color: var(--muted); margin-bottom: 32px; }

        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #e6faf2; color: var(--green-d); border: 1px solid #b7f0d4; }
        .alert-danger  { background: #fef2f2; color: var(--danger);  border: 1px solid #fecaca; }

        .field { margin-bottom: 20px; }
        .field label { display: block; font-size: 13px; font-weight: 600; color: var(--ink); margin-bottom: 7px; }
        .field input, .field textarea { width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: 10px; font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--ink); background: #fff; outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .field input:focus, .field textarea:focus { border-color: var(--green-l); box-shadow: 0 0 0 3px rgba(34,197,115,0.15); }
        .field input::placeholder, .field textarea::placeholder { color: #b0c9bc; }
        .field textarea { min-height: 100px; resize: vertical; }

        .field-hint { font-size: 11px; color: var(--muted); margin-top: 5px; }

        .btn-submit { width: 100%; padding: 14px; background: linear-gradient(90deg, var(--green-d), var(--green-m)); color: #fff; border: none; border-radius: 10px; font-family: 'DM Sans', sans-serif; font-size: 15px; font-weight: 700; cursor: pointer; transition: opacity 0.15s, transform 0.15s; margin-top: 8px; }
        .btn-submit:hover { opacity: 0.92; transform: translateY(-1px); }

        .btn-back { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--muted); text-decoration: none; margin-bottom: 20px; }
        .btn-back:hover { color: var(--ink); }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">🪙</div>
        <div class="sidebar-logo-text">Nabung Bareng<span>Saving Together</span></div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>
        <a href="index.php"       class="nav-link"><span class="nav-icon">🏠</span> Beranda</a>
        <a href="dashboard.php"   class="nav-link"><span class="nav-icon">📊</span> Dashboard</a>
        <a href="goals.php"       class="nav-link"><span class="nav-icon">🎯</span> Goals</a>
        <a href="transaksi.php"   class="nav-link"><span class="nav-icon">💸</span> Transaksi</a>
        <a href="create_goal.php" class="nav-link active"><span class="nav-icon">➕</span> Buat Goal</a>
        <div class="nav-section-label" style="margin-top:20px">Akun</div>
        <a href="profile.php" class="nav-link"><span class="nav-icon">👤</span> Profile</a>
        <a href="logout.php"  class="nav-link"><span class="nav-icon">🚪</span> Logout</a>
    </nav>
    <div class="sidebar-footer">
        <a href="profile.php" class="sidebar-user">
            <div class="sidebar-avatar">👤</div>
            <div>
                <div class="sidebar-user-name"><?= htmlspecialchars($nama) ?></div>
                <div class="sidebar-user-role">⭐ Premium Member</div>
            </div>
        </a>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div class="topbar-title">Buat Goals Baru</div>
    </div>
    <div class="page">
        <div class="form-card">
            <a href="goals.php" class="btn-back">← Kembali ke Goals</a>
            <div class="form-card-title">🎯 Goals Baru</div>
            <div class="form-card-desc">Tentukan target tabunganmu dan mulai menabung bersama!</div>

            <?php if ($error):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?> <a href="goals.php" style="margin-left:8px;color:var(--green-d);font-weight:700">Lihat Goals →</a></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label>Nama Goals</label>
                    <input type="text" name="title" placeholder="Contoh: Beli Laptop Baru" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>Deskripsi Goals <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
                    <textarea name="description" placeholder="Jelaskan detail goals kamu..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="field">
                    <label>Target Tabungan (Rp)</label>
                    <input type="number" name="target_amount" placeholder="Contoh: 5000000" value="<?= htmlspecialchars($_POST['target_amount'] ?? '') ?>" min="1000" required>
                    <div class="field-hint">Minimal Rp1.000</div>
                </div>
                <div class="field">
                    <label>Deadline</label>
                    <input type="date" name="deadline" value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>
                <button type="submit" name="save" class="btn-submit">🚀 Buat Goals</button>
            </form>
        </div>
    </div>
</main>
</body>
</html>