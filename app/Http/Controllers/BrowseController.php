<?php

namespace App\Http\Controllers;

use App\Models\AMSUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BrowseController extends Controller
{
    public function redisTest(): Response
    {
        // PHPRedis::set('testowe', time());
        return response(\AMSRedis::get('category', 'main'));
    }
}
