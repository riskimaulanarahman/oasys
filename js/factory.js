app.factory('AuthenticationService', function ($http, $localStorage,$window,$rootScope) {
	var service = {};
	var API = 'api/';
	service.Login = Login;
	service.Logout = Logout;
	service.parseJwt = parseJwt;
	service.isAuthed = isAuthed;
	service.renewToken = renewToken;
	//service.isLogin = isLogin;
	return service;
	function Login(username, password, callback) {
		$http.post(API+'login', { username: username, password: password }).then(function onSuccess(response) {
			resp= response.data;		
			if (resp.jwt) {
				$rootScope.$broadcast("loginChanged", true);
				$localStorage.currentUser = { username: username, token: resp.jwt };
				$http.defaults.headers.common.Authorization = 'Bearer ' + resp.jwt;
				var respon = {message:response.message,status:'success'}
				callback(respon);
			} else {
				if (response.data.message){
					var respon = {message:response.data.message,status:'error'}
				}else{
					var respon = {message:'Login failed',status:'error'}
				}
				callback(respon);
			}			
		}, function(error){
			var respon = {message:error.data.message,status:'error'}
				callback(respon);
		}).catch(function onError(error) {
			var respon = {message:error.data.message,status:'error'}
			callback(respon)	; 		
		})
	}
	function renewToken(){
		$http.defaults.headers.common.Authorization = 'Bearer ' + $localStorage.currentUser.token;
		$http.post(API+'renewtoken').then(function onSuccess(response) {
			resp= response.data;					
			if (resp.jwt) {			
				var username= $localStorage.currentUser.username;
				$localStorage.currentUser = { username: username, token: resp.jwt };
				$http.defaults.headers.common.Authorization = 'Bearer ' + resp.jwt;
				return true;
			} else {
				return false
			}			
		}).catch(function onError(error) {
			return false		
		})
	}
	function isAuthed() {
		var token = $localStorage.currentUser.token;
		if(token) {
			var params = parseJwt(token);
			return  Math.round(new Date().getTime() / 1000) <= params.exp;
		} else {
			return false;
		}
	}
	function parseJwt(token) {
		var base64Url = token.split('.')[1];
		var base64 = base64Url.replace('-', '+').replace('_', '/');
		return JSON.parse($window.atob(base64));
	}
	function Logout(callback) {		
		$http.post(API+'logout').then(function onSuccess(response) {
			delete $localStorage.currentUser;
			$rootScope.$broadcast("loginChanged", false);
			$http.defaults.headers.common.Authorization = '';
			var respon = {message:response.message,status:'success'}
			callback(respon);
		}).catch(function onError(error) {
			var respon = {message:error.data.message,status:'error'}
			callback(respon); 		
		})
	}
});
app.run(function ($rootScope, $http, $location, $localStorage,AuthenticationService) {
	if ($localStorage.currentUser) {
		if (AuthenticationService.isAuthed()){
			AuthenticationService.renewToken();
			$rootScope.isLogin = true;
			$rootScope.isAdmin = $localStorage.currentUser.isAdmin;
			$http.defaults.headers.common.Authorization = 'Bearer ' + $localStorage.currentUser.token;
			//console.log( $localStorage.currentUser.token);
		}else{
			delete $localStorage.currentUser;
			$http.defaults.headers.common.Authorization = '';
			$rootScope.isLogin = false;
			$rootScope.isAdmin = false;
		}
	}else{
		$http.defaults.headers.common.Authorization = '';
		$rootScope.isLogin = false;
		$rootScope.isAdmin = false;
	}
	/*
	$rootScope.$on('$locationChangeStart', function (event, next, current) {
		var publicPages = ['/login'];
		var restrictedPage = publicPages.indexOf($location.path()) === -1;
		if (restrictedPage && !$localStorage.currentUser) {
			$location.path('/login');
		}
	});*/
});
