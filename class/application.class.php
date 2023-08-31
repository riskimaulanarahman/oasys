<?php
Class Application {
	public $dataMember = array();
	public $posts = array();
	public function __construct(){
		ActiveRecord\Config::initialize(function($cfg)
		{
			$cfg->set_model_directory(MODEL);
			$cfg->set_connections(array('dev' => 'mysql://'.DB_USER.':'.DB_PASSWORD.'@'.DB_HOST.'/'.DB_NAME));
			//$cfg->set_connections(array('production' => 'mysql://kdu:KDUPlanning$3rv3r@localhost/oasys'));
			// you can change the default connection with the below
			$cfg->set_default_connection('dev');
		});
		//$this->connectDB();
		// if(session_id() == '') session_start();
		// $this->session = $_SESSION;
		
		//session_write_close();
		$this->jwt = new jwtAuth();
		$this->currentUser= $this->jwt->getUser();	
	}
	public function connectDB(){
		//global $options;
		try{
			//$this->mysql = new db(DSN, DB_USER, DB_PASSWORD);
		}catch(Exception $e){
			 echo 'Database connection error, \n Caught exception: ',  $e->getMessage(), "\n";
		}
		
	}
	public function __set($name, $value)
    {
        $this->dataMember[$name] = $value;
    }
    public function __get($name)
    {
        if (array_key_exists($name, $this->dataMember)) {
            return $this->dataMember[$name];
        }
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in <b>'.$trace[0]['file'].
            '</b> on line <b>'.$trace[0]['line'].'</b>',
            E_USER_NOTICE);
        return null;
    }
    public function __isset($name)
    {
        return isset($this->dataMember[$name]);
    }
    public function __unset($name)
    {
        unset($this->dataMember[$name]);
    }
	public function showError(){
		return '<div class="alert alert-error"><a class="close" data-dismiss="alert">&times;</a><b>Error !</b><br>'.$this->pesan.'</div>';
	}
	public function showSuccess(){
		return "<div class='alert alert-success'><a class='close' data-dismiss='alert'>&times;</a><b>Information</b><br>".$this->pesan."</div>";
	}
	

	public function mycopy($s1) {

		$user = '.\\admin_temp';
		$password = 'KFPl4nn1ng$3rv3r';

		exec('net use "\\\\172.18.83.38\\www" /user:"'.$user.'" "'.$password.'" /persistent:no');
		$remote_directory = "\\\\172.18.83.38\\www\\oasys\\".$s1;
			
		$path = pathinfo($remote_directory);
		if (!file_exists($path['dirname'])) {
			mkdir($path['dirname'], 0777, true);
		}
		try {
			if(copy($s1,$remote_directory)){
				return "success";
			}else{
				$errors= error_get_last();
				$err =  "COPY ERROR: ".$errors['type'];
				$err .= "<br />\n".$errors['message'];
				return $err;
			}
		}catch (Exception $e){
			return $e->getMessage(); 
		}

		exec('net use "\\\\172.18.83.38\\www" /delete /yes');

	}

	public function processcopy($path) {
		try {
			$copy = $this->mycopy($path); 
			if ($copy!=="success"){
				echo "500";
			} else {
				unlink($path);
			}
		}catch (Exception $e){
			die(" cannot copy file ".$e->getMessage()); 
		}
	}

}
?>