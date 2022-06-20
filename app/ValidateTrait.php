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
 
    private $properrules = [
        'deleted_at'    => ['nullable','date',],
        'created_at'    => ['date',],
        'created_by'    => ['string',],
        'updated_at'    => ['date',],
        'updated_by'    => ['string',],
        'approved_at'   => ['nullable','date',],
        'approved_by'   => ['nullable','string',],
        'start_on'      => ['nullable','date',],
        'end_on'        => ['nullable','date',],
    ];

    public function save(array $options = [])
    {
        $rules = $this->rules();
        $rules = array_merge($rules, $this->properrules);
        if (count($rules)){
            $subject   = $this->attributes;
            $validator = Validator::make($subject, $rules);
            if ($validator->fails()){
                throw new ValidationException($validator);
            }
        }
        return parent::save($options);
    }

    public function csvSave(array $options = [])
    {
        $rules = $this->rules();
        $rules = array_merge($rules, $this->properrules);
        if (count($rules)){
            $subject   = $this->attributes;
            $validator = Validator::make($subject, $rules);
            if ($validator->fails()){
                $errors = $validator->errors()->toArray();
                return $errors;
            }
        }
        return parent::save($options);
    }

    public function csvCheck()
    {
        $rules = $this->rules();
        $rules = array_merge($rules, $this->properrules);
        if (count($rules)){
            $subject   = $this->attributes;
            $validator = Validator::make($subject, $rules);
            if ($validator->fails()){
                $errors = $validator->errors()->toArray();
                return $errors;
            } else {
                return null;
            }
        }
    }
}