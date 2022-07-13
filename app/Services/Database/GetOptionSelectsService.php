<?php
declare(strict_types=1);
namespace App\Services\Database;

class GetOptionSelectsService 
{
    public function __construct() {
    }

    // card表示用にoption用のセレクトリストを用意する
    public function getOptionSelects($columnsprop) {
        $optionselects = [];
        // option用セレクトの実体を得る
        foreach ($columnsprop AS $columnname => $prop) {
            if (substr($columnname, -4) =='_opt') {
                $optionselectrows = $this->getOptReferenceSelects($columnname);
                $optionreferencename = $columnname.'_reference';
                $optionselects[$optionreferencename] = $optionselectrows;
            }
        }
        return $optionselects;
    }

    // 参照用selects作成
    private function getOptReferenceSelects($columnname) {
        $modelname = 'App\Models\Base\OptionChoice';
        $rows = $modelname::where('variablename_systrem', $columnname)
            ->orderBy('no')
            ->get();
        foreach ($rows AS $row) {
            $optreferenceselects[$row->id] = $row->valuename;
        }
        return $optreferenceselects;
    }
}