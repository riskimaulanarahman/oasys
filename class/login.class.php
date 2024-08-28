<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
Class Login extends Application{
	public function __construct(){
		parent::__construct();
		$this->get = isset($this->get)?$this->get:$_GET;
		$this->post = isset($this->post)?$this->post:$_POST;
		$this->heading = "";
		$this->output = "";
		$this->script = "";
		$this->ip = USER_IP;	
		if (isset($this->get)){
			switch ($this->get['action']){
				case 'logout' :
					$this->logout();
					break;
				case 'login':
					$this->checkLogin();
					break;
				case 'renewtoken' :
					$this->renewToken();
					break;
				default:
					$this->output='';
					$user = Userlog::find('first', array('conditions' => array("user_id=? and LoginIP=? and isActive='1'",$this->currentUser->id,$this->ip )));
					if(($user) && !($this->post['criteria'])){
						$user->lastaccess =date("Y-m-d H:i:s");
						$user->save();
					}
					break;
			}
		}
	}
	public function checkLogin(){
		$ldap=new LDAP();
		$ldap->server = LDAP_SERVER;
		$ldap->domain = DOMAIN;
		// $ldap2=new LDAP();
		// $ldap2->server = 'ns.lcl';
		// $ldap2->server = 'ldap://172.18.209.1';
		// $ldap2->server = 'ldap://172.24.8.2';
		// $ldap2->domain = 'NS' ;
		$data = json_decode(file_get_contents("php://input"));
		$username = isset($this->post['username'])?$this->post['username']:"";
		$password = isset($this->post['password'])?$this->post['password']:"";
		if (empty($username) or empty($password)){
			http_response_code(400);
			echo json_encode(array("message" => "Please input username & password"));
		}else if ((MAINTENANCE==1) && !($user->username=="admin")){
			http_response_code(400);
			echo json_encode(array("message" => "Server under maintenance please come back later"));
		}else {
			$ldap->username = $username;
			$ldap->password = $password;
			// $ldap2->username = $username;
			// $ldap2->password = $password;
			$user = User::find('first', array('conditions' => array("UserName=?",$username)));
			if ($user){
					if(password_verify($password, $user->password)){
						$log=$user->userlogs[0];
						$d1=new DateTime(date("Y-m-d H:i:s"));
						$d2=new DateTime($log->lastaccess);
						$diff=abs($d1->getTimestamp() - $d2->getTimestamp())/60;
						if(($log->isactive) && ($diff<=60) && ($log->loginip)!=$this->ip){
							echo json_encode(array("message" => "Login is used by IP :".$log->loginip));
						}else{
							if (($diff>60) || ($log->loginip==$this->ip)){
								$log->isactive = false;
								$log->save();
							}							
							// $user->create_userlogs(array(
							// 	'LoginTime'		=>date("Y-m-d H:i:s"),
							// 	'LoginIP'		=>$this->ip,
							// 	'displayname'	=>$user->firstname." ".$user->lastname,
							// 	'isActive'		=>true,
							// 	'LastAccess'	=>date("Y-m-d H:i:s"),
							// 	'LoginApp'		=>'Web'
							// ));	
							
							http_response_code(200);	
							
							$this->jwt->data = array("id"=>$user->id,"usertype"=>"sql_user",'isadmin'=>$user->isadmin,"username"=>$user->username,"firstname"=>$user->firstname,"lastname"=>$user->lastname,'email'=>$user->email);
							$jwt = $this->jwt->generateToken();
							echo json_encode(array("message" => "Successful login.","jwt" => $jwt));								
						}						
					}else{
						http_response_code(401);
						$err = new Errorlog();
						$err->errortype = "ClassicLogin";
						$err->errordate = date("Y-m-d h:i:s");
						$err->errormessage = "Invalid Credentials";
						$err->user = $username;
						$err->ip = $this->ip;
						$err->save();
						echo json_encode(array("message" => "Login Error : Invalid Credentials"));
					}					
			}else if ($ldap->connect()) {
				if($ldap->bind()){
					$log = Userlog::find('first', array('conditions' => array("ldap_userid=? and isActive='1'",$ldap->username )));
					$adb = Addressbook::find('first', array('conditions' => array("username=?",$ldap->username)));
					$emp = Employee::find('first', array('conditions' => array("loginname=?",$ldap->username)));
					$jml=($emp)?count($emp->to_array()):0;
					if($jml==0){
						http_response_code(401);
						echo json_encode(array("message" => "Login Error : Your login is not linked to the Employee data, please contact IT, If you require any further information"));
						// $comp = Company::find('first', array('conditions' => array("companycode=?",$adb->company)));
						// $empl= new Employee();
						// $empl->loginname = $ldap->username;
						// $empl->fullname = $adb->fullname;
						// if($comp){
							// $empl->company_id = $comp->id;
						// }
						// $empl->save();
					}else{
						$d1=new DateTime(date("Y-m-d H:i:s"));
						$d2=($log)?new DateTime($log->lastaccess):new DateTime(date("Y-m-d H:i:s"));
						$diff=abs($d1->getTimestamp() - $d2->getTimestamp())/60;
						if(($log->isactive) && ($diff<=60) && ($log->loginip!=$this->ip)){
							echo json_encode(array("message" => "Login is used by IP :".$log->loginip));
						}else if(($log->isactive) && ($log->loginip==$this->ip)){
							$log->lastaccess =date("Y-m-d H:i:s");
							$log->save();
							http_response_code(200);
							$this->jwt->data = array("id"=>"4","usertype"=>"ldap_user",'isadmin'=>false,"username"=>$ldap->username,"firstname"=>$adb->fullname,"lastname"=>"",'email'=>$adb->email);
							$jwt = $this->jwt->generateToken();
							echo json_encode(array("message" => "Successful re login.","jwt" => $jwt));
						}else{
							if (($diff>60) || ($log->loginip==$this->ip)){
								$log->isactive = false;
								$log->save();
							}							
							Userlog::create(array(
								'user_id'		=>'4',
								'ldap_userid'	=>$ldap->username,
								'displayname'	=>$adb->fullname,
								'LoginTime'		=>date("Y-m-d H:i:s"),
								'LoginIP'		=>$this->ip,
								'isActive'		=>true,
								'LastAccess'	=>date("Y-m-d H:i:s"),
								'LoginApp'		=>'Web'
							));	
							
							http_response_code(200);	
							
							$this->jwt->data = array("id"=>"4","usertype"=>"ldap_user",'isadmin'=>false,"username"=>$ldap->username,"firstname"=>$adb->fullname,"lastname"=>"",'email'=>$adb->email);
							$jwt = $this->jwt->generateToken();
							echo json_encode(array("message" => "Successful login. ok","jwt" => $jwt));								
						}
					}				
				}else{
				// 	 if ($ldap2->connect()) {
				// 		if($ldap2->bind()){
				// 			$log = Userlog::find('first', array('conditions' => array("ldap_userid=? and isActive='1'",$ldap2->username )));
				// 			$adb = Addressbook::find('first', array('conditions' => array("username=?",$ldap2->username)));
				// 			$emp = Employee::find('first', array('conditions' => array("loginname=?",$ldap2->username)));
				// 			$jml=($emp)?count($emp->to_array()):0;
				// 			if($jml==0){
				// 				http_response_code(401);
				// 				echo json_encode(array("message" => "Login Error : Your login is not linked to the Employee data, please contact System Administrator"));
				// 			}else{
				// 				$d1=new DateTime(date("Y-m-d H:i:s"));
				// 				$d2=($log)?new DateTime($log->lastaccess):new DateTime(date("Y-m-d H:i:s"));
				// 				$diff=abs($d1->getTimestamp() - $d2->getTimestamp())/60;
				// 				if(($log->isactive) && ($diff<=60) && ($log->loginip!=$this->ip)){
				// 					echo json_encode(array("message" => "Login is used by IP :".$log->loginip));
				// 				}else if(($log->isactive) && ($log->loginip==$this->ip)){
				// 					$log->lastaccess =date("Y-m-d H:i:s");
				// 					$log->save();
				// 					http_response_code(200);
				// 					$this->jwt->data = array("id"=>"4","usertype"=>"ldap_user",'isadmin'=>false,"username"=>$ldap->username,"firstname"=>$adb->fullname,"lastname"=>"",'email'=>$adb->email);
				// 					$jwt = $this->jwt->generateToken();
				// 					echo json_encode(array("message" => "Successful re login.","jwt" => $jwt));
				// 				}else{
				// 					if (($diff>60) || ($log->loginip==$this->ip)){
				// 						$log->isactive = false;
				// 						$log->save();
				// 					}							
				// 					Userlog::create(array(
				// 						'user_id'		=>'4',
				// 						'ldap_userid'	=>$ldap->username,
				// 						'displayname'	=>$adb->fullname,
				// 						'LoginTime'		=>date("Y-m-d H:i:s"),
				// 						'LoginIP'		=>$this->ip,
				// 						'isActive'		=>true,
				// 						'LastAccess'	=>date("Y-m-d H:i:s"),
				// 						'LoginApp'		=>'Web'
				// 					));	
									
				// 					http_response_code(200);	
									
				// 					$this->jwt->data = array("id"=>"4","usertype"=>"ldap_user",'isadmin'=>false,"username"=>$ldap->username,"firstname"=>$adb->fullname,"lastname"=>"",'email'=>$adb->email);
				// 					$jwt = $this->jwt->generateToken();
				// 					echo json_encode(array("message" => "Successful login. ok","jwt" => $jwt));
				// 				}
				// 			}			
				// 			exit;				
				// 		}else{
				// 			$err = new Errorlog();
				// 			$err->errortype = "LDAPLogin";
				// 			$err->errordate = date("Y-m-d h:i:s");
				// 			$err->errormessage = $ldap2->error;
				// 			$err->user = $username;
				// 			$err->ip = $this->ip;
				// 			$err->save();
				// 			http_response_code(401);
				// 			echo json_encode(array("message" => "Login Error : ".$ldap2->error));
				// 			exit;
				// 		}
				// 	}
					$err = new Errorlog();
					$err->errortype = "LDAPLogin";
					$err->errordate = date("Y-m-d h:i:s");
					$err->errormessage = $ldap->error;
					$err->user = $username;
					$err->ip = $this->ip;
					$err->save();
					http_response_code(401);
					echo json_encode(array("message" => "Login Error : ".$ldap->error.", please contact IT, If you require any further information"));
				}
			}else{
				$err = new Errorlog();
				$err->errortype = "GlobalLogin";
				$err->errordate = date("Y-m-d h:i:s");
				$err->errormessage = "Login Failed ";
				$err->user = $username;
				$err->ip = $this->ip;
				$err->save();
				http_response_code(401);
				echo json_encode(array("message" => "Login Failed : User not found"));
			}
		}
	}
	public function renewToken(){
		$auth = $this->jwt->checkAuth();
		if ($auth){
			$jwt = $this->jwt->renewToken();
			$user = Userlog::find('first', array('conditions' => array("user_id=? and LoginIP=? and isActive='1'",$this->currentUser->id,$this->ip )));
			$user->lastaccess =date("Y-m-d H:i:s");
			$user->save();
			echo json_encode(array("message" => "Successful login.","jwt" => $jwt));
		}
	}
	public function logout(){
		$users = Userlog::find('all', array('conditions' => array("user_id=? and LoginIP=? and isActive='1'",$this->currentUser->id,$this->ip )));
		foreach ($users as $user) {
			$user->isactive = false;
			$user->lastaccess =date("Y-m-d H:i:s");
			$user->save();
		}
		echo json_encode(array("message"=>"You have been logged out"));
	}
}
?>