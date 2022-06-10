<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class AccountController extends Controller {

    public function __construct() {
    }

    public function index() {
        // accountの確認へ
        return view('common/menu');
    }
}