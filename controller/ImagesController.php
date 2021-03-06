<?php 

function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}


class ImagesController{
	function index(){
		if($this->is_image($_FILES["file"]) ){
			$content = file_get_contents( $_FILES["file"]['tmp_name']);
			$remotepath =  'images/'.date('Y/m/d/');
			$filename = $_FILES['file']['name'];
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			$remotefile = $remotepath.generateRandomString(6).".".$ext;
			$result = onedrive::upload(config('onedrive_root').$remotefile, $content);
			
			if($result){
				$cachefile = CACHE_PATH . md5('dir_'.config('onedrive_root').$remotepath) . '.php';
				unlink($cachefile);
				$root = get_absolute_path(dirname($_SERVER['SCRIPT_NAME'])).config('root_path');
				$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
				$url = $http_type.$_SERVER['HTTP_HOST'].$root.$remotefile;
				if (!is_null($_GET['noredir'])) {
					return 'url: '.$url."\n";
				} else {
					view::direct($url.((config('root_path') == '?')?'&s':'?s'));
				}
			}
		}
		return view::load('images/index');
	}

	function is_image($file){
		$config = config('images@base');
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if(!in_array($ext,$config['exts'])){
			return false;
		}
		if($file['size'] > 10485760 || $file['size'] == 0){
			return false;
		}

		return true;
	}
}
