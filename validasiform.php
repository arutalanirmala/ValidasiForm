<?php
// ===== Registrasi Lomba Coding =====

// Fungsi helper untuk output aman (mencegah XSS)
function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Whitelist pilihan
$kategoriList = [
    'web'    => 'Web Development',
    'mobile' => 'Mobile Development',
    'algo'   => 'Competitive Programming',
    'game'   => 'Game Development',
];
$pesertaList = [
    'individu' => 'Individu',
    'tim'      => 'Tim',
];

$errors  = [];
$old     = ['nama' => '', 'email' => '', 'hp' => '', 'umur' => '', 'github' => '', 'kategori' => '', 'peserta' => ''];
$success = false;
$data    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & bersihkan input
    foreach ($old as $key => $_) {
        $old[$key] = isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
    }

    // 1. Nama (wajib, 3-50 karakter)
    $len = mb_strlen($old['nama'], 'UTF-8');
    if ($old['nama'] === '') {
        $errors['nama'] = 'Nama wajib diisi.';
    } elseif ($len < 3 || $len > 50) {
        $errors['nama'] = 'Nama harus 3-50 karakter (saat ini ' . $len . ').';
    }

    // 2. Email (wajib & valid)
    if ($old['email'] === '') {
        $errors['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }

    // 3. Nomor HP dengan REGEXP (format Indonesia: 08xx / 628xx / +628xx)
    if ($old['hp'] === '') {
        $errors['hp'] = 'Nomor HP wajib diisi.';
    } elseif (!preg_match('/^(?:\+62|62|0)8[1-9][0-9]{7,10}$/', $old['hp'])) {
        $errors['hp'] = 'Nomor HP tidak valid. Contoh: 081234567890 atau +6281234567890.';
    }

    // 4. Umur 17-19 tahun
    if ($old['umur'] === '') {
        $errors['umur'] = 'Umur wajib diisi.';
    } elseif (filter_var($old['umur'], FILTER_VALIDATE_INT) === false) {
        $errors['umur'] = 'Umur harus berupa angka bulat.';
    } else {
        $umur = (int)$old['umur'];
        if ($umur < 17 || $umur > 19) {
            $errors['umur'] = 'PERINGATAN: Umur harus 17-19 tahun. Data tidak dapat disimpan.';
        }
    }

    // 5. URL GitHub (opsional, harus URL valid jika diisi)
    if ($old['github'] !== '') {
        $url = $old['github'];
        if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url)) {
            $errors['github'] = 'URL tidak valid (harus diawali http:// atau https://).';
        } else {
            $host = strtolower((string)parse_url($url, PHP_URL_HOST));
            if ($host !== 'github.com' && $host !== 'www.github.com') {
                $errors['github'] = 'URL harus mengarah ke github.com.';
            }
        }
    }

    // 6. Kategori (select) dengan whitelist
    if ($old['kategori'] === '') {
        $errors['kategori'] = 'Kategori wajib dipilih.';
    } elseif (!array_key_exists($old['kategori'], $kategoriList)) {
        $errors['kategori'] = 'Kategori tidak valid.';
    }

    // 7. Peserta (radio) dengan whitelist
    if ($old['peserta'] === '') {
        $errors['peserta'] = 'Jenis peserta wajib dipilih.';
    } elseif (!array_key_exists($old['peserta'], $pesertaList)) {
        $errors['peserta'] = 'Jenis peserta tidak valid.';
    }

    // Jika tidak ada error -> sukses
    if (!$errors) {
        $success = true;
        $data = $old;
        // (Di sini bisa ditambahkan penyimpanan ke database/file)
        $old = array_fill_keys(array_keys($old), ''); // kosongkan form setelah sukses
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi Lomba Coding</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f2f4f8; margin: 0; padding: 24px; }
        .card { max-width: 520px; margin: 0 auto; background: #fff; padding: 28px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        h1 { margin-top: 0; font-size: 22px; }
        .field { margin-bottom: 16px; }
        label.title { display: block; font-weight: bold; margin-bottom: 6px; }
        input[type=text], input[type=email], input[type=number], input[type=url], select {
            width: 100%; padding: 10px; border: 1px solid #c4c9d4; border-radius: 6px; font-size: 14px;
        }
        input.invalid, select.invalid { border-color: #d93025; background: #fff6f5; }
        .error { color: #d93025; font-size: 13px; margin-top: 4px; }
        .hint { color: #666; font-size: 12px; margin-top: 4px; }
        .radio-group label { margin-right: 16px; font-weight: normal; }
        button { background: #1a73e8; color: #fff; border: 0; padding: 11px 20px; border-radius: 6px; font-size: 15px; cursor: pointer; }
        button:hover { background: #1558b0; }
        .success { background: #e6f4ea; border: 1px solid #34a853; padding: 14px; border-radius: 6px; margin-bottom: 20px; }
        .success table { margin-top: 8px; border-collapse: collapse; }
        .success td { padding: 3px 10px 3px 0; vertical-align: top; }
        .alert { background: #fdecea; border: 1px solid #d93025; color: #a50e0e; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Registrasi Lomba Coding</h1>

    <?php if ($success): ?>
        <div class="success">
            <strong>Pendaftaran berhasil disimpan!</strong>
            <table>
                <tr><td>Nama</td><td>: <?= e($data['nama']) ?></td></tr>
                <tr><td>Email</td><td>: <?= e($data['email']) ?></td></tr>
                <tr><td>No. HP</td><td>: <?= e($data['hp']) ?></td></tr>
                <tr><td>Umur</td><td>: <?= e($data['umur']) ?> tahun</td></tr>
                <tr><td>GitHub</td><td>: <?= $data['github'] !== '' ? e($data['github']) : '-' ?></td></tr>
                <tr><td>Kategori</td><td>: <?= e($kategoriList[$data['kategori']]) ?></td></tr>
                <tr><td>Peserta</td><td>: <?= e($pesertaList[$data['peserta']]) ?></td></tr>
            </table>
        </div>
    <?php elseif ($errors): ?>
        <div class="alert">Terdapat <?= count($errors) ?> kesalahan. Periksa kembali isian Anda.</div>
    <?php endif; ?>

    <form method="post" action="" novalidate>
        <div class="field">
            <label class="title" for="nama">Nama Lengkap *</label>
            <input type="text" id="nama" name="nama" value="<?= e($old['nama']) ?>"
                   class="<?= isset($errors['nama']) ? 'invalid' : '' ?>">
            <?php if (isset($errors['nama'])): ?><div class="error"><?= e($errors['nama']) ?></div><?php endif; ?>
        </div>

        <div class="field">
            <label class="title" for="email">Email *</label>
            <input type="email" id="email" name="email" value="<?= e($old['email']) ?>"
                   class="<?= isset($errors['email']) ? 'invalid' : '' ?>">
            <?php if (isset($errors['email'])): ?><div class="error"><?= e($errors['email']) ?></div><?php endif; ?>
        </div>

        <div class="field">
            <label class="title" for="hp">Nomor HP *</label>
            <input type="text" id="hp" name="hp" value="<?= e($old['hp']) ?>" placeholder="081234567890"
                   class="<?= isset($errors['hp']) ? 'invalid' : '' ?>">
            <?php if (isset($errors['hp'])): ?><div class="error"><?= e($errors['hp']) ?></div><?php endif; ?>
        </div>

        <div class="field">
            <label class="title" for="umur">Umur *</label>
            <input type="number" id="umur" name="umur" value="<?= e($old['umur']) ?>"
                   class="<?= isset($errors['umur']) ? 'invalid' : '' ?>">
            <div class="hint">Rentang umur peserta: 17-19 tahun.</div>
            <?php if (isset($errors['umur'])): ?><div class="error"><?= e($errors['umur']) ?></div><?php endif; ?>
        </div>

        <div class="field">
            <label class="title" for="github">URL Repositori GitHub (opsional)</label>
            <input type="text" id="github" name="github" value="<?= e($old['github']) ?>"
                   placeholder="https://github.com/username/repo"
                   class="<?= isset($errors['github']) ? 'invalid' : '' ?>">
            <?php if (isset($errors['github'])): ?><div class="error"><?= e($errors['github']) ?></div><?php endif; ?>
        </div>

        <div class="field">
            <label class="title" for="kategori">Kategori *</label>
            <select id="kategori" name="kategori" class="<?= isset($errors['kategori']) ? 'invalid' : '' ?>">
                <option value="">-- Pilih kategori --</option>
                <?php foreach ($kategoriList as $val => $label): ?>
                    <option value="<?= e($val) ?>" <?= $old['kategori'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['kategori'])): ?><div class="error"><?= e($errors['kategori']) ?></div><?php endif; ?>
        </div>

        <div class="field">
            <span class="title" style="display:block;font-weight:bold;margin-bottom:6px;">Peserta *</span>
            <div class="radio-group">
                <?php foreach ($pesertaList as $val => $label): ?>
                    <label>
                        <input type="radio" name="peserta" value="<?= e($val) ?>" <?= $old['peserta'] === $val ? 'checked' : '' ?>>
                        <?= e($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <?php if (isset($errors['peserta'])): ?><div class="error"><?= e($errors['peserta']) ?></div><?php endif; ?>
        </div>

        <button type="submit">Daftar</button>
    </form>
</div>
</body>
</html>