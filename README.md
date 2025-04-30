Halaman menu
<img src="./public/Screenshot (20).png" alt="Halaman Awal" width="100%" />
Sistem Prasyarat<br/>
    -PHP: ^8.1<br/>
    -Laravel Framework: ^10.10<br/>

Paket yang Dibutuhkan
Dependensi Utama:<br/>
    -Guzzle HTTP Client: ^7.2<br/>
    -Laravel AdminLTE: ^3.15<br/>
    -Laravel Sanctum: ^3.3<br/>
    -Laravel Tinker: ^2.8<br/>
    -Laravel UI: ^4.6<br/>
    -Spatie Laravel Permission: ^6.17<br/>
    -Yajra Laravel Datatables Oracle: ^10.11<br/>

Dependensi Pengembangan:<br/>
    -FakerPHP: ^1.9.1<br/>
    -Laravel Pint: ^1.0<br/>
    -Laravel Sail: ^1.18<br/>
    -Mockery: ^1.4.4<br/>
    -PHPUnit: ^10.1<br/>
    -Spatie Laravel Ignition: ^2.0<br/>

Autoloading<br/>
    -PSR-4 Autoloading digunakan untuk folder app/, database/factories/, database/seeders/, dan tests/.<br/>

Konfigurasi Tambahan<br/>
    -Autoloader dioptimalkan: Untuk kecepatan lebih baik.<br/>
    -Instalasi plugin: Diizinkan untuk pestphp/pest-plugin dan php-http/discovery.<br/>

Cara Instalasi

Untuk menjalankan proyek ini, pastikan Anda telah menginstal Composer, Node.js, dan PHP pada sistem Anda. Ikuti langkah-langkah di bawah ini untuk menginstal dan mengonfigurasi proyek dengan benar.
1. Instalasi Dependencies menggunakan Composer

Buka terminal Anda dan jalankan perintah berikut untuk menginstal dependensi PHP yang diperlukan:
```bash
composer install
```
Perintah ini akan mengunduh semua paket PHP yang dibutuhkan dan menyimpannya di dalam folder vendor.
2. Instalasi Dependencies menggunakan npm

Setelah Composer selesai, jalankan perintah berikut untuk menginstal dependensi frontend menggunakan npm:
```bash
npm install
```
Perintah ini akan mengunduh dan menginstal semua paket JavaScript yang diperlukan di dalam folder node_modules.
3. Menyiapkan File Konfigurasi .env

Salin file .env.example ke .env dan sesuaikan dengan konfigurasi sistem Anda. Anda bisa menggunakan perintah berikut untuk menyalin file:
```bash
cp .env.example .env
```
Buka file .env dan sesuaikan dengan konfigurasi seperti database, APP_KEY, dan pengaturan lainnya sesuai dengan kebutuhan Anda.
4. Generate APP_KEY

Setelah menyiapkan file .env, Anda perlu menghasilkan APP_KEY untuk aplikasi Laravel Anda. Jalankan perintah berikut:
```bash
php artisan key:generate
```
Perintah ini akan menghasilkan kunci aplikasi unik yang diperlukan untuk enkripsi dan keamanan aplikasi Laravel Anda. Kunci ini akan disimpan di file .env pada baris APP_KEY yang digunakan untuk berbagai fitur keamanan di Laravel, seperti hashing password dan enkripsi data.
5. Migrasi Database dan Seeding

Setelah file .env siap dan kunci aplikasi sudah digenerate, jalankan perintah berikut untuk membuat tabel-tabel database dan mengisi data awal menggunakan seeder:
```bash
php artisan migrate --seed
```
Perintah ini akan menjalankan migrasi database dan mengisi data awal (seeder) sesuai dengan yang ada di dalam database.
6. Pengaturan Menu dan Role Permissions

Setelah migrasi selesai, Anda perlu mengatur menu dan role permissions. Ikuti langkah-langkah berikut:
- Menyiapkan Menu (MenuSeeder)

Buka file database/seeders/MenuSeeder.php dan tambahkan menu yang ingin Anda buat. Sesuaikan dengan kebutuhan proyek Anda.
- Menyiapkan Role dan Permissions (RolePermissionSeeder)

Buka file database/seeders/RolePermissionSeeder.php untuk mengatur izin (permissions) untuk setiap menu yang telah Anda buat. Di sini Anda bisa menyesuaikan role yang ada, seperti Admin dan User, dan mengaitkan izin mereka terhadap menu yang sesuai.
7. Menambahkan Data User Default

Buka file database/seeders/UserSeeder.php dan tambahkan akun-akun default untuk aplikasi Anda. Berikut adalah contoh data user default:
- Role Admin

    Email: admin@example.com

    Password: password

- Role User

    Email: user@example.com

    Password: password

Setelah itu, jalankan perintah berikut untuk menambahkan user:
```bash
php artisan db:seed --class=UserSeeder
```
8. Selesai

Setelah langkah-langkah di atas selesai, aplikasi Anda siap digunakan! Anda dapat mengakses aplikasi di browser dan login menggunakan akun yang telah Anda buat.
