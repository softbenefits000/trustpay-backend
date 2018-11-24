<?php
namespace App\Library;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;

trait Utils {
    public static function getUserId () {
        return JWTAuth::parseToken()->authenticate()->id;
    }
    public static function getUser() {
        return JWTAuth::parseToken()->authenticate();
    }
    
    public static function getMissingParams (ValidationException $e) {
       
        $message = "";
        foreach($e->getResponse()->getOriginalContent() as $key=>$value) {
            $message = $message . $value[0].'\n';
        }
        return $message;
    }

    //to return response in each method
    public static function dataResponse ($message, $data = null, $status = "success") {
        if($status == "error")
            $statusCode = Response::HTTP_CREATED;
        else 
            $statusCode = Response::HTTP_OK;

    	if($data == null){
    		return new JsonResponse([
	            'status' => $status,
	            'message' => $message
            ], $statusCode);
    	}

        return new JsonResponse([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}