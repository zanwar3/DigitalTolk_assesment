<?php

namespace DTApi\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    protected function sendResponse($data, $code = 200)
    {
        return response()->json($data, $code);
    }

    protected function sendError($message, $code = 400)
    {
        return response()->json(['error' => $message], $code);
    }
    protected function validateRequest(Request $request, array $rules)
{
    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        throw new ValidationException($validator);
    }
}
}
