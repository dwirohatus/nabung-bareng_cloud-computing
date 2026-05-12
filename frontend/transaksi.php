<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // sementara testing
    $_SESSION['user_id'] = 1;
}

$userId = $_SESSION['user_id'];
$nama   = $_SESSION['name'] ?? ($_SESSION['nama'] ?? 'Pengguna');

/* =========================
   SETOR TABUNGAN
========================= */
$alertMsg  = '';
$alertType = '';

if (isset($_POST['setor'])) {
    $data = [
        'goal_id' => $_POST['goal_id'],
        'user_id' => $userId,
        'amount'  => $_POST['amount'],
    ];
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ]
    ];
    $context = stream_context_create($options);
    $result  = @file_get_contents('http://backend_server/api/transaksi/deposit.php', false, $context);

    if ($result === false) {
        $alertMsg  = 'Gagal konek ke backend transaksi.';
        $alertType = 'danger';
    } else {
        $response  = json_decode($result, true);
        if ($response && isset($response['status']) && $response['status'] == true) {
            $alertMsg  = 'Setoran berhasil! Dana sudah masuk ke goals kamu.';
            $alertType = 'success';
        } else {
            $alertMsg  = htmlspecialchars($response['message'] ?? 'Gagal setor.');
            $alertType = 'danger';
        }
    }
}

/* =========================
   GET GOALS
========================= */
$resGoals = @file_get_contents('http://backend_server/api/goals/get_goals.php?user_id=' . $userId);
$goals    = [];
if ($resGoals !== false) {
    $decoded = json_decode($resGoals, true);
    if ($decoded && isset($decoded['data'])) {
        $goals = $decoded['data'];
    }
}

/* =========================
   GET TRANSAKSI
========================= */
$resTx     = @file_get_contents('http://backend_server/api/transaksi/get_transaksi.php?user_id=' . $userId);
$transaksi = [];
$txError   = false;
if ($resTx === false) {
    $txError = true;
} else {
    $responseTx = json_decode($resTx, true);
    $transaksi  = $responseTx['data'] ?? [];
}

$totalTx     = count($transaksi);
$totalSetor  = array_sum(array_column($transaksi, 'amount'));
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transaksi – Nabung Bareng</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --sidebar-w:  248px;
    --topbar-h:   64px;

    --forest:     #0b3d24;
    --green:      #18a75e;
    --green-lt:   #d6f5e6;
    --green-text: #0e7a43;

    --ink:        #0d1f14;
    --muted:      #5a7a68;
    --hint:       #94b0a0;
    --border:     #e2ebe5;
    --surface:    #ffffff;
    --bg:         #f4f6f5;

    --danger:     #dc2626;
    --danger-bg:  #fef2f2;
    --danger-bd:  #fecaca;
    --success-bg: #f0fdf4;
    --success-bd: #bbf7d0;
    --success-tx: #15803d;

    --amber:      #f59e0b;
    --amber-bg:   #fef3c7;
    --blue:       #3b82f6;
    --blue-bg:    #eff6ff;

    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 18px;
    --r-xl: 24px;
}

html { font-size: 15px; }
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg); color: var(--ink);
    display: flex; min-height: 100vh;
    -webkit-font-smoothing: antialiased;
}
a { text-decoration: none; color: inherit; }

/* =====================
   SIDEBAR
===================== */
.sidebar {
    width: var(--sidebar-w); min-height: 100vh;
    background: var(--forest);
    position: fixed; top: 0; left: 0;
    display: flex; flex-direction: column;
    z-index: 100; overflow: hidden;
}

.sidebar::before {
    content: '';
    position: absolute; inset: 0;
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
    width: 38px; height: 38px; background: var(--green);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; box-shadow: 0 4px 12px rgba(24,167,94,.4);
}
.logo-mark i { font-size: 20px; color: #fff; }
.logo-name { font-size: 15px; font-weight: 800; color: #fff; letter-spacing: -.3px; line-height: 1.1; }
.logo-sub  { font-size: 10.5px; color: rgba(255,255,255,.38); font-weight: 400; margin-top: 2px; }

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
    font-size: 13.5px; font-weight: 500; margin-bottom: 2px;
    transition: background .15s, color .15s;
}
.nav-link i { font-size: 18px; flex-shrink: 0; }
.nav-link:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.9); }
.nav-link.active { background: var(--green); color: #fff; box-shadow: 0 3px 10px rgba(24,167,94,.35); }
.nav-badge {
    margin-left: auto; background: rgba(255,255,255,.18); color: #fff;
    font-size: 10px; font-weight: 700; padding: 2px 7px;
    border-radius: 99px; min-width: 20px; text-align: center;
}

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

/* =====================
   MAIN
===================== */
.main {
    margin-left: var(--sidebar-w);
    flex: 1; display: flex; flex-direction: column; min-height: 100vh;
}

.topbar {
    height: var(--topbar-h); background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0 28px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 50;
}
.topbar-left { display: flex; flex-direction: column; gap: 1px; }
.topbar-title { font-size: 17px; font-weight: 800; color: var(--ink); letter-spacing: -.4px; }
.topbar-sub   { font-size: 12px; color: var(--muted); }

.page { padding: 24px 28px 40px; flex: 1; }

/* ALERT */
.alert {
    display: flex; align-items: center; gap: 9px;
    padding: 12px 16px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 500; margin-bottom: 20px;
}
.alert-success { background: var(--success-bg); color: var(--success-tx); border: 1px solid var(--success-bd); }
.alert-danger  { background: var(--danger-bg);  color: var(--danger);     border: 1px solid var(--danger-bd); }
.alert i { font-size: 17px; flex-shrink: 0; }

/* SUMMARY BAR */
.summary-bar {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 12px; margin-bottom: 22px;
    animation: fadeUp .35s ease both;
}
.sum-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r-lg); padding: 16px 18px;
    display: flex; align-items: center; gap: 13px;
}
.sum-icon {
    width: 40px; height: 40px; border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.sum-icon i { font-size: 20px; }
.sum-icon.green  { background: var(--green-lt);  color: var(--green-text); }
.sum-icon.amber  { background: var(--amber-bg);  color: var(--amber); }
.sum-icon.blue   { background: var(--blue-bg);   color: var(--blue); }
.sum-label { font-size: 11px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .6px; }
.sum-val   { font-size: 17px; font-weight: 800; color: var(--ink); letter-spacing: -.4px; margin-top: 2px; }

/* LAYOUT 2-COL */
.layout-2col {
    display: grid; grid-template-columns: 360px 1fr;
    gap: 18px; align-items: start;
}

/* SETOR CARD */
.card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r-xl); overflow: hidden;
    animation: fadeUp .4s ease both;
}

.card-head {
    padding: 18px 22px 14px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 10px;
}
.card-head-icon {
    width: 36px; height: 36px; border-radius: var(--r-sm);
    background: var(--green-lt);
    display: flex; align-items: center; justify-content: center;
}
.card-head-icon i { font-size: 18px; color: var(--green-text); }
.card-title { font-size: 14px; font-weight: 800; color: var(--ink); letter-spacing: -.2px; }
.card-body  { padding: 20px 22px; }

/* FORM */
.field { margin-bottom: 16px; }
.field label {
    display: block; font-size: 12px; font-weight: 700;
    color: var(--ink); letter-spacing: .1px; margin-bottom: 7px;
    text-transform: uppercase;
}

.field select,
.field input[type="number"] {
    width: 100%; padding: 10px 13px;
    border: 1.5px solid var(--border); border-radius: var(--r-sm);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13.5px; color: var(--ink);
    background: var(--surface); outline: none;
    transition: border-color .15s, box-shadow .15s;
    appearance: none;
}

.field select:focus,
.field input[type="number"]:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(24,167,94,.12);
}

.field select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394b0a0' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }

/* quick amount pills */
.quick-amounts {
    display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px;
}
.quick-btn {
    padding: 5px 11px;
    border: 1.5px solid var(--border); border-radius: 99px;
    background: var(--bg); color: var(--muted);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all .15s;
}
.quick-btn:hover { border-color: var(--green); color: var(--green-text); background: var(--green-lt); }

.btn-submit {
    width: 100%; padding: 12px;
    background: var(--green); color: #fff;
    border: none; border-radius: var(--r-sm);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 14px; font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 7px;
    transition: background .15s, transform .15s;
    box-shadow: 0 3px 10px rgba(24,167,94,.3);
}
.btn-submit i { font-size: 17px; }
.btn-submit:hover { background: var(--green-text); transform: translateY(-1px); }

/* RIWAYAT TABLE */
.table-wrap { overflow-x: auto; }

table {
    width: 100%; border-collapse: collapse;
    font-size: 13px;
}

thead th {
    padding: 10px 16px;
    background: var(--bg);
    color: var(--muted); font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .7px;
    border-bottom: 1px solid var(--border);
    text-align: left; white-space: nowrap;
}

tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background .12s;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #fbfdfb; }

tbody td {
    padding: 12px 16px;
    color: var(--ink);
    vertical-align: middle;
}

.tx-goal-name { font-weight: 600; color: var(--ink); }
.tx-date      { font-size: 12px; color: var(--hint); margin-top: 2px; }

.tx-amount {
    font-size: 14px; font-weight: 800;
    color: var(--green-text); letter-spacing: -.3px;
}

.status-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700;
    padding: 3px 9px; border-radius: 99px;
}
.status-success { background: var(--success-bg); color: var(--success-tx); }
.status-pending { background: var(--amber-bg);   color: #92400e; }
.status-failed  { background: var(--danger-bg);  color: var(--danger); }

.tx-icon-wrap {
    width: 34px; height: 34px; border-radius: 9px;
    background: var(--green-lt);
    display: flex; align-items: center; justify-content: center;
}
.tx-icon-wrap i { font-size: 16px; color: var(--green-text); }

.empty-tx {
    display: flex; flex-direction: column; align-items: center;
    padding: 48px 20px; color: var(--hint); gap: 8px;
}
.empty-tx i { font-size: 34px; opacity: .35; }
.empty-tx p { font-size: 13px; font-weight: 500; }

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>

<!-- SIDEBAR -->
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
            <a href="goals.php" class="nav-link">
                <i class="ti ti-target"></i> Goals
                <?php if (count($goals) > 0): ?>
                    <span class="nav-badge"><?= count($goals) ?></span>
                <?php endif; ?>
            </a>
            <a href="transaksi.php" class="nav-link active">
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

<!-- MAIN -->
<main class="main">

    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-title">Transaksi</div>
            <div class="topbar-sub">Setor tabungan & riwayat</div>
        </div>
    </div>

    <div class="page">

        <!-- ALERT -->
        <?php if ($alertMsg): ?>
        <div class="alert alert-<?= $alertType ?>">
            <i class="ti ti-<?= $alertType === 'success' ? 'circle-check' : 'alert-circle' ?>"></i>
            <?= $alertMsg ?>
        </div>
        <?php endif; ?>

        <!-- SUMMARY BAR -->
        <div class="summary-bar">
            <div class="sum-card">
                <div class="sum-icon green"><i class="ti ti-arrows-exchange-2"></i></div>
                <div>
                    <div class="sum-label">Total Transaksi</div>
                    <div class="sum-val"><?= $totalTx ?> transaksi</div>
                </div>
            </div>
            <div class="sum-card">
                <div class="sum-icon green"><i class="ti ti-wallet"></i></div>
                <div>
                    <div class="sum-label">Total Disetor</div>
                    <div class="sum-val">Rp<?= number_format($totalSetor, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="sum-card">
                <div class="sum-icon blue"><i class="ti ti-target"></i></div>
                <div>
                    <div class="sum-label">Goals Aktif</div>
                    <div class="sum-val"><?= count($goals) ?> goals</div>
                </div>
            </div>
        </div>

        <!-- 2 COLUMN -->
        <div class="layout-2col">

            <!-- FORM SETOR -->
            <div class="card" style="animation-delay:.05s">
                <div class="card-head">
                    <div class="card-head-icon">
                        <i class="ti ti-send"></i>
                    </div>
                    <div class="card-title">Setor Tabungan</div>
                </div>

                <div class="card-body">
                    <form method="POST">

                        <div class="field">
                            <label>Pilih Goals</label>
                            <select name="goal_id" required>
                                <option value="">-- Pilih Goals --</option>
                                <?php foreach ($goals as $goal):
                                    $pct = $goal['target_amount'] > 0
                                        ? round(($goal['current_amount'] / $goal['target_amount']) * 100)
                                        : 0;
                                ?>
                                <option value="<?= $goal['id'] ?>">
                                    <?= htmlspecialchars($goal['title']) ?> (<?= $pct ?>%)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label>Jumlah Setor (Rp)</label>
                            <input
                                type="number"
                                name="amount"
                                id="amountInput"
                                placeholder="Contoh: 100.000"
                                min="1"
                                required
                            >
                        </div>

                        <div class="quick-amounts">
                            <?php foreach ([50000, 100000, 250000, 500000, 1000000] as $nominal): ?>
                            <button type="button" class="quick-btn" onclick="setAmount(<?= $nominal ?>)">
                                <?= 'Rp' . number_format($nominal, 0, ',', '.') ?>
                            </button>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" name="setor" class="btn-submit">
                            <i class="ti ti-send"></i> Setor Sekarang
                        </button>

                    </form>
                </div>
            </div>

            <!-- RIWAYAT TRANSAKSI -->
            <div class="card" style="animation-delay:.12s">
                <div class="card-head">
                    <div class="card-head-icon">
                        <i class="ti ti-list"></i>
                    </div>
                    <div class="card-title">Riwayat Transaksi</div>
                </div>

                <?php if ($txError): ?>
                <div class="alert alert-danger" style="margin:16px 20px 0;">
                    <i class="ti ti-alert-circle"></i>
                    Gagal mengambil data transaksi.
                </div>
                <?php elseif (empty($transaksi)): ?>
                <div class="empty-tx">
                    <i class="ti ti-receipt-off"></i>
                    <p>Belum ada transaksi</p>
                </div>
                <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Goals</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($transaksi as $tx):
                            $statusClass = match(strtolower($tx['status'] ?? '')) {
                                'success', 'berhasil' => 'success',
                                'pending'             => 'pending',
                                default               => 'failed',
                            };
                            $statusLabel = match(strtolower($tx['status'] ?? '')) {
                                'success', 'berhasil' => 'Berhasil',
                                'pending'             => 'Pending',
                                default               => 'Gagal',
                            };
                        ?>
                        <tr>
                            <td>
                                <div class="tx-icon-wrap">
                                    <i class="ti ti-arrow-down-circle"></i>
                                </div>
                            </td>
                            <td>
                                <div class="tx-goal-name"><?= htmlspecialchars($tx['goal_title'] ?? '-') ?></div>
                                <div class="tx-date">
                                    <i class="ti ti-clock" style="font-size:11px;vertical-align:-1px;"></i>
                                    <?= date('d M Y, H:i', strtotime($tx['created_at'])) ?>
                                </div>
                            </td>
                            <td>
                                <div class="tx-amount">
                                    +Rp<?= number_format($tx['amount'], 0, ',', '.') ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /layout-2col -->

    </div><!-- /page -->

</main>

<script>
function setAmount(val) {
    document.getElementById('amountInput').value = val;
}
</script>

</body>
</html>