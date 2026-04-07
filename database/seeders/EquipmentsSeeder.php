<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\Project;
use App\Models\Company;
use App\Models\Worker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class EquipmentsSeeder extends Seeder
{
    /**
     * Seed equipment from the flat CSV structure.
     */
    public function run(): void
    {
        if (!Schema::hasTable('equipment') || !Schema::hasTable('equipment_types')) {
            $this->command?->warn('Skipping EquipmentsSeeder: required tables are missing.');
            return;
        }

        $firstProject = Project::orderBy('id')->first();
        $firstCompany = Company::orderBy('id')->first();

        if (!$firstProject || !$firstCompany) {
            $this->command?->warn('Skipping EquipmentsSeeder: first project/company not found.');
            return;
        }

        $csvPath = database_path('seeders/data/equipments.csv');

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
            $driverName = $this->normalizeText($row[0] ?? '');
            $equipmentTypeName = $this->normalizeType($row[1] ?? '');
            $equipmentCode = $this->normalizeText($row[2] ?? '');
            $equipmentNumber = $this->normalizeText($row[3] ?? '');
            $equipmentOption = $this->normalizeText($row[4] ?? '');

            if ($driverName === '' && $equipmentTypeName === '' && $equipmentCode === '' && $equipmentNumber === '' && $equipmentOption === '') {
                continue;
            }

            if ($driverName === 'الإسم' || $equipmentTypeName === 'نوع المُعدة' || $equipmentCode === 'كود المُعدة') {
                continue;
            }

            if ($equipmentTypeName !== '') {
                EquipmentType::firstOrCreate(
                    ['name' => $equipmentTypeName],
                    ['description' => null, 'is_active' => true]
                );
            }

            // Try to find a matching worker by name
            $workerDriverId = $driverName !== ''
                ? ($workerMap[$this->normalizeName($driverName)] ?? null)
                : null;

            Equipment::create([
                'project_name' => $firstProject->name,
                'company_id' => $firstCompany->id,
                'previous_driver' => null,
                'current_driver' => $driverName !== '' ? $driverName : null,
                'driver_worker_id' => $workerDriverId,
                'equipment_type' => $equipmentTypeName !== '' ? $equipmentTypeName : null,
                'model_year' => (string) rand(2000, 2009),
                'equipment_code' => $equipmentCode !== '' ? $equipmentCode : null,
                'equipment_number' => $equipmentNumber !== '' ? $equipmentNumber : null,
                'manufacture' => null,
                'entry_per_ser' => null,
                'reg_no' => null,
                'equip_reg_issue' => null,
                'custom_clearance' => null,
                'equipment_option' => $equipmentOption !== '' ? $equipmentOption : null,
            ]);

            $imported++;
        }

        fclose($handle);

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

}
