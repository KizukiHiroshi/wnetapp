<?php
declare(strict_types=1);
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MenuController extends Controller {
    public function index(Request $request) {
        return view('common/menu');
    }
}
