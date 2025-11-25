<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\GlobalDisaster;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-global-disasters',
    description: 'Import global disasters data from a CSV file',
)]
class ImportGlobalDisastersCommand extends Command
{
    private const BATCH_SIZE = 100;
    private const CSV_HEADERS = [
        'date',
        'country',
        'disaster_type',
        'severity_index',
        'casualties',
        'economic_loss_usd',
        'response_time_hours',
        'aid_amount_usd',
        'response_efficiency_score',
        'recovery_days',
        'latitude',
        'longitude',
    ];

    private EntityManagerInterface $entityManager;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
        parent::__construct();
        $this->resetEntityManager();
    }

    private function resetEntityManager(): void
    {
        $em = $this->managerRegistry->getManager();
        if (!$em instanceof EntityManagerInterface) {
            throw new \RuntimeException('Expected EntityManagerInterface');
        }
        $this->entityManager = $em;
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'file',
                InputArgument::OPTIONAL,
                'Path to the CSV file to import',
                'data/global_disasters.csv'
            )
            ->addOption(
                'truncate',
                't',
                InputOption::VALUE_NONE,
                'Truncate the table before importing'
            )
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Validate the CSV without inserting records'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');
        $truncate = $input->getOption('truncate');
        $dryRun = $input->getOption('dry-run');

        // Validate file exists
        if (!file_exists($filePath)) {
            $io->error(sprintf('File not found: %s', $filePath));
            return Command::FAILURE;
        }

        if (!is_readable($filePath)) {
            $io->error(sprintf('File is not readable: %s', $filePath));
            return Command::FAILURE;
        }

        $io->title('Global Disasters CSV Import');

        if ($dryRun) {
            $io->note('Running in dry-run mode - no data will be inserted');
        }

        // Count total lines for progress bar (excluding header)
        $totalLines = $this->countCsvLines($filePath);
        if ($totalLines === 0) {
            $io->warning('The CSV file is empty or contains only headers');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d records to import', $totalLines));

        // Truncate if requested
        if ($truncate && !$dryRun) {
            $this->truncateTable($io);
        }

        // Process the CSV
        $result = $this->processFile($filePath, $totalLines, $io, $dryRun);

        // Report results
        $this->reportResults($result, $io, $dryRun);

        return $result['errors'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function countCsvLines(string $filePath): int
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return 0;
        }

        $count = 0;
        // Skip header
        fgetcsv($handle, escape: '\\');
        while (fgetcsv($handle, escape: '\\') !== false) {
            $count++;
        }
        fclose($handle);

        return $count;
    }

    private function truncateTable(SymfonyStyle $io): void
    {
        $io->info('Truncating existing data...');
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $tableName = $this->entityManager->getClassMetadata(GlobalDisaster::class)->getTableName();

        // Use platform-specific truncate
        $connection->executeStatement($platform->getTruncateTableSQL($tableName, true));
        $io->success('Table truncated successfully');
    }

    /**
     * @return array{imported: int, skipped: int, errors: int, errorDetails: array<int, string>}
     */
    private function processFile(string $filePath, int $totalLines, SymfonyStyle $io, bool $dryRun): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => 1, 'errorDetails' => ['Could not open file']];
        }

        // Disable SQL logging to prevent memory exhaustion
        $connection = $this->entityManager->getConnection();
        $connection->getConfiguration()->setMiddlewares([]);

        $result = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => 0,
            'errorDetails' => [],
        ];

        // Validate and skip header
        $header = fgetcsv($handle, escape: '\\');
        if ($header === false || !$this->validateHeader($header)) {
            fclose($handle);
            $result['errors'] = 1;
            $result['errorDetails'][] = 'Invalid CSV header. Expected columns: ' . implode(', ', self::CSV_HEADERS);
            return $result;
        }

        $progressBar = new ProgressBar($io, $totalLines);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();

        $rowNumber = 1; // Start at 1 since we skipped header
        $batchCount = 0;

        while (($row = fgetcsv($handle, escape: '\\')) !== false) {
            $rowNumber++;
            $progressBar->advance();

            // Skip empty rows
            if ($this->isEmptyRow($row)) {
                $result['skipped']++;
                continue;
            }

            // Validate row has correct number of columns
            if (count($row) !== count(self::CSV_HEADERS)) {
                $result['errors']++;
                $result['errorDetails'][] = sprintf(
                    'Row %d: Invalid number of columns (expected %d, got %d)',
                    $rowNumber,
                    count(self::CSV_HEADERS),
                    count($row)
                );
                continue;
            }

            try {
                $disaster = $this->createEntityFromRow($row, $rowNumber);

                if (!$dryRun) {
                    $this->entityManager->persist($disaster);
                    $batchCount++;

                    // Flush and clear in batches to manage memory
                    if ($batchCount >= self::BATCH_SIZE) {
                        try {
                            $this->entityManager->flush();
                            $this->entityManager->clear();
                            gc_collect_cycles();
                        } catch (\Exception $e) {
                            $result['errors']++;
                            if (count($result['errorDetails']) < 50) {
                                $result['errorDetails'][] = sprintf('Batch flush error at row %d: %s', $rowNumber, $e->getMessage());
                            }
                            // Reset the EntityManager if it was closed
                            if (!$this->entityManager->isOpen()) {
                                $this->managerRegistry->resetManager();
                                $this->resetEntityManager();
                            }
                        }
                        $batchCount = 0;
                    }
                }

                $result['imported']++;
            } catch (\Exception $e) {
                $result['errors']++;
                if (count($result['errorDetails']) < 50) {
                    $result['errorDetails'][] = sprintf('Row %d: %s', $rowNumber, $e->getMessage());
                }
            }
        }

        // Flush remaining entities
        if (!$dryRun && $batchCount > 0) {
            try {
                $this->entityManager->flush();
                $this->entityManager->clear();
                gc_collect_cycles();
            } catch (\Exception $e) {
                $result['errors']++;
                if (count($result['errorDetails']) < 50) {
                    $result['errorDetails'][] = sprintf('Final flush error: %s', $e->getMessage());
                }
            }
        }

        $progressBar->finish();
        $io->newLine(2);

        fclose($handle);

        return $result;
    }

    /**
     * @param array<int, string|null> $header
     */
    private function validateHeader(array $header): bool
    {
        $normalizedHeader = array_map(
            fn ($col) => strtolower(trim((string) $col)),
            $header
        );

        return $normalizedHeader === self::CSV_HEADERS;
    }

    /**
     * @param array<int, string|null> $row
     */
    private function isEmptyRow(array $row): bool
    {
        return count($row) === 1 && ($row[0] === null || $row[0] === '');
    }

    /**
     * @param array<int, string|null> $row
     */
    private function createEntityFromRow(array $row, int $rowNumber): GlobalDisaster
    {
        $data = array_combine(self::CSV_HEADERS, $row);
        if ($data === false) {
            throw new \InvalidArgumentException('Could not combine headers with row data');
        }

        $disaster = new GlobalDisaster();

        // Parse and validate date
        $date = \DateTime::createFromFormat('Y-m-d', trim($data['date'] ?? ''));
        if ($date === false) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid date format "%s" (expected YYYY-MM-DD)',
                $data['date'] ?? ''
            ));
        }
        $disaster->setDate($date);

        // Required string fields
        $country = trim($data['country'] ?? '');
        if ($country === '') {
            throw new \InvalidArgumentException('Country cannot be empty');
        }
        $disaster->setCountry($country);

        $disasterType = trim($data['disaster_type'] ?? '');
        if ($disasterType === '') {
            throw new \InvalidArgumentException('Disaster type cannot be empty');
        }
        $disaster->setDisasterType($disasterType);

        // Decimal fields (stored as strings)
        $disaster->setSeverityIndex($this->parseDecimal($data['severity_index'] ?? '', 'severity_index'));
        $disaster->setEconomicLossUsd($this->parseDecimal($data['economic_loss_usd'] ?? '', 'economic_loss_usd'));
        $disaster->setResponseTimeHours($this->parseDecimal($data['response_time_hours'] ?? '', 'response_time_hours'));
        $disaster->setAidAmountUsd($this->parseDecimal($data['aid_amount_usd'] ?? '', 'aid_amount_usd'));
        $disaster->setResponseEfficiencyScore($this->parseDecimal($data['response_efficiency_score'] ?? '', 'response_efficiency_score'));
        $disaster->setLatitude($this->parseDecimal($data['latitude'] ?? '', 'latitude'));
        $disaster->setLongitude($this->parseDecimal($data['longitude'] ?? '', 'longitude'));

        // Integer fields
        $disaster->setCasualties($this->parseInteger($data['casualties'] ?? '', 'casualties'));
        $disaster->setRecoveryDays($this->parseInteger($data['recovery_days'] ?? '', 'recovery_days'));

        return $disaster;
    }

    private function parseDecimal(string $value, string $fieldName): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new \InvalidArgumentException(sprintf('%s cannot be empty', $fieldName));
        }
        if (!is_numeric($trimmed)) {
            throw new \InvalidArgumentException(sprintf('%s must be a valid number, got "%s"', $fieldName, $trimmed));
        }
        return $trimmed;
    }

    private function parseInteger(string $value, string $fieldName): int
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new \InvalidArgumentException(sprintf('%s cannot be empty', $fieldName));
        }
        if (!ctype_digit($trimmed) && !($trimmed[0] === '-' && ctype_digit(substr($trimmed, 1)))) {
            throw new \InvalidArgumentException(sprintf('%s must be a valid integer, got "%s"', $fieldName, $trimmed));
        }
        return (int) $trimmed;
    }

    /**
     * @param array{imported: int, skipped: int, errors: int, errorDetails: array<int, string>} $result
     */
    private function reportResults(array $result, SymfonyStyle $io, bool $dryRun): void
    {
        $action = $dryRun ? 'validated' : 'imported';

        $io->section('Import Summary');
        $io->listing([
            sprintf('Records %s: %d', $action, $result['imported']),
            sprintf('Records skipped (empty rows): %d', $result['skipped']),
            sprintf('Errors encountered: %d', $result['errors']),
        ]);

        if ($result['errors'] > 0) {
            $io->section('Error Details');
            // Show first 10 errors to avoid flooding the console
            $errorsToShow = array_slice($result['errorDetails'], 0, 10);
            foreach ($errorsToShow as $error) {
                $io->error($error);
            }
            if (count($result['errorDetails']) > 10) {
                $io->warning(sprintf(
                    '... and %d more errors (showing first 10)',
                    count($result['errorDetails']) - 10
                ));
            }
        }

        if ($result['errors'] === 0 && $result['imported'] > 0) {
            if ($dryRun) {
                $io->success('Dry run completed successfully. All records are valid.');
            } else {
                $io->success(sprintf('Import completed successfully. %d records imported.', $result['imported']));
            }
        }
    }
}
