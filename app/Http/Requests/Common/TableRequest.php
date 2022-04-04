<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;
use App\Service\Common\TableService;
use Illuminate\Http\Request;

class TableRequest extends FormRequest
{
    private $tableservice;
    public function __construct(TableService $tableservice) {
            $this->tableservice = $tableservice;
        }

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
    public function rules(Request $request)
    {
        $tablename = $request->tablename;
        // $rule = $this->tableservice->getRule($tablename);
        // return $rule;
    }
}
