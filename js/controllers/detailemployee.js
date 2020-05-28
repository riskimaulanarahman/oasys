(function (app) {
app.register.controller('detailemployeeCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	$scope.data = [];  
	CrudService.GetAll('div').then(function (resp) {
        $scope.divDatasource=new DevExpress.data.DataSource(resp);
    });
	CrudService.GetAll('des').then(function (resp) {
        $scope.desDatasource=new DevExpress.data.DataSource(resp);
    });
	CrudService.GetAll('dept').then(function (resp) {
        $scope.deptDatasource=new DevExpress.data.DataSource(resp);
    });
	CrudService.GetAll('company').then(function (resp) {
        $scope.compDatasource=new DevExpress.data.DataSource(resp);
    });
	CrudService.GetAll('grade').then(function (resp) {
        $scope.gradeDatasource=new DevExpress.data.DataSource(resp);
    });
	CrudService.GetAll('religion').then(function (resp) {
        $scope.religionDatasource=new DevExpress.data.DataSource(resp);
    });
	CrudService.GetAll('loc').then(function (resp) {
        $scope.locDatasource=new DevExpress.data.DataSource(resp);
    });
	$scope.gender = [{id:'M',gender:'Male'},{id:'F',gender:'Female'}];
	CrudService.GetById('emp',$scope.Employeeid).then(function(response){
		$scope.data = response;
		if($scope.mode!=="add"){
			CrudService.GetById('leavebyemp',$scope.Employeeid).then(function (resp) {
				$scope.dataGrid1=resp;
			}).finally(function() {
				$scope.grid1Loaded = true;
			});
		}
		$scope.detailFormOptions = { 
			onInitialized: function(e) {
				$scope.formInstance = e.component;
			},
			onContentReady: function(e){
				$scope.formInstance = e.component;
			},
			readOnly : ($scope.mode=='view')?true:false,
			labelLocation : "left",
			minColWidth  :300,
			colCount : 1,	
			formData:$scope.data,
			
			items: [{
					itemType: "group",
					caption: "Data Employee",
					colCount : 2,
					items: [
				{dataField:'sapid',caption:'SAP ID',validationRules: [{
							type: "required",
							message: "SAP ID is required"
						}]},
				{ dataField: "fullname", label: { text: 'Employee Name' },validationRules: [{
							type: "required",
							message: "Employee Name is required"
						}]},
				{
					dataField: "company_id", label: { text: 'Company' },
					editorType: "dxSelectBox",
					editorOptions: {
						dataSource: $scope.compDatasource,
						displayExpr: "companyname",
						valueExpr: "id",
					},validationRules: [{
							type: "required",
							message: "Company is required"
						}]
				},
				{
					dataField: "department_id", label: { text: 'Department' },
					editorType: "dxSelectBox",
					validationRules: [{
							type: "required",
							message: "Department is required"
						}],
					editorOptions: {
						dataSource: $scope.deptDatasource,
						displayExpr: "departmentname",
						valueExpr: "id",
						onValueChanged: function (e) {
							$scope.formInstance.option('formData.division_id', ""); 
							$scope.formInstance.option('formData.designation_id', ""); 
							$scope.divDatasource.filter(["department_id", "=", e.value]);
							$scope.divDatasource.load();
						}
					}
				},{
					dataField: "division_id", label: { text: 'Division' },
					editorType: "dxSelectBox",
					editorOptions: {
						dataSource: $scope.divDatasource,
						displayExpr: "divisionname",
						valueExpr: "id",
						onValueChanged: function (e) {
							$scope.formInstance.option('formData.designation_id', ""); 
							$scope.desDatasource.filter(["division_id", "=", e.value]);
							$scope.desDatasource.load();
						}
					}
				},
				{
					dataField: "designation_id", label: { text: 'Designation' },
					editorType: "dxSelectBox",
					editorOptions: {
						dataSource: $scope.desDatasource,
						displayExpr: "designationname",
						valueExpr: "id",
					}
				},
				{
					dataField: "grade_id",
					label: { text: 'Grade' },
					editorType: "dxSelectBox",
					editorOptions: {
						dataSource: $scope.gradeDatasource,
						displayExpr: "grade",
						valueExpr: "id",
					}		
				},
				{
					dataField: "location_id",
					label: { text: 'Location' },
					editorType: "dxSelectBox", 
					editorOptions: {
						dataSource: $scope.locDatasource,
						displayExpr: "location",
						valueExpr: "id",
					}		
				},
				{dataField:'loginname',label: { text: 'User Login' },validationRules: [{
							type: "required",
							message: "User Login is required"
						}]},
				{
					dataField:'joindate',
					editorType: "dxDateBox",
					editorOptions: {
						type: "date",
						displayFormat: "yyyy-MM-dd",
						focusStateEnabled:true,
						hoverStateEnabled:true,
						width:"100%"
					},
					label: { text: 'Join Date' }
				},
				{
					dataField: "gender",
					label: { text: 'Gender' },
					editorType: "dxSelectBox",
					editorOptions: {
						dataSource: $scope.gender,
						displayExpr: "gender",
						valueExpr: "id",
					}		
				},
				{dataField:'address',label: { text: 'Address' }},
				{
					dataField: "religion_id",
					label: { text: 'Religion' },
					editorType: "dxSelectBox",
					editorOptions: {
						dataSource: $scope.religionDatasource,
						displayExpr: "religion",
						valueExpr: "id",
					}		
				},
			{dataField:'maritalstatus',label: { text: 'Marital Status' }}]},
				{
					itemType: "group",
					caption: "",
					colCount:3,
					items: [{
						itemType: "button",
						horizontalAlignment: "right",
						buttonOptions: {
							text: "Back",
							type: "danger",
							onClick: function(){	
								$scope.dataEmployee();
							},
							useSubmitBehavior: false
						}
					},{
						itemType: "button",
						horizontalAlignment: "left",
						buttonOptions: {
							text: "Save data",
							type: "success",
							visible: ($scope.mode=='view')?false:true,
							useSubmitBehavior: true
						}
					}]
				},
			],			
		};
	});
	$scope.tabs = [
		{ id:1, TabName : "Leave", title: 'Employee History Leave', template: "tab1"   },
		{ id:2, TabName : "DayOFf", title: 'Employee History Day Off', template: "tab2" },
		//{ id:3, TabName : "Travel", title: 'Employee History Travel Request', template: "tab3"  },
		//{ id:4, TabName : "History", title: 'Employee Education History', template: "tab4" },
	];
	$scope.showHistory = ($scope.mode=="add")?false:true;
	$scope.loadPanelVisible = false;
	$scope.dataGrid1 = [];
	$scope.grid1Loaded = false;
	$scope.grid1Component = {};
	$scope.grid1Options = {
		bindingOptions :{
			dataSource: "dataGrid1"
		},
		allowColumnResizing: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
			{dataField:'requestdate',width: 200,fixed: true,fixedPosition: "left"},
			{dataField:'requeststatus',width: 200,fixed: true,fixedPosition: "left",encodeHtml: false ,
			customizeText: function (e) {
					var rDesc = ["<span class='btn btn-default'>Saved as Draft</span>","<span class='btn btn-info'>Waiting Approval</span>","<span class='btn btn-warning'>Require Rework</span>","<span class='btn btn-success'>Approved</span>","<span class='btn btn-danger'>Rejected</span>",""];
					return rDesc[e.value];
				}},
			{dataField:'remarks',width: 200,fixed: true,fixedPosition: "left"}
		],
		onEditorPreparing: function (e) {  
			$scope.grid1Component = e.component;
		},		
    };
	
	$scope.dataGrid2 = [];
	$scope.grid2Component = {};
	$scope.grid2Loaded = false;
	$scope.grid2Options = {
		bindingOptions :{
			dataSource: "dataGrid2"
		},
		allowColumnResizing: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
			{dataField:'requestdate',width: 200,fixed: true,fixedPosition: "left"},
			{dataField:'requeststatus',width: 200,fixed: true,fixedPosition: "left",encodeHtml: false ,
			customizeText: function (e) {
					var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-secondary'>Saved as Draft</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
						return rDesc[e.value];
				}},
			{dataField:'remarks',width: 200,fixed: true,fixedPosition: "left"},
				{
					dataField: "approveddoc",
					caption:"Approval Doc",
					width: 100,
					allowFiltering: false,
					allowSorting: false,
					formItem: { visible: false},
					cellTemplate: function (container, options) {
						if ((options.value!="") && (options.value)){
							$("<div />").dxButton({
								icon: 'download',
								stylingMode: "contained",
								type: "success",
								target : '_blank',
								width: 50,
								onClick: function (e) {
									window.open(options.value, '_blank');
								}
							}).appendTo(container);
						}
					}
				},
		],
		onEditorPreparing: function (e) {  
			$scope.grid2Component = e.component;
		},		
    };
	$scope.selectedTab = 0;
	$scope.tabSettings = {
		dataSource: $scope.tabs,
		animationEnabled:true,
		swipeEnabled : false,
		bindingOptions: {
			selectedIndex: 'selectedTab'
		},
		onSelectionChanged : function (e){
			if (e.component.option("selectedIndex") ==0){
				if (!$scope.grid1Loaded){
					setTimeout(function(){
						$scope.loadPanelVisible = true;
					}, 50);
					CrudService.GetById('leavebyemp',$scope.Employeeid).then(function (resp) {
						$scope.dataGrid1=resp;
					}).finally(function() {
						setTimeout(function(){
							$scope.loadPanelVisible = false;
						}, 50);
						$scope.grid1Loaded = true;
					});
				}
			}else if (e.component.option("selectedIndex") ==1){
				if (!$scope.grid2Loaded){
					setTimeout(function(){
						$scope.loadPanelVisible = true;
					}, 50);
					CrudService.GetById('dayoffbyemp',$scope.Employeeid).then(function (resp) {
						$scope.dataGrid2=resp;
					}).finally(function() {
						setTimeout(function(){
							$scope.loadPanelVisible = false;
						}, 50);
						$scope.grid2Loaded = true;
					});
				}
			}else if (e.component.option("selectedIndex") ==2){

			}else if (e.component.option("selectedIndex") ==3){

			}
		}
	}
	$scope.onFormSubmit = function(e) {
		e.preventDefault();
		var data = $scope.formInstance.option("formData");
		data.joindate = $filter("date")(data.joindate, 'yyyy-MM-dd')
		//console.log($scope.formInstance.option("formData"));
		if ($scope.mode=='edit'){
			CrudService.Update('emp',data.id,data).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}else{
					DevExpress.ui.notify({
						message: "Data has been Updated",
						type: "success",
						displayTime: 2000,
						height: 80,
						position: {
						   my: 'top center', 
						   at: 'center center', 
						   of: window, 
						   offset: '0 0' 
						}
					});
				}
				$scope.dataEmployee();
			}); 
		}else if ($scope.mode=='add'){
			CrudService.Create('emp',data).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.notify(response.message,"error");
				}else{
					DevExpress.ui.notify({
					message: "Data has been Saved",
					type: "success",
					displayTime: 2000,
					height: 80,
					position: {
					   my: 'top center',
					   at: 'center center',
					   of: window,
					   offset: '0 0'
				   }
				});
				}
				$scope.dataEmployee();
			});
		}
    };
}]);
})(app || angular.module("kduApp"));