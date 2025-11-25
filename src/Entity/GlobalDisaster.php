<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GlobalDisasterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GlobalDisasterRepository::class)]
#[ORM\Table(name: 'global_disaster')]
#[ORM\Index(columns: ['date'], name: 'idx_global_disaster_date')]
#[ORM\Index(columns: ['country'], name: 'idx_global_disaster_country')]
#[ORM\Index(columns: ['disaster_type'], name: 'idx_global_disaster_type')]
#[ORM\Index(columns: ['date', 'country'], name: 'idx_global_disaster_date_country')]
#[ORM\Index(columns: ['disaster_type', 'severity_index'], name: 'idx_global_disaster_type_severity')]
class GlobalDisaster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 100)]
    private ?string $country = null;

    #[ORM\Column(length: 50)]
    private ?string $disasterType = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $severityIndex = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $casualties = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $economicLossUsd = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $responseTimeHours = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $aidAmountUsd = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $responseEfficiencyScore = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $recoveryDays = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    private ?string $longitude = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getDisasterType(): ?string
    {
        return $this->disasterType;
    }

    public function setDisasterType(string $disasterType): static
    {
        $this->disasterType = $disasterType;

        return $this;
    }

    public function getSeverityIndex(): ?string
    {
        return $this->severityIndex;
    }

    public function setSeverityIndex(string $severityIndex): static
    {
        $this->severityIndex = $severityIndex;

        return $this;
    }

    public function getCasualties(): ?int
    {
        return $this->casualties;
    }

    public function setCasualties(int $casualties): static
    {
        $this->casualties = $casualties;

        return $this;
    }

    public function getEconomicLossUsd(): ?string
    {
        return $this->economicLossUsd;
    }

    public function setEconomicLossUsd(string $economicLossUsd): static
    {
        $this->economicLossUsd = $economicLossUsd;

        return $this;
    }

    public function getResponseTimeHours(): ?string
    {
        return $this->responseTimeHours;
    }

    public function setResponseTimeHours(string $responseTimeHours): static
    {
        $this->responseTimeHours = $responseTimeHours;

        return $this;
    }

    public function getAidAmountUsd(): ?string
    {
        return $this->aidAmountUsd;
    }

    public function setAidAmountUsd(string $aidAmountUsd): static
    {
        $this->aidAmountUsd = $aidAmountUsd;

        return $this;
    }

    public function getResponseEfficiencyScore(): ?string
    {
        return $this->responseEfficiencyScore;
    }

    public function setResponseEfficiencyScore(string $responseEfficiencyScore): static
    {
        $this->responseEfficiencyScore = $responseEfficiencyScore;

        return $this;
    }

    public function getRecoveryDays(): ?int
    {
        return $this->recoveryDays;
    }

    public function setRecoveryDays(int $recoveryDays): static
    {
        $this->recoveryDays = $recoveryDays;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }
}
