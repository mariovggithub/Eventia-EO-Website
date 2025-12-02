<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\EventType;
use App\Models\EventOrganizer;
use App\Models\VendorCategory;
use App\Models\VendorProduct;
use App\Models\JobListing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Event Types
        $eventTypes = [
            ['name' => 'Birthday', 'image' => 'assets/Birthday.jpg'],
            ['name' => 'Wedding', 'image' => 'assets/Wedding.jpg'],
            ['name' => 'Open House', 'image' => 'assets/OpenHouse.jpg'],
            ['name' => 'Workshop', 'image' => 'assets/Workshop.jpg'],
            ['name' => 'Seminar', 'image' => 'assets/Seminar.jpg'],
            ['name' => 'Concert', 'image' => 'assets/Concert.jpg'],
            ['name' => 'Reuni', 'image' => 'assets/Reunion.jpg'],
        ];
        foreach ($eventTypes as $type) {
            EventType::create($type);
        }

        // Vendor Categories
        $categories = ['Catering', 'Decoration', 'Photo & Video', 'Souvenir'];
        foreach ($categories as $cat) {
            VendorCategory::create(['name' => $cat]);
        }

        // Demo Users
        $userDemo = User::create([
            'name' => 'Demo User',
            'email' => 'user@demo.com',
            'password' => Hash::make('password'),
            'role' => 'user'
        ]);

        // Create EO Users with their EO Profiles
        $eoUser1 = User::create([
            'name' => 'EventPro Admin',
            'email' => 'eo@demo.com',
            'password' => Hash::make('password'),
            'role' => 'eo'
        ]);

        $eo1 = EventOrganizer::create([
            'user_id' => $eoUser1->id,
            'name' => 'EventPro',
            'logo' => 'assets/EventPro.png',
            'description' => 'Profesional Event Handler',
            'phone' => '08123456789',
            'address' => 'Jl. Raya Darmo No. 123',
            'city' => 'Surabaya',
            'portfolio' => 'Telah menangani 500+ event berskala nasional dan internasional',
            'experience_years' => 10,
            'price_min' => 50000000,
            'price_max' => 150000000,
            'is_active' => true
        ]);
        $eoUser1->update(['eo_id' => $eo1->id]);

        $eoUser2 = User::create([
            'name' => 'Jaya Kreasi Admin',
            'email' => 'eo2@demo.com',
            'password' => Hash::make('password'),
            'role' => 'eo'
        ]);

        $eo2 = EventOrganizer::create([
            'user_id' => $eoUser2->id,
            'name' => 'Jaya Kreasi',
            'logo' => 'assets/JayaKreasi.png',
            'description' => 'Kreatif Inovatif Kolaboratif',
            'phone' => '08123456790',
            'address' => 'Jl. HR Muhammad No. 456',
            'city' => 'Surabaya',
            'portfolio' => 'Spesialis wedding & corporate event dengan sentuhan modern',
            'experience_years' => 8,
            'price_min' => 80000000,
            'price_max' => 200000000,
            'is_active' => true
        ]);
        $eoUser2->update(['eo_id' => $eo2->id]);

        $eoUser3 = User::create([
            'name' => 'OnStage Admin',
            'email' => 'eo3@demo.com',
            'password' => Hash::make('password'),
            'role' => 'eo'
        ]);

        $eo3 = EventOrganizer::create([
            'user_id' => $eoUser3->id,
            'name' => 'OnStage',
            'logo' => 'assets/OnStage.png',
            'description' => 'Stage & Show Specialist',
            'phone' => '08123456791',
            'address' => 'Jl. Basuki Rahmat No. 789',
            'city' => 'Surabaya',
            'portfolio' => 'Expert dalam concert, seminar, dan stage performance',
            'experience_years' => 12,
            'price_min' => 40000000,
            'price_max' => 120000000,
            'is_active' => true
        ]);
        $eoUser3->update(['eo_id' => $eo3->id]);

        // Vendor User
        $vendorUser = User::create([
            'name' => 'Vendor Demo',
            'email' => 'vendor@demo.com',
            'password' => Hash::make('password'),
            'role' => 'vendor'
        ]);

        // Vendor Products
        $products = [
            ['vendor_category_id' => 1, 'user_id' => $vendorUser->id, 'name' => 'Selerasa', 'price' => 35000000, 'quantity' => 1, 'image' => 'assets/Selera.jpg'],
            ['vendor_category_id' => 1, 'user_id' => $vendorUser->id, 'name' => 'ModernPlate', 'price' => 45000000, 'quantity' => 1, 'image' => 'assets/ModPlate.jpg'],
            ['vendor_category_id' => 2, 'user_id' => $vendorUser->id, 'name' => 'FloraDek', 'price' => 25000000, 'quantity' => 1, 'image' => 'assets/Decor.jpg'],
            ['vendor_category_id' => 3, 'user_id' => $vendorUser->id, 'name' => 'LensMax', 'price' => 15000000, 'quantity' => 1, 'image' => 'assets/Lens.jpg'],
            ['vendor_category_id' => 4, 'user_id' => $vendorUser->id, 'name' => 'GoodieBox', 'price' => 4000000, 'quantity' => 500, 'image' => 'assets/Souvenir.jpg'],
        ];
        foreach ($products as $product) {
            VendorProduct::create($product);
        }

        // Job Listings
        $jobs = [
            ['eo_id' => $eo1->id, 'role' => 'Crew Produksi', 'slots' => 3, 'image' => 'assets/Crew.jpg'],
            ['eo_id' => $eo1->id, 'role' => 'MC/Host', 'slots' => 4, 'image' => 'assets/MC.png'],
            ['eo_id' => $eo2->id, 'role' => 'Photographer', 'slots' => 5, 'image' => 'assets/Photographer.jpg'],
        ];
        foreach ($jobs as $job) {
            JobListing::create($job);
        }
    }
}