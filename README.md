# Global Disasters Dashboard

A comprehensive web application for analyzing, visualizing, and providing statistical insights on global disaster data. Built with Symfony 7.3 and modern frontend technologies.

## Features

- **Interactive Dashboard**: Dark-themed UI with summary cards and interactive charts
- **Data Visualization**: Multiple chart types (bar, line, doughnut) powered by Chart.js
- **Comprehensive Statistics**: Total disasters, casualties, economic losses, aid amounts, response times
- **Country & Type Analysis**: Rankings by country and disaster type
- **Data Import**: CLI tool for importing disaster data from CSV files

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Symfony 7.3, PHP 8.2+ |
| Database | PostgreSQL 16+ |
| ORM | Doctrine 3.5 |
| Frontend | Stimulus.js, Hotwired Turbo |
| CSS | Tailwind CSS 4.1 |
| Charts | Chart.js 4.5 |
| Build | Webpack Encore |
| Deployment | Upsun (Platform.sh) |

## Requirements

- PHP 8.2 or higher
- PostgreSQL 16 or higher
- Node.js 18+ and npm
- Composer 2.x
- Docker (optional, for containerized development)

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd gd
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

### 3. Configure Environment

Copy the environment file and configure your database:

```bash
cp .env .env.local
```

Edit `.env.local` and set:
```
DATABASE_URL=postgresql://user:password@localhost:5432/global_disasters?serverVersion=16
```

### 4. Start Database (Docker)

```bash
docker-compose up -d
```

### 5. Run Migrations

```bash
symfony console doctrine:migrations:migrate
```

### 6. Import Sample Data

```bash
symfony console app:import-global-disasters data/global_disasters.csv --truncate
```

### 7. Build Assets

```bash
# For development
npm run dev

# For production
npm run build
```

### 8. Start the Server

```bash
symfony server:start
```

Visit `http://localhost:8000` to access the dashboard.

## Development

### Available npm Scripts

| Command | Description |
|---------|-------------|
| `npm run dev` | Build assets for development |
| `npm run watch` | Watch mode with auto-rebuild |
| `npm run dev-server` | Development server with HMR |
| `npm run build` | Production build |

### Symfony Console Commands

| Command | Description |
|---------|-------------|
| `app:import-global-disasters` | Import disaster data from CSV |

#### Import Command Options

```bash
# Standard import
symfony console app:import-global-disasters data/global_disasters.csv

# Dry run (validate without importing)
symfony console app:import-global-disasters data/global_disasters.csv --dry-run

# Truncate existing data before import
symfony console app:import-global-disasters data/global_disasters.csv --truncate
```

## Data Model

The application uses a single entity `GlobalDisaster` with the following fields:

| Field | Type | Description |
|-------|------|-------------|
| date | DATE | Disaster occurrence date |
| country | VARCHAR(100) | Country name |
| disaster_type | VARCHAR(50) | Type (Earthquake, Flood, etc.) |
| severity_index | DECIMAL(5,2) | Severity rating (0-10) |
| casualties | INT | Number of fatalities |
| economic_loss_usd | DECIMAL(15,2) | Financial impact |
| response_time_hours | DECIMAL(8,2) | Response time |
| aid_amount_usd | DECIMAL(15,2) | Aid provided |
| response_efficiency_score | DECIMAL(5,2) | Efficiency (0-100) |
| recovery_days | INT | Recovery period |
| latitude/longitude | DECIMAL(10,6) | Geographic coordinates |

## Dataset

The included dataset (`data/global_disasters.csv`) contains:
- **50,000 records** spanning 2018-2024
- **18+ countries** including USA, Brazil, India, China, Japan, etc.
- **10 disaster types**: Earthquake, Hurricane, Tornado, Flood, Wildfire, Drought, Extreme Heat, Storm Surge, Volcanic Eruption, Landslide

## Testing

```bash
# Run all tests
php bin/phpunit

# Run with coverage
php bin/phpunit --coverage-html coverage/
```

## Deployment

The application is configured for deployment on Upsun (Platform.sh successor):

```bash
# Push to Upsun
upsun push

# View environment URLs
upsun url
```

Configuration is in `.upsun/config.yaml`.

## Project Structure

```
├── src/
│   ├── Controller/      # HTTP controllers
│   ├── Entity/          # Doctrine entities
│   ├── Repository/      # Database repositories
│   ├── Service/         # Business logic services
│   └── Command/         # CLI commands
├── templates/           # Twig templates
├── assets/              # Frontend assets (JS, CSS)
├── data/                # CSV data files
├── config/              # Symfony configuration
├── migrations/          # Database migrations
├── tests/               # PHPUnit tests
└── public/              # Web root
```

## License

This project is proprietary software.
