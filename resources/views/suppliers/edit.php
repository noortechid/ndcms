<?php

use App\Core\Csrf;

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$escape = fn ($value) => htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
);
?>

<div class="mx-auto max-w-4xl space-y-6">

    <!-- Header -->
    <div>
        <a href="/suppliers" class="text-sm font-medium text-pink-600 hover:text-pink-700">
            ← Kembali ke Supplier
        </a>

        <h1 class="mt-3 text-2xl font-semibold text-stone-800">
            Edit Supplier
        </h1>

        <p class="mt-1 text-sm text-stone-500">
            Perbarui informasi pemasok bahan baku ND Coffee.
        </p>
    </div>

    <!-- Flash Error -->
    <?php if ($flashError): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= $escape($flashError) ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form
        action="/suppliers/update"
        method="POST"
        class="overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">

        <input
            type="hidden"
            name="_token"
            value="<?= $escape(Csrf::token()) ?>">

        <input
            type="hidden"
            name="id"
            value="<?= (int) $supplier['id'] ?>">

        <!-- Basic Information -->
        <div class="border-b border-stone-100 px-6 py-5">
            <h2 class="font-semibold text-stone-800">
                Informasi Supplier
            </h2>

            <p class="mt-1 text-sm text-stone-500">
                Perbarui informasi dasar pemasok.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">

            <div>
                <label for="supplier_code" class="mb-2 block text-sm font-medium text-stone-700">
                    Kode Supplier
                </label>

                <input
                    type="text"
                    id="supplier_code"
                    name="supplier_code"
                    maxlength="30"
                    value="<?= $escape($supplier['supplier_code']) ?>"
                    placeholder="Contoh: SUP-001"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">

                <p class="mt-1 text-xs text-stone-400">
                    Opsional. Harus unik jika diisi.
                </p>
            </div>

            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-stone-700">
                    Nama Supplier <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    maxlength="150"
                    required
                    value="<?= $escape($supplier['name']) ?>"
                    placeholder="Nama perusahaan atau pemasok"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">
            </div>

            <div>
                <label for="contact_person" class="mb-2 block text-sm font-medium text-stone-700">
                    Nama Kontak
                </label>

                <input
                    type="text"
                    id="contact_person"
                    name="contact_person"
                    maxlength="100"
                    value="<?= $escape($supplier['contact_person']) ?>"
                    placeholder="Nama penanggung jawab"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">
            </div>

            <div>
                <label for="phone" class="mb-2 block text-sm font-medium text-stone-700">
                    Nomor Telepon
                </label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    maxlength="30"
                    value="<?= $escape($supplier['phone']) ?>"
                    placeholder="08xxxxxxxxxx"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">
            </div>

            <div class="sm:col-span-2">
                <label for="email" class="mb-2 block text-sm font-medium text-stone-700">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    maxlength="100"
                    value="<?= $escape($supplier['email']) ?>"
                    placeholder="supplier@email.com"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100">
            </div>

        </div>

        <!-- Additional Information -->
        <div class="border-y border-stone-100 px-6 py-5">
            <h2 class="font-semibold text-stone-800">
                Informasi Tambahan
            </h2>

            <p class="mt-1 text-sm text-stone-500">
                Perbarui alamat dan catatan pemasok.
            </p>
        </div>

        <div class="space-y-5 p-6">

            <div>
                <label for="address" class="mb-2 block text-sm font-medium text-stone-700">
                    Alamat
                </label>

                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    placeholder="Alamat lengkap supplier"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100"><?= $escape($supplier['address']) ?></textarea>
            </div>

            <div>
                <label for="notes" class="mb-2 block text-sm font-medium text-stone-700">
                    Catatan
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    placeholder="Informasi tambahan mengenai supplier..."
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100"><?= $escape($supplier['notes']) ?></textarea>
            </div>

        </div>

        <!-- Actions -->
        <div class="flex flex-col-reverse gap-3 border-t border-stone-100 bg-stone-50/50 px-6 py-5 sm:flex-row sm:justify-end">

            <a
                href="/suppliers"
                class="rounded-xl border border-stone-200 bg-white px-5 py-2.5 text-center text-sm font-medium text-stone-600 transition hover:bg-stone-50">
                Batal
            </a>

            <button
                type="submit"
                class="rounded-xl bg-pink-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-pink-700">
                Simpan Perubahan
            </button>

        </div>

    </form>

</div>
