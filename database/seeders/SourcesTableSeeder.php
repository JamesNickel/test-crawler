<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourcesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $sources = [
            [
                'name'      => 'digikala',
                'base_url'  => 'https://www.digikala.com',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'      => 'technolife',
                'base_url'  => 'https://www.technolife.com',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($sources as $source) {
            DB::table('sources')->updateOrInsert(
                ['name' => $source['name']],
                $source
            );
        }
    }
}
