<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Jobs\TransStockshell;
use App\Jobs\TransStockRyutu;
use App\Jobs\TransCompanyBuyer;

class ScheduleJobController extends Controller
{
    public function index(Request $request) {
        // $job = new TransStockshell();
        // $job = new TransStockshellRyutu();
        $job = new TransCompanyBuyer();
        $job->handle();
    }
}
