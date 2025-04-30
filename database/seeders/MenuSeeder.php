<?php
namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // Buat menu
        $dashboardMenu = Menu::firstOrCreate([
            'name' => 'Dashboard', 
            'url' => '/dashboard', 
            'icon' => 'fas fa-tachometer-alt',
            'category' =>  null
        ]);
        // Buat menu
        $penggunaMenu = Menu::firstOrCreate([
            'name' => 'Pengguna', 
            'url' => '/users', 
            'icon' => 'fas fa-user',
            'category' =>  null
        ]);

        $categoryMenu = Menu::firstOrCreate([
            'name' => 'Kategori', 
            'url' => '/categories', 
            'icon' => 'fa fa-th-large',
            'category' => 'Master Data'
        ]);

        $productMenu = Menu::firstOrCreate([
            'name' => 'Produk', 
            'url' => '/products', 
            'icon' => 'fas fa-cube',
            'category' => 'Master Data'
        ]);

        $supplierMenu = Menu::firstOrCreate([
            'name' => 'Supplier', 
            'url' => '/suppliers', 
            'icon' => 'fa fa-truck',
            'category' => 'Master Data'
        ]);

        $employeMenu = Menu::firstOrCreate([
            'name' => 'Karyawan', 
            'url' => '/employees', 
            'icon' => 'fa fa-users',
            'category' => 'Master Data'
        ]);






        $transactionsMenu = Menu::firstOrCreate([
            'name' => 'Pembelian', 
            'url' => '/transactions', 
            'icon' => 'fa fa-exchange-alt',
            'category' => 'Transaksi'
        ]);  

        $transactionSalesMenu = Menu::firstOrCreate([
            'name' => 'Penjualan', 
            'url' => '/transaction-sales', 
            'icon' => 'fa fa-exchange-alt',
            'category' => 'Transaksi'
        ]);  

        $transactionSalesMenu = Menu::firstOrCreate([
            'name' => 'Penerimaan', 
            'url' => '/receipts', 
            'icon' => 'fa fa-exchange-alt',
            'category' => 'Transaksi'
        ]);  

        // $shipmentsMenu = Menu::firstOrCreate([
        //     'name' => 'Pengiriman', 
        //     'url' => '/shipments', 
        //     'icon' => 'fa fa-truck',
        //     'category' => 'Transaksi'
        // ]);

        // Buat role
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $userRole = Role::firstOrCreate(['name' => 'User']);

        // Berikan menu ke role
        $adminRole->menus()->attach([
        	$dashboardMenu->id, 
        	$penggunaMenu->id,
            $categoryMenu->id,
            $productMenu->id,
            $supplierMenu->id,
            $employeMenu->id,
            $transactionsMenu->id,
            $transactionSalesMenu->id,
            // $shipmentsMenu->id
        ]);
        $userRole->menus()->attach([
            $dashboardMenu->id,
            $categoryMenu->id,
            $productMenu->id,
            $supplierMenu->id,
            $employeMenu->id,
            $transactionsMenu->id,
            $transactionSalesMenu->id,
            // $shipmentsMenu->id
        ]); // Role 'User' hanya memiliki akses ke dashboard
    }
}
