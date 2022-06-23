<?php
declare(strict_types=1);
namespace App\Services\Session;

class BasevalueService {

    public function __construct() {
    }
    public function getPaginatecnt() {
        $paginatecnt = 15;
        return $paginatecnt;
    }
}
