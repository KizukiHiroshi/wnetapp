<?php
 
namespace App;
 
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
 
trait ValidateOnSave
{
    protected function rules():array
    {
        return [];
    }
 
    public function save(array $options = [])
    {
        $rules = $this->rules();
        if (count($rules)) {
            $subject   = $this->attributes;
            $validator = Validator::make($subject, $rules);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
        return parent::save($options);
    }
}