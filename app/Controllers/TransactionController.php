<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Product;
use App\Models\Shift;
use App\Services\TransactionService;
use App\Models\Transaction;
use InvalidArgumentException;
use Throwable;

final class TransactionController extends Controller
{
    private Product $products;
    private Shift $shifts;
    private TransactionService $transactions;
    private Transaction $transactionModel;

    public function __construct()
    {
        $this->products = new Product();
        $this->shifts = new Shift();
        $this->transactions = new TransactionService();
        $this->transactionModel = new Transaction();
    }

    /**
     * Halaman utama POS.
     */
    public function index(): void
    {
        $user = Auth::user();

        if (!$user) {
            header('Location: /login');
            exit;
        }

        $shift = $this->shifts->findOpenByUser((int) $user['id']);

        $this->view('pos/index', [
            'user' => $user,
            'shift' => $shift,
            'products' => $shift
                ? $this->products->availableForPos()
                : [],
            'csrfToken' => Csrf::token(),
        ], 'layouts/pos');
    }

    /**
     * Proses pembayaran POS.
     */
    public function store(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('419 - Invalid CSRF Token');
        }

        $user = Auth::user();

        if (!$user) {
            header('Location: /login');
            exit;
        }

        $shift = $this->shifts->findOpenByUser((int) $user['id']);

        if (!$shift) {
            $_SESSION['flash_error'] = 'Silakan buka shift sebelum melakukan transaksi.';
            header('Location: /pos');
            exit;
        }

        $rawItems = $_POST['items'] ?? null;

        if (!is_array($rawItems)) {
            $_SESSION['flash_error'] = 'Data keranjang tidak valid.';
            header('Location: /pos');
            exit;
        }

        $items = [];

        foreach ($rawItems as $item) {
            if (!is_array($item)) {
                $_SESSION['flash_error'] = 'Data keranjang tidak valid.';
                header('Location: /pos');
                exit;
            }

            $items[] = [
                'product_id' => $item['product_id'] ?? null,
                'quantity' => $item['quantity'] ?? null,
            ];
        }

        try {
            $transactionId = $this->transactions->create([
                'user_id' => (int) $user['id'],
                'shift_id' => (int) $shift['id'],
                'items' => $items,
                'discount' => $_POST['discount'] ?? '0',
                'payment_method' => $_POST['payment_method'] ?? '',
                'amount_paid' => $_POST['amount_paid'] ?? '0',
            ]);

            $_SESSION['flash_success'] = 'Transaksi berhasil disimpan.';

            header('Location: /transactions/show?id=' . $transactionId);
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        } catch (Throwable $e) {
            error_log($e->getMessage());

            $_SESSION['flash_error'] = 'Terjadi kesalahan saat memproses transaksi.';
        }

        header('Location: /pos');
        exit;
    }


/**
 * Halaman riwayat transaksi.
 */
public function history(): void
{
    $user = Auth::user();

    if (!$user) {
        header('Location: /login');
        exit;
    }

    $filters = [
        'keyword' => trim($_GET['keyword'] ?? ''),
        'date_from' => trim($_GET['date_from'] ?? ''),
        'date_to' => trim($_GET['date_to'] ?? ''),
        'status' => trim($_GET['status'] ?? ''),
        'payment_method' => trim($_GET['payment_method'] ?? ''),
    ];

    // Validasi format tanggal.
    foreach (['date_from', 'date_to'] as $field) {
        if (
            $filters[$field] !== ''
            && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters[$field])
        ) {
            $filters[$field] = '';
        }
    }

    if (
        $filters['date_from'] !== ''
        && $filters['date_to'] !== ''
        && $filters['date_from'] > $filters['date_to']
    ) {
        $filters['date_to'] = '';
    }

    // Kasir hanya dapat melihat transaksi miliknya sendiri.
    $userId = ($user['role'] ?? '') === 'cashier'
        ? (int) $user['id']
        : null;

    $transactions = $this->transactionModel->all(
        $filters,
        $userId
    );

    $summary = $this->transactionModel->summary(
        $filters,
        $userId
    );

    $this->view('transactions/index', [
        'transactions' => $transactions,
        'summary' => $summary,
        'filters' => $filters,
    ]);
}

public function show(): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        http_response_code(404);
        exit('Transaksi tidak ditemukan.');
    }

    $transaction = $this->transactionModel->findById($id);

    if (!$transaction) {
        http_response_code(404);
        exit('Transaksi tidak ditemukan.');
    }

    $items = $this->transactionModel->items($id);

    $user = Auth::user();

    if (
        $user
        && in_array($user['role'], ['cashier'], true)
        && (int) $transaction['user_id'] !== (int) $user['id']
    ) {
        http_response_code(403);
        exit('Anda tidak memiliki akses ke transaksi ini.');
    }

    $this->view('transactions/show', [
        'transaction' => $transaction,
        'items' => $items,
    ]);
}
}
