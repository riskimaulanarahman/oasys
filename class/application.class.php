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
}
?>