<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;

$nama = $_SESSION['name'] ?? ($_SESSION['nama'] ?? 'Sobat Nabung'); // fallback (support both keys)

$resProfil = @file_get_contents(
    'http://backend/api/profile/get_profile.php?user_id=' . $user_id
);

if ($resProfil !== false) {
    $decodedProfil = json_decode($resProfil, true);
    if ($decodedProfil && isset($decodedProfil['data']['name'])) {
        $nama = $decodedProfil['data']['name'];
        $_SESSION['name'] = $nama; // perbarui session agar tetap sinkron
    }
}

$totalGoals    = 0;
$totalTabungan = 0;
$goals         = [];

$resGoals = @file_get_contents(
    'http://backend/api/goals/get_goals.php?user_id=' . $user_id
);

if ($resGoals !== false) {
    $decodedGoals = json_decode($resGoals, true);
    if ($decodedGoals && isset($decodedGoals['data'])) {
        $goals         = $decodedGoals['data'];
        $totalGoals    = count($goals);
        foreach ($goals as $goal) {
            $totalTabungan += (int)$goal['current_amount'];
        }
    }
}

$transaksi = [];

$resTx = @file_get_contents(
    'http://backend/api/transaksi/get_transaksi.php?user_id=' . $user_id
);

if ($resTx !== false) {
    $decodedTx = json_decode($resTx, true);
    if ($decodedTx && isset($decodedTx['data'])) {
        $transaksi = $decodedTx['data'];
    }
}

$totalTx = count($transaksi);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Nabung Bareng</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">

<style>

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --sidebar-w: 248px;
    --topbar-h: 64px;

    --c-bg:         #f4f6f5;
    --c-surface:    #ffffff;
    --c-border:     #e2ebe5;
    --c-border-mid: #c8d9ce;

    --c-forest:     #0b3d24;
    --c-forest-mid: #145c38;
    --c-green:      #18a75e;
    --c-green-lt:   #d6f5e6;
    --c-green-text: #0e7a43;

    --c-text:       #0d1f14;
    --c-muted:      #5a7a68;
    --c-hint:       #94b0a0;

    --c-amber:      #f59e0b;
    --c-amber-bg:   #fef3c7;
    --c-blue:       #3b82f6;
    --c-blue-bg:    #eff6ff;
    --c-purple:     #8b5cf6;
    --c-purple-bg:  #f5f3ff;

    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 20px;
    --r-xl: 24px;

    --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow-md: 0 4px 16px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
}

html { font-size: 15px; }

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--c-bg);
    color: var(--c-text);
    display: flex;
    min-height: 100vh;
    -webkit-font-smoothing: antialiased;
}

a { text-decoration: none; color: inherit; }

.sidebar {
    width: var(--sidebar-w);
    min-height: 100vh;
    background: var(--c-forest);
    position: fixed;
    top: 0; left: 0;
    display: flex;
    flex-direction: column;
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
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 20px 14px 24px;
}

.logo {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 6px 6px 22px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    margin-bottom: 20px;
}

.logo-mark {
    width: 38px; height: 38px;
    background: var(--c-green);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(24,167,94,.4);
}

.logo-mark i { font-size: 20px; color: #fff; }

.logo-words { flex: 1; }
.logo-name {
    font-size: 15px; font-weight: 800; color: #fff;
    letter-spacing: -.3px; line-height: 1.1;
}
.logo-sub {
    font-size: 10.5px; color: rgba(255,255,255,.38);
    font-weight: 400; margin-top: 2px;
    letter-spacing: .2px;
}

.nav-section { margin-bottom: 6px; }
.nav-label {
    font-size: 9.5px; font-weight: 700;
    color: rgba(255,255,255,.28);
    letter-spacing: 1.1px;
    text-transform: uppercase;
    padding: 4px 8px 8px;
}

.nav-link {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 10px;
    border-radius: var(--r-sm);
    color: rgba(255,255,255,.6);
    font-size: 13.5px; font-weight: 500;
    margin-bottom: 2px;
    transition: background .15s, color .15s;
    position: relative;
}

.nav-link i { font-size: 18px; flex-shrink: 0; }

.nav-link:hover {
    background: rgba(255,255,255,.07);
    color: rgba(255,255,255,.9);
}

.nav-link.active {
    background: var(--c-green);
    color: #fff;
    box-shadow: 0 3px 10px rgba(24,167,94,.35);
}

.nav-link.active i { color: #fff; }

.nav-badge {
    margin-left: auto;
    background: rgba(255,255,255,.18);
    color: #fff;
    font-size: 10px; font-weight: 700;
    padding: 2px 7px;
    border-radius: 99px;
    min-width: 20px; text-align: center;
}

.nav-link.active .nav-badge { background: rgba(255,255,255,.25); }

.sidebar-footer {
    margin-top: auto;
    padding-top: 14px;
    border-top: 1px solid rgba(255,255,255,.07);
}

.user-chip {
    display: flex; align-items: center; gap: 10px;
    padding: 10px;
    border-radius: var(--r-sm);
    background: rgba(255,255,255,.06);
}

.user-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: var(--c-green);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff;
    flex-shrink: 0;
}

.user-name {
    font-size: 12.5px; font-weight: 600; color: rgba(255,255,255,.85);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 120px;
}

.user-role {
    font-size: 10.5px; color: rgba(255,255,255,.35);
    margin-top: 1px;
}
.main {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.topbar {
    height: var(--topbar-h);
    background: var(--c-surface);
    border-bottom: 1px solid var(--c-border);
    padding: 0 28px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 50;
}

.topbar-left { display: flex; align-items: center; gap: 10px; }

.topbar-title {
    font-size: 17px; font-weight: 800;
    color: var(--c-text);
    letter-spacing: -.4px;
}

.topbar-greeting {
    font-size: 13px; color: var(--c-muted);
    font-weight: 400;
}

.topbar-right { display: flex; align-items: center; gap: 10px; }

.topbar-date {
    display: flex; align-items: center; gap: 6px;
    font-size: 12.5px; font-weight: 500;
    color: var(--c-muted);
    background: var(--c-bg);
    border: 1px solid var(--c-border);
    padding: 6px 12px;
    border-radius: 99px;
}
.topbar-date i { font-size: 14px; color: var(--c-green); }

.topbar-btn {
    width: 36px; height: 36px;
    border-radius: 50%;
    border: 1px solid var(--c-border);
    background: var(--c-surface);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--c-muted);
    transition: background .15s;
}
.topbar-btn:hover { background: var(--c-bg); color: var(--c-green); }
.topbar-btn i { font-size: 17px; }

.page {
    padding: 24px 28px 40px;
    flex: 1;
}

.stats {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr;
    gap: 14px;
    margin-bottom: 24px;
}

.stat {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-xl);
    padding: 20px 22px;
    transition: box-shadow .2s;
    position: relative;
    overflow: hidden;
}

.stat:hover { box-shadow: var(--shadow-md); }

.stat.featured {
    background: var(--c-forest);
    border-color: transparent;
    color: #fff;
}

.stat.featured::after {
    content: '';
    position: absolute;
    bottom: -20px; right: -20px;
    width: 100px; height: 100px;
    border-radius: 50%;
    background: rgba(24,167,94,.2);
    pointer-events: none;
}

.stat-icon-wrap {
    width: 38px; height: 38px;
    border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
}

.stat-icon-wrap.green  { background: var(--c-green-lt);   color: var(--c-green-text); }
.stat-icon-wrap.amber  { background: var(--c-amber-bg);   color: var(--c-amber); }
.stat-icon-wrap.blue   { background: var(--c-blue-bg);    color: var(--c-blue); }
.stat-icon-wrap.purple { background: var(--c-purple-bg);  color: var(--c-purple); }
.stat-icon-wrap.white  { background: rgba(255,255,255,.12); color: rgba(255,255,255,.9); }

.stat-icon-wrap i { font-size: 19px; }

.stat-label {
    font-size: 10.5px; font-weight: 700;
    letter-spacing: .8px; text-transform: uppercase;
    color: var(--c-muted);
    margin-bottom: 6px;
}

.stat.featured .stat-label { color: rgba(255,255,255,.5); }

.stat-value {
    font-size: 26px; font-weight: 800;
    color: var(--c-text);
    letter-spacing: -1px; line-height: 1;
    margin-bottom: 6px;
}

.stat.featured .stat-value { color: #fff; font-size: 22px; }

.stat-sub {
    font-size: 11.5px; color: var(--c-hint);
}

.stat.featured .stat-sub { color: rgba(255,255,255,.45); }

.stat-trend {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 11px; font-weight: 600;
    padding: 3px 8px; border-radius: 99px;
    margin-top: 6px;
}

.stat-trend.up   { background: var(--c-green-lt); color: var(--c-green-text); }
.stat-trend.zero { background: var(--c-bg); color: var(--c-muted); }

.row-2 {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.section {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-xl);
    overflow: hidden;
}

.section-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--c-border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--c-surface);
}

.section-title {
    font-size: 13.5px; font-weight: 800;
    color: var(--c-text);
    letter-spacing: -.2px;
}

.see-all {
    font-size: 12px; font-weight: 600;
    color: var(--c-green);
    display: flex; align-items: center; gap: 3px;
    transition: gap .15s;
}
.see-all:hover { gap: 6px; }
.see-all i { font-size: 13px; }

.goal-row {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--c-border);
    transition: background .15s;
    cursor: pointer;
}

.goal-row:last-child { border-bottom: none; }
.goal-row:hover { background: #fbfdfb; }

.goal-avatar {
    width: 42px; height: 42px;
    border-radius: 13px;
    background: var(--c-green-lt);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.goal-info { flex: 1; min-width: 0; }

.goal-title {
    font-size: 13.5px; font-weight: 700;
    color: var(--c-text);
    margin-bottom: 7px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.progress-track {
    height: 5px;
    background: var(--c-border);
    border-radius: 99px; overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--c-green), #5de8a8);
    border-radius: 99px;
    transition: width .6s ease;
}

.goal-meta {
    font-size: 11px; color: var(--c-hint); margin-top: 5px;
    display: flex; align-items: center; gap: 4px;
}
.goal-meta i { font-size: 11px; }

.goal-right { text-align: right; flex-shrink: 0; }

.goal-pct {
    font-size: 15px; font-weight: 800;
    color: var(--c-green-text);
    letter-spacing: -.3px;
}

.goal-target {
    font-size: 11px; color: var(--c-hint);
    margin-top: 2px;
}

.add-goal-row {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 14px 20px;
    color: var(--c-hint);
    font-size: 12.5px; font-weight: 600;
    border: none; background: none; width: 100%; cursor: pointer;
    transition: color .15s;
    border-top: 1px dashed var(--c-border);
}

.add-goal-row:hover { color: var(--c-green); }
.add-goal-row i { font-size: 15px; }

.summary-body { padding: 16px 18px; }

.sum-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 12px;
    border-radius: var(--r-md);
    margin-bottom: 8px;
    background: var(--c-bg);
}

.sum-dot {
    width: 8px; height: 8px;
    border-radius: 50%; flex-shrink: 0;
}

.sum-label { font-size: 11.5px; color: var(--c-muted); }
.sum-val {
    font-size: 14.5px; font-weight: 800;
    color: var(--c-text);
    margin-top: 1px;
    letter-spacing: -.3px;
}

.chart-label {
    font-size: 10px; font-weight: 700; letter-spacing: .8px;
    text-transform: uppercase; color: var(--c-hint);
    margin: 12px 0 8px;
}

.mini-bars {
    display: flex; align-items: flex-end; gap: 4px;
    height: 64px;
}

.mini-bar {
    flex: 1;
    border-radius: 4px 4px 0 0;
    background: var(--c-border);
    min-height: 6px;
    transition: background .2s;
}

.mini-bar.hi { background: var(--c-green); }
.mini-bar:hover { background: var(--c-green-text); }

.section-full { margin-bottom: 0; }

.tx-empty {
    display: flex; flex-direction: column; align-items: center;
    padding: 40px 20px;
    color: var(--c-hint);
    gap: 8px;
}

.tx-empty i { font-size: 36px; opacity: .4; }
.tx-empty p { font-size: 13px; font-weight: 500; }

.tx-row {
    display: flex; align-items: center; gap: 14px;
    padding: 13px 20px;
    border-bottom: 1px solid var(--c-border);
    transition: background .15s;
}

.tx-row:last-child { border-bottom: none; }
.tx-row:hover { background: #fbfdfb; }

.tx-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: var(--c-green-lt);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.tx-icon i { font-size: 17px; color: var(--c-green-text); }

.tx-name {
    font-size: 13px; font-weight: 700; color: var(--c-text);
}

.tx-date {
    font-size: 11px; color: var(--c-hint);
    margin-top: 2px;
}

.tx-amount {
    margin-left: auto;
    font-size: 14px; font-weight: 800;
    color: var(--c-green-text);
    letter-spacing: -.3px;
}

.empty {
    padding: 36px 20px;
    text-align: center;
    color: var(--c-hint);
    font-size: 13px;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.empty i { font-size: 30px; opacity: .35; }

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

.stats > * {
    animation: fadeUp .4s ease both;
}
.stats > *:nth-child(1) { animation-delay: .05s; }
.stats > *:nth-child(2) { animation-delay: .10s; }
.stats > *:nth-child(3) { animation-delay: .15s; }
.stats > *:nth-child(4) { animation-delay: .20s; }

.row-2 > *,
.section-full {
    animation: fadeUp .4s ease .25s both;
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
            <div class="logo-words">
                <div class="logo-name">Nabung Bareng</div>
                <div class="logo-sub">Saving Together</div>
            </div>
        </div>

        <div class="nav-section">
            <div class="nav-label">Menu Utama</div>

            <a href="dashboard.php" class="nav-link active">
                <i class="ti ti-layout-dashboard"></i>
                Dashboard
            </a>

            <a href="goals.php" class="nav-link">
                <i class="ti ti-target"></i>
                Goals
                <?php if ($totalGoals > 0): ?>
                    <span class="nav-badge"><?= $totalGoals ?></span>
                <?php endif; ?>
            </a>

            <a href="transaksi.php" class="nav-link">
                <i class="ti ti-arrows-exchange-2"></i>
                Transaksi
            </a>

            <a href="create_goal.php" class="nav-link">
                <i class="ti ti-circle-plus"></i>
                Buat Goal
            </a>
        </div>

        <div class="nav-section" style="margin-top:8px;">
            <div class="nav-label">Akun</div>

            <a href="profile.php" class="nav-link">
                <i class="ti ti-user-circle"></i>
                Profile
            </a>

            <a href="logout.php" class="nav-link">
                <i class="ti ti-logout"></i>
                Logout
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

<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="topbar-title">Dashboard</div>
                <div class="topbar-greeting">Selamat datang, <?= htmlspecialchars($nama) ?> 👋</div>
            </div>
        </div>

        <div class="topbar-right">
            <div class="topbar-date">
                <i class="ti ti-calendar"></i>
                <?= date('d F Y') ?>
            </div>

            <a href="create_goal.php" class="topbar-btn" title="Buat Goal Baru">
                <i class="ti ti-plus"></i>
            </a>

            <a href="profile.php" class="topbar-btn" title="Profile">
                <i class="ti ti-user"></i>
            </a>
        </div>
    </div>

    <!-- PAGE -->
    <div class="page">

        <!-- STAT CARDS -->
        <div class="stats">

            <!-- Total Tabungan (featured) -->
            <div class="stat featured">
                <div class="stat-icon-wrap white">
                    <i class="ti ti-wallet"></i>
                </div>
                <div class="stat-label">Total Tabungan</div>
                <div class="stat-value">
                    Rp<?= number_format($totalTabungan, 0, ',', '.') ?>
                </div>
                <div class="stat-sub">Dari semua goals aktif</div>
            </div>

            <!-- Goals Aktif -->
            <div class="stat">
                <div class="stat-icon-wrap green">
                    <i class="ti ti-target"></i>
                </div>
                <div class="stat-label">Goals Aktif</div>
                <div class="stat-value"><?= $totalGoals ?></div>
                <div class="stat-sub">Goals berjalan</div>
                <div class="stat-trend <?= $totalGoals > 0 ? 'up' : 'zero' ?>">
                    <i class="ti ti-<?= $totalGoals > 0 ? 'trending-up' : 'minus' ?>"></i>
                    <?= $totalGoals > 0 ? 'On track' : 'Belum ada' ?>
                </div>
            </div>

            <!-- Transaksi -->
            <div class="stat">
                <div class="stat-icon-wrap amber">
                    <i class="ti ti-arrows-exchange-2"></i>
                </div>
                <div class="stat-label">Transaksi</div>
                <div class="stat-value"><?= $totalTx ?></div>
                <div class="stat-sub">Total transaksi</div>
                <div class="stat-trend zero">
                    <i class="ti ti-clock"></i>
                    Bulan ini
                </div>
            </div>

            <!-- User -->
            <div class="stat">
                <div class="stat-icon-wrap purple">
                    <i class="ti ti-user-circle"></i>
                </div>
                <div class="stat-label">Pengguna</div>
                <div class="stat-value" style="font-size:20px; letter-spacing:-.2px;">
                    <?= htmlspecialchars(mb_strimwidth($nama, 0, 12, '…')) ?>
                </div>
                <div class="stat-sub">Akun aktif</div>
            </div>

        </div>

        <!-- ROW 2-COL: Goals + Summary -->
        <div class="row-2">

            <!-- GOALS -->
            <div class="section">
                <div class="section-head">
                    <span class="section-title">Goals Tabungan</span>
                    <a href="goals.php" class="see-all">
                        Lihat semua <i class="ti ti-arrow-right"></i>
                    </a>
                </div>

                <?php if (empty($goals)): ?>
                    <div class="empty">
                        <i class="ti ti-target-off"></i>
                        <p>Belum ada goals</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($goals as $goal):
                        $persen = 0;
                        if ($goal['target_amount'] > 0) {
                            $persen = round(($goal['current_amount'] / $goal['target_amount']) * 100);
                            if ($persen > 100) $persen = 100;
                        }
                        $terkumpul = 'Rp' . number_format($goal['current_amount'], 0, ',', '.');
                    ?>
                    <a href="detail_goal.php?id=<?= $goal['id'] ?>" class="goal-row">

                        <div class="goal-avatar">🎯</div>

                        <div class="goal-info">
                            <div class="goal-title"><?= htmlspecialchars($goal['title']) ?></div>
                            <div class="progress-track">
                                <div class="progress-fill" style="width:<?= $persen ?>%"></div>
                            </div>
                            <div class="goal-meta">
                                <i class="ti ti-coin"></i>
                                <?= $terkumpul ?> terkumpul
                            </div>
                        </div>

                        <div class="goal-right">
                            <div class="goal-pct"><?= $persen ?>%</div>
                            <div class="goal-target">
                                Rp<?= number_format($goal['target_amount'], 0, ',', '.') ?>
                            </div>
                        </div>

                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>

                <a href="create_goal.php" class="add-goal-row">
                    <i class="ti ti-plus"></i>
                    Tambah Goal Baru
                </a>
            </div>

            <!-- SUMMARY WIDGET -->
            <div class="section">
                <div class="section-head">
                    <span class="section-title">Ringkasan</span>
                </div>

                <div class="summary-body">

                    <?php
                        $totalTarget = 0;
                        foreach ($goals as $g) $totalTarget += (int)$g['target_amount'];
                        $sisaTarget  = max(0, $totalTarget - $totalTabungan);
                    ?>

                    <div class="sum-item">
                        <div class="sum-dot" style="background:var(--c-green);"></div>
                        <div>
                            <div class="sum-label">Dana terkumpul</div>
                            <div class="sum-val">Rp<?= number_format($totalTabungan, 0, ',', '.') ?></div>
                        </div>
                    </div>

                    <div class="sum-item">
                        <div class="sum-dot" style="background:var(--c-amber);"></div>
                        <div>
                            <div class="sum-label">Sisa target</div>
                            <div class="sum-val">Rp<?= number_format($sisaTarget, 0, ',', '.') ?></div>
                        </div>
                    </div>

                    <div class="sum-item">
                        <div class="sum-dot" style="background:var(--c-blue);"></div>
                        <div>
                            <div class="sum-label">Jumlah goals</div>
                            <div class="sum-val"><?= $totalGoals ?> goals aktif</div>
                        </div>
                    </div>

                    <div class="chart-label">Aktivitas 6 Bulan</div>
                    <div class="mini-bars">
                        <div class="mini-bar" style="height:28%;"></div>
                        <div class="mini-bar" style="height:45%;"></div>
                        <div class="mini-bar" style="height:38%;"></div>
                        <div class="mini-bar" style="height:60%;"></div>
                        <div class="mini-bar hi" style="height:75%;"></div>
                        <div class="mini-bar hi" style="height:100%;"></div>
                    </div>

                </div>
            </div>

        </div>

        <!-- TRANSAKSI TERBARU -->
        <div class="section section-full">
            <div class="section-head">
                <span class="section-title">Transaksi Terbaru</span>
                <a href="transaksi.php" class="see-all">
                    Lihat semua <i class="ti ti-arrow-right"></i>
                </a>
            </div>

            <?php if (empty($transaksi)): ?>
                <div class="tx-empty">
                    <i class="ti ti-receipt-off"></i>
                    <p>Belum ada transaksi</p>
                </div>
            <?php else: ?>
                <?php foreach (array_slice($transaksi, 0, 5) as $tx): ?>
                <div class="tx-row">
                    <div class="tx-icon">
                        <i class="ti ti-arrow-down-circle"></i>
                    </div>
                    <div>
                        <div class="tx-name">
                            Setor — <?= htmlspecialchars($tx['goal_title']) ?>
                        </div>
                        <div class="tx-date">
                            <i class="ti ti-clock" style="font-size:11px; vertical-align:-1px;"></i>
                            <?= date('d M Y, H:i', strtotime($tx['created_at'])) ?>
                        </div>
                    </div>
                    <div class="tx-amount">
                        +Rp<?= number_format($tx['amount'], 0, ',', '.') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div><!-- /page -->

</div><!-- /main -->

</body>
</html>