<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $galleries = [
            [
                'title' => 'AI Workshop untuk Siswa SD',
                'description' => 'Workshop pengenalan AI untuk siswa SD dengan aktivitas fun learning.',
                'image' => 'placeholder.jpg',
                'category' => 'event',
                'event_date' => now()->subDays(45),
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Robotics Competition 2025',
                'description' => 'Kompetisi robotika tingkat nasional.',
                'image' => 'placeholder.jpg',
                'category' => 'event',
                'event_date' => now()->subDays(60),
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Python Programming Class',
                'description' => 'Kelas Python programming untuk siswa SMA.',
                'image' => 'placeholder.jpg',
                'category' => 'activity',
                'event_date' => now()->subDays(30),
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'title' => 'AI Research Laboratory',
                'description' => 'Fasilitas laboratorium AI dengan workstation high-performance.',
                'image' => 'placeholder.jpg',
                'category' => 'facility',
                'event_date' => now()->subDays(90),
                'is_featured' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Smart Home Project',
                'description' => 'Project smart home automation menggunakan IoT dan AI.',
                'image' => 'placeholder.jpg',
                'category' => 'achievement',
                'event_date' => now()->subDays(25),
                'is_featured' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($galleries as $gallery) {
            Gallery::create($gallery);
        }

        $this->command->info('Gallery items seeded successfully!');
    }
}
