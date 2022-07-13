<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Jobs\TransProductitem;

class ScheduleJobController extends Controller
{
    public function index(Request $request) {
        $job = new TransProductitem();
        $job->handle();
    }
}
