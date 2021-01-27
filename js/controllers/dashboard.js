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
	$scope.pendingMMF28=0;
	$scope.pendingMMF28Req=0;
	$scope.pendingMMF30=0;
	$scope.pendingMMF30Req=0;
	$scope.pendingTR=0;
	$scope.pendingTRReq=0;
	$scope.pendingSPKL=0;
	$scope.pendingSPKLReq = 0;
	$scope.pendingSPKLTMS = 0;
	$scope.pendingSPKLTMSReq = 0;
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
			criteria = {status:'pending'};
			CrudService.FindData('mmfbyemp',criteria).then(function (response){
				$scope.pendingMMF28Req=response.jml;
			});
			criteria = {mypending:'true'};
			CrudService.FindData('mmfapp',criteria).then(function (response){
				$scope.pendingMMF28=response.jml;
			});
			criteria = {status:'pending'};
			CrudService.FindData('mmf30byemp',criteria).then(function (response){
				$scope.pendingMMF30Req=response.jml;
			});
			criteria = {mypending:'true'};
			CrudService.FindData('mmf30app',criteria).then(function (response){
				$scope.pendingMMF30=response.jml;
			});
			criteria = {status:'pending'};
			CrudService.FindData('trbyemp',criteria).then(function (response){
				$scope.pendingTRReq=response.jml;
			});
			criteria = {mypending:'true'};
			CrudService.FindData('trapp',criteria).then(function (response){
				$scope.pendingTR=response.jml;
			});
			criteria = {status:'pending'};
			CrudService.FindData('spklbyemp',criteria).then(function (response){
				$scope.pendingSPKLReq=response.jml;
			});
			criteria = {mypending:'true'};
			CrudService.FindData('spklapp',criteria).then(function (response){
				$scope.pendingSPKL=response.jml;
			});
			criteria = {status:'pending'};
			CrudService.FindData('spkltms',criteria).then(function (response){
				$scope.pendingSPKLTMSReq=response.jml;
			});
			criteria = {mypending:'true'};
			CrudService.FindData('spkltmsapp',criteria).then(function (response){
				$scope.pendingSPKLTMS=response.jml;
			});
			criteria = {status:'pending'};
			CrudService.FindData('iteiebyemp',criteria).then(function (response){
				$scope.pendingITEIEReq=response.jml;
			});
			criteria = {mypending:'true'};
			CrudService.FindData('iteieapp',criteria).then(function (response){
				$scope.pendingITEIE=response.jml;
			});
			criteria = {status:'pending'};
			CrudService.FindData('itimailbyemp',criteria).then(function (response){
				$scope.pendingITIMAILReq=response.jml;
			});
			criteria = {mypending:'true'};
			CrudService.FindData('itimailapp',criteria).then(function (response){
				$scope.pendingITIMAIL=response.jml;
			});
			criteria = {status:'pending'};
			CrudService.FindData('itsharefbyemp',criteria).then(function (response){
				$scope.pendingITSHAREFReq=response.jml;
			});
			criteria = {mypending:'true'};
			CrudService.FindData('itsharefapp',criteria).then(function (response){
				$scope.pendingITSHAREF=response.jml;
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