<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Jobs\TransStock;

class ScheduleJobController extends Controller
{
    public function index(Request $request) {
        $job = new TransStock();
        $job->handle();
    }
}
