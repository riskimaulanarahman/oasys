app.controller('mainCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$ocLazyLoad', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$ocLazyLoad) {
	$rootScope.curUser = {};
	
	$scope.viewCompany =false;
	$scope.viewDepartment=false;
	$scope.viewDivision=false;
	$scope.viewDesignation=false;
	$scope.viewEmployee=false;
	if($rootScope.isLogin){
		CrudService.getCurrentUser()
			.then(function (user) {			
				$rootScope.isAdmin = user.isadmin;
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
				if(!$rootScope.startRefresh) {
					$rootScope.startRefresh = setInterval($scope.refreshUsers, 1000);
				}
			});
		
	}
	
	$scope.template = "template/dashboard.html?v=2.02";
	$scope.dataUser= function(){
		$scope.template = "template/user.html?v=2.02";
	}
	$scope.dashboard= function(){
		$scope.template = "template/dashboard.html?v=2.02";
	}
	$scope.dataRole= function(){
		$scope.template = "template/role.html?v=2.02";
	}
	$scope.dataModule= function(){
		$scope.template = "template/module.html?v=2.02";
	}
	$scope.dataAccessUser= function(){
		$scope.template = "template/accessuser.html?v=2.02";
	}
	$scope.refreshData=function(){
		$rootScope.$broadcast("dataRefreshing", true);
	}
	$scope.dataDayoff= function(){	
		loadModule($rootScope.viewDayoff,"approval",false);
		$rootScope.$broadcast("initDO", "");
	}
	$scope.dataRFC= function(){	
		loadModule($rootScope.viewRFC,"rfcapproval",false);
		$rootScope.$broadcast("initRFC", "");
	}
	$scope.myDayoff= function(){
		$scope.Filter=false;
		$scope.template = "template/dayoff.html?v=2.02";
	}
	$scope.myRFC= function(){
		$scope.Filter=false;
		$scope.template = "template/rfc.html?v=2.02";
	}
	$scope.dataLeave= function(){	
		// loadModule($rootScope.viewDayoff,"approval",false);$rootScope.$broadcast("initDO", "");
		DevExpress.ui.notify({
			message: "Under Construction",
			type: "warning",
			displayTime: 5000,
			height: 80,
			position: {
			   my: 'top center', 
			   at: 'center center', 
			   of: window, 
			   offset: '0 0' 
		   }
		});
	}
	$scope.myLeave= function(){
		$scope.Filter=false;
		DevExpress.ui.notify({
			message: "Under Construction",
			type: "warning",
			displayTime: 5000,
			height: 80,
			position: {
			   my: 'top center', 
			   at: 'center center', 
			   of: window, 
			   offset: '0 0' 
		   }
		});
		//$scope.template = "template/leave.html?v=2.02";
	}
	$scope.leaveApproval= function(){ 
		//loadModule(true,"approval",true);$rootScope.$broadcast("initLeave", "");
		DevExpress.ui.notify({
				message: "Under Construction",
				type: "warning",
				displayTime: 5000,
				height: 80,
				position: {
				   my: 'top center', 
				   at: 'center center', 
				   of: window, 
				   offset: '0 0' 
			   }
			});
	} 
	$scope.dataCompany=function(){ loadModule($rootScope.viewCompany,"company"); } 
	$scope.dataHoliday=function(){ loadModule($rootScope.viewHoliday,"holiday"); } 
	$scope.dataDepartment=function(){loadModule($rootScope.viewDepartment,"department"); } 
	$scope.dataDivision=function(){loadModule($rootScope.viewDivision,"division"); } 
	$scope.dataDesignation= function(){loadModule($rootScope.viewDesignation,"designation");}  
	$scope.dataEmployee= function(){loadModule($rootScope.viewEmployee,"employee");} 
	$scope.dataApprover= function(){loadModule($rootScope.viewApprover,"approver");} 
	$scope.dataRFCActivity= function(){loadModule($rootScope.viewRFCActivity,"rfcactivity");} 
	$scope.dataContractor= function(){loadModule($rootScope.viewRFCContractor,"rfccontractor");} 
	$scope.dataSKRate= function(){loadModule($rootScope.viewSKRate,"skrate");} 
	$scope.dayoffApproval= function(){ loadModule(true,"approval",true);$rootScope.$broadcast("initDO", "");} 
	$scope.RFCApproval= function(){ loadModule(true,"rfcapproval",true);$rootScope.$broadcast("initRFC", "");} 
	//$scope.dataDayoff= function(){loadModule($rootScope.viewDayoff,"dayoff");} 
	function loadModule(access,template,filter){
		if(access || $rootScope.isAdmin){
			$scope.Filter=filter;
			$scope.template = "template/"+template+".html?v=2.02";
		}else{
			DevExpress.ui.notify({
				message: "You are not authorized to view this page",
				type: "error",
				displayTime: 5000,
				height: 80,
				position: {
				   my: 'top center', 
				   at: 'center center', 
				   of: window, 
				   offset: '0 0' 
			   }
			});
		}
	}
	$scope.loadUser= function(data){
		$scope.Userid = data.id;
		$scope.FirstName = data.firstname;
		$scope.LastName = data.lastname;
		$scope.template = "template/detailuser.html?v=2.02";
	}
	$scope.loadEmployee= function(data,mode){
		$scope.mode = mode;
		$scope.Employeeid = data.id;
		$scope.FirstName = data.firstname;
		$scope.LastName = data.lastname;
		$scope.template = "template/detailemployee.html?v=2.02";
	}
	$scope.loadDayoff= function(data,mode,filter){
		$scope.Filter=filter;
		if (mode=='add'){
			criteria = {status:'pending',username:data.username};
			CrudService.FindData('dayoff',criteria).then(function (response){
				if(response.jml>0){
					DevExpress.ui.notify({
						message: "Cannot add more request, You still have unsubmitted draft or pending request",
						type: "warning",
						displayTime: 5000,
						height: 80,
						position: {
						   my: 'top center', 
						   at: 'center center', 
						   of: window, 
						   offset: '0 0' 
					   }
					});
				}else{
					CrudService.Create('dayoff',data).then(function (response) {
						if(response.status=="error"){
							 DevExpress.ui.notify(response.message,"error");
						}else if(response.status=="autherror"){
							DevExpress.ui.notify(response.message,"error");
							$scope.logout();
						}else{
							$scope.mode = mode;
							$scope.Requestid = response.id;
							$scope.Employeeid = response.employee_id;
							$scope.template = "template/detaildayoff.html?v=2.02";
						}
					});
				}			
			})
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$scope.template = "template/detaildayoff.html?v=2.02";
		}
		
	}
	$scope.loadRFC= function(data,mode,filter){
		//if($rootScope.curUser.username=="purwanto_ihm"){
		$scope.Filter=filter;
		if (mode=='add'){
			CrudService.Create('rfc',data).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$scope.template = "template/detailrfc.html?v=2.02";
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$scope.template = "template/detailrfc.html?v=2.02";
		}
		//}
	}
	// $scope.$on('detailData', function(event, id) {
		// $scope.Userid = id;
		// $scope.template = "template/detailuser.html?v=2.02";
		// setTimeout(function(){ $rootScope.$broadcast("loaddetailData", id); }, 10);		
	// });
	$scope.users = [];
	$scope.editProfile = function(){
		DevExpress.ui.notify({
			message: "Under Construction",
			type: "info",
			displayTime: 5000,
			height: 80,
			position: {
			   my: 'top center', 
			   at: 'center center', 
			   of: window, 
			   offset: '0 0' 
		   }
		});
	}
	$scope.logout= function(){		
		AuthenticationService.Logout( function (result) {
			$rootScope.isAdmin= false;
			$rootScope.isLogin = false;
			$rootScope.$broadcast("namechanged", "");
			$rootScope.$broadcast("passchanged", "");
			$scope.template = "template/dashboard.html?v=2.02";
			stopRefresh();
		})
		
	}
	$scope.config={};
	$scope.appConfig = function(){
		if (!$rootScope.isAdmin){
			DevExpress.ui.notify({
				message: "You are not authorized to view this page",
				type: "error",
				displayTime: 5000,
				height: 80,
				position: {
				   my: 'top center', 
				   at: 'center center', 
				   of: window, 
				   offset: '0 0' 
			   }
			});
		} else{
			DevExpress.ui.notify({
				message: "Not Ready",
				type: "warning",
				displayTime: 5000,
				height: 80,
				position: {
				   my: 'top center', 
				   at: 'center center', 
				   of: window, 
				   offset: '0 0' 
			   }
			});
		}
	}
	$scope.refreshUsers = function() {
       if ($rootScope.isLogin){
			CrudService.getActiveUser()
			.then(function (resp) {			
				if (resp.status=="error"){
					DevExpress.ui.notify("Authentication Expired, please refresh your browser & login again","error");
					console.log("Refresh Error");
					$scope.logout();
					$rootScope.$broadcast("goterror", "Authentication Expired, please login again");
					stopRefresh();
				}else if(resp.status=="autherror"){
					console.log("Autherror");
					DevExpress.ui.notify("Authentication Expired, please refresh your browser & login again","error");
					$scope.logout();
					$rootScope.$broadcast("goterror", "Authentication Expired, please login again");
					stopRefresh();
				}else{
					if(resp.data.status=="autherror"){
						console.log("ErrorAuth");
						DevExpress.ui.notify("Authentication Expired, please refresh your browser & login again","error");
						$scope.logout();
						$rootScope.$broadcast("goterror", "Authentication Expired, please login again");
						stopRefresh();
					}else{
						//console.log(resp.data);
						$scope.users=resp.data;
					}
				}
			},function(data){
				console.log("Refresh user logout");
				DevExpress.ui.notify("Authentication Expired, please refresh your browser & login again","error");
				$scope.logout();
				
				$rootScope.$broadcast("goterror", "Authentication Expired, please login again");
				stopRefresh();
			});
		}
    }
	
    // $scope.$on('$destroy',function(){
        // if(promise)
            // stopRefresh();   
    // });
	function stopRefresh() {
	  clearInterval($rootScope.startRefresh);
	  $rootScope.startRefresh = null;
	}
	// window.onbeforeunload = function () {
		// $scope.logout();
	// };
}]);