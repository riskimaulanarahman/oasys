<?php
Class Datalogger extends Application{
	private $username;
	private $module;
	private $action;
	private $olddata;
	private $newdata;
	public function __construct($module="",$action="",$olddata="",$newdata=""){
		parent::__construct();
		$this->currentUser= $this->jwt->getUser();
		$this->username = $this->currentUser->username;
		$this->module = $module;
		$this->action = $action;
		$this->olddata = $olddata;
		$this->newdata = $newdata;
	}
	public function SaveData(){
		try {
			$data = new Audittrail();
			$data->username = $this->username;
			$data->module = $this->module;
			$data->action = $this->action;
			$data->olddata = $this->olddata;
			$data->newdata = $this->newdata;
			$data->save();
		}catch (Exception $e){
			$data = array("status"=>"error","message"=>$e->getMessage(), "data"=>array());
		}
		return $data;
	}
	public function getData(){
		try {
			$data = Audittrail::find('all');
			foreach ($data as &$result) {
				$result = $result->to_array();
			}
		}catch (Exception $e){
			$data = array("status"=>"error","message"=>$e->getMessage(), "data"=>array());
		}
		return $data;
	}
}