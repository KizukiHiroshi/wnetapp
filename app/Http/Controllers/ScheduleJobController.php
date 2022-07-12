<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Jobs\TransStockshellRyutu;

class ScheduleJobController extends Controller
{
    public function index(Request $request) {
        $job = new TransStockshellRyutu();
        $job->handle();
    }
}
