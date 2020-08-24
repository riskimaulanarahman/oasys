 app.controller('LoginController', ['$rootScope','$scope', '$http', '$interval','$location','AuthenticationService','CrudService', function($rootScope,$scope, $http, $interval,$location,AuthenticationService,CrudService)  {
        var vm = this;
        /*initController();
        function initController() {
            AuthenticationService.Logout();		
        };*/
        $scope.$on("namechanged", function(event, name) {
            $scope.username = name;
        });
        $scope.$on("passchanged", function(event, name) {
            $scope.password = name;
        });
		$scope.$on("goterror", function(event, name) {
            $scope.error = name;
        });
        $scope.login = function() {
            $scope.loading = true;
            AuthenticationService.Login($scope.username, $scope.password, function (result) {
                if (result.status === 'success') {                  
                    CrudService.getCurrentUser()
                    .then(function (user) {
                        $rootScope.isLogin = true;
                        $scope.loading = false;
                        $rootScope.isAdmin= user.isadmin;
                        $rootScope.curUser = user;
						CrudService.checkAccess('Company',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewCompany = access.allowview;
						});
						CrudService.checkAccess('Department',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewDepartment = access.allowview;
						});
						CrudService.checkAccess('Division',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewDivision = access.allowview;
						});
						CrudService.checkAccess('Designation',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewDesignation = access.allowview;
						});
						CrudService.checkAccess('Employee',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewEmployee = access.allowview;
						});
						CrudService.checkAccess('Dayoff',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewDayoff = access.allowview;
						});
						CrudService.checkAccess('Approver',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewApprover = access.allowview;
						});
						CrudService.checkAccess('Holiday',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewHoliday = access.allowview;
						});
						CrudService.checkAccess('RFCActivity',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewRFCActivity = access.allowview;
						});
						CrudService.checkAccess('RFCContractor',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewRFCContractor = access.allowview;
						});
						CrudService.checkAccess('SKRate',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewSKRate = access.allowview;
						});
						CrudService.checkAccess('RFC',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewRFC = access.allowview;
						});
						CrudService.checkAccess('ApprovedWPHC',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewDayoffdetail = access.allowview;
						});
						CrudService.checkAccess('TR',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewTR = access.allowview;
						});
						CrudService.checkAccess('SPKL',$rootScope.curUser.username).then(function (access) {
							$rootScope.viewSPKL = access.allowview;
						});
						if(!$rootScope.startRefresh) {
							$rootScope.startRefresh = setInterval($scope.refreshUsers, 1000);
						}
                    });
                } else {
					console.log(result);
                    $scope.error = result.message;
					$scope.password ='';
					$rootScope.isLogin = false;
					$rootScope.isAdmin= false;
					$rootScope.curUser = [];
                    $scope.loading = false;
                }
            },
			function(data) {
				console.log(data);
				$rootScope.isLogin = false;
				$rootScope.isAdmin= false;
				$rootScope.curUser = [];
				$scope.loading = false;
			});
        };
        
    }]);