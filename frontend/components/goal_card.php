<?php

$title = "Liburan Bali";
$current = 3500000;
$target = 10000000;

$progress = ($current / $target) * 100;

?>

<div class="card shadow border-0 rounded-4 p-4 mb-4">

    <div class="d-flex justify-content-between">

        <h4 class="fw-bold">
            <?= $title ?>
        </h4>

        <span class="badge bg-success">
            Aktif
        </span>

    </div>

    <p class="text-muted">
        Goals tabungan bersama
    </p>

    <div class="progress mb-3">

        <div class="progress-bar bg-success"
             style="width: <?= $progress ?>%">

            <?= round($progress) ?>%

        </div>

    </div>

    <div class="d-flex justify-content-between mb-3">

        <span>
            Rp<?= number_format($current) ?>
        </span>

        <span>
            Rp<?= number_format($target) ?>
        </span>

    </div>

    <button class="btn btn-success w-100">
        Detail Goals
    </button>

</div>