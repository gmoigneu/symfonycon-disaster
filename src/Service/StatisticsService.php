<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\GlobalDisasterRepository;

class StatisticsService
{
    public function __construct(
        private readonly GlobalDisasterRepository $repository,
    ) {
    }

    /**
     * Get all homepage statistics.
     *
     * @return array<string, mixed>
     */
    public function getHomepageStatistics(): array
    {
        return [
            'summary' => $this->getSummaryStats(),
            'disastersByYear' => $this->repository->getDisasterCountByYear(),
            'casualtiesByYear' => $this->repository->getCasualtiesByYear(),
            'disastersByCountry' => $this->repository->getDisasterCountByCountry(10),
            'deadliestCountries' => $this->repository->getDeadliestCountries(10),
            'safestCountries' => $this->repository->getSafestCountries(10),
            'disastersByType' => $this->repository->getDisasterCountByType(),
            'economicLossByType' => $this->repository->getAverageEconomicLossByType(),
        ];
    }

    /**
     * Get summary statistics (totals and averages).
     *
     * @return array<string, mixed>
     */
    public function getSummaryStats(): array
    {
        return [
            'totalDisasters' => $this->repository->getTotalCount(),
            'totalCasualties' => $this->repository->getTotalCasualties(),
            'totalEconomicLoss' => $this->repository->getTotalEconomicLoss(),
            'totalAidAmount' => $this->repository->getTotalAidAmount(),
            'avgResponseTime' => round($this->repository->getAverageResponseTime(), 1),
            'avgRecoveryDays' => round($this->repository->getAverageRecoveryDays(), 0),
        ];
    }

    /**
     * Format large numbers for display (e.g., 1.5B, 250M).
     */
    public function formatLargeNumber(float $number): string
    {
        if ($number >= 1_000_000_000) {
            return number_format($number / 1_000_000_000, 1) . 'B';
        }
        if ($number >= 1_000_000) {
            return number_format($number / 1_000_000, 1) . 'M';
        }
        if ($number >= 1_000) {
            return number_format($number / 1_000, 1) . 'K';
        }

        return number_format($number, 0);
    }

    /**
     * Format currency for display.
     */
    public function formatCurrency(float $amount): string
    {
        return '$' . $this->formatLargeNumber($amount);
    }
}
