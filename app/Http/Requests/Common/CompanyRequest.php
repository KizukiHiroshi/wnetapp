<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
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
            'code' => ['required','string','max:4',
                Rule::unique('companies')->ignore($this->input('id')),'regex:/\d{4}/'],
            'name' => ['required','string','max:30','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_kana' => ['required','string','max:30','regex:/^[ァ-ンヴー]+$/'],
            'name_short' => ['required','string','max:10','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'postalcode' => ['required','string','max:8','regex:/^[0-9]{3}-[0-9]{4}$/'],
            'address1' => ['required','string','max:40',],
            'address2' => ['nullable','string','max:40',],
            'telno' => ['required','string','max:13','regex:/^[a-zA-Z0-9-]+$/'],
            'foxno' => ['nullable','string','max:13','regex:/^[a-zA-Z0-9-]+$/'],
            'url' => ['nullable','string','max:255','url'],
            'is_buyer' => ['required','boolean',],
            'is_vendor' => ['required','boolean',],
            'can_order' => ['required','boolean',],
            'can_sale' => ['required','boolean',],
            'can_stock' => ['required','boolean',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],        
        ];
    }
}
