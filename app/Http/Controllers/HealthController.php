<?php

namespace App\Http\Controllers;

use App\Core\BaseController;
use App\SRE\HealthCheck;

class HealthController extends BaseController
{
    public function check(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(HealthCheck::check());
        exit;
    }
}
