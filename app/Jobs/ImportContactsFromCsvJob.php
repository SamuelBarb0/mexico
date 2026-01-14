<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportContactsFromCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600; // 10 minutes for large files

    protected Tenant $tenant;
    protected string $filePath;
    protected array $mapping;
    protected ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(Tenant $tenant, string $filePath, array $mapping, ?int $userId = null)
    {
        $this->tenant = $tenant;
        $this->filePath = $filePath;
        $this->mapping = $mapping;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting CSV contact import', [
            'tenant_id' => $this->tenant->id,
            'file_path' => $this->filePath,
        ]);

        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        try {
            // Read CSV file
            $csvContent = Storage::disk('local')->get($this->filePath);
            $rows = array_map('str_getcsv', explode("\n", $csvContent));

            // Remove header row
            $header = array_shift($rows);

            Log::info('CSV file loaded', [
                'total_rows' => count($rows),
                'header' => $header,
            ]);

            foreach ($rows as $index => $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $stats['total']++;

                try {
                    // Map CSV columns to contact fields
                    $contactData = $this->mapRowToContact($row, $header);

                    // Validate required fields
                    if (empty($contactData['phone'])) {
                        Log::warning('Skipping row - missing phone number', [
                            'row' => $index + 2, // +2 because we removed header and arrays are 0-indexed
                            'data' => $row,
                        ]);
                        $stats['skipped']++;
                        continue;
                    }

                    // Format and validate phone number
                    $contactData['phone'] = $this->formatPhoneNumber($contactData['phone']);

                    if (!$this->isValidPhoneNumber($contactData['phone'])) {
                        Log::warning('Skipping row - invalid phone number', [
                            'row' => $index + 2,
                            'phone' => $contactData['phone'],
                        ]);
                        $stats['skipped']++;
                        continue;
                    }

                    // Check if contact exists
                    $contact = Contact::where('tenant_id', $this->tenant->id)
                        ->where('phone', $contactData['phone'])
                        ->first();

                    if ($contact) {
                        // Update existing contact
                        $contact->update($contactData);
                        $stats['updated']++;

                        Log::info('Contact updated', [
                            'contact_id' => $contact->id,
                            'phone' => $contactData['phone'],
                        ]);
                    } else {
                        // Create new contact
                        $contactData['tenant_id'] = $this->tenant->id;
                        $contact = Contact::create($contactData);
                        $stats['created']++;

                        Log::info('Contact created', [
                            'contact_id' => $contact->id,
                            'phone' => $contactData['phone'],
                        ]);
                    }

                } catch (\Exception $e) {
                    Log::error('Error processing CSV row', [
                        'row' => $index + 2,
                        'error' => $e->getMessage(),
                        'data' => $row,
                    ]);
                    $stats['errors']++;
                }
            }

            Log::info('CSV import completed', $stats);

            // Clean up - delete the CSV file
            Storage::disk('local')->delete($this->filePath);

        } catch (\Exception $e) {
            Log::error('Fatal error during CSV import', [
                'tenant_id' => $this->tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Map CSV row to contact data based on mapping
     */
    protected function mapRowToContact(array $row, array $header): array
    {
        $contactData = [];

        foreach ($this->mapping as $csvColumn => $contactField) {
            // Skip if mapping is empty
            if (empty($contactField)) {
                continue;
            }

            // Find the index of the CSV column
            $columnIndex = array_search($csvColumn, $header);

            if ($columnIndex !== false && isset($row[$columnIndex])) {
                $value = trim($row[$columnIndex]);

                // Skip empty values
                if ($value === '') {
                    continue;
                }

                $contactData[$contactField] = $value;
            }
        }

        return $contactData;
    }

    /**
     * Format phone number - remove + and keep only numbers
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters including +
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        return $cleaned;
    }

    /**
     * Validate phone number format
     */
    protected function isValidPhoneNumber(string $phoneNumber): bool
    {
        // Basic validation: only digits, 10-15 characters
        return preg_match('/^\d{10,15}$/', $phoneNumber) === 1;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ImportContactsFromCsvJob failed', [
            'tenant_id' => $this->tenant->id,
            'file_path' => $this->filePath,
            'exception' => $exception->getMessage(),
        ]);

        // Clean up the file on failure
        try {
            Storage::disk('local')->delete($this->filePath);
        } catch (\Exception $e) {
            Log::error('Failed to delete CSV file after job failure', [
                'file_path' => $this->filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
