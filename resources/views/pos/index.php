<?php

declare(strict_types=1);

$pageTitle = 'Point of Sale';

$money = static fn ($value): string =>
    'Rp ' . number_format((float) $value, 0, ',', '.');

?>

<div class="min-h-screen flex flex-col">

    <!-- Header -->
    <header class="h-16 bg-white border-b border-[#eadfd9] px-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-[#302923]">ND Coffee</h1>
            <p class="text-xs text-[#92847d]">Point of Sale</p>
        </div>

        <div class="flex items-center gap-5">
            <div class="text-right">
                <p class="text-sm font-semibold">
                    <?= htmlspecialchars($user['name'] ?? 'Kasir', ENT_QUOTES, 'UTF-8') ?>
                </p>
                <p class="text-xs text-[#92847d]">Kasir</p>
            </div>

            <a href="/"
               class="text-sm text-[#ad755e] hover:text-[#302923]">
                Kembali ke Dashboard
            </a>
        </div>
    </header>

    <?php if (!$shift): ?>

        <main class="flex-1 flex items-center justify-center p-6">
            <div class="max-w-md w-full bg-white rounded-2xl border border-[#eadfd9] p-8 text-center">
                <div class="text-4xl mb-4">☕</div>

                <h2 class="text-xl font-semibold mb-2">
                    Belum Ada Shift Aktif
                </h2>

                <p class="text-sm text-[#92847d] mb-6">
                    Silakan buka shift terlebih dahulu sebelum melakukan transaksi.
                </p>

                <a href="/shifts"
                   class="inline-block bg-[#ad755e] hover:bg-[#80513e] text-white px-6 py-3 rounded-xl text-sm font-medium">
                    Buka Halaman Shift
                </a>
            </div>
        </main>

    <?php else: ?>

        <main class="flex-1 grid grid-cols-1 xl:grid-cols-[1fr_420px] gap-6 p-6">

            <!-- Product Catalog -->
            <section class="min-w-0">

                <div class="mb-6">
                    <h2 class="text-2xl font-semibold">Menu Produk</h2>
                    <p class="text-sm text-[#92847d] mt-1">
                        Pilih produk untuk ditambahkan ke keranjang.
                    </p>
                </div>

                <div class="mb-5">
                    <input
                        type="search"
                        id="product-search"
                        placeholder="Cari nama produk..."
                        class="w-full bg-white border border-[#eadfd9] rounded-xl px-4 py-3 text-sm outline-none focus:border-[#ad755e]"
                    >
                </div>

                <div id="product-grid"
                     class="grid grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 gap-4">

                    <?php foreach ($products as $product): ?>

                        <button
                            type="button"
                            class="product-card text-left bg-white border border-[#eadfd9] rounded-2xl p-4 hover:border-[#c99a86] hover:shadow-sm transition"
                            data-id="<?= (int) $product['id'] ?>"
                            data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                            data-price="<?= htmlspecialchars((string) $product['price'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                            <div class="aspect-square bg-[#f8eee9] rounded-xl mb-3 flex items-center justify-center overflow-hidden">
                                <?php if (!empty($product['image_path'])): ?>
                                    <img
                                        src="<?= htmlspecialchars($product['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                        class="w-full h-full object-cover"
                                    >
                                <?php else: ?>
                                    <span class="text-4xl">☕</span>
                                <?php endif; ?>
                            </div>

                            <p class="font-semibold text-sm">
                                <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p class="text-xs text-[#92847d] mt-1">
                                <?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p class="font-semibold text-[#80513e] mt-3">
                                <?= $money($product['price']) ?>
                            </p>
                        </button>

                    <?php endforeach; ?>

                </div>

                <?php if (empty($products)): ?>
                    <div class="bg-white border border-[#eadfd9] rounded-2xl p-8 text-center text-sm text-[#92847d]">
                        Belum ada produk yang tersedia untuk dijual.
                    </div>
                <?php endif; ?>

            </section>

            <!-- Cart -->
            <aside class="bg-white border border-[#eadfd9] rounded-2xl p-5 flex flex-col">
                <div class="flex items-center justify-between border-b border-[#F1E9E5] pb-4">
                    <div>
                        <h2 class="text-lg font-semibold">Keranjang</h2>
                        <p class="text-xs text-[#92847d]">Pesanan pelanggan</p>
                    </div>

                    <button type="button" id="clear-cart"
                            class="text-xs text-red-500 hover:text-red-700">
                        Kosongkan
                    </button>
                </div>

                <div id="cart-items" class="flex-1 py-4 space-y-3 min-h-40">
                    <p id="empty-cart" class="text-sm text-center text-[#92847d] py-10">
                        Keranjang masih kosong.
                    </p>
                </div>

                <form action="/transactions" method="POST" id="checkout-form">
                    <input type="hidden" name="_token"
                           value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div id="cart-inputs"></div>

                    <div class="border-t border-[#F1E9E5] pt-4 space-y-3">

                        <div class="flex justify-between text-sm">
                            <span class="text-[#92847d]">Subtotal</span>
                            <strong id="subtotal">Rp 0</strong>
                        </div>

                        <div>
                            <label for="discount" class="text-sm text-[#92847d]">
                                Diskon (Rp)
                            </label>
                            <input
                                type="number"
                                name="discount"
                                id="discount"
                                min="0"
                                value="0"
                                class="w-full mt-1 border border-[#eadfd9] rounded-lg px-3 py-2 text-sm"
                            >
                        </div>

                        <div class="flex justify-between text-base">
                            <span class="font-semibold">Total</span>
                            <strong id="total" class="text-[#80513e]">Rp 0</strong>
                        </div>

                        <div>
                            <label for="payment_method" class="text-sm text-[#92847d]">
                                Metode Pembayaran
                            </label>
                            <select name="payment_method" id="payment_method"
                                    class="w-full mt-1 border border-[#eadfd9] rounded-lg px-3 py-2 text-sm">
                                <option value="cash">Tunai</option>
                                <option value="qris">QRIS</option>
                                <option value="debit">Debit</option>
                            </select>
                        </div>

                        <div>
                            <label for="amount_paid" class="text-sm text-[#92847d]">
                                Uang Diterima (Rp)
                            </label>
                            <input
                                type="number"
                                name="amount_paid"
                                id="amount_paid"
                                min="0"
                                value="0"
                                class="w-full mt-1 border border-[#eadfd9] rounded-lg px-3 py-2 text-sm"
                            >
                        </div>

                        <div class="flex justify-between text-sm">
                            <span class="text-[#92847d]">Kembalian</span>
                            <strong id="change">Rp 0</strong>
                        </div>

                        <button
                            type="submit"
                            id="checkout-button"
                            disabled
                            class="w-full bg-[#ad755e] hover:bg-[#80513e] disabled:bg-[#DCCBC5] disabled:cursor-not-allowed text-white rounded-xl py-3 font-semibold text-sm transition"
                        >
                            Proses Pembayaran
                        </button>

                    </div>
                </form>
            </aside>

        </main>

    <?php endif; ?>

</div>
