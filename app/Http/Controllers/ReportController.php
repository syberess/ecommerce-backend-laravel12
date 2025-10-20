<?php

namespace App\Http\Controllers;

use App\Core\Services\ReportService;

class ReportController extends Controller
{
    protected ReportService $service;

    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    public function sales()
    {
        $report = $this->service->getSalesSummary();
        return response()->json($report);
    }
}
