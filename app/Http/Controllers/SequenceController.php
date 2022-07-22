<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\SequenceService;

class SequenceController extends Controller
{
    public function index(Request $request) {
        $sequenceservice = new SequenceService;
        $newno = $sequenceservice->getNewNo('transaction');
    }
}
