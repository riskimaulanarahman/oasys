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
	$scope.pendingITEIEReq = 0;
	$scope.pendingITEIE=0;
	$scope.pendingITIMAILReq=0;
	$scope.pendingITIMAIL=0;
	$scope.pendingITSHAREFReq=0;
	$scope.pendingITSHAREF=0;
	$scope.pendingAdvanceReq=0;
	$scope.pendingAdvance=0;
	$scope.pendingAdvpaymentReq=0;
	$scope.pendingAdvpayment=0;
	$scope.pendingAdvexpenseReq=0;
	$scope.pendingAdvexpense=0;
    function initController() {
		setTimeout(function(){ 
			// criteria = {status:'dashboard'};
			// CrudService.FindData('dayoffbyemp',criteria).then(function (response){
			// 	$scope.pendingDayoffReq=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('doapp',criteria).then(function (response){
			// 	$scope.pendingDayoff=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('rfcbyemp',criteria).then(function (response){
			// 	$scope.pendingRFCReq=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('rfcapp',criteria).then(function (response){
			// 	$scope.pendingRFC=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('mmfbyemp',criteria).then(function (response){
			// 	$scope.pendingMMF28Req=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('mmfapp',criteria).then(function (response){
			// 	$scope.pendingMMF28=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('mmf30byemp',criteria).then(function (response){
			// 	$scope.pendingMMF30Req=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('mmf30app',criteria).then(function (response){
			// 	$scope.pendingMMF30=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('trbyemp',criteria).then(function (response){
			// 	$scope.pendingTRReq=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('trapp',criteria).then(function (response){
			// 	$scope.pendingTR=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('spklbyemp',criteria).then(function (response){
			// 	$scope.pendingSPKLReq=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('spklapp',criteria).then(function (response){
			// 	$scope.pendingSPKL=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('spkltms',criteria).then(function (response){
			// 	$scope.pendingSPKLTMSReq=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('spkltmsapp',criteria).then(function (response){
			// 	$scope.pendingSPKLTMS=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('iteiebyemp',criteria).then(function (response){
			// 	$scope.pendingITEIEReq=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('iteieapp',criteria).then(function (response){
			// 	$scope.pendingITEIE=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('itimailbyemp',criteria).then(function (response){
			// 	$scope.pendingITIMAILReq=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('itimailapp',criteria).then(function (response){
			// 	$scope.pendingITIMAIL=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('itsharefbyemp',criteria).then(function (response){
			// 	$scope.pendingITSHAREFReq=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('itsharefapp',criteria).then(function (response){
			// 	$scope.pendingITSHAREF=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('advancebyemp',criteria).then(function (response){
			// 	$scope.pendingAdvanceReq=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('advanceapp',criteria).then(function (response){
			// 	$scope.pendingAdvance=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('advpaymentbyemp',criteria).then(function (response){
			// 	$scope.pendingAdvpaymentReq=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('advpaymentapp',criteria).then(function (response){
			// 	$scope.pendingAdvpayment=response.jml;
			// });
			// criteria = {status:'pending'};
			// CrudService.FindData('advexpensebyemp',criteria).then(function (response){
			// 	$scope.pendingAdvexpenseReq=response.jml;
			// });
			// criteria = {mypending:'true'};
			// CrudService.FindData('advexpenseapp',criteria).then(function (response){
			// 	$scope.pendingAdvexpense=response.jml;
			// });

			criteria = {status:'pendingapproval'};
			CrudService.FindData('dashboard',criteria).then(function (response){
				$.each(response,function(x,y){
					console.log(y);
					if(y.module == 'Dayoff') {
						$scope.pendingDayoff=y.jml;
					} else if(y.module == 'SPKL') {
						$scope.pendingSPKL=y.jml;
					} else if(y.module == 'SPKL_OT') {
						$scope.pendingSPKLTMS=y.jml;
					} else if(y.module == 'TR') {
						$scope.pendingTR=y.jml;
					} else if(y.module == 'RFC') {
						$scope.pendingRFC=y.jml;
					} else if(y.module == 'MMF28') {
						$scope.pendingMMF28=y.jml;
					} else if(y.module == 'MMF30') {
						$scope.pendingMMF30=y.jml;
					} else if(y.module == 'AD') {
						$scope.pendingITEIE=y.jml;
					} else if(y.module == 'ITMail') {
						$scope.pendingITIMAIL=y.jml;
					} else if(y.module == 'ITShare') {
						$scope.pendingITSHAREF=y.jml;
					} else if(y.module == 'Advance') {
						$scope.pendingAdvance=y.jml;
					} else if(y.module == 'Payment') {
						$scope.pendingAdvpayment=y.jml;
					} else if(y.module == 'Expense') {
						$scope.pendingAdvexpense=y.jml;
					}
				})
				
			});

			criteria = {status:'pendingrequest'};
			CrudService.FindData('dashboard',criteria).then(function (response){
				$.each(response,function(x,y){
					console.log(y);
					if(y.module == 'Dayoff') {
						$scope.pendingDayoffReq=y.jml;
					} else if(y.module == 'SPKL') {
						$scope.pendingSPKLReq=y.jml;
					} else if(y.module == 'SPKL_OT') {
						$scope.pendingSPKLTMSReq=y.jml;
					} else if(y.module == 'TR') {
						$scope.pendingTRReq=y.jml;
					} else if(y.module == 'RFC') {
						$scope.pendingRFCReq=y.jml;
					} else if(y.module == 'MMF28') {
						$scope.pendingMMF28Req=y.jml;
					} else if(y.module == 'MMF30') {
						$scope.pendingMMF30Req=y.jml;
					} else if(y.module == 'AD') {
						$scope.pendingITEIEReq=y.jml;
					} else if(y.module == 'ITMail') {
						$scope.pendingITIMAILReq=y.jml;
					} else if(y.module == 'ITShare') {
						$scope.pendingITSHAREFReq=y.jml;
					} else if(y.module == 'Advance') {
						$scope.pendingAdvanceReq=y.jml;
					} else if(y.module == 'Payment') {
						$scope.pendingAdvpaymentReq=y.jml;
					} else if(y.module == 'Expense') {
						$scope.pendingAdvexpenseReq=y.jml;
					}
				})
			});
			
		}, 500);
		
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