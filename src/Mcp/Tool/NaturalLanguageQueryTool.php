<?php

declare(strict_types=1);

namespace App\Mcp\Tool;

use Doctrine\DBAL\Connection;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Bridge\Anthropic\PlatformFactory;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * MCP Tool for querying the database using natural language.
 * Uses Claude to translate natural language to SQL queries (read-only).
 */
class NaturalLanguageQueryTool
{
    private const DATABASE_SCHEMA = <<<'SCHEMA'
Table: global_disaster

Columns:
- id (INTEGER, PRIMARY KEY): Unique identifier
- date (DATE): Date when the disaster occurred
- country (VARCHAR(100)): Name of the country affected
- disaster_type (VARCHAR(50)): Type of disaster (Earthquake, Hurricane, Tornado, Flood, Wildfire, Drought, Extreme Heat, Storm Surge, Volcanic Eruption, Landslide)
- severity_index (DECIMAL(5,2)): Severity rating from 0.00 to 10.00
- casualties (INTEGER): Number of fatalities
- economic_loss_usd (DECIMAL(15,2)): Financial impact in US dollars
- response_time_hours (DECIMAL(8,2)): Time to respond in hours
- aid_amount_usd (DECIMAL(15,2)): Aid provided in US dollars
- response_efficiency_score (DECIMAL(5,2)): Efficiency score from 0.00 to 100.00
- recovery_days (INTEGER): Number of days to recover
- latitude (DECIMAL(10,6)): Geographic latitude coordinate
- longitude (DECIMAL(10,6)): Geographic longitude coordinate

Countries in dataset: Australia, Bangladesh, Brazil, Canada, China, France, Germany, Greece, India, Indonesia, Italy, Japan, Mexico, Nigeria, Philippines, South Africa, Spain, USA

Data range: 2018-01-01 to 2024-12-31
Total records: ~50,000
SCHEMA;

    public function __construct(
        private readonly Connection $connection,
        private readonly HttpClientInterface $httpClient,
        private readonly string $anthropicApiKey,
    ) {
    }

    /**
     * Query the disaster database using natural language.
     * The query is translated to SQL by Claude and executed read-only.
     *
     * @param string $query Natural language query describing what data you want
     * @return array<string, mixed> Query results or error message
     */
    #[McpTool(name: 'query_disasters', description: 'Query the disaster database using natural language. Describe what you want to find (e.g., "Show me the 5 deadliest earthquakes in Japan" or "What was the total economic loss from hurricanes in 2023?"). Only read operations are allowed.')]
    public function query(string $query): array
    {
        try {
            $sql = $this->translateToSql($query);

            if (!$this->isReadOnlyQuery($sql)) {
                return [
                    'success' => false,
                    'error' => 'Only read-only queries (SELECT) are allowed.',
                    'query' => $query,
                ];
            }

            $results = $this->connection->executeQuery($sql)->fetchAllAssociative();

            return [
                'success' => true,
                'query' => $query,
                'sql' => $sql,
                'result_count' => count($results),
                'results' => $results,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'query' => $query,
            ];
        }
    }

    /**
     * Translate natural language to SQL using Claude.
     */
    private function translateToSql(string $naturalLanguageQuery): string
    {
        $platform = PlatformFactory::create(
            apiKey: $this->anthropicApiKey,
            httpClient: $this->httpClient,
        );

        $databaseSchema = self::DATABASE_SCHEMA;
        $systemPrompt = <<<PROMPT
You are a SQL query generator for a PostgreSQL database containing global disaster data.
Your task is to translate natural language questions into valid, read-only SQL queries.

DATABASE SCHEMA:
{$databaseSchema}

RULES:
1. Only generate SELECT queries - no INSERT, UPDATE, DELETE, DROP, or any other write operations
2. Always use the exact table name: global_disaster
3. Always use the exact column names as shown in the schema
4. Use PostgreSQL syntax (e.g., EXTRACT(YEAR FROM date) for year extraction)
5. Limit results to a reasonable number (max 100) unless specifically asked for more
6. Return ONLY the SQL query, nothing else - no explanations, no markdown, no code blocks
7. If the query is ambiguous, make reasonable assumptions

EXAMPLES:
Question: "Show me the 5 deadliest earthquakes"
SQL: SELECT * FROM global_disaster WHERE disaster_type = 'Earthquake' ORDER BY casualties DESC LIMIT 5

Question: "What is the average economic loss for hurricanes?"
SQL: SELECT AVG(economic_loss_usd) as average_loss FROM global_disaster WHERE disaster_type = 'Hurricane'

Question: "How many disasters occurred in Japan in 2022?"
SQL: SELECT COUNT(*) as disaster_count FROM global_disaster WHERE country = 'Japan' AND EXTRACT(YEAR FROM date) = 2022
PROMPT;

        $messages = new MessageBag(
            Message::forSystem($systemPrompt),
            Message::ofUser($naturalLanguageQuery),
        );

        $result = $platform->invoke('claude-sonnet-4-20250514', $messages, [
            'max_tokens' => 500,
            'temperature' => 0,
        ]);

        $sql = trim($result->asText());

        // Remove any markdown code blocks if present
        $sql = preg_replace('/^```sql?\s*/i', '', $sql);
        $sql = preg_replace('/\s*```$/i', '', $sql);

        return trim($sql);
    }

    /**
     * Check if the query is read-only (SELECT only).
     */
    private function isReadOnlyQuery(string $sql): bool
    {
        $sql = trim(strtoupper($sql));

        // Must start with SELECT
        if (!str_starts_with($sql, 'SELECT')) {
            return false;
        }

        // Block dangerous keywords
        $dangerousKeywords = [
            'INSERT',
            'UPDATE',
            'DELETE',
            'DROP',
            'TRUNCATE',
            'ALTER',
            'CREATE',
            'REPLACE',
            'GRANT',
            'REVOKE',
            'EXEC',
            'EXECUTE',
        ];

        foreach ($dangerousKeywords as $keyword) {
            if (preg_match('/\b' . $keyword . '\b/', $sql)) {
                return false;
            }
        }

        return true;
    }
}
