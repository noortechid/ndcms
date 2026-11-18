<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Report;
use DateTimeImmutable;

final class ReportController extends Controller
{
    private Report $report;

    public function __construct()
    {
        $this->report = new Report();
    }

    /**
     * Menampilkan halaman laporan penjualan.
     */
    public function index(): void
    {
        $today = date('Y-m-d');

        $startDate = $this->validateDate(
            $_GET['start_date'] ?? $today
        );

        $endDate = $this->validateDate(
            $_GET['end_date'] ?? $today
        );

        // Pastikan tanggal awal tidak melebihi tanggal akhir.
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $this->view('reports/index', [
            'title' => 'Laporan Penjualan',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $this->report->summary(
                $startDate,
                $endDate
            ),
            'paymentSummary' => $this->report->paymentSummary(
                $startDate,
                $endDate
            ),
            'dailySales' => $this->report->dailySales(
                $startDate,
                $endDate
            ),
            'topProducts' => $this->report->topProducts(
                $startDate,
                $endDate
            ),
            'transactions' => $this->report->transactionHistory(
                $startDate,
                $endDate
            ),
        ]);
    }

    /**
     * Memvalidasi format tanggal Y-m-d.
     */
    private function validateDate(string $date): string
    {
        $parsed = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $date
        );

        $errors = DateTimeImmutable::getLastErrors();

        if (
            !$parsed ||
            ($errors !== false &&
                ($errors['warning_count'] > 0 ||
                 $errors['error_count'] > 0)) ||
            $parsed->format('Y-m-d') !== $date
        ) {
            return date('Y-m-d');
        }

        return $date;
    }
}
