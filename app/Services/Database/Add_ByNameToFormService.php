<?php
// ServiceではIlluminate\Http\Requestにアクセスしない
// 汎用性のある関数を登録する

declare(strict_types=1);
namespace App\Services\Database;

class Add_ByNameToFormService {
    // Formに_byを加える
    public function add_ByNameToForm($byname, $form, $columnnames, $mode) {
        if (in_array('created_by', $columnnames) && $mode == 'store') {
            $form['created_by'] = $byname;
        }        
        if (in_array('updated_by', $columnnames)) {
            $form['updated_by'] = $byname;
        }        
        return $form;
    }
}