# MCP Server Specification

## Overview

This document specifies the requirements for the MCP (Model Context Protocol) server endpoint that enables AI clients to query the global disasters database.

## Requirements

### Endpoint

- **URL**: `/mcp`
- **Format**: Streamable HTTP MCP server
- **Access**: Read-only (no write, update, or delete operations)

### Tools to Implement

The MCP server should provide tools that match the functionality of the main web application controller. Based on the `HomepageController` and `GlobalDisasterRepository`, the following tools should be available:

#### 1. Summary Statistics Tools

| Tool | Description |
|------|-------------|
| `get_total_disasters` | Get total count of disasters |
| `get_total_casualties` | Get total number of casualties |
| `get_total_economic_loss` | Get total economic loss in USD |
| `get_total_aid_amount` | Get total aid provided in USD |
| `get_average_response_time` | Get average response time in hours |
| `get_average_recovery_days` | Get average recovery period in days |

#### 2. Filtering Tools

| Tool | Parameters | Description |
|------|------------|-------------|
| `find_disasters_by_country` | `country: string` | Find disasters in a specific country |
| `find_disasters_by_type` | `type: string` | Find disasters of a specific type |
| `find_disasters_by_date_range` | `start: date, end: date` | Find disasters within a date range |
| `find_disasters_by_severity` | `min_severity: float` | Find disasters above a severity threshold |

#### 3. Listing Tools

| Tool | Description |
|------|-------------|
| `list_disaster_types` | Get all unique disaster types |
| `list_countries` | Get all unique countries in the database |

#### 4. Analytics Tools

| Tool | Parameters | Description |
|------|------------|-------------|
| `get_disasters_by_year` | - | Get disaster counts grouped by year |
| `get_casualties_by_year` | - | Get casualty counts grouped by year |
| `get_disasters_by_type` | - | Get disaster counts grouped by type |
| `get_disasters_by_country` | `limit: int` | Get disaster counts by country (top N) |
| `get_deadliest_countries` | `limit: int` | Get countries ranked by total casualties |
| `get_safest_countries` | `limit: int` | Get countries with lowest average casualties |
| `get_economic_loss_by_type` | - | Get average economic loss by disaster type |

#### 5. Natural Language Query Tool

| Tool | Parameters | Description |
|------|------------|-------------|
| `natural_language_query` | `query: string` | Execute a natural language query against the database |

**Implementation Requirements for Natural Language Query:**

1. Use Claude API to interpret the natural language query
2. Provide Claude with the database schema (GlobalDisaster entity)
3. Generate appropriate Doctrine DQL or repository method calls
4. Execute the query and return results
5. Ensure read-only access (SELECT queries only)
6. Validate generated queries before execution

### Database Schema Context

The MCP server should be aware of the following entity structure:

```php
Entity: GlobalDisaster
Table: global_disaster

Fields:
- id: integer (primary key)
- date: date (indexed)
- country: string (100 chars, indexed)
- disaster_type: string (50 chars, indexed)
- severity_index: decimal (5,2) - range 0-10
- casualties: integer
- economic_loss_usd: decimal (15,2)
- response_time_hours: decimal (8,2)
- aid_amount_usd: decimal (15,2)
- response_efficiency_score: decimal (5,2) - range 0-100
- recovery_days: integer
- latitude: decimal (10,6)
- longitude: decimal (10,6)
```

### Security Requirements

1. **Read-Only Access**: No INSERT, UPDATE, DELETE, or DDL operations allowed
2. **Query Validation**: Validate all generated SQL/DQL before execution
3. **Input Sanitization**: Sanitize all user inputs
4. **Rate Limiting**: Consider implementing rate limiting for the NL query tool
5. **API Key**: Use the configured `ANTHROPIC_API_KEY` from environment

### Configuration

The Anthropic API key is already configured in the Symfony application:
- Environment variable: `ANTHROPIC_API_KEY`
- Location: `.env.local`

### Deployment

1. Create a new branch: `mcpserver`
2. Develop and test the MCP server locally
3. Push to Upsun and activate the environment
4. Provide the `/mcp` endpoint URL for testing

## MCP Protocol Reference

The server should implement the Model Context Protocol specification for Streamable HTTP transport. Key aspects:

- Support tool listing and execution
- Return structured responses
- Handle streaming for long-running queries
- Implement proper error handling and status codes
