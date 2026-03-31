<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['id' => 1,  'name' => 'ADMN'],
            ['id' => 2, 'name' => 'Message Center'],
            ['id' => 3,  'name' => 'BGCU'],
            ['id' => 4,  'name' => 'CIU'],
            ['id' => 5,  'name' => 'COMMAND'],
            ['id' => 6,  'name' => 'ISU'],
            ['id' => 7,  'name' => 'LSO'],
            ['id' => 8,  'name' => 'PAU'],
            ['id' => 9,  'name' => 'PG1'],
            ['id' => 10, 'name' => 'PG3'],
            ['id' => 11, 'name' => 'PG4'],
            ['id' => 12, 'name' => 'PG10'],
            ['id' => 13, 'name' => 'PPBU'],
            ['id' => 14, 'name' => 'TFD'],
            ['id' => 15, 'name' => 'Resumption NCO'],
            ['id' => 16, 'name' => 'TOP NCO'],
            ['id' => 17, 'name' => 'Restoration NCO'],
            ['id' => 18, 'name' => 'Prior Years NCO'],
            ['id' => 19, 'name' => 'Pension Differential 18-19'],
            ['id' => 20, 'name' => 'Own Right NCO'],
            ['id' => 21, 'name' => 'Posthumous NCO'],
            ['id' => 22, 'name' => 'Retirement NCO'],
            ['id' => 23, 'name' => 'RSAB NCO'],
            ['id' => 24, 'name' => 'CDD NCO'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['id' => $unit['id']],
                ['name' => $unit['name']]
            );

            $this->command->info("Unit created: {$unit['name']}");
        }

        $this->command->newLine();
        $this->command->info('All units created successfully!');
    }
}