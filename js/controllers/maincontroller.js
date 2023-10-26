app.controller('mainCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$ocLazyLoad', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$ocLazyLoad) {
	$rootScope.curUser = {};
	
	$scope.viewCompany =false;
	$scope.viewDepartment=false;
	$scope.viewDivision=false;
	$scope.viewDesignation=false;
	$scope.viewEmployee=false;
	$scope.viewContract=false;
	if($rootScope.isLogin){
		CrudService.getCurrentUser()
			.then(function (user) {			
				$rootScope.isAdmin = user.isadmin;
				$rootScope.curUser = user;
				CrudService.checkAccess('Contract',$rootScope.curUser.username).then(function (access) {
					$rootScope.viewContract = access.allowview;
				});
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
				CrudService.checkAccess('Internalhiring',$rootScope.curUser.username).then(function (access) {
					$rootScope.viewInternalhiringmaster = access.allowview;
					$rootScope.viewInternalhiringreport = access.allowview;
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
				CrudService.checkAccess('MMF',$rootScope.curUser.username).then(function (access) {
					$rootScope.viewMMF = access.allowview;
				});
				CrudService.checkAccess('MMF30',$rootScope.curUser.username).then(function (access) {
					$rootScope.viewMMF30 = access.allowview;
				});
				CrudService.checkAccess('IT',$rootScope.curUser.username).then(function (access) {
					$rootScope.viewITEIE = access.allowview;
					$rootScope.viewITIMAIL = access.allowview;
					$rootScope.viewITSHAREF = access.allowview;
				});
				CrudService.checkAccess('Advance',$rootScope.curUser.username).then(function (access) {
					$rootScope.viewAdvance = access.allowview;
					$rootScope.viewAdvPayment = access.allowview;
					$rootScope.viewAdvExpense = access.allowview;
				});
				// CrudService.checkAccess('AdvPayment',$rootScope.curUser.username).then(function (access) {
				// });
				// CrudService.checkAccess('AdvExpense',$rootScope.curUser.username).then(function (access) {
				// });
				if(!$rootScope.startRefresh) {
					$rootScope.startRefresh = setInterval($scope.refreshUsers, 1000);
				}
			});
		
	}
	$scope.refreshData=function(){
		$rootScope.$broadcast("dataRefreshing", true);
	}
	$scope.dataDayoff= function(){	
		loadModule($rootScope.viewDayoff,"doreport",false);
		$rootScope.$broadcast("initDO", "");
	}
	$scope.detailDayoff= function(){	
		loadModule($rootScope.viewDayoffdetail,"repdo");
		$rootScope.$broadcast("initRepDO", "");
	}
	$scope.dataRFC= function(){	
		loadModule($rootScope.viewRFC,"rfcreport",false);
		$rootScope.$broadcast("initRFC", "");
	}
	$scope.dataTR= function(){	
		loadModule($rootScope.viewTR,"trreport",false);
		$rootScope.$broadcast("initTR", "");
	}
	$scope.dataSPKL= function(){	
		loadModule($rootScope.viewSPKL,"spklreport",false);
		$rootScope.$broadcast("initSPKL", "");
	}
	$scope.detailSPKL= function(){	
		loadModule($rootScope.viewSPKL,"spkltmsreport");
		$rootScope.$broadcast("initSPKLTMSReport", "");
	}
	$scope.dataMMF= function(){	
		loadModule($rootScope.viewMMF,"mmfreport",false);
		$rootScope.$broadcast("initMMF", "");
	}
	$scope.dataMMF30= function(){	
		loadModule($rootScope.viewMMF30,"mmf30report",false);
		$rootScope.$broadcast("initMMF30", "");
	}
	$scope.dataITEIE= function(){	
		loadModule($rootScope.viewITEIE,"iteiereport",false);
		$rootScope.$broadcast("initITEIE", "");
	}
	$scope.dataITIMAIL= function(){	
		loadModule($rootScope.viewITIMAIL,"itimailreport",false);
		$rootScope.$broadcast("initITIMAIL", "");
	}
	$scope.dataITSHAREF= function(){	
		loadModule($rootScope.viewITSHAREF,"itsharefolderreport",false);
		$rootScope.$broadcast("initITSHAREF", "");
	}
	$scope.dataAdvance= function(){	
		loadModule($rootScope.viewAdvance,"advancereport",false);
		$rootScope.$broadcast("initAdvance", "");
	}
	$scope.dataAdvPayment= function(){	
		loadModule($rootScope.viewAdvPayment,"advancepaymentreport",false);
		$rootScope.$broadcast("initAdvPayment", "");
	}
	$scope.dataAdvExpense= function(){	
		loadModule($rootScope.viewAdvExpense,"advexpensereport",false);
		$rootScope.$broadcast("initAdvExpense", "");
	}


	$scope.myDayoff= function(){
		$location.path( "/dayoff" );
	}
	$scope.myRFC= function(){
		$location.path( "/rfc" );
	}
	$scope.myTR= function(){
		$location.path( "/tr" );
	}
	$scope.myMMF= function(){
		$location.path( "/mmf" );
	}
	$scope.myMMF30= function(){
		$location.path( "/mmf30" );
	}
	$scope.myITEIE= function(){
		$location.path( "/iteie" );
	}
	$scope.myITIMAIL= function(){
		$location.path( "/itimail" );
	}
	$scope.myITSHAREF= function(){
		$location.path( "/itsharefolder" );
	}
	$scope.myAdvance= function(){
		$location.path( "/advance" );
	}
	$scope.myAdvpayment= function(){
		$location.path( "/advancepayment" );
	}
	$scope.myAdvexpense= function(){
		$location.path( "/advexpense" );
	}

	$scope.internalhiring= function(){
		$location.path( "/internalhiring" );
	}

	$scope.mySPKL= function(){
		$location.path( "/spkl" );
	}
	$scope.myTimesheet= function(){
		$location.path( "/spkltms" );
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
		//$scope.template = "template/leave.html?v=2.08";
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
	$scope.dataInternalhiringmaster= function(){loadModule($rootScope.viewInternalhiringmaster,"internalhiringmaster");} 
	$scope.dataInternalhiringreport= function(){loadModule($rootScope.viewInternalhiringreport,"internalhiringreport");} 
	$scope.dataApprover= function(){loadModule($rootScope.viewApprover,"approver");} 
	$scope.dataRFCActivity= function(){loadModule($rootScope.viewRFCActivity,"rfcactivity");} 
	$scope.dataContractor= function(){loadModule($rootScope.viewRFCContractor,"rfccontractor");} 
	$scope.dataSKRate= function(){loadModule($rootScope.viewSKRate,"skrate");} 
	$scope.dayoffApproval= function(){ loadModule(true,"doapproval",true);$rootScope.$broadcast("initDO", "");} 
	$scope.RFCApproval= function(){ loadModule(true,"rfcapproval",true);$rootScope.$broadcast("initRFC", "");} 
	$scope.TRApproval= function(){ loadModule(true,"trapproval",true);$rootScope.$broadcast("initTR", "");} 
	$scope.mmfApproval= function(){ loadModule(true,"mmfapproval",true);$rootScope.$broadcast("initMMF", "");} 
	$scope.mmf30Approval= function(){ loadModule(true,"mmf30approval",true);$rootScope.$broadcast("initMMF30", "");} 
	$scope.iteieApproval= function(){ loadModule(true,"iteieapproval",true);$rootScope.$broadcast("initITEIE", "");} 
	$scope.itimailApproval= function(){ loadModule(true,"itimailapproval",true);$rootScope.$broadcast("initITIMAIL", "");} 
	$scope.itsharefApproval= function(){ loadModule(true,"itsharefolderapproval",true);$rootScope.$broadcast("initITSHAREF", "");} 
	$scope.advanceApproval= function(){ loadModule(true,"advanceapproval",true);$rootScope.$broadcast("initAdvance", "");} 
	$scope.advpaymentApproval= function(){ loadModule(true,"advancepaymentapproval",true);$rootScope.$broadcast("initPayment", "");} 
	$scope.advexpenseApproval= function(){ loadModule(true,"advexpenseapproval",true);$rootScope.$broadcast("initAdvExpense", "");} 
	$scope.SPKLApproval= function(){ loadModule(true,"spklapproval",true);$rootScope.$broadcast("initSPKL", "");} 
	$scope.SPKLTMSApproval = function(){ loadModule(true,"spkltmsapproval",true);$rootScope.$broadcast("initSPKLTMS", "");} 
	$scope.ContractRegister= function(){loadModule($rootScope.viewContract,"contract");} 
	
	function loadModule(access,template,filter){
		if(access || $rootScope.isAdmin){
			$scope.Filter=filter;
			$location.path( "/"+template );
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
		$location.path( "/detailuser" );
	}
	$scope.loadEmployee= function(data,mode){
		$scope.mode = mode;
		$scope.Employeeid = data.id;
		$scope.FirstName = data.firstname;
		$scope.LastName = data.lastname;
		$location.path( "/detailemployee" );
	}
	$scope.loadDayoff= function(data,mode,filter){
		$scope.Filter=filter;
		if (mode=='add'){
			CrudService.Create('dayoff',data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
					//DevExpress.ui.notify(response.message,"error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/dodetail" );
				}
			});
			// criteria = {status:'pending',username:data.username};
			// CrudService.FindData('dayoff',criteria).then(function (response){
				// if(response.jml>0){
					// DevExpress.ui.notify({
						// message: "Cannot add more request, You still have unsubmitted draft or pending request",
						// type: "warning",
						// displayTime: 5000,
						// height: 80,
						// position: {
						   // my: 'top center', 
						   // at: 'center center', 
						   // of: window, 
						   // offset: '0 0' 
					   // }
					// });
				// }else{
					// CrudService.Create('dayoff',data).then(function (response) {
						// if(response.status=="error"){
							 // DevExpress.ui.notify(response.message,"error");
						// }else if(response.status=="autherror"){
							// DevExpress.ui.notify(response.message,"error");
							// $scope.logout();
						// }else{
							// $scope.mode = mode;
							// $scope.Requestid = response.id;
							// $scope.Employeeid = response.employee_id;
							// $location.path( "/dodetail" );
						// }
					// });
				// }			
			// })
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/dodetail" );
		}
		
	}
	$scope.loadTR= function(data,mode,filter){
		$scope.Filter=filter;
		if (mode=='add'){
			CrudService.Create('tr',data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/trdetail" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/trdetail" );
		}
	}
	$scope.loadMMF= function(data,mode,filter){
		$scope.Filter=filter;
		if (mode=='add'){
			CrudService.Create('mmf',data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/mmfdetail" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/mmfdetail" );
		}
	}
	$scope.loadMMF30= function(data,mode,filter){
		$scope.Filter=filter;
		console.log(data);
		if (mode=='add'){
			CrudService.Create('mmf30',data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/mmf30detail" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/mmf30detail" );
		}
	}
	$scope.loadITEIE= function(data,mode,filter){
		$scope.Filter=filter;
		if (mode=='add'){
			CrudService.Create('iteie',data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/iteiedetail" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/iteiedetail" );
		}
	}
	$scope.loadITIMAIL= function(data,mode,filter){
		$scope.Filter=filter;
		if (mode=='add'){
			CrudService.Create('itimail',data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/itimaildetail" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/itimaildetail" );
		}
	}
	$scope.loadITSHAREF= function(data,mode,filter){
		$scope.Filter=filter;
		if (mode=='add'){
			CrudService.Create('itsharef',data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/itsharefolderdetail" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/itsharefolderdetail" );
		}
	}
	$scope.loadAdvance= function(data,mode,filter){
		$scope.Filter=filter;
		console.log(data);
		if (mode=='add'){
			CrudService.Create('advance',data).then(function (response) {
				console.log(response);
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/advancedetail" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/advancedetail" );
		}
	}
	$scope.loadAdvpayment= function(data,mode,filter){
		$scope.Filter=filter;
		console.log(data);
		if (mode=='add'){
			CrudService.Create('advpayment',data).then(function (response) {
				console.log(response);
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/advancepaymentdetail" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/advancepaymentdetail" );
		}
	}

	$scope.loadAdvexpense= function(data,mode,filter){
		$scope.Filter=filter;
		console.log(data);
		if (mode=='add'){
			CrudService.Create('advexpense',data).then(function (response) {
				console.log(response);
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/advexpensedetail" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/advexpensedetail" );
		}
	}
	$scope.loadSPKL= function(data,mode,filter){
		$scope.Filter=filter;
		if (mode=='add'){
			CrudService.Create('spkl',data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/detailspkl" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/detailspkl" );
		}
	}
	$scope.loadSPKLTMS= function(data,mode,filter){
		if (mode=='edit'){
			CrudService.Update('spkltms',data.id,data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else{
					$scope.Filter=filter;
					$scope.mode = mode;
					$scope.Requestid = data.id;
					$scope.Employeeid = data.employee_id;
					$location.path( "/detailspkltms" );
				}
			});	
		}else{
			$scope.Filter=filter;
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/detailspkltms" );
		}
	}
	$scope.loadRFC= function(data,mode,filter){
		$scope.Filter=filter;
		if (mode=='add'){
			CrudService.Create('rfc',data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$scope.Employeeid = response.employee_id;
					$location.path( "/detailrfc" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$scope.Employeeid = data.employee_id;
			$location.path( "/detailrfc" );
		}
	}
	$scope.loadContract= function(data,mode,filter){
		$scope.Filter=filter;
		if (mode=='add'){
			CrudService.Create('contractreg',data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}else if(response.status=="autherror"){
					DevExpress.ui.notify(response.message,"error");
					$scope.logout();
				}else{
					$scope.mode = mode;
					$scope.Requestid = response.id;
					$location.path( "/detailcontract" );
				}
			});
		}else{
			$scope.mode = mode;
			$scope.Requestid = data.id;
			$location.path( "/detailcontract" );
		}
	}
	// $scope.$on('detailData', function(event, id) {
		// $scope.Userid = id;
		// $scope.template = "template/detailuser.html?v=2.08";
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
			$location.path( "/" );
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
	$scope.isActive = function (viewLocation) { 
		return viewLocation === $location.path(); 
	};
}]);