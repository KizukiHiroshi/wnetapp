<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobtypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('jobtypes')->insert([
            'code' => '010',
            'name' => '発注入荷',
            'name_system' => 'OrderArival',
            'remarks' => '商品を発注し、入荷を管理する',
            'updated_by' => '初期開発'
        ]);
        DB::table('jobtypes')->insert([
            'code' => '019',
            'name' => '支払管理',
            'name_system' => 'Payment',
            'remarks' => '仕入と支払を管理する',
            'updated_by' => '初期開発'
        ]);
        DB::table('jobtypes')->insert([
            'code' => '020',
            'name' => '受注出荷',
            'name_system' => 'GetorderShip',
            'remarks' => '商品を受注し、出荷を管理する',
            'updated_by' => '初期開発'
        ]);
        DB::table('jobtypes')->insert([
            'code' => '021',
            'name' => '見積管理',
            'name_system' => 'Estimate',
            'remarks' => '見積を作成する',
            'updated_by' => '初期開発'
        ]);
        DB::table('jobtypes')->insert([
            'code' => '029',
            'name' => '売上管理',
            'name_system' => 'Sales',
            'remarks' => '売上と入金を管理する',
            'updated_by' => '初期開発'
        ]);
        DB::table('jobtypes')->insert([
            'code' => '030',
            'name' => '在庫管理',
            'name_system' => 'InventoryControl',
            'remarks' => '商品を入出庫し、維持管理する',
            'updated_by' => '初期開発'
        ]);
        DB::table('jobtypes')->insert([
            'code' => '040',
            'name' => '流通管理',
            'name_system' => 'Distribution',
            'remarks' => '受発注を代行管理する',
            'updated_by' => '初期開発'
        ]);
        DB::table('jobtypes')->insert([
            'code' => '070',
            'name' => '運営資料',
            'name_system' => 'Management',
            'remarks' => '経営に必要な資料を作成する',
            'updated_by' => '初期開発'
        ]);
        DB::table('jobtypes')->insert([
            'code' => '080',
            'name' => '勤務登録',
            'name_system' => 'Attendance',
            'remarks' => '勤務時間を管理する',
            'updated_by' => '初期開発'
        ]);
        DB::table('jobtypes')->insert([
            'code' => '090',
            'name' => 'マスター管理',
            'name_system' => 'MasterMgmt',
            'remarks' => 'マスターを管理する',
            'updated_by' => '初期開発'
        ]);
        DB::table('jobtypes')->insert([
            'code' => '100',
            'name' => 'システム管理',
            'name_system' => 'SystemMgmt',
            'remarks' => 'システムを管理する',
            'updated_by' => '初期開発'
        ]);        
    }
}
