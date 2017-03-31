<?php
if(!defined("SHA")) die("Access denied!");
require_once 'dbc/dbc.php';
require 'autoload.php';
require_once 'Input.php';
require_once 'config.php';
class Http{ var $http_method; public $db; protected $route_url=[]; public $next_object=[];
	public function Http(){ set_error_handler('getError');
		$this->http_method = $_SERVER['REQUEST_METHOD'];
		try{
			if(DB_STATUS == true){
				$this->db = new dbc([
					'database_type' => DATABASE_TYPE,
					'database_name' => DATABASE,
					'server' => HOST,
					'username' => USERNAME,
					'password' => PASSWORD,
					'charset' => 'utf8'
				]);
			}
		}catch(Exception $e){
			if($e){
				die(Http::json(["Database Connection failed"]));
			}
		}
		
		$this->input = new Input;
	}
	public function __call($name,$args){
		die('<p align="center">Error : '.$name.'() method is invalid');
	}
	public function routes($target=NULL,$callback=NULL){
		$pre = (filter_var($target, FILTER_SANITIZE_URL));
		if($pre!='' && is_array($callback)){ # multiple url calls
		 $splitter = explode("/",$pre); 
		 if(count($splitter)>=1){
			 $method = $splitter[0]; $addSlash = (isset($splitter[1]) && $splitter[1]!='')?'/':'';
			 array_shift($splitter);
			 $preUrl = $addSlash.implode("/",$splitter); #die;
			 foreach($callback as $url=>$call_function){
				$subUrl = (filter_var($url, FILTER_SANITIZE_URL));
				if($method == 'PAGE'){
					if($this->http_method == 'GET' || $this->http_method == 'POST' && $callback!=NULL) self::switchPage($preUrl.'/'.$subUrl,$call_function,false);
				 }else{ 
					if($this->http_method == $method && $callback!=NULL) self::switchPage($preUrl.'/'.$subUrl,$call_function);
				 }
			 }
		 }else{
			 die($this->setHeader("500","Bad format of routes"));
		 }
		}else{ # It may string OR func($app){}			
			$splitter = explode("/",$pre); 
			 if(count($splitter)>=1){
				 $method = $splitter[0]; $addSlash = (isset($splitter[1]) && $splitter[1]!='')?'/':'';
				 array_shift($splitter);
				 $preUrl = $addSlash.implode("/",$splitter); #die;
				 if($method == 'PAGE'){
					if($this->http_method == 'GET' || $this->http_method == 'POST' && $callback!=NULL) self::switchPage($preUrl,$callback,false);
				 }else{
					if($this->http_method == $method && $callback!=NULL) self::switchPage($preUrl,$callback);
				 }
			 }
		}
		#die;
	}
	public function db_routes(){ #$datas = $this->db->select("tbl_routes","*");
		$callStack = [];
		if(DB_STATUS == true){
			$datas = $this->db->select("tbl_routes","*",["status"=>"1"]);
			foreach($datas as $stack){
				$callStack[$stack['route']] = 'cms::'.$stack['type'];
			}
			return $callStack;	
		}else{
			die($this->setHeader("500","Enble DB_STATUS in config.php"));
		}
	}
	public function getDynamicContent($url,$table){ #$datas = $this->db->select("tbl_routes","*");
		$checkTypes = array('page'=>"tbl_pages",'blog'=>"tbl_blogs",'service'=>"tbl_service");
		$datas = $this->db->query("SELECT p.title,p.content,p.when_created,p.when_updated FROM tbl_routes r INNER JOIN `".$checkTypes[$table]."` p ON r.route_id=p.route_id WHERE r.route = '".$url."' ")->fetchAll();
		return $datas[0];
	}
	public function db_page(){ #$datas = $this->db->select("tbl_routes","*");
		$callStack = [];
		$datas = $this->db->query("SELECT * FROM tbl_routes r INNER JOIN tbl_pages p ON r.route_id=p.route_id")->fetchAll();
		foreach($datas as $stack){
			$callStack[$stack['route']] = array('cms::'.$stack['type'],$stack);
		} #echo 'running';
		return $callStack;
	}
	public function db_service(){ #$datas = $this->db->select("tbl_routes","*");
		$callStack = [];
		$datas = $this->db->query("SELECT p.title,p.content,p.when_created FROM tbl_routes r INNER JOIN tbl_service p ON r.route_id=p.route_id")->fetchAll();
		foreach($datas as $stack){
			$callStack[$stack['route']] = array('cms::'.$stack['type'],$stack);
		}
		return $callStack;
	}
	public function db_blog(){ 
		$callStack = [];
		$datas = $this->db->query("SELECT * FROM tbl_routes r INNER JOIN tbl_blogs p ON r.route_id=p.route_id")->fetchAll();
		foreach($datas as $stack){
			$callStack[$stack['route']] = array('cms::'.$stack['type'],$stack);
		}
		return $callStack;
	}
	public function droutes($target=NULL,$callback=false){ # CMS
		$pre = (filter_var($target, FILTER_SANITIZE_URL));
		if($pre!='' && $callback==true){  # multiple url calls			
			
			$splitter = explode("/",$pre);
			$method = $splitter[0]; $addSlash = (isset($splitter[1]) && $splitter[1]!='')?'/':'';
			
			$callback = $this->db_routes();
			array_shift($splitter);
			$preUrl = $addSlash.implode("/",$splitter); #die;
			foreach($callback as $url=>$call_function){
				$subUrl = (filter_var($url, FILTER_SANITIZE_URL));
				if($method == 'PAGE'){
					if($this->http_method == 'GET' || $this->http_method == 'POST' && $callback!=NULL) self::switchPage($preUrl.'/'.$subUrl,$call_function,false);
				 }else{
					if($this->http_method == $method && $callback!=NULL) self::switchPage($preUrl.'/'.$subUrl,$call_function);
				 }
			 }
						 
		}
		
	}
	public function get($target=NULL,$callback=NULL){
		$argUrl = (filter_var($target, FILTER_SANITIZE_URL));
		if($this->http_method == 'GET' && $callback!=NULL) self::switchPage($argUrl,$callback);
	}
	public function post($target=NULL,$callback=NULL){
		$argUrl = (filter_var($target, FILTER_SANITIZE_URL));
		if($this->http_method == 'POST' && $callback!=NULL) self::switchPage($argUrl,$callback);
	}
	public function put($target=NULL,$callback=NULL){
		$argUrl = (filter_var($target, FILTER_SANITIZE_URL));
		if($this->http_method == 'PUT' && $callback!=NULL) self::switchPage($argUrl,$callback);
	}
	public function delete($target=NULL,$callback=NULL){
		$argUrl = (filter_var($target, FILTER_SANITIZE_URL));
		if($this->http_method == 'DELETE' && $callback!=NULL) self::switchPage($argUrl,$callback);
	}
	public function page($target=NULL,$callback=NULL){
		$argUrl = (filter_var($target, FILTER_SANITIZE_URL));
		if(($this->http_method == 'GET' || $this->http_method == 'POST') && $callback!=NULL) self::switchPage($argUrl,$callback,false);
	}
	public function url($offset=NULL){
		$returnUrl = self::getCurrentUri();
		if($offset!='' && is_integer($offset)){
			$ex = explode("/", self::getCurrentUri());
			$returnUrl = (isset($ex[$offset]))?$ex[$offset]:'';
		}
		return $returnUrl;
	}
	private static function setHeader($status,$body=""){
		if($status!=""){
			header("HTTP/1.1 ".$status."");
			header("Content-Type: application/json");
			return json_encode(["status"=>$status,"body"=>$body]);
		}
	}
	private function response(){
		// set headder & status
		
	}
	public function clean_url($str, $replace=array(), $delimiter='-') {
		if($str != ''){
			if( !empty($replace) ) {
				$str = str_replace((array)$replace, ' ', $str);
			   }	   
			   $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
			   $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
			   $clean = strtolower(trim($clean, '-'));
			   $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
			  
			   return $clean;	
		}
   }
	public function file_save($file_path,$file_stream){
		if($file_path != "" && $file_stream != ""){
			$explode = explode(";base64,",$file_stream); # ;base64,
			$data	= explode("/",$explode[0]);
			$extension = $data[1];
			$output_file = $file_path.".".$extension;
			$ifp = fopen($output_file, "wb");	
			fwrite($ifp, base64_decode($explode[1])); 
			fclose($ifp);
		}
	}
	public function file_decode($file_stream){
		if($file_stream != ""){
			$explode = explode(";base64,",$file_stream); # ;base64,
			return base64_decode($explode[1]);
		}
	}
	public static function body(){
		return json_decode(file_get_contents("php://input"));
	}
	public static function json($content,$object=false){
		// application/json
		if($object==true){
			return json_decode(Http::setHeader("200",$content));
		}else{
			die(Http::setHeader("200",$content));
		}
	}
	
	private function error(){
		
	}
	private function has_duplicate($array){
		 $dupe_array = array();
		 foreach($array as $val){
		  if(++$dupe_array[$val] > 1){
		   return true;
		  }
		 }
		 return false;
	}
	function array_has_dupes($array) {
	   return count($array) !== count(array_unique($array));
	}
	private function get_colon_vars($str){ $colonVar=[];
		if(strpos($str,'/:') !==false){
			$slashColon = explode('/:', $str);
			array_shift($slashColon);
			foreach($slashColon as $value){
				if(strpos($value,'/') !==false){
					$colonVar[] = strstr($value, '/',true);
				}else{
					$colonVar[] = $value;
				}
			} 
		} return $colonVar;
	}
	private function getCurrentUri(){
		$basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
		$uri = substr($_SERVER['REQUEST_URI'], strlen($basepath));
		if (strstr($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));
		$uri = '/' . trim($uri, '/');
		return $uri;
	}
	private function switchPage($argUrl,$callback,$check_auth=true){
		if(isset($this->route_url[$this->http_method]) && in_array($argUrl,$this->route_url[$this->http_method])){
			die($this->setHeader("500",$this->http_method.': Duplicate URL called '.$argUrl.' multiple times called '));
		}else{

			$this->route_url[$this->http_method][] = ['url'=>$argUrl,'method'=>$this->http_method];
			
			if(strpos($argUrl, '/:')!==false){ # dynamic {name} url
				#$dynamic_route_args=[];
				$pattern = "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($argUrl)) . "$@D";
        		$matches = $route_args = Array();
        		if(isset($this->http_method) && preg_match($pattern, self::getCurrentUri(), $matches)){
        			  #array_shift($matches);
        			  #$this->dynamic_route_args[] = $matches;
        			  if($callback!=NULL) $this->access = $callback; 
					  $this->check_auth = $check_auth;
			    }

			}else{ 
				switch($argUrl){
					case self::getCurrentUri(): 
						if($callback!=NULL) $this->access = $callback;
						$this->check_auth = $check_auth;
					break;
					default:
					
				}
			}
		}
	}
	
	public function run($sh=NULL){
		# Auto Init Extender
		if(is_dir('extender/init')){
			foreach (glob("extender/init/*.php") as $ext_file){
				if(file_exists($ext_file)) require_once $ext_file;
			}
		}
		if(is_string($sh)){ $ext_file = EXT_PATH.$sh.'.php';
			if(file_exists($ext_file)) require_once $ext_file;
		}else if(is_array($sh)){
			foreach($sh as $sh_file){ $ext_file = EXT_PATH.$sh_file.'.php';
				if(file_exists($ext_file)) require_once $ext_file;
			}
		}
		switch($this->http_method){
			case ('GET' || 'POST' || 'PUT' || 'DELETE' || 'PAGE'): #echo self::getCurrentUri(); print_r($this->route_url[$this->http_method]);

				$routeCount = count($this->route_url[$this->http_method]); $notMatchCount=0;
				foreach($this->route_url[$this->http_method] as $route){ #echo self::getCurrentUri();#echo $route;
					$pattern = "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($route['url'])) . "$@D";
        			$matches = $route_args = Array();

        			if(isset($this->route_url[$this->http_method]) && $route['method']==$this->http_method && preg_match($pattern, self::getCurrentUri(), $matches)){
        			  array_shift($matches);
        			  if(count($matches) > 0){	
			            	$route_args = array_combine(self::get_colon_vars($route['url']),$matches);
			            }
			          #die;
					  if((isset($_SERVER['HTTP_'.SH_KEY]) && $_SERVER['HTTP_'.SH_KEY] == SH_VALUE) || SHA==FALSE || $this->check_auth == FALSE){
							$call = $this->access;
							if(is_string($call)){ # Routes
								$splitC = explode('::',$call);
								if($splitC[0] == 'cms'){ # CMS Process
										$cmsFile = CMS_PATH.$splitC[1].'.php';
										if($splitC[1] == 'service'){
											$datas = self::getDynamicContent(substr(self::getCurrentUri(), 1),$splitC[1]);
											$this->json($datas);										
										}else{
											if(file_exists($cmsFile)){
												$datas = self::getDynamicContent(substr(self::getCurrentUri(), 1),$splitC[1]);
												extract($datas);
												require_once $cmsFile; die;
											}
										}
								}elseif(count($splitC) == 2){ 
									$controller = $splitC[0]; $method = $splitC[1];
									
										if(file_exists(CONTROLLER_PATH.$controller.'.php')){
											require_once CONTROLLER_PATH.$controller.'.php';
											if (method_exists($controller,$method)){
												$obj = new $controller;
												$obj->$method();
												#call_user_func($call);
											}else{
												die($this->setHeader("500","Bad format of calling method"));
											}
										}else{
											die($this->setHeader("500","Bad format of calling controller"));
										}
									
								}else{
										die($this->setHeader("500","Bad format of calling controller"));
								}
							}elseif(is_array($call)){ # Check $splitC[0] cms::page or normal
								$splitC = explode('::',$call[0]);
								
								if($splitC[0] == 'cms'){ # CMS Process
										$cmsFile = CMS_PATH.$splitC[1].'.php';
										if($splitC[1] == 'service'){
											$this->json($call[1]);
										}else{
											if(file_exists($cmsFile)){
												#extract($call[1]);
												require_once $cmsFile; die;
											}	
										}
								}
							}else{ # Individual
								require_once 'Request.php';
								require_once 'Response.php';
								# $Request = (object)$route_args
								$call( new Http() , $Request = (new Request($route_args)) , $Response=(new Response) );
								#unset($_GET,$_POST);
							}
					  }else{ #echo $_SERVER['HTTP_'.SH_KEY];
						    if(SHA==true && !isset($_SERVER['HTTP_'.SH_KEY])){
								die($this->setHeader("401","Unauthorized"));
							}elseif($_SERVER['HTTP_'.SH_KEY] != SH_VALUE){
								die($this->setHeader("401","Unable to verify your token Value."));	
							}
							
					 }
					}else{ 
						$notMatchCount +=1;
					}
					# End Loop
				} 
				if($routeCount == $notMatchCount){
			    	die($this->setHeader("400","Bad Request"));
			    }
			break;
			default:
			
		}
	}
	public function controller($controller=NULL){
		if(file_exists(CONTROLLER_PATH.$controller.'.php')){
			require_once CONTROLLER_PATH.$controller.'.php';
			if(class_exists($controller)) return new $controller;
		}
	}
	public static function model($model=NULL){
		if(file_exists(MODEL_PATH.$model.'.php')){
			require_once MODEL_PATH.$model.'.php';
			if(class_exists($model)) return new $model;
		}
	}
	public static function view($file=NULL,$args=NULL){
			return self::html($file,$args);
	}
	public function html($file=NULL){ $args = func_get_args();
			if(count($args)>0 && $args[0]!=''){
				if(isset($args[1]) && $args[1]!=NULL){ 
					extract($args[1]);
				}
				$file = HTML_PATH.$args[0].'.php';
				if(file_exists($file)) require_once $file;
			} return new Http;
	}
	public static function library($class=NULL,$object=true){ $args = func_get_args();
			if(count($args)>0 && $args[0]!=''){
				$file = LIBRARY_PATH.$args[0].'.php';
				if(file_exists($file)) require_once $file;
				if(class_exists($args[0]) && $object==true) return new $args[0];
			}
	}
	public function db(){
		if(DB_STATUS == true){
			return $this->db;
		}else{ die($this->setHeader("500","Enble DB_STATUS in config.php")); }
	}
}
function http(){
	return new Http();
}
function db(){
	if(DB_STATUS == true){
		$dbc = new Http();
		return $dbc->db;
	}else{ die($this->setHeader("500","Enble DB_STATUS in config.php")); }
}
function getError($number, $msg, $file, $line, $vars){
	   $error = debug_backtrace(); #var_dump($error);
	   $msg = (isset($error[0]['file']))?'<pre><div style="margin:auto;"><p align="center">File : '.$error[0]['file'].'<br>':'';
	   $msg .= (isset($error[0]['line']))?'Line : '.$error[0]['line'].'<br>':'';
	   $msg .= (isset($error[0]['args'][1]))?'Error : '.$error[0]['args'][1].'</div></p></pre>':'';
	   die($msg);
}
