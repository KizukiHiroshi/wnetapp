<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => '本部 杵築',
            'email' => 'kizuki@wisecorp.net',
            'password' =>  bcrypt('altoids3267'),
        ]);
    }
}
