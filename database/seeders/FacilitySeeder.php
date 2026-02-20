<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = $this->getFacilities();

        foreach ($facilities as $facility) {
            Facility::create($facility);
        }

        $this->command->info('Facilities seeded successfully!');
    }

    protected function getFacilities(): array
    {
        return [
            [
                'name' => 'AI Research Laboratory',
                'slug' => 'ai-research-laboratory',
                'description' => 'Laboratorium penelitian AI dengan workstation high-performance untuk deep learning dan computer vision research.',
                'type' => 'lab',
                'quantity' => 1,
                'specifications' => json_encode([
                    'capacity' => 30,
                    'features' => ['GPU workstations', 'Cloud computing access', 'Large dataset storage'],
                    'equipment' => ['15 GPU Workstations', '10 4K Monitors', '100TB NAS'],
                    'location' => 'Gedung Pertamina, Lantai 4',
                    'operating_hours' => 'Senin-Jumat: 08:00-20:00',
                ]),
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Robotics Workshop',
                'slug' => 'robotics-workshop',
                'description' => 'Workshop robotika dengan berbagai robot kit dan tools untuk pembelajaran hands-on.',
                'type' => 'lab',
                'quantity' => 1,
                'specifications' => json_encode([
                    'capacity' => 25,
                    'features' => ['Robot assembly stations', 'Electronics workbenches', 'Testing arena'],
                    'equipment' => ['20 LEGO Mindstorms', '15 Arduino kits', '10 Raspberry Pi'],
                    'location' => 'Gedung Pertamina, Lantai 4',
                    'operating_hours' => 'Senin-Jumat: 08:00-20:00',
                ]),
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Smart Classroom',
                'slug' => 'smart-classroom',
                'description' => 'Ruang kelas pintar dengan teknologi interactive learning dan AR/VR equipment.',
                'type' => 'room',
                'quantity' => 1,
                'specifications' => json_encode([
                    'capacity' => 40,
                    'features' => ['Smart board 86 inch', 'Student tablets', 'VR headsets'],
                    'equipment' => ['1 Smart Board', '40 Tablets', '10 VR headsets'],
                    'location' => 'Gedung Pertamina, Lantai 4',
                    'operating_hours' => 'Senin-Sabtu: 08:00-20:00',
                ]),
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'LEGO Mindstorms EV3',
                'slug' => 'lego-mindstorms-ev3',
                'description' => 'Robot kit untuk pembelajaran robotika tingkat menengah.',
                'type' => 'kit',
                'quantity' => 20,
                'specifications' => json_encode([
                    'brand' => 'LEGO Education',
                    'suitable_for' => 'SD-SMP',
                    'features' => ['Programmable brick', 'Motors', 'Sensors', 'Building blocks'],
                ]),
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Arduino Starter Kit',
                'slug' => 'arduino-starter-kit',
                'description' => 'Kit elektronika untuk belajar programming dan IoT.',
                'type' => 'kit',
                'quantity' => 15,
                'specifications' => json_encode([
                    'brand' => 'Arduino',
                    'suitable_for' => 'SMP-SMA',
                    'features' => ['Arduino board', 'Sensors', 'LEDs', 'Components'],
                ]),
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];
    }
}
