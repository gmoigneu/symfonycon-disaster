# Application Architecture

## Overview

The Global Disasters Dashboard follows a standard Symfony MVC architecture with additional service and repository layers for clean separation of concerns.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                         Client                               │
│                    (Web Browser)                             │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      Symfony Router                          │
│                    (config/routes.yaml)                      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                       Controllers                            │
│                 (src/Controller/)                            │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ HomepageController                                   │    │
│  │ - index(): Renders dashboard with statistics         │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                        Services                              │
│                   (src/Service/)                             │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ StatisticsService                                    │    │
│  │ - getHomepageStatistics(): Aggregates all data       │    │
│  │ - getSummaryStats(): Summary statistics              │    │
│  │ - formatLargeNumber(): Number formatting             │    │
│  │ - formatCurrency(): Currency formatting              │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      Repositories                            │
│                   (src/Repository/)                          │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ GlobalDisasterRepository                             │    │
│  │ - 20+ query methods for data access                  │    │
│  │ - Filtering, aggregation, and ranking queries        │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                       Entities                               │
│                    (src/Entity/)                             │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ GlobalDisaster                                       │    │
│  │ - Doctrine ORM entity with 12 fields                 │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     PostgreSQL Database                      │
│                    (global_disaster table)                   │
└─────────────────────────────────────────────────────────────┘
```

## Frontend Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      Browser                                 │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   Hotwired Turbo                             │
│            (SPA-like page transitions)                       │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Stimulus.js                               │
│                (Reactive Controllers)                        │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ chart_controller.js                                  │    │
│  │ - connect(): Initialize chart on DOM mount           │    │
│  │ - Supports: bar, line, doughnut, pie                 │    │
│  │ - Dynamic color generation                           │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      Chart.js                                │
│              (Data Visualization Library)                    │
└─────────────────────────────────────────────────────────────┘
```

## Request Flow

1. **HTTP Request** arrives at Symfony front controller (`public/index.php`)
2. **Router** matches URL to controller action
3. **Controller** calls **Service** methods for business logic
4. **Service** uses **Repository** for database queries
5. **Repository** queries the database via Doctrine ORM
6. **Entity** objects are hydrated and returned
7. **Controller** passes data to **Twig Template**
8. **Template** renders HTML with Stimulus controller attributes
9. **Browser** executes Stimulus controllers, initializing Chart.js

## Layer Responsibilities

### Controllers (`src/Controller/`)
- Handle HTTP requests and responses
- Coordinate between services and views
- No direct database access or business logic

### Services (`src/Service/`)
- Encapsulate business logic
- Data transformation and formatting
- Aggregate data from multiple repository calls

### Repositories (`src/Repository/`)
- All database queries
- Query building with Doctrine QueryBuilder
- Return entities or structured arrays

### Entities (`src/Entity/`)
- Represent database records
- Define relationships and constraints
- Contain getters/setters only (anemic model)

### Commands (`src/Command/`)
- CLI tools for administrative tasks
- Data import/export operations
- Scheduled jobs and maintenance

## Asset Pipeline

```
assets/
├── app.js                    # Entry point, imports Stimulus
├── styles/app.css            # Tailwind CSS entry
├── stimulus_bootstrap.js     # Auto-loads controllers
└── controllers/
    └── chart_controller.js   # Chart.js integration

        │
        ▼ Webpack Encore Build

public/build/
├── app.js                    # Bundled JS
├── app.css                   # Processed CSS
├── manifest.json             # Asset versioning
└── entrypoints.json          # Chunk loading
```

## Configuration Files

| File | Purpose |
|------|---------|
| `config/packages/doctrine.yaml` | Database & ORM configuration |
| `config/packages/framework.yaml` | Symfony framework settings |
| `config/packages/twig.yaml` | Template engine settings |
| `config/services.yaml` | Service container configuration |
| `config/routes.yaml` | URL routing definitions |
| `webpack.config.js` | Asset bundling configuration |
| `tailwind.config.js` | CSS framework configuration |
| `compose.yaml` | Docker services (PostgreSQL) |
| `.upsun/config.yaml` | Cloud deployment configuration |
