# CLAUDE.md - Global Disasters Dashboard

A Symfony 7.3 web application for analyzing and visualizing global disaster data.

## Tech Stack

- **Backend**: Symfony 7.3, PHP 8.2+, PostgreSQL 16+, Doctrine ORM
- **Frontend**: Stimulus.js, Hotwired Turbo, Tailwind CSS, Chart.js
- **Deployment**: Upsun (Project ID: `esz2g6fiagi32`)

## Quick Commands

```bash
composer install && npm install   # Install dependencies
npm run build                     # Build assets
symfony server:start              # Start server
php bin/phpunit                   # Run tests
```

## Code Style

- Follow Symfony coding standards
- Use Doctrine repositories for database queries (no raw SQL in controllers)
- Keep business logic in services (StatisticsService pattern)
- Use Stimulus controllers for JavaScript functionality

## Documentation

- **Architecture & Schema**: See `knowledge/` directory
- **Agents**: Use `@symfony-ai-developer` and `@symfony-7-development-expert` for development
