<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$debug = '';

if (isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $data    = ['email' => $email, 'password' => $password];
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context = stream_context_create($options);
        $result  = @file_get_contents('http://backend/api/auth/login.php', false, $context);

        $debug = ($result === false)
            ? 'file_get_contents RETURN FALSE — tidak bisa konek ke backend'
            : $result;

        if ($result === false) {
            $error = 'Gagal konek ke backend. Coba lagi.';
        } else {
            $clean = preg_replace('/<br\s*\/?>/i', '', $result);
            $clean = preg_replace('/<b>[^<]*<\/b>/i', '', $clean);
            $clean = preg_replace('/Warning:[^\n]*\n?/', '', $clean);
            $clean = trim($clean);

            preg_match('/\{.*\}/s', $clean, $matches);
            $json     = $matches[0] ?? '{}';
            $response = json_decode($json, true);

            if ($response && $response['status'] === true) {
                $_SESSION['user']    = $response['data'];
                $_SESSION['user_id'] = $response['data']['id'];
                $_SESSION['nama']    = $response['data']['name'] ?? $response['data']['nama'] ?? 'Pengguna';
                header('Location: index.php');
                exit;
            } else {
                $error = htmlspecialchars($response['message'] ?? 'Email atau password salah.');
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk – Nabung Bareng</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --forest:      #0b3d24;
    --forest-mid:  #145c38;
    --green:       #18a75e;
    --green-lt:    #d6f5e6;
    --green-text:  #0e7a43;
    --green-glow:  rgba(24,167,94,.2);

    --ink:         #0d1f14;
    --muted:       #5a7a68;
    --hint:        #94b0a0;
    --border:      #e2ebe5;
    --surface:     #ffffff;
    --bg:          #f4f6f5;

    --danger:      #dc2626;
    --danger-bg:   #fef2f2;
    --danger-bd:   #fecaca;

    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 18px;
    --r-xl: 24px;
}

html { font-size: 15px; }

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    background: var(--bg);
    -webkit-font-smoothing: antialiased;
}

.left-panel {
    width: 440px;
    flex-shrink: 0;
    background: var(--forest);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 40px 44px;
}

.left-panel::before {
    content: '';
    position: absolute;
    top: -100px; right: -100px;
    width: 360px; height: 360px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(24,167,94,.22) 0%, transparent 70%);
    pointer-events: none;
}

.left-panel::after {
    content: '';
    position: absolute;
    bottom: -80px; left: -60px;
    width: 280px; height: 280px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(24,167,94,.14) 0%, transparent 65%);
    pointer-events: none;
}

.left-panel .dots {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.06) 1px, transparent 1px);
    background-size: 24px 24px;
    pointer-events: none;
}

.left-top { position: relative; z-index: 1; }
.left-bottom { position: relative; z-index: 1; }

.brand {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 56px;
}

.brand-mark {
    width: 40px; height: 40px;
    background: var(--green);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(24,167,94,.45);
}

.brand-mark i { font-size: 21px; color: #fff; }

.brand-name {
    font-size: 16px; font-weight: 800;
    color: #fff; letter-spacing: -.3px;
    line-height: 1.1;
}

.brand-sub {
    font-size: 10.5px; color: rgba(255,255,255,.38);
    font-weight: 400; margin-top: 2px;
}

.panel-headline {
    font-size: 34px; font-weight: 800;
    color: #fff; line-height: 1.2;
    letter-spacing: -.8px;
    margin-bottom: 14px;
}

.panel-headline span {
    color: #5de8a8;
}

.panel-desc {
    font-size: 14px; color: rgba(255,255,255,.6);
    line-height: 1.7;
    max-width: 300px;
}

.features {
    display: flex; flex-direction: column; gap: 10px;
    margin-top: 40px;
}

.feature-item {
    display: flex; align-items: center; gap: 10px;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: var(--r-md);
    padding: 10px 14px;
}

.feature-icon {
    width: 30px; height: 30px;
    background: rgba(24,167,94,.2);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.feature-icon i { font-size: 16px; color: #5de8a8; }

.feature-text {
    font-size: 12.5px; font-weight: 500; color: rgba(255,255,255,.75);
    line-height: 1.3;
}

.quote-block {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: var(--r-lg);
    padding: 16px 18px;
}

.quote-text {
    font-size: 13px; color: rgba(255,255,255,.7);
    line-height: 1.6; font-style: italic;
    margin-bottom: 10px;
}

.quote-author {
    display: flex; align-items: center; gap: 8px;
}

.quote-avatar {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--green);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff;
}

.quote-name {
    font-size: 12px; font-weight: 600; color: rgba(255,255,255,.6);
}

.right-panel {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
}

.form-wrap {
    width: 100%;
    max-width: 400px;
    animation: fadeUp .4s ease both;
}

.form-head { margin-bottom: 28px; }

.form-title {
    font-size: 26px; font-weight: 800;
    color: var(--ink); letter-spacing: -.5px;
    margin-bottom: 6px;
}

.form-sub {
    font-size: 13.5px; color: var(--muted);
}

.form-sub a {
    color: var(--green); font-weight: 600;
    text-decoration: none;
}

.form-sub a:hover { text-decoration: underline; }

.alert {
    display: flex; align-items: flex-start; gap: 9px;
    padding: 11px 14px;
    border-radius: var(--r-sm);
    font-size: 13px; font-weight: 500;
    margin-bottom: 20px;
    line-height: 1.4;
}

.alert-danger {
    background: var(--danger-bg);
    color: var(--danger);
    border: 1px solid var(--danger-bd);
}

.alert i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }

.field { margin-bottom: 16px; }

.field label {
    display: block;
    font-size: 12.5px; font-weight: 700;
    color: var(--ink);
    letter-spacing: .1px;
    margin-bottom: 7px;
}

.field input {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13.5px; color: var(--ink);
    background: var(--surface);
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}

.field input:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(24,167,94,.12);
}

.field input::placeholder { color: #b0c9bc; }

.pw-wrap { position: relative; }

.pw-toggle {
    position: absolute;
    right: 12px; top: 50%;
    transform: translateY(-50%);
    background: none; border: none;
    cursor: pointer;
    color: var(--hint);
    display: flex; align-items: center;
    padding: 4px;
    transition: color .15s;
}

.pw-toggle:hover { color: var(--green); }
.pw-toggle i { font-size: 17px; }

.form-extras {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 20px;
}

.remember {
    display: flex; align-items: center; gap: 7px;
    font-size: 12.5px; color: var(--muted); cursor: pointer;
}

.remember input[type="checkbox"] {
    width: 15px; height: 15px;
    accent-color: var(--green);
    cursor: pointer;
}

.forgot {
    font-size: 12.5px; font-weight: 600;
    color: var(--green); text-decoration: none;
}

.forgot:hover { text-decoration: underline; }

.btn-submit {
    width: 100%;
    padding: 13px;
    background: var(--green);
    color: #fff;
    border: none;
    border-radius: var(--r-sm);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 14px; font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: background .15s, transform .15s, box-shadow .15s;
    box-shadow: 0 3px 12px rgba(24,167,94,.3);
}

.btn-submit:hover {
    background: var(--green-text);
    transform: translateY(-1px);
    box-shadow: 0 5px 16px rgba(24,167,94,.35);
}

.btn-submit:active { transform: translateY(0); }
.btn-submit i { font-size: 17px; }

/* Divider */
.divider {
    display: flex; align-items: center; gap: 10px;
    margin: 20px 0;
    color: var(--hint); font-size: 12px;
}

.divider::before, .divider::after {
    content: ''; flex: 1;
    height: 1px; background: var(--border);
}

.register-cta {
    text-align: center;
    font-size: 13px; color: var(--muted);
}

.register-cta a {
    color: var(--green); font-weight: 700;
    text-decoration: none;
}

.register-cta a:hover { text-decoration: underline; }

.debug-box {
    background: #0d1117;
    color: #58d68d;
    font-family: monospace;
    font-size: 11.5px;
    padding: 16px 20px;
    border-top: 2px solid var(--green);
    white-space: pre-wrap;
    word-break: break-all;
    line-height: 1.6;
}

.debug-box h4 {
    color: #f0e68c;
    margin-bottom: 8px;
    font-size: 12px;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .left-panel { display: none; }
}
</style>
</head>
<body>

<div class="left-panel">
    <div class="dots"></div>

    <div class="left-top">
        <div class="brand">
            <div class="brand-mark">
                <i class="ti ti-pig-money"></i>
            </div>
            <div>
                <div class="brand-name">Nabung Bareng</div>
                <div class="brand-sub">Saving Together</div>
            </div>
        </div>

        <div class="panel-headline">
            Wujudkan<br>impianmu <span>bersama.</span>
        </div>

        <p class="panel-desc">
            Atur dan pantau tabungan kamu bersama orang-orang terdekat — transparan, mudah, dan menyenangkan.
        </p>

        <div class="features">
            <div class="feature-item">
                <div class="feature-icon"><i class="ti ti-target"></i></div>
                <div class="feature-text">Buat goals tabungan dengan target yang jelas</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="ti ti-chart-line"></i></div>
                <div class="feature-text">Pantau progres secara real-time</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><i class="ti ti-users"></i></div>
                <div class="feature-text">Nabung bareng teman atau keluarga</div>
            </div>
        </div>
    </div>

    <div class="left-bottom">
        <div class="quote-block">
            <div class="quote-text">
                "Akhirnya bisa nabung buat liburan bareng keluarga tanpa ribet. Semuanya kelihatan jelas!"
            </div>
            <div class="quote-author">
                <div class="quote-avatar">A</div>
                <div class="quote-name">Anisa R. — pengguna aktif</div>
            </div>
        </div>
    </div>
</div>

<div class="right-panel">
    <div class="form-wrap">

        <div class="form-head">
            <div class="form-title">Masuk ke akun</div>
            <div class="form-sub">
                Belum punya akun? <a href="register.php">Daftar gratis</a>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="ti ti-alert-circle"></i>
            <?= $error ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="on">

            <div class="field">
                <label for="email">Alamat Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="contoh@email.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                    autocomplete="email"
                >
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="pw-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="pw-toggle" id="pw-toggle" aria-label="Tampilkan password">
                        <i class="ti ti-eye" id="pw-icon"></i>
                    </button>
                </div>
            </div>

            <div class="form-extras">
                <label class="remember">
                    <input type="checkbox" name="remember">
                    Ingat saya
                </label>
                <a href="forgot_password.php" class="forgot">Lupa password?</a>
            </div>

            <button type="submit" name="login" class="btn-submit">
                <i class="ti ti-login"></i>
                Masuk Sekarang
            </button>

        </form>

        <div class="divider">atau</div>

        <div class="register-cta">
            Belum punya akun? <a href="register.php">Daftar sekarang →</a>
        </div>

    </div>
</div>

<?php if ($debug !== ''): ?>
<div class="debug-box">
    <h4>DEBUG — Raw Response dari Backend (hapus setelah selesai):</h4>
<?= htmlspecialchars($debug) ?>
</div>
<?php endif; ?>

<script>
(function () {
    const input  = document.getElementById('password');
    const toggle = document.getElementById('pw-toggle');
    const icon   = document.getElementById('pw-icon');

    toggle.addEventListener('click', function () {
        const isHidden = input.type === 'password';
        input.type     = isHidden ? 'text' : 'password';
        icon.className = isHidden ? 'ti ti-eye-off' : 'ti ti-eye';
        toggle.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
    });
})();
</script>
</body>
</html>