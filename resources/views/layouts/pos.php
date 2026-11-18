<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Point of Sale';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | ND Coffee</title>

    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="min-h-screen bg-[#FAF7F5] text-[#493B36] antialiased">

    <?= $content ?? '' ?>

<script src="/assets/js/pos.js" defer></script>
</body>
</html>
