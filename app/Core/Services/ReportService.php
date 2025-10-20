<?php

namespace App\Core\Services;

use App\Core\Interfaces\IReportRepository;

class ReportService
{
    protected IReportRepository $repository;

    public function __construct(IReportRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getSalesSummary()
    {
        return $this->repository->getSalesSummary();
    }
}
