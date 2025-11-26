<?php

declare(strict_types=1);

namespace App\Mcp\Tool;

use App\Repository\GlobalDisasterRepository;
use Mcp\Capability\Attribute\McpTool;

/**
 * MCP Tool for retrieving global disaster statistics.
 */
class DisasterStatisticsTool
{
    public function __construct(
        private readonly GlobalDisasterRepository $repository,
    ) {
    }

    /**
     * Get summary statistics for all disasters in the database.
     *
     * @return array<string, mixed> Summary statistics including totals and averages
     */
    #[McpTool(name: 'get_disaster_statistics', description: 'Get summary statistics for all disasters: total count, casualties, economic loss, aid amount, average response time and recovery days.')]
    public function getStatistics(): array
    {
        return [
            'total_disasters' => $this->repository->getTotalCount(),
            'total_casualties' => $this->repository->getTotalCasualties(),
            'total_economic_loss_usd' => $this->repository->getTotalEconomicLoss(),
            'total_aid_amount_usd' => $this->repository->getTotalAidAmount(),
            'average_response_time_hours' => round($this->repository->getAverageResponseTime(), 1),
            'average_recovery_days' => round($this->repository->getAverageRecoveryDays(), 0),
        ];
    }

    /**
     * Get disaster count grouped by year.
     *
     * @return array<int, array{year: string, count: int}> Disasters per year
     */
    #[McpTool(name: 'get_disasters_by_year', description: 'Get the number of disasters grouped by year. Returns an array of year and count pairs.')]
    public function getDisastersByYear(): array
    {
        return $this->repository->getDisasterCountByYear();
    }

    /**
     * Get casualties grouped by year.
     *
     * @return array<int, array{year: string, casualties: int}> Casualties per year
     */
    #[McpTool(name: 'get_casualties_by_year', description: 'Get the total casualties grouped by year. Returns an array of year and casualties pairs.')]
    public function getCasualtiesByYear(): array
    {
        return $this->repository->getCasualtiesByYear();
    }

    /**
     * Get disaster count grouped by country.
     *
     * @param int $limit Maximum number of countries to return (default 10)
     * @return array<int, array{country: string, count: int}> Top countries by disaster count
     */
    #[McpTool(name: 'get_disasters_by_country', description: 'Get the number of disasters grouped by country, ordered by count descending. Returns top N countries.')]
    public function getDisastersByCountry(int $limit = 10): array
    {
        return $this->repository->getDisasterCountByCountry($limit);
    }

    /**
     * Get the deadliest countries by total casualties.
     *
     * @param int $limit Maximum number of countries to return (default 10)
     * @return array<int, array{country: string, totalCasualties: int, disasterCount: int}> Deadliest countries
     */
    #[McpTool(name: 'get_deadliest_countries', description: 'Get countries ranked by total casualties (most deadly first). Returns country name, total casualties, and disaster count.')]
    public function getDeadliestCountries(int $limit = 10): array
    {
        return $this->repository->getDeadliestCountries($limit);
    }

    /**
     * Get the safest countries by average casualties per disaster.
     *
     * @param int $limit Maximum number of countries to return (default 10)
     * @return array<int, array{country: string, avgCasualties: float, disasterCount: int}> Safest countries
     */
    #[McpTool(name: 'get_safest_countries', description: 'Get countries ranked by lowest average casualties per disaster (safest first). Only includes countries with at least 5 disasters.')]
    public function getSafestCountries(int $limit = 10): array
    {
        return $this->repository->getSafestCountries($limit);
    }

    /**
     * Get disaster count grouped by type.
     *
     * @return array<int, array{disasterType: string, count: int}> Disasters by type
     */
    #[McpTool(name: 'get_disasters_by_type', description: 'Get the number of disasters grouped by disaster type (Earthquake, Hurricane, Flood, etc.).')]
    public function getDisastersByType(): array
    {
        return $this->repository->getDisasterCountByType();
    }

    /**
     * Get average economic loss grouped by disaster type.
     *
     * @return array<int, array{disasterType: string, avgLoss: float}> Average economic loss by type
     */
    #[McpTool(name: 'get_economic_loss_by_type', description: 'Get the average economic loss in USD grouped by disaster type, ordered by highest loss first.')]
    public function getEconomicLossByType(): array
    {
        return $this->repository->getAverageEconomicLossByType();
    }

    /**
     * Get all available disaster types.
     *
     * @return string[] List of disaster types
     */
    #[McpTool(name: 'get_disaster_types', description: 'Get a list of all disaster types in the database (e.g., Earthquake, Hurricane, Flood, Wildfire, etc.).')]
    public function getDisasterTypes(): array
    {
        return $this->repository->findAllDisasterTypes();
    }

    /**
     * Get all countries in the database.
     *
     * @return string[] List of countries
     */
    #[McpTool(name: 'get_countries', description: 'Get a list of all countries that have disaster records in the database.')]
    public function getCountries(): array
    {
        return $this->repository->findAllCountries();
    }
}
