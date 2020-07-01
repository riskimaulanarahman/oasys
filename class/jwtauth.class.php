<?php
use \Firebase\JWT\JWT;
Class jwtAuth {
	public $data;
	private $token;
	private $jwt;
	public function __construct(){
		$authHeader = apache_request_headers();
		$arr = explode(" ", $authHeader['Authorization']);
		$this->jwt = $arr[1];
	}
	public function checkAuth(){
		if($this->jwt){
			try {
				$decoded = JWT::decode($this->jwt, SKEY, array('HS256'));
				return true;
			}catch (Exception $e){
				http_response_code(401);
				echo json_encode(array(
					"status"=>"autherror",
					"message" => "Access denied.",
					"error" => $e->getMessage()
				));
				return false;
			}
		}else{
			http_response_code(401);
			echo json_encode(array(
				"status"=>"autherror",
				"message" => "Access denied.",
				"error"=>"Access Token required"
			));
			return false;
		}
	}
	
	public function getUser(){
		$user=array();
		if($this->jwt){
			try {
				$decoded = JWT::decode($this->jwt, SKEY, array('HS256'));
				$user=$decoded->data;
			}catch (Exception $e){
				return array();
			}
		}
		return $user;
	}
	public function generateToken(){
		$this->token = array(
			"iss" => ISS,
			"aud" => AUD,
			"iat" => IAT,
			"nbf" => NBF,
			"exp" => EXPR,
			"data" => $this->data);
		$jwt = JWT::encode($this->token, SKEY);
		return $jwt;
	}
	public function renewToken(){
		if($this->jwt){
			try {
				$decoded = JWT::decode($this->jwt, SKEY, array('HS256'));
				$this->data =$decoded->data;
				$jwt= $this->generateToken();
			}catch (Exception $e){
				return array();
			}
		}
		return $jwt;
	}
}