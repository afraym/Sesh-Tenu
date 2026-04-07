<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\Project;
use App\Models\Company;
use App\Models\Worker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class EquipmentStatusCsvSeeder extends Seeder
{
    /**
     * Seed equipment and equipment types from equipment status CSV.
     */
    public function run(): void
    {
        if (!Schema::hasTable('equipment') || !Schema::hasTable('equipment_types')) {
            $this->command?->warn('Skipping EquipmentStatusCsvSeeder: required tables are missing.');
            return;
        }

        $firstProject = Project::orderBy('id')->first();
        $firstCompany = Company::orderBy('id')->first();

        if (!$firstProject || !$firstCompany) {
            $this->command?->warn('Skipping EquipmentStatusCsvSeeder: first project/company not found.');
            return;
        }

        $csvPath = database_path('seeders/data/equipment-status.csv');

        if (!file_exists($csvPath)) {
            $this->command?->warn("CSV file not found: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->command?->warn("Unable to open CSV file: {$csvPath}");
            return;
        }

        $seenCodes = [];
        $imported = 0;

        // Build a lookup map: normalised name => worker id
        $workerMap = Worker::all(['id', 'name'])
            ->mapWithKeys(fn ($w) => [$this->normalizeName($w->name) => $w->id])
            ->all();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) {
                continue;
            }

            $serial = $this->normalizeText($row[0] ?? '');
            if ($serial === '') {
                continue;
            }

            $driverName = $this->normalizeText($row[1] ?? '');
            $equipmentTypeName = $this->normalizeType($row[2] ?? '');
            $rawCode = $this->normalizeText($row[3] ?? '');
            $equipmentNumber = $this->normalizeText($row[4] ?? '');

            // Use serial as fallback for missing driver and equipment type
            if ($driverName === '') {
                $driverName = 'Driver ' . $serial;
            }
            if ($equipmentTypeName === '') {
                $equipmentTypeName = 'Equipment Type ' . $serial;
            }

            // Try to find a matching worker by name
            $workerDriverId = $workerMap[$this->normalizeName($driverName)] ?? null;

            EquipmentType::firstOrCreate(
                ['name' => $equipmentTypeName],
                ['description' => null, 'is_active' => true]
            );

            $equipmentCode = $this->buildUniqueCode($rawCode, (int) $serial, $seenCodes);

            Equipment::updateOrCreate(
                ['equipment_code' => $equipmentCode],
                [
                    'project_name' => $firstProject->name,
                    'company_id' => $firstCompany->id,
                    'previous_driver' => null,
                    'current_driver' => $driverName,
                    'driver_worker_id' => $workerDriverId,
                    'equipment_type' => $equipmentTypeName,
                    'model_year' => (string) rand(2000, 2009),
                    'equipment_number' => $equipmentNumber !== '' ? $equipmentNumber : null,
                    'manufacture' => null,
                    'entry_per_ser' => null,
                    'reg_no' => null,
                    'equip_reg_issue' => null,
                    'custom_clearance' => null,
                    'equipment_option' => rand(0, 1) === 0 ? 'فعلي' : 'اختياري',
                ]
            );

            $imported++;
        }

        fclose($handle);

        $this->command?->info("Imported rows assigned to project: {$firstProject->name}, company ID: {$firstCompany->id}");
        $this->command?->info("Equipment CSV import done. Imported/updated: {$imported}");
    }

    private function normalizeName(?string $value): string
    {
        // Collapse whitespace and lowercase for fuzzy matching
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $value)));
    }

    private function normalizeText(?string $value): string
    {
        return trim((string) $value);
    }

    private function normalizeType(?string $value): string
    {
        $normalized = preg_replace('/\s+/u', ' ', $this->normalizeText($value));
        return trim((string) $normalized);
    }

    /**
     * Generate a unique code if source code is missing or duplicated in the same file.
     */
    private function buildUniqueCode(string $rawCode, int $serial, array &$seenCodes): string
    {
        // If raw code is empty, leave it empty instead of generating auto code
        if ($rawCode === '') {
            return '';
        }

        $candidate = $rawCode;
        $base = $candidate;
        $suffix = 2;

        while (isset($seenCodes[$candidate])) {
            $candidate = $base . '-DUP' . $suffix;
            $suffix++;
        }

        $seenCodes[$candidate] = true;

        return $candidate;
    }
}
