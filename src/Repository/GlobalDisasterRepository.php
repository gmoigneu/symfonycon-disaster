<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GlobalDisaster;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GlobalDisaster>
 */
class GlobalDisasterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GlobalDisaster::class);
    }

    /**
     * @return GlobalDisaster[]
     */
    public function findByCountry(string $country): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.country = :country')
            ->setParameter('country', $country)
            ->orderBy('g.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return GlobalDisaster[]
     */
    public function findByDisasterType(string $disasterType): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.disasterType = :type')
            ->setParameter('type', $disasterType)
            ->orderBy('g.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return GlobalDisaster[]
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.date >= :startDate')
            ->andWhere('g.date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('g.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return GlobalDisaster[]
     */
    public function findBySeverityAbove(float $minSeverity): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.severityIndex >= :minSeverity')
            ->setParameter('minSeverity', $minSeverity)
            ->orderBy('g.severityIndex', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return string[]
     */
    public function findAllDisasterTypes(): array
    {
        $result = $this->createQueryBuilder('g')
            ->select('DISTINCT g.disasterType')
            ->orderBy('g.disasterType', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'disasterType');
    }

    /**
     * @return string[]
     */
    public function findAllCountries(): array
    {
        $result = $this->createQueryBuilder('g')
            ->select('DISTINCT g.country')
            ->orderBy('g.country', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'country');
    }

    /**
     * Get total count of disasters.
     */
    public function getTotalCount(): int
    {
        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get total casualties across all disasters.
     */
    public function getTotalCasualties(): int
    {
        return (int) $this->createQueryBuilder('g')
            ->select('SUM(g.casualties)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get total economic loss in USD.
     */
    public function getTotalEconomicLoss(): float
    {
        return (float) $this->createQueryBuilder('g')
            ->select('SUM(g.economicLossUsd)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get total aid amount in USD.
     */
    public function getTotalAidAmount(): float
    {
        return (float) $this->createQueryBuilder('g')
            ->select('SUM(g.aidAmountUsd)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get disaster count grouped by year.
     *
     * @return array<int, array{year: string, count: int}>
     */
    public function getDisasterCountByYear(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT EXTRACT(YEAR FROM date) as year, COUNT(*) as count
                FROM global_disaster
                GROUP BY EXTRACT(YEAR FROM date)
                ORDER BY year";

        return $conn->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * Get casualties grouped by year.
     *
     * @return array<int, array{year: string, casualties: int}>
     */
    public function getCasualtiesByYear(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT EXTRACT(YEAR FROM date) as year, SUM(casualties) as casualties
                FROM global_disaster
                GROUP BY EXTRACT(YEAR FROM date)
                ORDER BY year";

        return $conn->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * Get disaster count grouped by country.
     *
     * @return array<int, array{country: string, count: int}>
     */
    public function getDisasterCountByCountry(int $limit = 10): array
    {
        return $this->createQueryBuilder('g')
            ->select('g.country, COUNT(g.id) as count')
            ->groupBy('g.country')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get countries ranked by total casualties (deadliest).
     *
     * @return array<int, array{country: string, totalCasualties: int, disasterCount: int}>
     */
    public function getDeadliestCountries(int $limit = 10): array
    {
        return $this->createQueryBuilder('g')
            ->select('g.country, SUM(g.casualties) as totalCasualties, COUNT(g.id) as disasterCount')
            ->groupBy('g.country')
            ->orderBy('totalCasualties', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get countries ranked by lowest average casualties per disaster (safest).
     *
     * @return array<int, array{country: string, avgCasualties: float, disasterCount: int}>
     */
    public function getSafestCountries(int $limit = 10): array
    {
        return $this->createQueryBuilder('g')
            ->select('g.country, AVG(g.casualties) as avgCasualties, COUNT(g.id) as disasterCount')
            ->groupBy('g.country')
            ->having('COUNT(g.id) >= 5')
            ->orderBy('avgCasualties', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get disaster count grouped by type.
     *
     * @return array<int, array{disasterType: string, count: int}>
     */
    public function getDisasterCountByType(): array
    {
        return $this->createQueryBuilder('g')
            ->select('g.disasterType, COUNT(g.id) as count')
            ->groupBy('g.disasterType')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get average response time in hours.
     */
    public function getAverageResponseTime(): float
    {
        return (float) $this->createQueryBuilder('g')
            ->select('AVG(g.responseTimeHours)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get average recovery days.
     */
    public function getAverageRecoveryDays(): float
    {
        return (float) $this->createQueryBuilder('g')
            ->select('AVG(g.recoveryDays)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get average economic loss by disaster type.
     *
     * @return array<int, array{disasterType: string, avgLoss: float}>
     */
    public function getAverageEconomicLossByType(): array
    {
        return $this->createQueryBuilder('g')
            ->select('g.disasterType, AVG(g.economicLossUsd) as avgLoss')
            ->groupBy('g.disasterType')
            ->orderBy('avgLoss', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
