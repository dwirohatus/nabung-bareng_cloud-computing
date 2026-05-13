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

$alertMsg  = '';
$alertType = '';

if (isset($_POST['delete_id'])) {
    $delete_id = (int) $_POST['delete_id'];
    $response  = callBackend('goals/delete_goal.php', ['id' => $delete_id]);
    if ($response === false) {
        $alertMsg  = 'Gagal konek ke backend.';
        $alertType = 'danger';
    } else {
        $alertMsg  = $response['status'] === true
            ? 'Goals berhasil dihapus.'
            : htmlspecialchars($response['message'] ?? 'Gagal menghapus goal.');
        $alertType = $response['status'] === true ? 'success' : 'danger';
    }
}

$response = callBackend('goals/get_goals.php?user_id=' . $user_id);
$goals    = $response && isset($response['data']) ? $response['data'] : [];
$total    = count($goals);

/* Hitung ringkasan */
$totalTabungan = 0;
$totalTarget   = 0;
foreach ($goals as $g) {
    $totalTabungan += (int)($g['current_amount'] ?? 0);
    $totalTarget   += (int)($g['target_amount']  ?? 0);
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Goals – Nabung Bareng</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --sidebar-w:   248px;
    --topbar-h:    64px;

    --forest:      #0b3d24;
    --forest-mid:  #145c38;
    --green:       #18a75e;
    --green-lt:    #d6f5e6;
    --green-text:  #0e7a43;

    --ink:         #0d1f14;
    --muted:       #5a7a68;
    --hint:        #94b0a0;
    --border:      #e2ebe5;
    --surface:     #ffffff;
    --bg:          #f4f6f5;

    --danger:      #dc2626;
    --danger-bg:   #fef2f2;
    --danger-bd:   #fecaca;
    --success-bg:  #f0fdf4;
    --success-bd:  #bbf7d0;
    --success-text:#15803d;

    --amber:       #f59e0b;
    --amber-bg:    #fef3c7;
    --blue:        #3b82f6;
    --blue-bg:     #eff6ff;

    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 18px;
    --r-xl: 24px;

    --shadow-hover: 0 8px 28px rgba(11,61,36,.1);
}

html { font-size: 15px; }
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--ink);
    display: flex;
    min-height: 100vh;
    -webkit-font-smoothing: antialiased;
}
a { text-decoration: none; color: inherit; }

.sidebar {
    width: var(--sidebar-w);
    min-height: 100vh;
    background: var(--forest);
    position: fixed;
    top: 0; left: 0;
    display: flex; flex-direction: column;
    padding: 0;
    z-index: 100;
    overflow: hidden;
}

.sidebar::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 160% 60% at 50% -10%, rgba(24,167,94,.18) 0%, transparent 70%),
        radial-gradient(ellipse 120% 80% at 110% 110%, rgba(24,167,94,.1) 0%, transparent 60%);
    pointer-events: none;
}

.sidebar-inner {
    position: relative; z-index: 1;
    display: flex; flex-direction: column;
    height: 100%; padding: 20px 14px 24px;
}

.logo {
    display: flex; align-items: center; gap: 11px;
    padding: 6px 6px 22px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    margin-bottom: 20px;
}

.logo-mark {
    width: 38px; height: 38px;
    background: var(--green); border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(24,167,94,.4);
}
.logo-mark i { font-size: 20px; color: #fff; }

.logo-name  { font-size: 15px; font-weight: 800; color: #fff; letter-spacing: -.3px; line-height: 1.1; }
.logo-sub   { font-size: 10.5px; color: rgba(255,255,255,.38); font-weight: 400; margin-top: 2px; }

.nav-section { margin-bottom: 6px; }
.nav-label {
    font-size: 9.5px; font-weight: 700;
    color: rgba(255,255,255,.28);
    letter-spacing: 1.1px; text-transform: uppercase;
    padding: 4px 8px 8px;
}

.nav-link {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 10px; border-radius: var(--r-sm);
    color: rgba(255,255,255,.6);
    font-size: 13.5px; font-weight: 500;
    margin-bottom: 2px;
    transition: background .15s, color .15s;
}
.nav-link i { font-size: 18px; flex-shrink: 0; }
.nav-link:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.9); }
.nav-link.active {
    background: var(--green); color: #fff;
    box-shadow: 0 3px 10px rgba(24,167,94,.35);
}

.nav-badge {
    margin-left: auto;
    background: rgba(255,255,255,.18); color: #fff;
    font-size: 10px; font-weight: 700;
    padding: 2px 7px; border-radius: 99px;
    min-width: 20px; text-align: center;
}
.nav-link.active .nav-badge { background: rgba(255,255,255,.25); }

.sidebar-footer {
    margin-top: auto; padding-top: 14px;
    border-top: 1px solid rgba(255,255,255,.07);
}

.user-chip {
    display: flex; align-items: center; gap: 10px;
    padding: 10px; border-radius: var(--r-sm);
    background: rgba(255,255,255,.06);
}

.user-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--green);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0;
}

.user-name { font-size: 12.5px; font-weight: 600; color: rgba(255,255,255,.85); }
.user-role { font-size: 10.5px; color: rgba(255,255,255,.35); margin-top: 1px; }

.main {
    margin-left: var(--sidebar-w);
    flex: 1; display: flex; flex-direction: column; min-height: 100vh;
}

.topbar {
    height: var(--topbar-h);
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0 28px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 50;
}

.topbar-left { display: flex; flex-direction: column; gap: 1px; }
.topbar-title { font-size: 17px; font-weight: 800; color: var(--ink); letter-spacing: -.4px; }
.topbar-sub   { font-size: 12px; color: var(--muted); }

.btn-primary {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 18px;
    background: var(--green); color: #fff;
    border: none; border-radius: var(--r-sm);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; font-weight: 700;
    cursor: pointer; transition: background .15s, transform .15s;
    box-shadow: 0 3px 10px rgba(24,167,94,.3);
}
.btn-primary i { font-size: 16px; }
.btn-primary:hover { background: var(--green-text); transform: translateY(-1px); }

.page { padding: 24px 28px 40px; flex: 1; }

.alert {
    display: flex; align-items: center; gap: 9px;
    padding: 12px 16px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 500;
    margin-bottom: 20px;
}
.alert-success { background: var(--success-bg); color: var(--success-text); border: 1px solid var(--success-bd); }
.alert-danger  { background: var(--danger-bg);  color: var(--danger);       border: 1px solid var(--danger-bd); }
.alert i { font-size: 17px; flex-shrink: 0; }

.summary-bar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 22px;
    animation: fadeUp .35s ease both;
}

.sum-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 16px 18px;
    display: flex; align-items: center; gap: 13px;
}

.sum-icon {
    width: 40px; height: 40px; border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sum-icon i { font-size: 20px; }
.sum-icon.green  { background: var(--green-lt);  color: var(--green-text); }
.sum-icon.amber  { background: var(--amber-bg);  color: var(--amber); }
.sum-icon.blue   { background: var(--blue-bg);   color: var(--blue); }

.sum-label { font-size: 11px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .6px; }
.sum-val   { font-size: 17px; font-weight: 800; color: var(--ink); letter-spacing: -.4px; margin-top: 2px; }

.goals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}

.goal-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-xl);
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
    animation: fadeUp .4s ease both;
}

.goal-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}

/* Card top accent bar */
.goal-card-top {
    height: 4px;
    background: linear-gradient(90deg, var(--green), #5de8a8);
}

.goal-card-header {
    padding: 18px 20px 14px;
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 10px;
}

.goal-card-icon {
    width: 44px; height: 44px;
    background: var(--green-lt);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 21px; flex-shrink: 0;
}

.goal-card-info { flex: 1; min-width: 0; }

.goal-card-title {
    font-size: 15px; font-weight: 800; color: var(--ink);
    letter-spacing: -.3px; margin-bottom: 3px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.goal-card-deadline {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; color: var(--muted); font-weight: 500;
}
.goal-card-deadline i { font-size: 12px; }

.goal-pct-badge {
    font-size: 20px; font-weight: 800;
    color: var(--green-text);
    letter-spacing: -.5px;
    flex-shrink: 0;
}

.goal-card-body { padding: 0 20px 18px; }

.progress-track {
    height: 6px; background: var(--border);
    border-radius: 99px; overflow: hidden;
    margin-bottom: 12px;
}

.progress-fill {
    height: 100%; border-radius: 99px;
    background: linear-gradient(90deg, var(--green), #5de8a8);
    transition: width .6s ease;
}

.goal-amounts {
    display: flex; align-items: baseline; justify-content: space-between;
    margin-bottom: 16px;
}

.goal-current {
    font-size: 18px; font-weight: 800;
    color: var(--green-text); letter-spacing: -.4px;
}

.goal-target-txt {
    font-size: 12px; color: var(--hint);
}

.goal-actions { display: flex; gap: 8px; }

.btn-detail {
    flex: 1; padding: 9px 12px;
    background: var(--forest); color: #fff;
    border: none; border-radius: var(--r-sm);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 12.5px; font-weight: 700;
    cursor: pointer; text-align: center;
    display: flex; align-items: center; justify-content: center; gap: 5px;
    transition: background .15s;
}
.btn-detail i { font-size: 14px; }
.btn-detail:hover { background: var(--green-text); }

.btn-hapus {
    padding: 9px 13px;
    background: var(--danger-bg); color: var(--danger);
    border: 1px solid var(--danger-bd);
    border-radius: var(--r-sm);
    font-size: 13px; font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s, color .15s;
}
.btn-hapus i { font-size: 15px; }
.btn-hapus:hover { background: var(--danger); color: #fff; border-color: var(--danger); }

/* EMPTY STATE */
.empty-state {
    text-align: center;
    padding: 70px 40px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-xl);
    animation: fadeUp .4s ease both;
}

.empty-icon-wrap {
    width: 72px; height: 72px;
    background: var(--green-lt);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
}
.empty-icon-wrap i { font-size: 34px; color: var(--green-text); }

.empty-title {
    font-size: 20px; font-weight: 800; color: var(--ink);
    letter-spacing: -.4px; margin-bottom: 8px;
}

.empty-desc {
    font-size: 13.5px; color: var(--muted);
    line-height: 1.6; margin-bottom: 24px;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-inner">

        <div class="logo">
            <div class="logo-mark">
                <i class="ti ti-pig-money"></i>
            </div>
            <div>
                <div class="logo-name">Nabung Bareng</div>
                <div class="logo-sub">Saving Together</div>
            </div>
        </div>

        <div class="nav-section">
            <div class="nav-label">Menu Utama</div>

            <a href="dashboard.php" class="nav-link">
                <i class="ti ti-layout-dashboard"></i> Dashboard
            </a>
            <a href="goals.php" class="nav-link active">
                <i class="ti ti-target"></i> Goals
                <?php if ($total > 0): ?>
                    <span class="nav-badge"><?= $total ?></span>
                <?php endif; ?>
            </a>
            <a href="transaksi.php" class="nav-link">
                <i class="ti ti-arrows-exchange-2"></i> Transaksi
            </a>
            <a href="create_goal.php" class="nav-link">
                <i class="ti ti-circle-plus"></i> Buat Goal
            </a>
        </div>

        <div class="nav-section" style="margin-top:8px;">
            <div class="nav-label">Akun</div>
            <a href="profile.php" class="nav-link">
                <i class="ti ti-user-circle"></i> Profile
            </a>
            <a href="logout.php" class="nav-link">
                <i class="ti ti-logout"></i> Logout
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="user-chip">
                <div class="user-avatar">
                    <?= mb_strtoupper(mb_substr($nama, 0, 1)) ?>
                </div>
                <div>
                    <div class="user-name"><?= htmlspecialchars($nama) ?></div>
                    <div class="user-role">Member</div>
                </div>
            </div>
        </div>

    </div>
</aside>

<main class="main">

    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-title">Goals Tabungan</div>
            <div class="topbar-sub"><?= $total ?> goals aktif</div>
        </div>
        <a href="create_goal.php" class="btn-primary">
            <i class="ti ti-plus"></i> Buat Goal Baru
        </a>
    </div>

    <div class="page">

        <!-- ALERT -->
        <?php if ($alertMsg): ?>
        <div class="alert alert-<?= $alertType ?>">
            <i class="ti ti-<?= $alertType === 'success' ? 'circle-check' : 'alert-circle' ?>"></i>
            <?= $alertMsg ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($goals)): ?>

        <div class="summary-bar">
            <div class="sum-card">
                <div class="sum-icon green"><i class="ti ti-target"></i></div>
                <div>
                    <div class="sum-label">Total Goals</div>
                    <div class="sum-val"><?= $total ?> goals</div>
                </div>
            </div>
            <div class="sum-card">
                <div class="sum-icon green"><i class="ti ti-wallet"></i></div>
                <div>
                    <div class="sum-label">Terkumpul</div>
                    <div class="sum-val">Rp<?= number_format($totalTabungan, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="sum-card">
                <div class="sum-icon amber"><i class="ti ti-flag"></i></div>
                <div>
                    <div class="sum-label">Total Target</div>
                    <div class="sum-val">Rp<?= number_format($totalTarget, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <div class="goals-grid">
            <?php foreach ($goals as $i => $goal):
                $current = (int)($goal['current_amount'] ?? 0);
                $target  = (int)($goal['target_amount']  ?? 1);
                $persen  = $target > 0 ? min(round(($current / $target) * 100), 100) : 0;
            ?>
            <div class="goal-card" style="animation-delay:<?= $i * 0.07 ?>s">
                <div class="goal-card-top"></div>

                <div class="goal-card-header">
                    <div class="goal-card-icon">🎯</div>
                    <div class="goal-card-info">
                        <div class="goal-card-title"><?= htmlspecialchars($goal['title']) ?></div>
                        <div class="goal-card-deadline">
                            <i class="ti ti-calendar"></i>
                            <?= htmlspecialchars($goal['deadline'] ?? '-') ?>
                        </div>
                    </div>
                    <div class="goal-pct-badge"><?= $persen ?>%</div>
                </div>

                <div class="goal-card-body">
                    <div class="progress-track">
                        <div class="progress-fill" style="width:<?= $persen ?>%"></div>
                    </div>

                    <div class="goal-amounts">
                        <div class="goal-current">
                            Rp<?= number_format($current, 0, ',', '.') ?>
                        </div>
                        <div class="goal-target-txt">
                            dari Rp<?= number_format($target, 0, ',', '.') ?>
                        </div>
                    </div>

                    <div class="goal-actions">
                        <a href="detail_goal.php?id=<?= $goal['id'] ?>" class="btn-detail">
                            <i class="ti ti-eye"></i> Lihat Detail
                        </a>
                        <form method="POST" onsubmit="return confirm('Yakin ingin menghapus goal ini?')">
                            <input type="hidden" name="delete_id" value="<?= $goal['id'] ?>">
                            <button type="submit" class="btn-hapus" title="Hapus goal">
                                <i class="ti ti-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php else: ?>

        <div class="empty-state">
            <div class="empty-icon-wrap">
                <i class="ti ti-target-off"></i>
            </div>
            <div class="empty-title">Belum ada goals</div>
            <div class="empty-desc">
                Yuk buat goals pertamamu dan mulai<br>menabung bersama orang terdekat!
            </div>
            <a href="create_goal.php" class="btn-primary" style="display:inline-flex;">
                <i class="ti ti-plus"></i> Buat Goal Pertama
            </a>
        </div>

        <?php endif; ?>

    </div>
</main>

</body>
</html>