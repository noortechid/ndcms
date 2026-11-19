<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

final class ShiftService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function openShift(
        int $userId,
        float $openingCash,
        string $note = ''
    ): int {
        if ($openingCash < 0) {
            throw new RuntimeException('Modal awal tidak boleh negatif.');
        }

        $this->db->beginTransaction();

        try {
            // Kunci baris user agar dua request tidak membuka
            // shift secara bersamaan.
            $stmt = $this->db->prepare(
                'SELECT id, is_active
                 FROM users
                 WHERE id = :id
                 FOR UPDATE'
            );

            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();

            if (!$user || !(bool) $user['is_active']) {
                throw new RuntimeException('Akun pengguna tidak aktif.');
            }

            $stmt = $this->db->prepare(
                "SELECT id
                 FROM cashier_shifts
                 WHERE user_id = :user_id
                 AND status = 'open'
                 LIMIT 1"
            );

            $stmt->execute(['user_id' => $userId]);

            if ($stmt->fetch()) {
                throw new RuntimeException(
                    'Kamu masih memiliki shift yang terbuka.'
                );
            }

            $stmt = $this->db->prepare(
                "INSERT INTO cashier_shifts (
                    user_id,
                    opening_cash,
                    opening_note,
                    status,
                    opened_at
                ) VALUES (
                    :user_id,
                    :opening_cash,
                    :opening_note,
                    'open',
                    NOW()
                )"
            );

            $stmt->execute([
                'user_id' => $userId,
                'opening_cash' => $openingCash,
                'opening_note' => $note ?: null,
            ]);

            $id = (int) $this->db->lastInsertId();

            $this->db->commit();

            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function closeShift(
        int $shiftId,
        int $closedBy,
        float $actualCash,
        string $note = ''
    ): void {
        if ($actualCash < 0) {
            throw new RuntimeException(
                'Jumlah uang aktual tidak boleh negatif.'
            );
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'SELECT *
                 FROM cashier_shifts
                 WHERE id = :id
                 FOR UPDATE'
            );

            $stmt->execute(['id' => $shiftId]);
            $shift = $stmt->fetch();

            if (!$shift || $shift['status'] !== 'open') {
                throw new RuntimeException(
                    'Shift tidak ditemukan atau sudah ditutup.'
                );
            }

            $expectedCash =
                (float) $shift['opening_cash']
                + (float) $shift['cash_sales']
                + (float) $shift['cash_in']
                - (float) $shift['cash_out'];

            $difference = $actualCash - $expectedCash;

            $stmt = $this->db->prepare(
                "UPDATE cashier_shifts SET
                    closed_by = :closed_by,
                    expected_cash = :expected_cash,
                    actual_cash = :actual_cash,
                    cash_difference = :cash_difference,
                    closing_note = :closing_note,
                    status = 'closed',
                    closed_at = NOW()
                 WHERE id = :id
                 AND status = 'open'"
            );

            $stmt->execute([
                'id' => $shiftId,
                'closed_by' => $closedBy,
                'expected_cash' => $expectedCash,
                'actual_cash' => $actualCash,
                'cash_difference' => $difference,
                'closing_note' => $note ?: null,
            ]);

            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException(
                    'Shift gagal ditutup. Silakan coba kembali.'
                );
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}
