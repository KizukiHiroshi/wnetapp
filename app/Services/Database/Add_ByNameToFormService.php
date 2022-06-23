<?php
// ServiceではIlluminate\Http\Requestにアクセスしない
// 汎用性のある関数を登録する

declare(strict_types=1);
namespace App\Services\Database;

class Add_ByNameToFormService {
    // Formに_byを加える
    public function Add_ByNameToForm($byname, $form, $mode, $columnnames = null) {
        if (!$columnnames) {
            if ($mode == 'store') {
                $form['created_by'] = $byname;
            }        
            $form['updated_by'] = $byname;
        } else {
            if (in_array('created_by', $columnnames) && $mode == 'store') {
                $form['created_by'] = $byname;
            }        
            if (in_array('updated_by', $columnnames)) {
                $form['updated_by'] = $byname;
            }                        
        }
        return $form;
    }
}