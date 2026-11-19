<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Shift;
use App\Services\ShiftService;
use RuntimeException;

final class ShiftController extends Controller
{
    private Shift $shifts;
    private ShiftService $service;

    public function __construct()
    {
        $this->shifts = new Shift();
        $this->service = new ShiftService();
    }

    public function index(): void
    {
        $user = Auth::user();
        $userId = (int) $user['id'];

        $canViewAll = in_array(
            $user['role'],
            ['owner', 'admin'],
            true
        );

        $this->view('shifts/index', [
            'currentShift' => $this->shifts->findOpenByUser($userId),
            'shifts' => $canViewAll
                ? $this->shifts->all()
                : $this->shifts->allByUser($userId),
            'canViewAll' => $canViewAll,
            'user' => $user,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function open(): void
    {
        if (!$this->verifyCsrf()) {
            return;
        }

        $user = Auth::user();

        $openingCash = $_POST['opening_cash'] ?? '';

        if (!is_numeric($openingCash) || (float) $openingCash < 0) {
            $_SESSION['flash_error'] = 'Modal awal tidak valid.';
            header('Location: /shifts');
            exit;
        }

        try {
            $this->service->openShift(
                (int) $user['id'],
                (float) $openingCash,
                trim($_POST['opening_note'] ?? '')
            );

            $_SESSION['flash_success'] = 'Shift berhasil dibuka.';
        } catch (RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: /shifts');
        exit;
    }

    public function close(): void
    {
        if (!$this->verifyCsrf()) {
            return;
        }

        $user = Auth::user();
        $shiftId = (int) ($_POST['shift_id'] ?? 0);
        $actualCash = $_POST['actual_cash'] ?? '';

        if (!is_numeric($actualCash) || (float) $actualCash < 0) {
            $_SESSION['flash_error'] = 'Jumlah uang aktual tidak valid.';
            header('Location: /shifts');
            exit;
        }

        $shift = $this->shifts->findById($shiftId);

        if (!$shift) {
            http_response_code(404);
            exit('Shift not found.');
        }

        $canCloseOthers = in_array(
            $user['role'],
            ['owner', 'admin'],
            true
        );

        if (
            (int) $shift['user_id'] !== (int) $user['id']
            && !$canCloseOthers
        ) {
            http_response_code(403);
            exit('You cannot close another user shift.');
        }

        try {
            $this->service->closeShift(
                $shiftId,
                (int) $user['id'],
                (float) $actualCash,
                trim($_POST['closing_note'] ?? '')
            );

            $_SESSION['flash_success'] = 'Shift berhasil ditutup.';
        } catch (RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: /shifts');
        exit;
    }

    private function verifyCsrf(): bool
    {
        if (Csrf::verify($_POST['_token'] ?? null)) {
            return true;
        }

        http_response_code(419);
        exit('419 - Invalid CSRF Token');
    }
}
