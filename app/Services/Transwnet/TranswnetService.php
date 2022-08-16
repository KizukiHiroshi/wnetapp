<?php
declare(strict_types=1);
namespace App\Services\Transwnet;

use Illuminate\Support\Facades\DB;
use App\Services\Database\AddIddictionaryService;
use App\Services\Database\ExcuteProcessService;
use App\Services\Database\FindValueService;

class TranswnetService {

    public function __construct() {
    }

    public function separateRawShopcode($shopcode) {
        $rawcompany = intval(intval($shopcode) / 100000);
        $companycode = substr('0000'.strval($rawcompany), -4);
        if ($companycode == '0000') { $companycode = '0001'; }
        $businessunitcode = substr('00000'.$shopcode, -5);
        $separatedshopcode = [
            'companycode' => $companycode,
            'businessunitcode' => $businessunitcode,
        ];
        return $separatedshopcode;
    }

    public function addIdBySeparatedShopcode($separatedshopcode, $iddictionary) {
        $addiddictionaryservice = new AddIddictionaryService;
        $companycode = $separatedshopcode['companycode'];
        // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        if ($companycode == null) {
            $stop = 0;
        }
        $foreginkey = 'companies?code='.urlencode($companycode);
        $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
        $company_id = $iddictionary[$foreginkey];
        $businessunitcode = $separatedshopcode['businessunitcode'];
        if ($businessunitcode == null) {
            $stop = 0;
        }
        // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $foreginkey = 'businessunits?company_id='.$company_id.'&&code='.urlencode($businessunitcode);
        $iddictionary = $addiddictionaryservice->addIddictionary($iddictionary, $foreginkey);
        return $iddictionary;
    }

    public function getLatest($mode, $systemname) {
        $targetcolumn = 'latest_'.$mode;
        $row = DB::table('tablereplacements')
            ->where('systemname', $systemname)
            ->select($targetcolumn)
            ->first();
        $latest = $row->$targetcolumn;
        return $latest;
    }

    public function updateTablereplacement($systemname, $oldtablename, $maxvalue = null) {
        $maxcreatedat = $this->getMaxAt('created_at', $oldtablename);
        $maxupdatedat = $this->getMaxAt('updated_at', $oldtablename);
        $this->updateLatest($maxcreatedat, $maxupdatedat, $systemname, $maxvalue);
    } 

    private function getMaxAt($targetcolumn, $oldtablename) {
        $maxat = DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename)
            ->max($targetcolumn);
        return $maxat;
    }

    private function updateLatest($maxcreatedat, $maxupdatedat, $systemname, $maxvalue) {
        // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $foreginkey = 'tablereplacements?systemname='.urlencode($systemname);
        $findvalueservice = new FindValueService;
        $id = $findvalueservice->findValue($foreginkey, 'id');
        $form['latest_created'] = $maxcreatedat;
        $form['latest_updated'] = $maxupdatedat;
        if ($maxvalue) {
            $form['maxvalue'] = $maxvalue;
        }
        $form['updated_at'] = time();
        $form['updated_by'] = 'transwnet';
        $excuteprocessservice = new ExcuteProcessService;
        $ret_id = $excuteprocessservice->excuteProcess('tablereplacements', $form, $id);
        if ($ret_id == $id) {
            // ok
        } else {
            // ★どうする？
        }
    }

    public function addCreatedToForm($created_at) {
        $form['created_at'] = $created_at == null ? time() :$created_at;
        $form['created_by'] = 'transwnet';
        return $form;
    }
}
