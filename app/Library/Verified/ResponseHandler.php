<?php
namespace App\Library\Verified;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

trait ResponseHandler {
	public static function error ($response, $message = null) {
		return json_encode([
			'status' => 'error',
			'message' => $message,
			'data'=> $response
		]);
	}

	public static function success ($response, $message = null) {
		return json_encode([
			'status' 	=> 'success',
			'message' => $message,
			'data'		=> $response
		]);
	}

	public static function processRequestResponse ($request) {
		return json_decode($request->getBody()->getContents(), true);
	}

	public static function statusHandling ($e) {
		return $e->getRequest();
		// if ($e->hasResponse()) {
		// 	echo Psr7\str($e->getResponse());
		// }
		// return;
		// if ($e->getResponse()->getStatusCode() == ‘422’)
		// {
		// 	$response = json_decode($e->getResponse()->getBody(true)->getContents());
		// 	return $response;
		// }
		// elseif ($e->getResponse()->getStatusCode() == ‘500’)
		// {
		// 	$response = json_decode($e->getResponse()->getBody(true)->getContents());
		// 	return $response;
		// }
		// elseif ($e->getResponse()->getStatusCode() == ‘401’)
		// {
		// 	$response = json_decode($e->getResponse()->getBody(true)->getContents());
		// 	return $response;
		// }
		// elseif ($e->getResponse()->getStatusCode() == ‘403’)
		// {
		// 	$response = json_decode($e->getResponse()->getBody(true)->getContents());
		// 	return $response;
		// }
		// else
		// {
		// 	$response = json_decode($e->getResponse()->getBody(true)->getContents());
		// 	return $response;
		// }
	}
}