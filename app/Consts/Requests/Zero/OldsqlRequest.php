<?php

namespace App\Consts\Requests\Zero;

use Illuminate\Foundation\Http\FormRequest;

class OldsqlRequest extends FormRequest
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
            'sqltype' => ['required','string','max:6',],
            'sqltext' => ['required','string','max:4000',],
            'is_checked' => ['required','boolean',],
        ];
    }
}
