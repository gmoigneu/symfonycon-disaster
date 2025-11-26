# Database Schema Documentation

## Overview

The Global Disasters Dashboard uses PostgreSQL 16+ as its database backend with Doctrine ORM for data access.

## Entity: GlobalDisaster

**Table Name**: `global_disaster`

### Field Definitions

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | INTEGER | PRIMARY KEY, AUTO INCREMENT | Unique identifier |
| `date` | DATE | NOT NULL, INDEXED | Date when the disaster occurred |
| `country` | VARCHAR(100) | NOT NULL, INDEXED | Name of the country affected |
| `disaster_type` | VARCHAR(50) | NOT NULL, INDEXED | Type of natural disaster |
| `severity_index` | DECIMAL(5,2) | NOT NULL | Severity rating from 0.00 to 10.00 |
| `casualties` | INTEGER | NOT NULL | Number of fatalities |
| `economic_loss_usd` | DECIMAL(15,2) | NOT NULL | Financial impact in US dollars |
| `response_time_hours` | DECIMAL(8,2) | NOT NULL | Time to respond in hours |
| `aid_amount_usd` | DECIMAL(15,2) | NOT NULL | Aid provided in US dollars |
| `response_efficiency_score` | DECIMAL(5,2) | NOT NULL | Efficiency score from 0.00 to 100.00 |
| `recovery_days` | INTEGER | NOT NULL | Number of days to recover |
| `latitude` | DECIMAL(10,6) | NOT NULL | Geographic latitude coordinate |
| `longitude` | DECIMAL(10,6) | NOT NULL | Geographic longitude coordinate |

### Indexes

| Index Name | Columns | Purpose |
|------------|---------|---------|
| PRIMARY | id | Primary key |
| idx_date | date | Fast date-based queries |
| idx_country | country | Fast country-based filtering |
| idx_disaster_type | disaster_type | Fast type-based filtering |
| idx_date_country | date, country | Compound queries by date and country |
| idx_type_severity | disaster_type, severity_index | Queries filtering by type and severity |

## Disaster Types

The following disaster types are present in the dataset:

1. Earthquake
2. Hurricane
3. Tornado
4. Flood
5. Wildfire
6. Drought
7. Extreme Heat
8. Storm Surge
9. Volcanic Eruption
10. Landslide

## Countries in Dataset

The dataset includes disasters from 18+ countries:

- Australia
- Bangladesh
- Brazil
- Canada
- China
- France
- Germany
- Greece
- India
- Indonesia
- Italy
- Japan
- Mexico
- Nigeria
- Philippines
- South Africa
- Spain
- USA

## Data Statistics

- **Total Records**: 50,000
- **Date Range**: 2018-01-01 to 2024-12-31
- **Severity Range**: 0.00 - 10.00
- **Efficiency Score Range**: 0.00 - 100.00

## Sample Queries

### Count disasters by type
```sql
SELECT disaster_type, COUNT(*) as count
FROM global_disaster
GROUP BY disaster_type
ORDER BY count DESC;
```

### Total casualties by country
```sql
SELECT country, SUM(casualties) as total_casualties
FROM global_disaster
GROUP BY country
ORDER BY total_casualties DESC;
```

### Average economic loss by year
```sql
SELECT EXTRACT(YEAR FROM date) as year, AVG(economic_loss_usd) as avg_loss
FROM global_disaster
GROUP BY EXTRACT(YEAR FROM date)
ORDER BY year;
```

### Disasters with high severity
```sql
SELECT * FROM global_disaster
WHERE severity_index >= 8.0
ORDER BY severity_index DESC;
```

## Doctrine Entity Reference

```php
#[ORM\Entity(repositoryClass: GlobalDisasterRepository::class)]
#[ORM\Table(name: 'global_disaster')]
#[ORM\Index(columns: ['date'], name: 'idx_date')]
#[ORM\Index(columns: ['country'], name: 'idx_country')]
#[ORM\Index(columns: ['disaster_type'], name: 'idx_disaster_type')]
#[ORM\Index(columns: ['date', 'country'], name: 'idx_date_country')]
#[ORM\Index(columns: ['disaster_type', 'severity_index'], name: 'idx_type_severity')]
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

    #[ORM\Column]
    private ?int $casualties = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $economicLossUsd = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $responseTimeHours = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $aidAmountUsd = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $responseEfficiencyScore = null;

    #[ORM\Column]
    private ?int $recoveryDays = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    private ?string $longitude = null;
}
```
