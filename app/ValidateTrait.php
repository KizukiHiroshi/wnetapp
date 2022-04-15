<?php
 
namespace App;
 
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
 
trait ValidateTrait
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

    public function csvSave(array $options = [])
    {
        $rules = $this->rules();
        if (count($rules)) {
            $subject   = $this->attributes;
            $validator = Validator::make($subject, $rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return $errors;
            }
        }
        return parent::save($options);
    }

    public function csvCheck()
    {
        $rules = $this->rules();
        if (count($rules)) {
            $subject   = $this->attributes;
            $validator = Validator::make($subject, $rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return $errors;
            } else {
                return null;
            }
        }
    }
}