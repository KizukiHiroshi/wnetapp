<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


class ConcernSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Zero\Concern::truncate();  // 既存データを削除
        \App\Models\Zero\Concern::factory(40)->create();
    }
}
