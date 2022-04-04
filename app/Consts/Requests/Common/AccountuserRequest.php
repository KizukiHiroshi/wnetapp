<?php

namespace App\Consts\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountuserRequest extends FormRequest
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
            'user_id' => ['required','integer','numeric',
                Rule::unique('accountusers')->ignore($this->input('id')),],
            'name' => ['required','string','max:12',
                Rule::unique('accountusers')->ignore($this->input('id'))->where(function($query) {
                $query->where('user_id', $this->input('user_id'));
            }),],
            'password' => ['required','string','max:16','regex:/^[a-zA-Z0-9-_]+$/'],
            'company_id' => ['required','integer','numeric',],
            'department_id' => ['required','integer','numeric',],
            'businessunit_id' => ['required','integer','numeric',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
