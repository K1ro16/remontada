<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Product permissions
            ['name' => 'view_products', 'description' => 'View products'],
            ['name' => 'create_products', 'description' => 'Create new products'],
            ['name' => 'edit_products', 'description' => 'Edit existing products'],
            ['name' => 'delete_products', 'description' => 'Delete products'],
            
            // Category permissions
            ['name' => 'manage_categories', 'description' => 'Manage product categories'],
            
            // Sales permissions
            ['name' => 'view_sales', 'description' => 'View sales records'],
            ['name' => 'create_sales', 'description' => 'Create new sales'],
            ['name' => 'edit_sales', 'description' => 'Edit sales records'],
            ['name' => 'delete_sales', 'description' => 'Delete sales records'],
            
            // Customer permissions
            ['name' => 'view_customers', 'description' => 'View customers'],
            ['name' => 'manage_customers', 'description' => 'Manage customers'],
            
            // Inventory permissions
            ['name' => 'view_inventory', 'description' => 'View inventory'],
            ['name' => 'manage_inventory', 'description' => 'Manage inventory'],
            
            // Financial permissions
            ['name' => 'view_financials', 'description' => 'View financial records'],
            ['name' => 'manage_financials', 'description' => 'Manage financial records'],
            
            // Reports permissions
            ['name' => 'view_reports', 'description' => 'View reports and analytics'],
            ['name' => 'export_data', 'description' => 'Export business data'],
            
            // Activity logs
            ['name' => 'view_activity_logs', 'description' => 'View activity logs'],
            
            // User management
            ['name' => 'manage_users', 'description' => 'Manage users and roles'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles
        $pemilik = Role::create([
            'name' => 'pemilik',
            'description' => 'Business owner with full access'
        ]);

        $staf = Role::create([
            'name' => 'staf',
            'description' => 'Staff member with limited access'
        ]);

        $kolaborator = Role::create([
            'name' => 'kolaborator',
            'description' => 'Collaborator with view-only access'
        ]);

        // Assign all permissions to pemilik
        $pemilik->permissions()->attach(Permission::all());

        // Assign permissions to staf (can manage products, sales, customers, inventory)
        $staf->permissions()->attach(
            Permission::whereIn('name', [
                'view_products',
                'create_products',
                'edit_products',
                'view_sales',
                'create_sales',
                'edit_sales',
                'view_customers',
                'manage_customers',
                'view_inventory',
                'manage_inventory',
                'view_financials',
            ])->pluck('id')
        );

        // Assign permissions to kolaborator (view-only access)
        $kolaborator->permissions()->attach(
            Permission::whereIn('name', [
                'view_products',
                'view_sales',
                'view_customers',
                'view_inventory',
                'view_reports',
            ])->pluck('id')
        );
    }
}
