<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Usecases\Jobmenu\MakeButtonListCase;

class JobMenuController extends Controller {

    public function __construct() {
    }

    public function index(Request $request) {
        // accountから、使用できるボタンリストを作る
        // 未実装
        $makebuttonlistcase = new MakeButtonListCase;
        $buttonlist = $makebuttonlistcase->makeButtonList();
        return view('common/jobmenu')->with($buttonlist);
    }
}
