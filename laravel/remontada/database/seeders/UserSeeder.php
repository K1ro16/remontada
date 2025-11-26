<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Business;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default business
        $business = Business::create([
            'name' => 'My Business',
            'description' => 'Default business',
            'email' => 'business@example.com',
            'phone' => '08123456789',
            'address' => 'Jakarta, Indonesia',
        ]);

        // Create owner user
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            'current_business_id' => $business->id,
        ]);

        // Get pemilik role
        $pemilikRole = Role::where('name', 'pemilik')->first();

        // Attach owner to business with pemilik role
        $owner->businesses()->attach($business->id, ['role_id' => $pemilikRole->id]);
    }
}
