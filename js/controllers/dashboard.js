(function (app) {
app.register.controller('dashboardCtrl', ['$rootScope','$scope', '$http', '$interval','$location','AuthenticationService','CrudService', function($rootScope,$scope, $http, $interval,$location,AuthenticationService,CrudService)  {
    
    $rootScope.$on("loginChanged", function(event, islogin) {
        $rootScope.isLogin = islogin;
        if ($rootScope.isLogin){
            initController();
        } 
    });
    if($rootScope.isLogin){
        initController();
    }
	$scope.pendingDayoff=0;
	$scope.pendingDayoffReq=0;
	$scope.pendingLeave=0;
	$scope.pendingLeaveReq=0;
	$scope.pendingRFC=0;
	$scope.pendingRFCReq=0;
	
    function initController() {
		setTimeout(function(){ 
			criteria = {status:'pending'};
			CrudService.FindData('dayoffbyemp',criteria).then(function (response){
				$scope.pendingDayoffReq=response.jml;
			});
			criteria = {mypending:'true'};
			CrudService.FindData('doapp',criteria).then(function (response){
				$scope.pendingDayoff=response.jml;
			});
			criteria = {status:'pending'};
			CrudService.FindData('rfcbyemp',criteria).then(function (response){
				$scope.pendingRFCReq=response.jml;
			});
			criteria = {mypending:'true'};
			CrudService.FindData('rfcapp',criteria).then(function (response){
				$scope.pendingRFC=response.jml;
			});
			
		}, 1000);
		
        // DevExpress.ui.notify({
            // message: "Welcome \r\n ",
            // type: "success",
            // displayTime: 5000,
            // height: 80,
            // position: {
               // my: 'top center', 
               // at: 'center center', 
               // of: window, 
               // offset: '0 0' 
           // }
        // });		
    };
    
}]);
})(app || angular.module("kduApp"));