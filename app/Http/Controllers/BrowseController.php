<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class BrowseController extends Controller
{
    public function redisTest(): Response
    {
        // PHPRedis::set('testowe', time());
        return response(\AMSRedis::get('category', 'main'));
    }
}
