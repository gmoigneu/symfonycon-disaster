# CLAUDE.md - Global Disasters Dashboard

This file provides context for AI assistants working on this project.

## Project Overview

This is a **Global Disasters Dashboard** - a Symfony 7.3 web application that analyzes, visualizes, and provides statistical insights on global disaster data. The application features a dark-themed dashboard with interactive charts and comprehensive statistics.

## Tech Stack

### Backend
- **Framework**: Symfony 7.3 (PHP 8.2+)
- **Database**: PostgreSQL 16+
- **ORM**: Doctrine ORM 3.5

### Frontend
- **JavaScript**: Stimulus.js 3.0 + Hotwired Turbo
- **CSS**: Tailwind CSS 4.1
- **Charts**: Chart.js 4.5
- **Build**: Webpack Encore 5.1

### Deployment
- **Platform**: Upsun (Platform.sh)
- **Project ID**: `esz2g6fiagi32`

## Project Structure

```
src/
├── Controller/
│   └── HomepageController.php    # Main dashboard controller
├── Entity/
│   └── GlobalDisaster.php        # Doctrine entity (12 fields)
├── Repository/
│   └── GlobalDisasterRepository.php  # 20+ query methods
├── Service/
│   └── StatisticsService.php     # Business logic for statistics
└── Command/
    └── ImportGlobalDisastersCommand.php  # CSV data import

templates/
├── base.html.twig                # Base HTML template
└── homepage/index.html.twig      # Dashboard page

assets/
├── app.js                        # Entry point
├── styles/app.css                # Tailwind CSS
└── controllers/
    └── chart_controller.js       # Stimulus chart controller

data/
└── global_disasters.csv          # Dataset (50,000 records)
```

## Database Schema

**Table: `global_disaster`**

| Field | Type | Description |
|-------|------|-------------|
| id | INT (PK) | Primary key |
| date | DATE | Disaster occurrence date (indexed) |
| country | VARCHAR(100) | Country name (indexed) |
| disaster_type | VARCHAR(50) | Type of disaster (indexed) |
| severity_index | DECIMAL(5,2) | Severity rating 0-10 |
| casualties | INT | Number of fatalities |
| economic_loss_usd | DECIMAL(15,2) | Financial impact in USD |
| response_time_hours | DECIMAL(8,2) | Response time in hours |
| aid_amount_usd | DECIMAL(15,2) | Aid provided in USD |
| response_efficiency_score | DECIMAL(5,2) | Efficiency rating 0-100 |
| recovery_days | INT | Recovery period in days |
| latitude | DECIMAL(10,6) | Geographic latitude |
| longitude | DECIMAL(10,6) | Geographic longitude |

**Indexes**: date, country, disaster_type, (date, country), (disaster_type, severity_index)

## Common Commands

```bash
# Install dependencies
composer install
npm install

# Build assets
npm run build           # Production
npm run dev            # Development
npm run watch          # Watch mode

# Database
symfony console doctrine:migrations:migrate
symfony console app:import-global-disasters data/global_disasters.csv --truncate

# Start server
symfony server:start

# Run tests
php bin/phpunit
```

## Key Repository Methods

The `GlobalDisasterRepository` provides these query methods:

- **Filtering**: `findByCountry()`, `findByDisasterType()`, `findByDateRange()`, `findBySeverityAbove()`
- **Lists**: `findAllDisasterTypes()`, `findAllCountries()`
- **Aggregations**: `getTotalCount()`, `getTotalCasualties()`, `getTotalEconomicLoss()`, `getTotalAidAmount()`, `getAverageResponseTime()`, `getAverageRecoveryDays()`
- **Grouped**: `getDisasterCountByYear()`, `getCasualtiesByYear()`, `getDisasterCountByType()`, `getDisasterCountByCountry()`
- **Rankings**: `getDeadliestCountries()`, `getSafestCountries()`, `getAverageEconomicLossByType()`

## Environment Variables

Key variables in `.env.local`:
- `DATABASE_URL` - PostgreSQL connection string
- `ANTHROPIC_API_KEY` - API key for Claude AI integration
- `APP_ENV` - Environment (dev/prod)
- `APP_SECRET` - Application secret

## Development Notes

- The dashboard uses a dark theme with Tailwind CSS (gray-900, gray-800, gray-700)
- Chart.js is integrated via Stimulus controllers for reactive updates
- Data import supports dry-run mode and batch processing (100 records/batch)
- PostgreSQL 16+ is required for the database

## Code Style

- Follow Symfony coding standards
- Use Doctrine repositories for database queries (no raw SQL in controllers)
- Keep business logic in services (StatisticsService pattern)
- Use Stimulus controllers for JavaScript functionality
