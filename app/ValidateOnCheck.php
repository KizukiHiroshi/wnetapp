<?php
 
namespace App;
 
use Illuminate\Support\Facades\Validator;
 
trait ValidateOnCheck
{
    protected function rules():array
    {
        return [];
    }
 
    public function check($modelname, $form)
    {
        $rules = $this->rules();
        if (count($rules)) {
            $subject   = $this->attributes;
            $validator = Validator::make($subject, $rules);
            if ($validator->fails()) {
                return $validator;
            }
        }
    }
}