<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\TransProduct;
use App\Jobs\TransProductItem;

class ScheduleJobController extends Controller
{
    public function index(Request $request) {
        $job = new TransProduct();
        // $job = new TransProductItem();
        $job->handle();
    }
}
