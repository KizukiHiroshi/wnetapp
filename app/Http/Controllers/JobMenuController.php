<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JobMenuController extends Controller {

    private $sessionservice;
    public function __construct(){
    }

    public function index(Request $request){
        // accountから、使用できるボタンリストを作る
        // 未実装
        return view('common/menu');
    }
}
