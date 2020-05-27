<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BoxFileResource;

use \Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Carbon\Carbon;

use App\ApiToken;
use App\BoxFile;



function var_debug($variable,$strlen=100,$width=25,$depth=10,$i=0,&$objects = array()){
		$search = array("\0", "\a", "\b", "\f", "\n", "\r", "\t", "\v");
		$replace = array('\0', '\a', '\b', '\f', '\n', '\r', '\t', '\v');
		
		$string = '';
		
		switch(gettype($variable)) {
			case 'boolean':      $string.= $variable?'true':'false'; break;
			case 'integer':      $string.= $variable;                break;
			case 'double':       $string.= $variable;                break;
			case 'resource':     $string.= '[resource]';             break;
			case 'NULL':         $string.= "null";                   break;
			case 'unknown type': $string.= '???';                    break;
			case 'string':
			$len = strlen($variable);
			$variable = str_replace($search,$replace,substr($variable,0,$strlen),$count);
			$variable = substr($variable,0,$strlen);
			if ($len<$strlen) $string.= '"'.$variable.'"';
			else $string.= 'string('.$len.'): "'.$variable.'"...';
			break;
			case 'array':
			$len = count($variable);
			if ($i==$depth) $string.= 'array('.$len.') {...}';
			elseif(!$len) $string.= 'array(0) {}';
			else {
				$keys = array_keys($variable);
				$spaces = str_repeat(' ',$i*2);
				$string.= "array($len)\n".$spaces.'{';
				$count=0;
				foreach($keys as $key) {
				if ($count==$width) {
					$string.= "\n".$spaces."  ...";
					break;
				}
				$string.= "\n".$spaces."  [$key] => ";
				$string.= var_debug($variable[$key],$strlen,$width,$depth,$i+1,$objects);
				$count++;
				}
				$string.="\n".$spaces.'}';
			}
			break;
			case 'object':
			$id = array_search($variable,$objects,true);
			if ($id!==false)
				$string.=get_class($variable).'#'.($id+1).' {...}';
			else if($i==$depth)
				$string.=get_class($variable).' {...}';
			else {
				$id = array_push($objects,$variable);
				$array = (array)$variable;
				$spaces = str_repeat(' ',$i*2);
				$string.= get_class($variable)."#$id\n".$spaces.'{';
				$properties = array_keys($array);
				foreach($properties as $property) {
				$name = str_replace("\0",':',trim($property));
				$string.= "\n".$spaces."  [$name] => ";
				$string.= var_debug($array[$property],$strlen,$width,$depth,$i+1,$objects);
				}
				$string.= "\n".$spaces.'}';
			}
			break;
		}
		
		if ($i>0) return $string;
		
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		do $caller = array_shift($backtrace); while ($caller && !isset($caller['file']));
		if ($caller) $string = $caller['file'].':'.$caller['line']."\n".$string;
		
		return $string;
}

function var_debug_print($variable){
	print('<pre>'.var_debug($variable).'</pre>');
}

class BoxFilesController extends Controller
{

	const BA_API_ENTRY = 'https://api.box.com/2.0';
	const BA_UPLOAD_ENTRY = 'https://upload.box.com/api/2.0';
	const BA_FOLDERS = '/folders';
	const BA_FILES = '/files';
	
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
		/* example response:
		{"access_token":"KV1hs4HO3Z7xboBigqKoJ6WOoZMtJklT","expires_in":3994,"restricted_to":[],"token_type":"bearer"}
		*/

		$data = json_decode($response->getBody()->getContents());
		// print_r($data);

		$token = ApiToken::create([
			'service' => 'box_JustBeginingTesting',
			'access_token' => $data->access_token,
			'type' => $data->token_type,
			'expiry' => $data->expires_in,
		]);
		// var_debug_print($token);
		$token->save();
		// var_debug_print($token->access_token);
		return $token;
    }

	private function isTokenExpired($token){
		return ((new Carbon($token->updated_at))->addSeconds($token->expiry - 600))->lessThan(Carbon::now());
	}

	private function getToken(){
		$token = ApiToken::where([
			['service', '=', 'box_JustBeginingTesting'],
			['stale', '=', false],
		// ])->firstOrFail();
		])->first();

		if (is_null($token)){
			$token = $this->obtain_token();
		} elseif($this->isTokenExpired($token)){
			$token->stale = true;
			$token->save();
			$token = $this->obtain_token();
		}

		return is_null($token) ? false : $token->access_token;
	}

	private function get_folder_info($token, $folder_id){
		$http_client = new Client(['base_uri' => self::BA_API_ENTRY]);
		
		try {
			$res = $http_client->request('GET', self::BA_FOLDERS.'/'.$folder_id, [
				\GuzzleHttp\RequestOptions::HEADERS => [
					'Accept' => 'application/json',
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer '.$token,
				]
			]);

			// print_r($res);
			
			return json_decode($res->getBody());
			// var_debug_print(json_decode($res->getBody()));
		
		} catch (\GuzzleHttp\Exception\BadResponseException $e) {
			
			print_r($e);
			
		}
	}

	private function uploadFileToBox($token, $full_file_path, $file_name){
		$http_client = new Client();

		try {
			$res = $http_client->request('POST', self::BA_UPLOAD_ENTRY.self::BA_FILES.'/content', [
				\GuzzleHttp\RequestOptions::HEADERS => [
					'Authorization' => 'Bearer '.$token,
				],
				\GuzzleHttp\RequestOptions::MULTIPART => [
					[
						'name'     => 'attributes',
						'contents' => json_encode([ 
							'name' => $file_name, 
							'parent' => [ 
								'id' => '0' 
							]
						]),
    				],
    				[
						'name'     => 'contents',
						'contents' => file_get_contents($full_file_path),
						'filename' => $file_name,
    				],
    				
				]
			]);
			// var_debug_print($res);
			return json_decode($res->getBody());

		} catch(\GuzzleHttp\Exception\BadResponseException $e) {
			var_debug_print($e);
			return false;
		}
	}

	private function registerFile($box_id){
		$boxfile = BoxFile::create([
			'box_id' => $box_id,
		]);
		$boxfile->save();
		return $boxfile;
	}

	private function deleteRemoteFile($token, $box_id){
		print('BoxFilesController.deleteRemoteFile: '.$token.'  --  '.$box_id);
		https://api.box.com/2.0/files/:file_id
		$http_client = new Client();
		try {
			$res = $http_client->request('DELETE', self::BA_API_ENTRY.self::BA_FILES.'/'.$box_id, [
				\GuzzleHttp\RequestOptions::HEADERS => [
					'Accept' => 'application/json',
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer '.$token,
				]
			]);

			// print_r($res);
			
			return json_decode($res->getBody());
			// var_debug_print(json_decode($res->getBody()));
		
		} catch (\GuzzleHttp\Exception\BadResponseException $e) {
			
			var_debug_print($e);
			
		}
	}

	public function deleteFile(Request $request, BoxFile $boxfile){
		// print('BoxFilesController.deleteFile: '.$boxfile->box_id);
		// var_debug_print($boxfile);

		// return json_encode([
		// 	'message' => 'OK!',
		// 	'object' => $boxfile,
		// 	'request' => $request,
		// ]);

		// return response(json_encode([
		// 	'message' => 'OK!',
		// 	'object' => $boxfile,
		// ]), 404);

		// abort(403, 'Unauthorized action.');

		// $this->obtain_token();
		$token = $this->getToken();
		// print($token);

		$this->deleteRemoteFile($token, $boxfile->box_id);
		$boxfile->delete();
	}

	public function test1(){
		// $this->obtain_token();
		$token = $this->getToken();
		print($token);
	}

    public function index(){
        return BoxFileResource::collection(BoxFile::paginate(10));
    }

	public function store(Request $request){

		$allFiles = $request->allFiles();
		$hasFile = $request->hasFile('file');
		$file = $request->file('file');

		Log::debug('BoxFilesController.store:', [
			// '$request' => $request, 
			// '$request->attributes' => $request->attributes,
			// '$request->path()' => $request->path(),
			// '$request->method()' => $request->method(),
			// '$request->all()' => $request->all(),
			// '$request->input(\'file\')' => $request->input('file'),
			// '$allFiles' => $allFiles,
			// '$hasFile' => $hasFile,
			// '$filek' => $filek,
			// 'path' => $filek->path(),
			// 'extension' => $filek->extension(),
			// 'getClientOriginalName' => $filek->getClientOriginalName(),
			'getRealPath' => $file->getRealPath(),
		]);
		// Log::debug(print_r($request, true));
		// print('BoxFilesController.store');

		// if (file_exists($filek->getRealPath())){
		// 	copy($filek->getRealPath(), '/tmp/phpLOOOOOOOOOOOOO');
		// }

		// return json_encode([
		// 	'realPath' => $file->getRealPath(),
		// 	'clientOriginalName' => $file->getClientOriginalName(),
		// 	'exists' => file_exists($file->getRealPath()),
		// ]);

		$token = $this->getToken();
		$upload_data = $this->uploadFileToBox(
			$token, $file->getRealPath(), $file->getClientOriginalName().'_'.\Illuminate\Support\Str::random(5)
		);
		// var_debug_print($upload_data);
		return $this->registerFile($upload_data->entries[0]->id);
		
	}

	private function _deb_list_tokens(){
		print('<pre>');
		foreach (ApiToken::where([
			['service', '=', 'box_JustBeginingTesting'],
			// ['stale', '=', false],
		])->cursor() as $token) {
			// var_debug_print($token);
			print(
				$token->access_token.'  '
				.($this->isTokenExpired($token) ? 'EXPIRED' : 'VALID').'  '
				.(string)$token->stale
				.'<br>');
		}
		print('</pre>');
	}
}
