<?php
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$roleLabels = [
    'owner' => 'Owner',
    'admin' => 'Admin',
    'cashier' => 'Cashier',
    'inventory_staff' => 'Inventory Staff',
];
?>

<div class="space-y-3">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">User Management</h1>
            <p class="text-sm text-text-secondary mt-1">
                Kelola akun dan hak akses pengguna sistem.
            </p>
        </div>

        <a href="/users/create"
           class="bg-primary hover:bg-primary-dark text-white px-5 py-3 rounded-xl text-sm font-semibold text-center">
            + Tambah Akun
        </a>
    </div>

    <?php if ($success): ?>
        <div class="bg-green-50 text-green-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-background text-text-secondary">
                    <tr>
                        <th class="px-6 py-4">Pengguna</th>
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Login Terakhir</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-text-secondary">
                                Belum ada akun pengguna.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($users as $account): ?>
                        <tr class="hover:bg-background/70">
                            <td class="px-6 py-4">
                                <div class="font-semibold">
                                    <?= htmlspecialchars($account['name']) ?>
                                    <?php if ((int) $account['id'] === $currentUserId): ?>
                                        <span class="text-xs text-primary-dark">(Anda)</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs text-text-secondary mt-1">
                                    @<?= htmlspecialchars($account['username']) ?>
                                </div>
                                <div class="text-xs text-text-secondary mt-1">
                                    <?= htmlspecialchars($account['email'] ?? '-') ?>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <?php if ($account['employee_id']): ?>
                                    <div><?= htmlspecialchars($account['employee_name']) ?></div>
                                    <div class="text-xs text-text-secondary mt-1">
                                        <?= htmlspecialchars($account['employee_code']) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-text-secondary">Tidak terhubung</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4">
                                <span class="bg-secondary/40 text-primary-dark px-3 py-1 rounded-full text-xs font-semibold">
                                    <?= htmlspecialchars($roleLabels[$account['role']] ?? $account['role']) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <?php if ($account['is_active']): ?>
                                    <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full text-xs">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs">
                                        Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-text-secondary">
                                <?= $account['last_login_at']
                                    ? date('d M Y H:i', strtotime($account['last_login_at']))
                                    : 'Belum pernah login' ?>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end items-center gap-3">
                                    <a href="/users/edit?id=<?= (int) $account['id'] ?>"
                                       class="text-primary-dark font-semibold">
                                        Edit
                                    </a>

                                    <?php if ((int) $account['id'] !== $currentUserId): ?>
                                        <form method="POST" action="/users/status">
                                            <input type="hidden" name="_token"
                                                   value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id"
                                                   value="<?= (int) $account['id'] ?>">

                                            <button
                                                onclick="return confirm('Ubah status akun ini?')"
                                                class="text-xs font-semibold text-text-secondary">
                                                <?= $account['is_active']
                                                    ? 'Nonaktifkan'
                                                    : 'Aktifkan' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
