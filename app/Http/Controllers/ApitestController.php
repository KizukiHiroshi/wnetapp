<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Api\GetFuriganaService;

class ApitestController extends Controller
{
    public function index(Request $request) {
        $getfuriganaservice = new GetFuriganaService;
        $furigana = $getfuriganaservice->GetFurigana('静風堂');
        return $furigana;
    }
}
