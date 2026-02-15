<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\JobType;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ManpowerCsvSeeder extends Seeder
{
    /**
     * Seed the application's database from manpower CSV.
     */
    public function run(): void
    {
        $csvPath = database_path('seeders/data/manpower.csv');

        if (!file_exists($csvPath)) {
            $this->command?->warn("CSV file not found: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            $this->command?->warn("Unable to open CSV file: {$csvPath}");
            return;
        }

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 13) {
                $skipped++;
                continue;
            }

            $serial = trim((string) $row[0]);
            if (!is_numeric($serial)) {
                $skipped++;
                continue;
            }

            $companyName = $this->normalizeText($row[1]);
            $name = $this->normalizeText($row[2]);
            $jobTypeName = $this->normalizeText($row[3]);
            $nationalId = $this->normalizeText($row[4]);

            if ($companyName === '' || $name === '' || $nationalId === '') {
                $skipped++;
                continue;
            }

            $company = Company::firstOrCreate([
                'name' => $companyName,
            ]);

            $jobTypeId = null;
            if ($jobTypeName !== '') {
                $jobType = JobType::firstOrCreate(
                    ['name' => $jobTypeName],
                    ['is_active' => true]
                );
                $jobTypeId = $jobType->id;
            }

            Worker::updateOrCreate(
                ['national_id' => $nationalId],
                [
                    'company_id' => $company->id,
                    'name' => $name,
                    'job_type_id' => $jobTypeId,
                    'phone_number' => $this->normalizeText($row[5]),
                    'has_housing' => $this->toBool($row[6]),
                    'is_local_community' => $this->toBool($row[7]),
                    'address' => $this->normalizeText($row[8]),
                    'join_date' => $this->parseDate($row[9]),
                    'end_date' => $this->parseDate($row[10]),
                    'is_on_company_payroll' => $this->toBool($row[11]),
                    'salary' => $this->parseSalary($row[12]),
                ]
            );

            $imported++;
        }

        fclose($handle);

        $this->command?->info("Manpower CSV import done. Imported/updated: {$imported}, Skipped: {$skipped}");
    }

    private function normalizeText(?string $value): string
    {
        return trim((string) $value);
    }

    private function toBool(?string $value): bool
    {
        $normalized = mb_strtolower($this->normalizeText($value));
        return in_array($normalized, ['yes', 'y', '1', 'true', 'نعم'], true);
    }

    private function parseDate(?string $value): ?string
    {
        $date = $this->normalizeText($value);

        if ($date === '') {
            return null;
        }

        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date)->format('Y-m-d');
            } catch (\Throwable $exception) {
            }
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function parseSalary(?string $value): ?float
    {
        $salary = $this->normalizeText($value);
        if ($salary === '') {
            return null;
        }

        $salary = str_replace([',', ' '], '', $salary);
        return is_numeric($salary) ? (float) $salary : null;
    }
}
