<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardMetricsAction;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    /**
     * Display the Executive Operations & Fleet Analytics Dashboard.
     */
    public function __invoke(GetDashboardMetricsAction $action): Response
    {
        return Inertia::render('Dashboard', $action->execute());
    }
}
