<?php

namespace App\Consts\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
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
            'code' => ['required','string','max:5',
                Rule::unique('departments')->ignore($this->id),'regex:/[0-9]{5}/'],
            'name' => ['required','string','max:30',
                Rule::unique('departments')->ignore($this->id),'regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_short' => ['required','string','max:10','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'department_hierarchy' => ['required','integer','numeric',],
            'departmentpath' => ['required','string','max:50','regex:/^[a-zA-Z0-9-]+$/'],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
