<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Menu untuk admin (dapat CRUD semua menu)
        $menusAdmin = [
            'users',
            'posts', 
            'categories',
            'products',
            'suppliers',
            'employees',
            'transactions',
            'transaction-sales',
            'receipts',
            'purchase-orders',
            'shipments'
        ];

        // Menu untuk user (CRUD pada beberapa menu)
        $menusUser = [
            'categories',
            'products',
            'suppliers',
            'employees',
            'transactions',
            'transaction-sales',
            'receipts',
            'purchase-orders',
            'shipments',
            'posts', // User bisa melihat posts, tapi tidak CRUD
        ];

        // Gabungkan menu admin dan user
        $menus = array_merge($menusAdmin, $menusUser);

        // Membuat Permissions untuk setiap menu
        foreach ($menus as $menu) {
            // CRUD permissions untuk setiap menu
            $permissions = [
                'create ' . $menu,
                'edit ' . $menu,
                'delete ' . $menu,
                'view ' . $menu,
            ];

            // Menambahkan permissions ke database (jika belum ada)
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
            }
        }

        // Membuat Roles jika belum ada
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $userRole = Role::firstOrCreate(['name' => 'User']);

        // Memberikan Permissions ke Role Admin
        $adminPermissions = Permission::all(); // Admin mendapatkan semua permissions
        $adminRole->givePermissionTo($adminPermissions);

        // Memberikan Permissions ke Role User
        // User mendapatkan CRUD permissions untuk beberapa menu
        foreach ($menusUser as $menu) {
            // Untuk User hanya memberikan permission untuk 'view' saja untuk posts
            if ($menu === 'posts') {
                $userRole->givePermissionTo('view ' . $menu); // Hanya bisa melihat posts
            } else {
                // User mendapatkan CRUD permissions untuk kategori lainnya
                $userPermissions = [
                    'create ' . $menu,
                    'edit ' . $menu,
                    'delete ' . $menu,
                    'view ' . $menu,
                ];
                $userRole->givePermissionTo($userPermissions);
            }
        }
    }
}
