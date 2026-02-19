<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            ['name' => 'La Paz', 'code' => 'LP'],
            ['name' => 'Cochabamba', 'code' => 'CB'],
            ['name' => 'Santa Cruz', 'code' => 'SC'],
            ['name' => 'Oruro', 'code' => 'OR'],
            ['name' => 'Potosí', 'code' => 'PT'],
            ['name' => 'Chuquisaca', 'code' => 'CH'],
            ['name' => 'Tarija', 'code' => 'TJ'],
            ['name' => 'Beni', 'code' => 'BN'],
            ['name' => 'Pando', 'code' => 'PN'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['name' => $department['name']],
                $department
            );
        }
    }
}
