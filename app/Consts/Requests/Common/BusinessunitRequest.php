<?php

namespace App\Consts\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessunitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'company_id' => ['required','integer','numeric',],
            'department_id' => ['required','integer','numeric',],
            'code' => ['required','string','max:5',
                Rule::unique('businessunits')->ignore($this->input('id')),'regex:/[0-9]{5}/'],
            'name' => ['required','string','max:30',
                Rule::unique('businessunits')->ignore($this->input('id')),'regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_short' => ['required','string','max:10','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'postalcode' => ['required','string','max:8','regex:/[0-9]{3}-?[0-9]{4}/'],
            'address1' => ['required','string','max:40',],
            'address2' => ['nullable','string','max:40',],
            'telno' => ['required','string','max:15','regex:/^[0-9-]+$/'],
            'foxno' => ['nullable','string','max:15','regex:/^[0-9-]+$/'],
            'url' => ['nullable','string','max:100','url'],
            'email' => ['nullable','string','max:50','email'],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
