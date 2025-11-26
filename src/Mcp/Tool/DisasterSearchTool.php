<?php

declare(strict_types=1);

namespace App\Mcp\Tool;

use App\Entity\GlobalDisaster;
use App\Repository\GlobalDisasterRepository;
use Mcp\Capability\Attribute\McpTool;

/**
 * MCP Tool for searching disasters with various filters.
 */
class DisasterSearchTool
{
    public function __construct(
        private readonly GlobalDisasterRepository $repository,
    ) {
    }

    /**
     * Search disasters by country.
     *
     * @param string $country The country name to search for
     * @return array<int, array<string, mixed>> List of disasters in the country
     */
    #[McpTool(name: 'search_by_country', description: 'Search for disasters in a specific country. Returns detailed disaster records including date, type, severity, casualties, and economic loss.')]
    public function searchByCountry(string $country): array
    {
        $disasters = $this->repository->findByCountry($country);

        return $this->formatDisasters($disasters);
    }

    /**
     * Search disasters by type.
     *
     * @param string $disasterType The disaster type (e.g., Earthquake, Hurricane, Flood)
     * @return array<int, array<string, mixed>> List of disasters of that type
     */
    #[McpTool(name: 'search_by_type', description: 'Search for disasters by type (e.g., Earthquake, Hurricane, Tornado, Flood, Wildfire, Drought, Extreme Heat, Storm Surge, Volcanic Eruption, Landslide).')]
    public function searchByType(string $disasterType): array
    {
        $disasters = $this->repository->findByDisasterType($disasterType);

        return $this->formatDisasters($disasters);
    }

    /**
     * Search disasters by date range.
     *
     * @param string $startDate Start date in Y-m-d format
     * @param string $endDate End date in Y-m-d format
     * @return array<int, array<string, mixed>> List of disasters in the date range
     */
    #[McpTool(name: 'search_by_date_range', description: 'Search for disasters within a date range. Dates should be in YYYY-MM-DD format (e.g., 2020-01-01 to 2020-12-31).')]
    public function searchByDateRange(string $startDate, string $endDate): array
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $disasters = $this->repository->findByDateRange($start, $end);

        return $this->formatDisasters($disasters);
    }

    /**
     * Search disasters by minimum severity.
     *
     * @param float $minSeverity Minimum severity index (0.0 to 10.0)
     * @return array<int, array<string, mixed>> List of disasters with severity above threshold
     */
    #[McpTool(name: 'search_by_severity', description: 'Search for disasters with severity index at or above a threshold. Severity ranges from 0.0 (low) to 10.0 (extreme).')]
    public function searchBySeverity(float $minSeverity): array
    {
        $disasters = $this->repository->findBySeverityAbove($minSeverity);

        return $this->formatDisasters($disasters);
    }

    /**
     * Format disaster entities to arrays.
     *
     * @param GlobalDisaster[] $disasters
     * @return array<int, array<string, mixed>>
     */
    private function formatDisasters(array $disasters): array
    {
        return array_map(fn (GlobalDisaster $d) => [
            'id' => $d->getId(),
            'date' => $d->getDate()?->format('Y-m-d'),
            'country' => $d->getCountry(),
            'disaster_type' => $d->getDisasterType(),
            'severity_index' => (float) $d->getSeverityIndex(),
            'casualties' => $d->getCasualties(),
            'economic_loss_usd' => (float) $d->getEconomicLossUsd(),
            'response_time_hours' => (float) $d->getResponseTimeHours(),
            'aid_amount_usd' => (float) $d->getAidAmountUsd(),
            'response_efficiency_score' => (float) $d->getResponseEfficiencyScore(),
            'recovery_days' => $d->getRecoveryDays(),
            'latitude' => (float) $d->getLatitude(),
            'longitude' => (float) $d->getLongitude(),
        ], $disasters);
    }
}
