<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\TransBrand;
use App\Jobs\TransBusinessunit;

class ScheduleJobController extends Controller
{
    public function index(Request $request) {
        $job = new TransBrand();
        // $job = new TransBusinessunit();
        $job->handle();
    }
}
