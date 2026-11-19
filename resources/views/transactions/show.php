<?php

declare(strict_types=1);

$pageTitle = 'Detail Transaksi';

$money = static fn ($value): string =>
    'Rp ' . number_format((float) $value, 0, ',', '.');

$transactionDate = date(
    'd M Y, H:i',
    strtotime($transaction['created_at'])
);
?>

<div class="min-h-screen bg-[#FAF7F5] py-10 px-4">

    <div class="max-w-2xl mx-auto">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="text-4xl mb-3">☕</div>

            <h1 class="text-2xl font-bold text-[#76564B]">
                ND Coffee
            </h1>

            <p class="text-sm text-[#A18B81] mt-1">
                Detail Transaksi
            </p>
        </div>

        <!-- Receipt -->
        <div class="bg-white rounded-2xl border border-[#EFE6E1] shadow-sm overflow-hidden">

            <div class="p-6 border-b border-dashed border-[#EADFD9]">

                <div class="flex justify-between items-start gap-4 mb-5">
                    <div>
                        <p class="text-xs text-[#A18B81]">Nomor Transaksi</p>

                        <p class="font-bold text-[#76564B] mt-1">
                            <?= htmlspecialchars($transaction['transaction_code'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>

                    <span class="bg-[#EAF4EB] text-[#547B59] text-xs font-semibold px-3 py-1 rounded-full">
                        <?= htmlspecialchars(ucfirst($transaction['status']), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-[#A18B81]">Tanggal</p>
                        <p class="font-medium mt-1">
                            <?= htmlspecialchars($transactionDate, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-[#A18B81]">Kasir</p>
                        <p class="font-medium mt-1">
                            <?= htmlspecialchars($transaction['cashier_username'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                </div>

            </div>

            <!-- Items -->
            <div class="p-6 border-b border-dashed border-[#EADFD9]">

                <h2 class="font-semibold mb-5">Rincian Pesanan</h2>

                <div class="space-y-4">

                    <?php foreach ($items as $item): ?>

                        <div class="flex justify-between gap-4 text-sm">

                            <div class="min-w-0">
                                <p class="font-medium">
                                    <?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?>
                                </p>

                                <p class="text-xs text-[#A18B81] mt-1">
                                    <?= (int) $item['quantity'] ?>
                                    × <?= $money($item['unit_price']) ?>
                                </p>
                            </div>

                            <p class="font-semibold whitespace-nowrap">
                                <?= $money($item['subtotal']) ?>
                            </p>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

            <!-- Payment Summary -->
            <div class="p-6 space-y-3">

                <div class="flex justify-between text-sm">
                    <span class="text-[#A18B81]">Subtotal</span>
                    <span><?= $money($transaction['subtotal']) ?></span>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-[#A18B81]">Diskon</span>
                    <span><?= $money($transaction['discount']) ?></span>
                </div>

                <div class="flex justify-between items-center border-t border-[#F1E9E5] pt-4">
                    <span class="font-semibold">Total Pembayaran</span>

                    <span class="text-xl font-bold text-[#A96F65]">
                        <?= $money($transaction['total']) ?>
                    </span>
                </div>

                <div class="flex justify-between text-sm pt-2">
                    <span class="text-[#A18B81]">Metode Pembayaran</span>

                    <span class="font-medium uppercase">
                        <?= htmlspecialchars($transaction['payment_method'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-[#A18B81]">Uang Diterima</span>
                    <span><?= $money($transaction['amount_paid']) ?></span>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-[#A18B81]">Kembalian</span>
                    <span class="font-semibold">
                        <?= $money($transaction['change_amount']) ?>
                    </span>
                </div>

            </div>

            <div class="bg-[#FAF7F5] p-6 text-center">
                <p class="text-sm font-medium text-[#76564B]">
                    Terima kasih telah berkunjung!
                </p>

                <p class="text-xs text-[#A18B81] mt-1">
                    ND Coffee — Enjoy Every Sip
                </p>
            </div>

        </div>

        <!-- Actions -->
        <div class="flex flex-wrap justify-center gap-3 mt-6">

            <button
                type="button"
                onclick="window.print()"
                class="bg-white border border-[#EADFD9] text-[#76564B] px-5 py-3 rounded-xl text-sm font-medium hover:bg-[#F8F0EC]"
            >
                Cetak Struk
            </button>

            <a
                href="/pos"
                class="bg-[#B98278] text-white px-5 py-3 rounded-xl text-sm font-medium hover:bg-[#A96F65]"
            >
                Transaksi Baru
            </a>

        </div>

    </div>

</div>

<style>
    @media print {
        body {
            background: white !important;
        }

        button,
        a {
            display: none !important;
        }

        .shadow-sm {
            box-shadow: none !important;
        }

        @page {
            margin: 10mm;
        }
    }
</style>
