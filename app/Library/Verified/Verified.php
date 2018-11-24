<?php
namespace App\Library\Verified;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\RequestOptions as _JSON;


class Verified {
	use ResponseHandler;

	private $user_id;
	private $apiKey;
	private $client;

	public function __construct () {

        $defaults = [
            'base_uri' => getenv('BASE_URL'),
            'headers'     => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'http_errors' => false,
            'handler'     => HandlerStack::create(new CurlHandler()) //use native curl
        ];

        $this->client = new Client($defaults);

		$this->user_id = getenv('USER_ID');
		$this->apiKey = getenv('API_KEY');

	}

	public function verify_single ($user_details) {
		if (empty($user_details) || !is_array($user_details)) {
			return;
		}
		if (!$this->verifyInput ($user_details))
			return ResponseHandler::error(null, "BVN and Firstname or Lastname or Phone Number Required");
			
		$data = [
			'json' => array_merge(
				$user_details, 
				[
					'api_key' => $this->apiKey,
					'userid' => $this->user_id	
				]
			)
		];

		try {
			$request = $this->client->post('sbmatch/wrapper', $data);
		 	$res = ResponseHandler::processRequestResponse($request);
		 	if('VALID' === $res['status']) {
		 		return ResponseHandler::success($res, "BVN Details are Valid");
			}
			else{
		 		return ResponseHandler::error($res, "Could not Verify BVN, Check Details");
		 	}
		}
		catch (RequestException $e) {
			$response = ResponseHandler::statusHandling($e);
			return ResponseHandler::error($response, 'Failed, Network Error');
		}
		catch (ClientException $e) {
			$response = ResponseHandler::statusHandling($e, 'Failed, Network/Client Error');
			return ResponseHandler::error($response);
		}
	}

	private function verifyInput ($user_details) {

		if(!array_key_exists('bvn', $user_details))
			return false;
		$keys = ['firstname', 'surname', 'phone'];
		foreach($keys as $key) {
			if (array_key_exists($key, $user_details)){
				return true;
			}
		}
		return false;

	}
}