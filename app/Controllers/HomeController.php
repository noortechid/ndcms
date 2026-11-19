<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Dashboard;

final class HomeController extends Controller
{
    public function index(): void
    {
        $dashboard = new Dashboard();

        $this->view('home', [
            'appName' => 'ND Coffee Management System',
            'user' => Auth::user(),
            'csrfToken' => Csrf::token(),

            'statistics' => $dashboard->summary(),
            'weeklySales' => $dashboard->weeklySales(),
            'recentTransactions' => $dashboard->recentTransactions(),
            'lowStockIngredients' => $dashboard->lowStockIngredients(),
        ]);
    }
}
