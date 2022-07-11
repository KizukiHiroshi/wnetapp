<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\TransProduct;
use App\Jobs\TransProductItem;
use App\Jobs\TransStockshell;

class ScheduleJobController extends Controller
{
    public function index(Request $request) {
        $job = new TransStockshell();
        $job->handle();
    }
}
