<?php
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error']);

function rupiah(float|int|string $amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}
?>

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold">Shift Management</h1>
        <p class="text-sm text-text-secondary mt-1">
            Kelola pembukaan dan penutupan shift kasir.
        </p>
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

    <?php if ($currentShift): ?>

        <div class="bg-white border border-border rounded-2xl p-6 space-y-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-bold text-lg">Shift Sedang Berjalan</h2>
                    <p class="text-sm text-text-secondary mt-1">
                        Dibuka <?= date('d M Y H:i', strtotime($currentShift['opened_at'])) ?>
                    </p>
                </div>

                <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                    OPEN
                </span>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="bg-background rounded-xl p-4">
                    <p class="text-xs text-text-secondary">Modal Awal</p>
                    <p class="text-xl font-bold mt-2">
                        <?= rupiah($currentShift['opening_cash']) ?>
                    </p>
                </div>

                <div class="bg-background rounded-xl p-4">
                    <p class="text-xs text-text-secondary">Penjualan Tunai</p>
                    <p class="text-xl font-bold mt-2">
                        <?= rupiah($currentShift['cash_sales']) ?>
                    </p>
                </div>
            </div>

            <div class="border-t border-border pt-5">
                <h3 class="font-semibold mb-3">Tutup Shift</h3>

                <form method="POST" action="/shifts/close" class="space-y-4">
                    <input type="hidden" name="_token"
                           value="<?= htmlspecialchars($csrfToken) ?>">

                    <input type="hidden" name="shift_id"
                           value="<?= (int) $currentShift['id'] ?>">

                    <div>
                        <label class="text-sm font-medium">
                            Uang Fisik Aktual (Rp) *
                        </label>
                        <input type="number" name="actual_cash"
                               min="0" step="0.01" required
                               placeholder="Contoh: 500000"
                               class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-medium">Catatan Penutupan</label>
                        <textarea name="closing_note" rows="2"
                                  class="mt-2 w-full border border-border rounded-xl p-3 text-sm"
                                  placeholder="Catatan jika ada selisih kas..."></textarea>
                    </div>

                    <button
                        onclick="return confirm('Yakin ingin menutup shift ini?')"
                        class="bg-primary hover:bg-primary-dark text-white px-5 py-3 rounded-xl text-sm font-semibold">
                        Tutup Shift
                    </button>
                </form>
            </div>
        </div>

    <?php else: ?>

        <div class="bg-white border border-border rounded-2xl p-6 space-y-5">
            <div>
                <h2 class="font-bold text-lg">Buka Shift Baru</h2>
                <p class="text-sm text-text-secondary mt-1">
                    Masukkan jumlah uang kas yang tersedia sebelum operasional dimulai.
                </p>
            </div>

            <form method="POST" action="/shifts/open" class="space-y-4">
                <input type="hidden" name="_token"
                       value="<?= htmlspecialchars($csrfToken) ?>">

                <div>
                    <label class="text-sm font-medium">Modal Awal (Rp) *</label>
                    <input type="number" name="opening_cash"
                           min="0" step="0.01" required
                           placeholder="Contoh: 300000"
                           class="mt-2 w-full border border-border rounded-xl p-3 text-sm">
                </div>

                <div>
                    <label class="text-sm font-medium">Catatan Pembukaan</label>
                    <textarea name="opening_note" rows="2"
                              class="mt-2 w-full border border-border rounded-xl p-3 text-sm"
                              placeholder="Catatan kondisi kas awal..."></textarea>
                </div>

                <button class="bg-primary hover:bg-primary-dark text-white px-5 py-3 rounded-xl text-sm font-semibold">
                    Buka Shift
                </button>
            </form>
        </div>

    <?php endif; ?>

    <div class="bg-white border border-border rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-border">
            <h2 class="font-bold">Riwayat Shift</h2>
            <p class="text-xs text-text-secondary mt-1">
                <?= $canViewAll ? 'Seluruh riwayat shift' : 'Riwayat shift milik Anda' ?>
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-background text-text-secondary">
                    <tr>
                        <?php if ($canViewAll): ?>
                            <th class="px-5 py-4">Kasir</th>
                        <?php endif; ?>
                        <th class="px-5 py-4">Waktu Buka</th>
                        <th class="px-5 py-4">Modal Awal</th>
                        <th class="px-5 py-4">Kas Seharusnya</th>
                        <th class="px-5 py-4">Kas Aktual</th>
                        <th class="px-5 py-4">Selisih</th>
                        <th class="px-5 py-4">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border">
                    <?php if (empty($shifts)): ?>
                        <tr>
                            <td colspan="<?= $canViewAll ? 7 : 6 ?>"
                                class="px-5 py-10 text-center text-text-secondary">
                                Belum ada riwayat shift.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($shifts as $shift): ?>
                        <tr>
                            <?php if ($canViewAll): ?>
                                <td class="px-5 py-4 font-medium">
                                    <?= htmlspecialchars($shift['cashier_name']) ?>
                                </td>
                            <?php endif; ?>

                            <td class="px-5 py-4">
                                <?= date('d M Y H:i', strtotime($shift['opened_at'])) ?>
                            </td>

                            <td class="px-5 py-4">
                                <?= rupiah($shift['opening_cash']) ?>
                            </td>

                            <td class="px-5 py-4">
                                <?= $shift['expected_cash'] !== null
                                    ? rupiah($shift['expected_cash'])
                                    : '-' ?>
                            </td>

                            <td class="px-5 py-4">
                                <?= $shift['actual_cash'] !== null
                                    ? rupiah($shift['actual_cash'])
                                    : '-' ?>
                            </td>

                            <td class="px-5 py-4 font-semibold">
                                <?= $shift['cash_difference'] !== null
                                    ? rupiah($shift['cash_difference'])
                                    : '-' ?>
                            </td>

                            <td class="px-5 py-4">
                                <?php if ($shift['status'] === 'open'): ?>
                                    <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full text-xs">
                                        Open
                                    </span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs">
                                        Closed
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
