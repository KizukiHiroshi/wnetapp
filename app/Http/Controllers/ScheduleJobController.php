<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Jobs\TransStockshellRyutu;
use App\Jobs\TransStockRyutu;
use App\Jobs\TransFirstOrder;

class ScheduleJobController extends Controller
{
    public function index(Request $request) {
        // $job = new TransStockshellRyutu();
        // $job = new TransStockRyutu();
        $job = new TransFirstOrder();
        $job->handle();
    }
}
