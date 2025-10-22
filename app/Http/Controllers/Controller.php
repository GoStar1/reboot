<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class Controller extends \Illuminate\Routing\Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected function success($data = null, $extra = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'code' => 0,
            'data' => $data,
            'extra' => $extra,
        ]);
    }


    protected function error($code, $msg, $data = null, $extra = null): \Illuminate\Http\JsonResponse
    {
        $code = (int)$code;
        $msg = (string)$msg;
        return response()->json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'extra' => $extra,
        ]);
    }
}
