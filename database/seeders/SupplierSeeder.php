<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'name' => 'Nanana Collection',
                'contact' => null,
                'note' => 'Produksi sendiri',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
