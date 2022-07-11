<?php
declare(strict_types=1);
namespace App\Services\Transwnet;

use Illuminate\Support\Facades\DB;
use App\Services\Database\ExcuteProcessService;
use App\Services\Database\FindValueService;

class TranswnetService {

    public function __construct() {
    }

    public function separateRawShopcode($shopcode) {
        $rawcompany = intval(intval($shopcode) / 100000);
        $companycode = substr('0000'.strval($rawcompany),-4);
        if ($companycode == '0000') { $companycode = '0001'; }
        $businessunitcode = substr('00000'.$shopcode, -5);
        $separatedshopcode = [
            'companycode' => $companycode,
            'businessunitcode' => $businessunitcode,
        ];
        return $separatedshopcode;
    }

    public function getFindvaluesetByRawShopcode($shopcode) {
        $findvalueservice = new FindValueService;
        $rawcompany = intval(intval($shopcode) / 100000);
        $companycode = substr('0000'.strval($rawcompany),-4);
        // $findvalueset = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $findvalueset = 'companies?code='.urlencode($companycode);
        $company_id = strval($findvalueservice->findValue($findvalueset, 'id'));
        $businessunitcode = substr('00000'.$shopcode, -5);
        // $findvalueset = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $findvalueset = 'businessunits?company_id='.urlencode($company_id).'&&code='.urlencode($businessunitcode);
        return $findvalueset;
    }

    public function getBusinessunitIdByRawShopcode($shopcode) {
        $findvalueservice = new FindValueService;
        $rawcompany = intval(intval($shopcode) / 100000);
        $companycode = substr('0000'.strval($rawcompany),-4);
        // $findvalueset = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $findvalueset = 'companies?code='.urlencode($companycode);
        $company_id = strval($findvalueservice->findValue($findvalueset, 'id'));
        $businessunitcode = substr('00000'.$shopcode, -5);
        // $findvalueset = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $findvalueset = 'businessunits?company_id='.urlencode($company_id).'&&code='.urlencode($businessunitcode);
        $businessunit_id = $findvalueservice->findValue($findvalueset, 'id');
        return $businessunit_id;
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

    public function updateTablereplacement($systemname, $oldtablename) {
        $maxcreatedat = $this->getMaxAt('created_at', $oldtablename);
        $maxupdatedat = $this->getMaxAt('updated_at', $oldtablename);
        $this->updateLatest($maxcreatedat, $maxupdatedat, $systemname);
    } 

    private function getMaxAt($targetcolumn, $oldtablename) {
        $maxat = DB::connection('sqlsrv')
            ->table('wise_login.'.$oldtablename)
            ->max($targetcolumn);
        return $maxat;
    }

    private function updateLatest($maxcreatedat, $maxupdatedat, $systemname) {
        // $findvalueset = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $findvalueset = 'tablereplacements?systemname='.urlencode($systemname);
        $findvalueservice = new FindValueService;
        $id = $findvalueservice->findValue($findvalueset, 'id');
        $form['latest_created'] = $maxcreatedat;
        $form['latest_updated'] = $maxupdatedat;
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
