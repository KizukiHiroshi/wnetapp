<?php
declare(strict_types=1);
namespace App\Services\Transwnet;

use Illuminate\Support\Facades\DB;
use App\Services\Database\ExcuteProcessService;
use App\Services\Database\FindValueService;

class TranswnetService {

    public function __construct() {
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
        $form['created_at'] = $created_at == null ? date("Y-m-d H:i:s") :$created_at;
        $form['created_by'] = 'transwnet';
        return $form;
    }
}
