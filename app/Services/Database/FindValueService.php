<?php
declare(strict_types=1);
namespace App\Services\Database;

use Illuminate\Support\Str;
use App\Services\SessionService;

class FindValueService
{
    public function __construct() {
    }

    // $findvalueset = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
    public function findValue($findvalueset, $targetcolumn = NULL) {
        if ($targetcolumn == NULL) { $targetcolumn = 'id'; }
        $findvalue = 0;
        $tablename = Str::plural(Str::before($findvalueset,'?'));
        $is_joinedunique = strpos(Str::after($findvalueset,'?'),'&&',) !== false ? true : false;
        if ($is_joinedunique) {
            $subcolset = explode('&&',Str::after($findvalueset,'?'));
        } else {
            $subcolset = explode('&',Str::after($findvalueset,'?'));
        }
        foreach($subcolset as $subcol) {
            $colset[Str::before($subcol,'=')] = Str::after($subcol,'=');
        }
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $modelname = $modelindex[$tablename]['modelname'];
        $tablequery = $modelname::query();
        // from句
        $tablequery = $tablequery->from($tablename);
        $wherecnt = 1;
        foreach ($colset as $columnnama => $value) {
            if ($wherecnt == 1 || $is_joinedunique) {
                $tablequery = $tablequery->where($tablename.'.'.$columnnama, '=', ''.urldecode($value).'');
            } else {
                $tablequery = $tablequery->orWhere($tablename.'.'.$columnnama, '=', ''.urldecode($value).'');
            }
            $wherecnt += 1;
        }
        $rows = $tablequery->get();
        if (count($rows) == 1) {
            foreach ($rows as $row) {
                $findvalue = $row->$targetcolumn;
            }
        } elseif (count($rows) > 1) {
            $findvalue = 'many';
        }
        return $findvalue;
    }
}