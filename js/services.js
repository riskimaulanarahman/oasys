app.factory('CrudService', CrudService);	
    CrudService.$inject = ['$http','$localStorage'];
    function CrudService($http,$localStorage) {
		var API = 'api/';
        var service = {};
	
        service.GetAll = GetAll;
        service.GetById = GetById;
		service.FindData = FindData;
        service.Create = Create;
        service.Update = Update;
        service.Delete = Delete;
		service.getCurrentUser = getCurrentUser;
        service.getActiveUser = getActiveUser;
		service.checkAccess = checkAccess;
        return service;

        function GetAll(module) {
			return $http.post(API+'api'+module,{'criteria':'all'}, "json").then(handleSuccess, handleError);
            // $.ajaxSetup({
                // headers : {
                  // 'Authorization' : 'Bearer ' + $localStorage.currentUser.token
                // }
              // });
			
            //return $.getJSON(API+'api'+module);
        }
        function GetById(module,id) {
            return $http.post(API+'api'+module,{'criteria':'byid','id':id}).then(handleSuccess, handleError);
        }
		
		function FindData(module,query) {
            return $http.post(API+'api'+module,{'criteria':'find','query':query}).then(handleSuccess, handleError);
        }
		
        function Create(module,data) {
            return $http.post(API+'api'+module,{'criteria':'create','data':data}).then(handleSuccess, handleError);
        }

        function Update(module,id,data) {
            return $http.post(API+'api'+module,{'criteria':'update','id':id,'data':data}).then(handleSuccess, handleError);
        }

        function Delete(module,id) {
            return $http.post(API+'api'+module,{'criteria':'delete','id':id}).then(handleSuccess, handleError);
        }
		
		function getCurrentUser() {
            var data = $http.post(API+'apiuser',{'criteria':'current'}).then(handleSuccess, handleError);
            return data;
        }
		
		function getActiveUser() {
            return $http.post(API+'apiuser',{'criteria':'isactive'}).then(handleSuccess, handleError);
        }
		
        function handleSuccess(res) {
            return res.data;
        }
		
		function checkAccess(module,username){
			return $http.post(API+'apiuser',{'criteria':'checkaccess','module':module,'test':'test','username':username}).then(handleSuccess, handleError);
		}
		
        function handleError(error) {
            return error;
        }
    }
