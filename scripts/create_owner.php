<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Core\Database;

if (PHP_SAPI !== 'cli') {
    exit("Script hanya boleh dijalankan melalui CLI.\n");
}

function prompt(string $label): string
{
    echo $label;
    return trim((string) fgets(STDIN));
}

function promptPassword(string $label): string
{
    echo $label;

    system('stty -echo');
    $password = trim((string) fgets(STDIN));
    system('stty echo');

    echo PHP_EOL;

    return $password;
}

try {
    $db = Database::connection();

    $ownerExists = $db->query(
        "SELECT id FROM users WHERE role = 'owner' LIMIT 1"
    )->fetch();

    if ($ownerExists) {
        exit("Akun Owner sudah tersedia. Pembuatan dibatalkan.\n");
    }

    echo "=== ND COFFEE - CREATE OWNER ===\n\n";

    $name = prompt('Nama lengkap: ');
    $username = prompt('Username: ');
    $email = prompt('Email (opsional): ');

    if ($name === '' || $username === '') {
        exit("Nama dan username wajib diisi.\n");
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit("Format email tidak valid.\n");
    }

    $password = promptPassword('Password: ');
    $confirmation = promptPassword('Konfirmasi password: ');

    if (strlen($password) < 8) {
        exit("Password minimal 8 karakter.\n");
    }

    if ($password !== $confirmation) {
        exit("Konfirmasi password tidak cocok.\n");
    }

    $stmt = $db->prepare(
        "INSERT INTO users
            (name, username, email, password, role, is_active)
         VALUES
            (:name, :username, :email, :password, 'owner', 1)"
    );

    $stmt->execute([
        'name' => $name,
        'username' => $username,
        'email' => $email !== '' ? $email : null,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    echo "\nAkun Owner berhasil dibuat!\n";
    echo "Username: {$username}\n";

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        exit("Username atau email sudah digunakan.\n");
    }

    exit("Database error: {$e->getMessage()}\n");
} catch (Throwable $e) {
    exit("Error: {$e->getMessage()}\n");
}
