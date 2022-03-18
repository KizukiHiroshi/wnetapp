<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;
use App\Service\Common\SessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TableRequest extends FormRequest
{
    private $sessionservice;
    public function __construct(SessionService $sessionservice) {
            $this->sessionservice = $sessionservice;
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
        $rule = $this->getRule($request);
        return $rule;
    }

    private function getRule($request) {
        $tablename = $request->tablename;
        $modelindex = $this->sessionservice->getSession('modelindex');
        $modelfullname = $modelindex[$tablename]['modelname'];
        $modelpathname = Str::beforeLast($modelfullname, '\\');
        $modelname = Str::afterLast($modelfullname, '\\');
        $modeldirname = Str::afterLast($modelpathname, '\\');
        $targetrequest = 'App\Http\Requests\\'.$modeldirname.'\\'.$modelname.'Request';
        $myrequest = app()->make($targetrequest);
        $rule = $myrequest->rules();
        return $rule;    
    }

}
