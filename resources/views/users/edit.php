<?php
$roleLabels = [
    'owner' => 'Owner',
    'admin' => 'Admin',
    'cashier' => 'Cashier',
    'inventory_staff' => 'Inventory Staff',
];
?>

<div class="max-w-3xl space-y-6">

    <div>
        <a href="/users" class="text-sm text-primary-dark">← Kembali</a>
        <h1 class="text-2xl font-bold mt-3">Edit Akun Pengguna</h1>
        <p class="text-sm text-text-secondary mt-1">
            Perbarui informasi dan hak akses akun.
        </p>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="POST" action="/users/update"
          class="bg-white border border-border rounded-2xl p-6 space-y-5">

        <input type="hidden" name="_token"
               value="<?= htmlspecialchars($csrfToken) ?>">

        <input type="hidden" name="id"
               value="<?= (int) $account['id'] ?>">

        <div>
            <label class="text-sm font-medium">Karyawan</label>
            <select name="employee_id"
                    class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                <option value="">Tanpa hubungan karyawan</option>

                <?php foreach ($employees as $employee): ?>
                    <option value="<?= (int) $employee['id'] ?>"
                        <?= (int) $account['employee_id'] === (int) $employee['id']
                            ? 'selected'
                            : '' ?>>
                        <?= htmlspecialchars(
                            $employee['employee_code'] . ' - ' .
                            $employee['name'] . ' (' .
                            $employee['position'] . ')'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="text-sm font-medium">Nama *</label>
                <input name="name" required
                       value="<?= htmlspecialchars($account['name']) ?>"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Username *</label>
                <input name="username" required
                       value="<?= htmlspecialchars($account['username']) ?>"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($account['email'] ?? '') ?>"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Role *</label>

                <?php if ((int) $account['id'] === $currentUserId): ?>
                    <input type="hidden" name="role"
                           value="<?= htmlspecialchars($account['role']) ?>">
                    <div class="mt-2 border border-border rounded-xl p-3 text-sm bg-background">
                        <?= htmlspecialchars($roleLabels[$account['role']] ?? $account['role']) ?>
                        <span class="text-xs text-text-secondary">(Role akun sendiri tidak dapat diubah)</span>
                    </div>
                <?php else: ?>
                    <select name="role" required
                            class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                        <?php foreach ($roleLabels as $value => $label): ?>
                            <option value="<?= $value ?>"
                                <?= $account['role'] === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div>
                <label class="text-sm font-medium">Status Akun</label>

                <?php if ((int) $account['id'] === $currentUserId): ?>
                    <input type="hidden" name="is_active" value="1">
                    <div class="mt-2 border border-border rounded-xl p-3 text-sm bg-background">
                        Aktif
                    </div>
                <?php else: ?>
                    <select name="is_active"
                            class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                        <option value="1" <?= $account['is_active'] ? 'selected' : '' ?>>
                            Aktif
                        </option>
                        <option value="0" <?= !$account['is_active'] ? 'selected' : '' ?>>
                            Nonaktif
                        </option>
                    </select>
                <?php endif; ?>
            </div>
        </div>

        <div class="border-t border-border pt-5 space-y-2">
            <h2 class="font-semibold">Ganti Password</h2>
            <p class="text-xs text-text-secondary">
                Kosongkan jika tidak ingin mengubah password.
            </p>

            <input type="password" name="password" minlength="8"
                   autocomplete="new-password"
                   placeholder="Password baru (minimal 8 karakter)"
                   class="w-full border border-border rounded-xl p-3 text-sm">
        </div>

        <div class="flex justify-end gap-3 pt-3">
            <a href="/users"
               class="px-5 py-3 rounded-xl border border-border text-sm">
                Batal
            </a>

            <button class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold">
                Simpan Perubahan
            </button>
        </div>

    </form>
</div>
