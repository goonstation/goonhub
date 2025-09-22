<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommonRequest;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Test
     */
    public function index(Request $request)
    {
        $commonRequest = app(CommonRequest::class);

        return response()->json([
            'fromServerId' => $commonRequest->fromServerId(),
            'fromServer' => $commonRequest->fromServer(),
            'fromServerGroup' => $commonRequest->fromServerGroup(),
        ]);
    }
}
