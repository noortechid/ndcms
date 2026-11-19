<div class="max-w-3xl space-y-6">

    <div>
        <a href="/users" class="text-sm text-primary-dark">← Kembali</a>
        <h1 class="text-2xl font-bold mt-3">Tambah Akun Pengguna</h1>
        <p class="text-sm text-text-secondary mt-1">
            Buat akun untuk mengakses ND Coffee Management System.
        </p>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="POST" action="/users"
          class="bg-white border border-border rounded-2xl p-6 space-y-5">

        <input type="hidden" name="_token"
               value="<?= htmlspecialchars($csrfToken) ?>">

        <div>
            <label class="text-sm font-medium">Hubungkan dengan Karyawan</label>
            <select name="employee_id"
                    class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                <option value="">Tanpa hubungan karyawan</option>

                <?php foreach ($employees as $employee): ?>
                    <option value="<?= (int) $employee['id'] ?>">
                        <?= htmlspecialchars(
                            $employee['employee_code'] . ' - ' .
                            $employee['name'] . ' (' .
                            $employee['position'] . ')'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="text-xs text-text-secondary mt-2">
                Opsional. Satu karyawan hanya dapat terhubung ke satu akun.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="text-sm font-medium">Nama Pengguna *</label>
                <input name="name" required
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Username *</label>
                <input name="username" required
                       autocomplete="off"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Role *</label>
                <select name="role" required
                        class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                    <option value="">Pilih Role</option>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                    <option value="cashier">Cashier</option>
                    <option value="inventory_staff">Inventory Staff</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="text-sm font-medium">Password *</label>
                <input type="password" name="password" required minlength="8"
                       autocomplete="new-password"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                <p class="text-xs text-text-secondary mt-2">
                    Minimal 8 karakter.
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-3">
            <a href="/users"
               class="px-5 py-3 rounded-xl border border-border text-sm">
                Batal
            </a>

            <button class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold">
                Buat Akun
            </button>
        </div>

    </form>
</div>
