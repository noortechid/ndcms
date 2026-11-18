<div class="max-w-3xl space-y-6">

    <div>
        <a href="/employees" class="text-sm text-primary-dark">← Kembali</a>
        <h1 class="text-2xl font-bold mt-3">Edit Karyawan</h1>
        <p class="text-sm text-text-secondary mt-1">
            Perbarui informasi karyawan.
        </p>
    </div>

    <form method="POST" action="/employees/update"
          class="bg-white border border-border rounded-2xl p-6 space-y-5">

        <input type="hidden" name="_token"
               value="<?= htmlspecialchars($csrfToken) ?>">

        <input type="hidden" name="id"
               value="<?= (int) $employee['id'] ?>">

        <div class="grid sm:grid-cols-2 gap-5">

            <div>
                <label class="text-sm font-medium">Kode Karyawan *</label>
                <input name="employee_code" required
                       value="<?= htmlspecialchars($employee['employee_code']) ?>"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Nama Lengkap *</label>
                <input name="name" required
                       value="<?= htmlspecialchars($employee['name']) ?>"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Jabatan *</label>
                <input name="position" required
                       value="<?= htmlspecialchars($employee['position']) ?>"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Nomor Telepon</label>
                <input name="phone"
                       value="<?= htmlspecialchars($employee['phone'] ?? '') ?>"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($employee['email'] ?? '') ?>"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Tanggal Masuk</label>
                <input type="date" name="hire_date"
                       value="<?= htmlspecialchars($employee['hire_date'] ?? '') ?>"
                       class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Status Karyawan</label>
                <select name="employment_status"
                        class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                    <option value="active"
                        <?= $employee['employment_status'] === 'active' ? 'selected' : '' ?>>
                        Aktif
                    </option>
                    <option value="inactive"
                        <?= $employee['employment_status'] === 'inactive' ? 'selected' : '' ?>>
                        Nonaktif
                    </option>
                </select>
            </div>

        </div>

        <div>
            <label class="text-sm font-medium">Alamat</label>
            <textarea name="address" rows="3"
                      class="mt-2 w-full border border-border rounded-xl p-3 text-sm"><?= htmlspecialchars($employee['address'] ?? '') ?></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-3">
            <a href="/employees"
               class="px-5 py-3 rounded-xl border border-border text-sm">
                Batal
            </a>

            <button class="bg-primary text-white px-5 py-3 rounded-xl text-sm font-semibold">
                Simpan Perubahan
            </button>
        </div>

    </form>
</div>
