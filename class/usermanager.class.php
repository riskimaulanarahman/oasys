<?php
Class UserManager extends Application{
	public function __construct(){
		parent::__construct();
		$this->get = isset($this->get)?$this->get:$_GET;
		$this->post = isset($this->post)?$this->post:$_POST;
		$this->heading = "";
		$this->output = "";
		$this->script = "";
		$this->method = $_SERVER['REQUEST_METHOD'];
		if (isset($this->get)){
			switch ($this->get['action']){
				case 'profile':
					$this->showProfile();
					break;
				case 'gantipass':
					$this->gantiPass();
					break;
				case 'resetpass':
					$this->resetPass();
					break;
				case 'apiuser':
					$this->userManager();
					break;
				case 'apirole':
					$this->roleManager();
					break;
				default:
					break;
			}
		}
	}
	public function showProfile(){
		echo json_encode($this->currentUser);
	}
	public function gantiPass(){
		$id = $this->post['id'];
		$oldpass = $this->post['oldpass'];
		$newpass = $this->post['newpass'];
		$user = User::find($id);
		if ($user){
			if(password_verify($oldpass, $user->password)){
				$user->password = password_hash($newpass, PASSWORD_BCRYPT);
				$user->save();
				echo json_encode(array("status"=>"success","message" => "Password updated"));
			}else{
				echo json_encode(array("status"=>"error","message" => "Old Password incorrect"));
			}
		}else{
			echo json_encode(array("status"=>"error","message" => "User not found"));
		}
	}
	public function resetPass(){
		$id = $this->post['id'];
		$newpass = $this->post['newpass'];
		$user = User::find($id);
		if ($user){
			$user->password = password_hash($newpass, PASSWORD_BCRYPT);
			$user->save();
			echo json_encode(array("status"=>"success","message" => "Password updated"));			
		}else{
			echo json_encode(array("status"=>"error","message" => "User not found"));
		}
	}
	public function userManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'create':
						$data = $this->post['data'];
						$data['password']=password_hash($data['password'], PASSWORD_BCRYPT);
						unset($data['__KEY__']);
						$user = User::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$user = User::find($id);
						$user->delete();
						echo json_encode($user);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$userx = User::find($id);
						foreach($data as $key=>$val){
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$userx->$key=$val;
						}
						$userx->save();
						echo json_encode($userx);
						break;
					case 'byid':
						$id = $this->post['id'];
						$user = User::find($id);
						echo json_encode($user->to_array());
						break;
					case 'isactive':
						$join = 'LEFT JOIN tbl_addressbook a ON(tbl_userlog.ldap_userid = a.username)';
						$user = Userlog::all(array('order'=>'LastAccess desc','joins' => $join,"conditions"=>array("isActive='1' and FLOOR(TIME_TO_SEC(TIMEDIFF(NOW(),LastAccess)) / 60) < 60 and not(ldap_userid='') "),'select' => '*,FLOOR(TIME_TO_SEC(TIMEDIFF(NOW(),LastAccess)) / 60) as min, TIME_TO_SEC(TIMEDIFF(NOW(),LastAccess)) % 60 as secs'));
						//$user = Userlog::all(array('joins' => array('user'),"conditions"=>array("isActive='1' and FLOOR(TIME_TO_SEC(TIMEDIFF(LastAccess,NOW())) / 60) < 60"),'select' => '*,FLOOR(TIME_TO_SEC(TIMEDIFF(NOW(),LastAccess)) / 60) as min, TIME_TO_SEC(TIMEDIFF(NOW(),LastAccess)) % 60 as secs'));	
						foreach ($user as &$result) {
							$result = $result->to_array();
						}
						$resultx = array("status"=>"success","data"=>$user);
						echo json_encode($resultx, JSON_NUMERIC_CHECK);						
						break;
					case 'current':
						$user = $this->currentUser;
						echo json_encode($user, JSON_NUMERIC_CHECK);
						break;	
					case 'checkaccess':
						$module = $this->post['module'];
						$username = $this->post['username'];
						try{
							$access = Accessuser::first(array('conditions' => array("tbl_module.module = ? and tbl_employee.loginname=?",$module,$username),'joins' => array('module', 'employee')));
							//echo "module: ".$module.", user: ".$username;
							if($access){
								echo json_encode($access->to_array(), JSON_NUMERIC_CHECK);
							}else{
								echo json_encode(array('allowview'=>false,'allowadd'=>false,'allowedit'=>false,'allowdelete'=>false,'message'=>'User has no access for module '.$module), JSON_NUMERIC_CHECK);
							}							
						}catch(Exception $e){
							$access= array('allowview'=>false,'allowadd'=>false,'allowedit'=>false,'allowdelete'=>false,'status'=>'error','message'=>$e->getMessage());
							echo json_encode($access, JSON_NUMERIC_CHECK);
						}						
						break;
					case 'all':
						$user = User::all();
						foreach ($user as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($user, JSON_NUMERIC_CHECK);
						break;
					case 'getcompany':
						$username = $this->post['username'];
						$Employee = Employee::find('first', array('conditions' => array("LoginName=?",$username),"include"=>array("location","department","company")));
						// $user = User::all();
						foreach ($Employee as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Employee, JSON_NUMERIC_CHECK);
						break;
					default:
						$user = User::all();
						foreach ($user as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($user, JSON_NUMERIC_CHECK);
						break;					
				}
			}
		}			
	}
	public function roleManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$role = Role::all();
						foreach ($role as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($role, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$role = Role::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$role = Role::find($id);
						$role->delete();
						echo json_encode($role);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$role = Role::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$role->$key=$val;
						}
						$role->save();
						echo json_encode($role);
						break;
					case 'byid':
						$id = $this->post['id'];
						$role = Role::find($id);
						echo json_encode($role, JSON_NUMERIC_CHECK);
						break;
					default:
						$role = Role::all();
						foreach ($role as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($role, JSON_NUMERIC_CHECK);

						break;					
				}
			}	
		}
	}
}