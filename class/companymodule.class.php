<?php
Class CompanyModule extends Application{
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
				case 'apicompany':
					$this->companyManager();
					break;
				case 'uploadimage':
					$this->uploadImage();
					break;
				default:
					break;
			}
		}
	}
	
	public function companyManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$company = Company::all();
						foreach ($company as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($company);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$company = Company::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$company = Company::find($id);
						$company->delete();
						echo json_encode($company);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$company = Company::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$company->$key=$val;
						}
						$company->save();
						echo json_encode($company);
						break;
					case 'byid':
						$id = $this->post['id'];
						$company = Company::find($id);
						echo json_encode($company);
						break;
					default:
						$company = Company::all();
						foreach ($company as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($company, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
	public function uploadImage(){
		$max_image_size = 2048*2048;
		if(isset($_FILES['cpLogo'])) {
			if(!is_uploaded_file($_FILES['cpLogo']['tmp_name'])) {
				http_response_code(400);
				echo "Unable to upload File";
				exit;
			}
			if($_FILES['cpLogo']['size'] > $max_image_size) {
				http_response_code(413);
				echo "File Size too Large";
				exit;
			}
			if((strpos($_FILES['cpLogo']['type'], "image") === false) ){
				http_response_code(415);
				echo "Only Accept Image File";
				exit;
			}
			$path_to_file = "images/logo/".time()."_".$_FILES['cpLogo']['name'];
			$path_to_file = str_replace("%","_",$path_to_file);
			$path_to_file = str_replace(" ","_",$path_to_file);
			echo $path_to_file;
			move_uploaded_file($_FILES['cpLogo']['tmp_name'], $path_to_file);
		}else if(isset($_FILES['cpKop'])) {
			if(!is_uploaded_file($_FILES['cpKop']['tmp_name'])) {
				http_response_code(400);
				echo "Unable to upload File";
				exit;
			}
			if($_FILES['cpKop']['size'] > $max_image_size) {
				http_response_code(413);
				echo "File Size too Large";
				exit;
			}
			if((strpos($_FILES['cpKop']['type'], "image") === false) ){
				http_response_code(415);
				echo "Only Accept Image File";
				exit;
			}
			$path_to_file = "images/kop/".time()."_".$_FILES['cpKop']['name'];
			$path_to_file = str_replace("%","_",$path_to_file);
			$path_to_file = str_replace(" ","_",$path_to_file);
			echo $path_to_file;
			move_uploaded_file($_FILES['cpKop']['tmp_name'], $path_to_file);
		}
		else {
			http_response_code(400);
			echo "There is no file to upload";
			exit;
		}
		$max_image_size = 2048*2048;
		
	}
}