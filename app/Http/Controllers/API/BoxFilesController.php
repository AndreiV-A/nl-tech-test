<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BoxFileResource;
use App\BoxFile;

use \Firebase\JWT\JWT;
use GuzzleHttp\Client;

class BoxFilesController extends Controller
{

	private function obtain_token(){
        // return UserResource::collection(User::paginate(10));
		// return base_path();

		$file_path = base_path().'/box_config.json';
		// print(file_exists($file_path) ? 'YES' : 'NO');
		$json = file_get_contents(base_path().'/box_config.json');
		$config = json_decode($json);
		// print_r($config->boxAppSettings);

		$private_key = $config->boxAppSettings->appAuth->privateKey;
		$passphrase = $config->boxAppSettings->appAuth->passphrase;
		$key = openssl_pkey_get_private($private_key, $passphrase);

		// var_dump($key);

		$authenticationUrl = 'https://api.box.com/oauth2/token';

		$claims = [
			'iss' => $config->boxAppSettings->clientID,
			'sub' => $config->enterpriseID,
			'box_sub_type' => 'enterprise',
			'aud' => $authenticationUrl,
			'jti' => base64_encode(random_bytes(64)),
			'exp' => time() + 45,
			'kid' => $config->boxAppSettings->appAuth->publicKeyID
		];

		// print_r($claims);

		$assertion = JWT::encode($claims, $key, 'RS512');

		// print_r($assertion);

		$params = [
			'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
			'assertion' => $assertion,
			'client_id' => $config->boxAppSettings->clientID,
			'client_secret' => $config->boxAppSettings->clientSecret
		];

		$client = new Client();
		$response = $client->request('POST', $authenticationUrl, [
			'form_params' => $params
		]);

		$data = $response->getBody()->getContents();
		$access_token = json_decode($data)->access_token;

		print_r($data);

		print_r($access_token);
    }

	private function use_token(){
		$token = 'iNkc7lYrfzyW4H9Qghv15DtIl18JrWRV';
		define('URL_BOX_FOLDER_CONTENTS', 'https://api.box.com/2.0/folders/0');


		// $http_client = new \GuzzleHttp\Client;
		$http_client = new Client();
		
		// try {
			$res = $http_client->post(URL_BOX_FOLDER_CONTENTS, [
				'headers' => [
					'Accept' => 'application/json',
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer iNkc7lYrfzyW4H9Qghv15DtIl18JrWRV',
				]
			]);

			print_r($res);
			
			// return $response->getBody();
		
		// } catch (\GuzzleHttp\Exception\BadResponseException $e) {
			
		// 	print_r($e);
			
		// }


	}

     public function index(){
        return BoxFileResource::collection(BoxFile::paginate(10));
    }
}
