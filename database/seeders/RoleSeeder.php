<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $role = Role::create(['name' => 'Developer']);
        $role = Role::create(['name' => 'Administrators']);
        $role = Role::create(['name' => 'Registered User']);
        $role = Role::create(['name' => 'Client User']);
        $role = Role::create(['name' => 'Care Giver User']);
        $role = Role::create(['name' => 'Nurse User']);
        $role = Role::create(['name' => 'Nurse Assistant User']);
        $role = Role::create(['name' => 'Physiotherapist User']);
        $role = Role::create(['name' => 'Doctor User']);
    }
}

