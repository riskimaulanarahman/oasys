(function (app) {
app.register.controller('trdetailCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	$scope.data = [];  
    if (typeof($scope.mode)=="undefined"){
		$location.path( "/" );
	}
	console.log($scope.mode);
	CrudService.GetById('tr',$scope.Requestid).then(function(response){
		if(response.status=="autherror"){
			$scope.logout();
		}else{
			$scope.data = response;
			if(($scope.mode=='approve')){
				$scope.data.remarks="";
			}
			//if($scope.mode!=="add"){
				CrudService.GetById('trschedule',$scope.Requestid).then(function (resp) {
					$scope.dataGrid1=resp;
				}).finally(function() {
					$scope.grid1Loaded = true;
				});
			//}
			$scope.allEmpDataSource = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						return CrudService.GetAll('emp').then(function (response) {
							if(response.status=="error"){
								DevExpress.ui.notify(response.message,"error");
							}else{
								return response;
							}
						});
					},
				}),
				sort: "id"
			}
			$scope.deptEmpDataSource = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						criteria = {filter:'bydept4',dept:$scope.data.department};
						return CrudService.FindData('emp',criteria).then(function (response) {
							if(response.status=="error"){
								DevExpress.ui.notify(response.message,"error");
							}else{
								return response;
							}
						});
					}
				}),
				sort: "id"
			}
			$scope.allDeptEmpDataSource = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						criteria = {filter:'bydept2',dept:$scope.data.department};
						return CrudService.FindData('emp',criteria).then(function (response) {
							if(response.status=="error"){
								DevExpress.ui.notify(response.message,"error");
							}else{
								return response;
							}
						});
					}
				}),
				sort: "id"
			}
			$scope.AppAction = [{id:1,appaction:"Ask Rework"},{id:2,appaction:"Approve"},{id:3,appaction:"Reject"}];
			$scope.reqStatus = 0;
			$scope.gridSelectedRowKeys =[];
			
			$scope.detailFormOptions = { 
				onInitialized: function(e) {
					$scope.formInstance = e.component;
				},
				onContentReady:function(e){
					$scope.formInstance = e.component;
				},
				readOnly : (($scope.mode=='view')||($scope.mode=='report'))?true:false,
				labelLocation : "top",
				minColWidth  :800,
				colCount : 2,
				formData:$scope.data,	
				items: [{	
						itemType: "group",
						name:" group1",
						caption: "Request by : "+$scope.data.fullname+" / Dept : "+$scope.data.department,
						colCount : 2,
						colSpan :2,
						items: [
						
						{dataField:'createddate',editorType: "dxDateBox",label: {text: "Creation Date"},editorOptions: {displayFormat:"dd/MM/yyyy",disabled: true}},
						{label: {
								text: "Created By"
							},
							dataField:"createdby",
							editorType: "dxDropDownBox",
							visible: true,
							disabled: true,
							editorOptions: { 
								dataSource:(($scope.mode=='add')||($scope.mode=='edit'))?$scope.deptEmpDataSource:$scope.allDeptEmpDataSource,  
								valueExpr: 'id',
								displayExpr: 'fullname',
								searchEnabled: true,
								contentTemplate: function(e){
									var $dataGrid = $("<div>").dxDataGrid({
										dataSource: e.component.option("dataSource"),
										columns: [{dataField:"fullname",width:100},{dataField:"company",width:50}, {dataField:"department",width:200}],
										height: 265,
										selection: { mode: "single" },
										selectedRowKeys: [e.component.option("value")],
										focusedRowEnabled: true,
										focusedRowKey: e.component.option("value"),
										searchPanel: {
											visible: true,
											width: 265,
											placeholder: "Search..."
										},
										onSelectionChanged: function(selectedItems){
											var keys = selectedItems.selectedRowKeys,
												hasSelection = keys.length;
											e.component.option("value", hasSelection ? keys[0] : null); 
											e.component.close();
										}
									});
									return $dataGrid;
								}
							},
							validationRules: [{
								type: "required",
								message: "Please select your direct superior"
							}]
						},
						{label: {
								text: "Create for Employee"
							},
							dataField:"employee_id",
							editorType: "dxDropDownBox",
							visible: true,
							disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
							editorOptions: { 
								dataSource:(($scope.mode=='add')||($scope.mode=='edit'))?$scope.deptEmpDataSource:$scope.allDeptEmpDataSource,    
								valueExpr: 'id',
								displayExpr: 'fullname',
								searchEnabled: true,
								contentTemplate: function(e){
									var $dataGrid = $("<div>").dxDataGrid({
										dataSource: e.component.option("dataSource"),
										columns: [{dataField:"fullname",width:100},{dataField:"company",width:50}, {dataField:"department",width:200}],
										height: 265,
										selection: { mode: "single" },
										selectedRowKeys: [e.component.option("value")],
										focusedRowEnabled: true,
										focusedRowKey: e.component.option("value"),
										searchPanel: {
											visible: true,
											width: 265,
											placeholder: "Search..."
										},
										onSelectionChanged: function(selectedItems){
											var keys = selectedItems.selectedRowKeys,
												hasSelection = keys.length;
											if(hasSelection){
												criteria = {status:'waiting',username:keys[0],id:$scope.Requestid};
												CrudService.FindData('trbyemp',criteria).then(function (response){
													if(response.jml>0){
														DevExpress.ui.notify({
															message: "Cannot add more request, Selected employee still have unsubmitted draft or pending request",
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
														
														e.component.option("value", keys[0]); 
														e.component.close();
													}
												})
											}
										}
									});
									return $dataGrid;
								},onValueChanged: function(e){
									console.log(e);
									criteria = {status:'chemp',employee_id:e.value,tr_id:$scope.Requestid};
									CrudService.FindData('tr',criteria).then(function (response){
										$scope.grid2Component.refresh();
									})
								}
							},
							validationRules: [{
								type: "required",
								message: "Please select your direct superior"
							}]
						},
						{dataField:'requeststatus',label: {text: "Request Status"},template: function(data, itemElement) {  
							var val = data.editorOptions.value;
							$scope.reqStatus = data.editorOptions.value;
							val=(val>=0)?val:5;
							var rClass = ["mb-2 mr-2 badge badge-pill badge-secondary","mb-2 mr-2 badge badge-pill badge-primary","mb-2 mr-2 badge badge-pill badge-warning","mb-2 mr-2 badge badge-pill badge-success","mb-2 mr-2 badge badge-pill badge-danger","mb-2 mr-2 badge badge-pill badge-alt"];
							var rDesc = ["Saved as Draft","Waiting Approval","Require Rework","Approved","Rejected","Not Saved"];
							$('<span>').appendTo(itemElement).addClass(rClass[val]).text(rDesc[val]);
						}},
						{dataField:'islandtransport',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,label:{text:"",visible:false},dataType:"boolean",editorType: "dxCheckBox",
							editorOptions: { 
								text:"VIA LAND TRANSPORTATION (VIA DARAT)",
								onValueChanged: function(e){
									var vis1 =(e.value==1)?true:false;
									$scope.formInstance.itemOption('group1.group2.ispersonalvehicle', 'visible', vis1);
									$scope.formInstance.itemOption('group1.group2.ispersonalvehicle', 'visibleIndex', 0);
									$scope.formInstance.updateData('ispersonalvehicle',  "");
									$scope.formInstance.itemOption('group1.group2.ispoolcar', 'visible', vis1);
									$scope.formInstance.itemOption('group1.group2.ispoolcar', 'visibleIndex', 1);
									$scope.formInstance.updateData('ispoolcar',  "");
									if (e.value==0){
										$scope.formInstance.itemOption('group1.group2.group4.isdropoffonly', 'visible', false);
										$scope.formInstance.itemOption('group1.group2.group4.isdropoffonly', 'visibleIndex', 1);
										$scope.formInstance.updateData('isdropoffonly',  "");
										$scope.formInstance.itemOption('group1.group2.group4.isuntiljobfinish', 'visible', false);
										$scope.formInstance.itemOption('group1.group2.group4.isuntiljobfinish', 'visibleIndex',2);
										$scope.formInstance.updateData('isuntiljobfinish',  "");
										$scope.formInstance.itemOption('group1.group2.group4.jobfinishdate', 'visible', false);
										$scope.formInstance.itemOption('group1.group2.group4.jobfinishdate', 'visibleIndex',3);
										$scope.formInstance.updateData('jobfinishdate',  null);
										$scope.formInstance.itemOption('group1.group2.otherlandtransportdesc', 'visible', false);
										$scope.formInstance.itemOption('group1.group2.otherlandtransportdesc', 'visibleIndex', 5);
										$scope.formInstance.updateData('otherlandtransportdesc',  "");
									}
									$scope.formInstance.itemOption('group1.group2.isbytrain', 'visible', vis1);
									$scope.formInstance.itemOption('group1.group2.isbytrain', 'visibleIndex', 3);
									$scope.formInstance.updateData('isbytrain',  "");
									$scope.formInstance.itemOption('group1.group2.isother', 'visible', vis1);
									$scope.formInstance.itemOption('group1.group2.isother', 'visibleIndex', 4);
									$scope.formInstance.updateData('isother',  "");
								}}
							},
						{dataField:'isairtransport',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,label:{text:"",visible:false},dataType:"boolean",editorType: "dxCheckBox",
							editorOptions: { 
								text:"VIA AIR TRANSPORTATION (VIA UDARA)",
								onValueChanged: function(e){
									var vis2 =(e.value==1)?true:false;
									$scope.formInstance.itemOption('group1.group3.iscommercialairline', 'visible', vis2);
									$scope.formInstance.itemOption('group1.group3.iscommercialairline', 'visibleIndex', 0);
									$scope.formInstance.updateData('iscommercialairline',  "");
									$scope.formInstance.itemOption('group1.group3.iscompanyaircraft', 'visible', vis2);
									$scope.formInstance.itemOption('group1.group3.iscompanyaircraft', 'visibleIndex', 1);
									$scope.formInstance.updateData('iscompanyaircraft',  "");
									
								}}
							},
						{	
							itemType: "group",
							caption: "",
							name:" group2",
							colSpan:1,
							colCount : 1,
							items: [
								{dataField:'ispersonalvehicle',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,name:'ispersonalvehicle',label:{text:"",visible:false},visible:($scope.data.islandtransport==1)?true:false,dataType:"boolean",editorType: "dxCheckBox",editorOptions: { text:"Personal Vehicle (Dengan Mobil Sendiri - BK)"}},
								{dataField:'ispoolcar',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,name:'ispoolcar',label:{text:"",visible:false},visible:($scope.data.islandtransport==1)?true:false,dataType:"boolean",editorType: "dxCheckBox",
									editorOptions: { 
										text:"Pool Car (Dengan Mobil Pool)",
										onValueChanged: function(e){
											var vis4 =(e.value==1)?true:false;
											$scope.formInstance.itemOption('group1.group2.group4.isdropoffonly', 'visible', vis4);
											$scope.formInstance.itemOption('group1.group2.group4.isdropoffonly', 'visibleIndex', 1);
											$scope.formInstance.updateData('isdropoffonly',  "");
											$scope.formInstance.itemOption('group1.group2.group4.isuntiljobfinish', 'visible', vis4);
											$scope.formInstance.itemOption('group1.group2.group4.isuntiljobfinish', 'visibleIndex',2);
											$scope.formInstance.updateData('isuntiljobfinish',  "");
											if (e.value==0){
												$scope.formInstance.itemOption('group1.group2.group4.jobfinishdate', 'visible', vis4);
												$scope.formInstance.itemOption('group1.group2.group4.jobfinishdate', 'visibleIndex',3);
												$scope.formInstance.updateData('jobfinishdate',  null);
											}
										}
									}
								},
								{	
								itemType: "group",
								caption: "",
								name:" group4",
								colSpan:1,
								colCount : 1,
								items: [
									{dataField:'isdropoffonly',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,name:'isdropoffonly',label:{text:"",visible:false},visible:($scope.data.ispoolcar==1)?true:false,dataType:"boolean",editorType: "dxCheckBox",editorOptions: { text:"Drop Off Only (Drop Saja)"}},
									{dataField:'isuntiljobfinish',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,name:'isuntiljobfinish',label:{text:"",visible:false},visible:($scope.data.ispoolcar==1)?true:false,dataType:"boolean",editorType: "dxCheckBox",
										editorOptions: { 
											text:"Until Job Finished (Sampai Tugas Selesai)",
											onValueChanged: function(e){
												var vis5 =(e.value==1)?true:false;
												$scope.formInstance.itemOption('group1.group2.group4.jobfinishdate', 'visible', vis5);
												$scope.formInstance.itemOption('group1.group2.group4.jobfinishdate', 'visibleIndex', 5);
												$scope.formInstance.updateData('jobfinishdate',  "");
											}
										}
									},
									{dataField:'jobfinishdate',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,name:'jobfinishdate',label:{text:"",visible:false},visible:($scope.data.ispoolcar==1)?true:false,editorType: "dxDateBox",editorOptions: {displayFormat:"dd/MM/yyyy"}},
									]
								},
								{dataField:'isbytrain',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,name:'isbytrain',label:{text:"",visible:false},visible:($scope.data.islandtransport==1)?true:false,dataType:"boolean",editorType: "dxCheckBox",editorOptions: { text:"By Train (Dengan Kereta Api)"}},
								{dataField:'isother',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,name:'isother',label:{text:"",visible:false},visible:($scope.data.islandtransport==1)?true:false,dataType:"boolean",editorType: "dxCheckBox",
									editorOptions: { 
										text:"Other (Please specify):",
										onValueChanged: function(e){
											var vis6 =(e.value==1)?true:false;
											$scope.formInstance.itemOption('group1.group2.otherlandtransportdesc', 'visible', vis6);
											$scope.formInstance.itemOption('group1.group2.otherlandtransportdesc', 'visibleIndex', 5);
											$scope.formInstance.updateData('otherlandtransportdesc',  "");
										}
									}
								},
								{dataField:'otherlandtransportdesc',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,name:'otherlandtransportdesc',dataType:"string",label:{text:"",visible:false},visible:($scope.data.isother==1)?true:false},
									
								]
						},
						{	
							itemType: "group",
							caption: "",
							name:" group3",
							colSpan:1,
							colCount : 1,
							items: [
								{dataField:'iscommercialairline',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,name:'iscommercialairline',label:{text:"",visible:false},visible:($scope.data.isairtransport==1)?true:false,dataType:"boolean",editorType: "dxCheckBox",editorOptions: { text:"Commercial Airline (Pesawat Komersial)"}},
								{dataField:'iscompanyaircraft',disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,name:'iscompanyaircraft',label:{text:"",visible:false},visible:($scope.data.isairtransport==1)?true:false,dataType:"boolean",editorType: "dxCheckBox",editorOptions: { text:"Company Aircraft (Pesawat Perusahaan)"}},
								{label: {
										text: "Department Head"
									},
									dataField:"depthead",
									editorType: "dxDropDownBox",
									visible: true,
									disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
									editorOptions: { 
										dataSource:$scope.allEmpDataSource,  
										valueExpr: 'id',
										displayExpr: 'fullname',
										searchEnabled: true,
										contentTemplate: function(e){
											var $dataGrid = $("<div>").dxDataGrid({
												dataSource: e.component.option("dataSource"),
												columns: [{dataField:"fullname",width:100},{dataField:"company",width:50}, {dataField:"department",width:200}],
												height: 265,
												selection: { mode: "single" },
												selectedRowKeys: [e.component.option("value")],
												focusedRowEnabled: true,
												focusedRowKey: e.component.option("value"),
												searchPanel: {
													visible: true,
													width: 265,
													placeholder: "Search..."
												},
												onSelectionChanged: function(selectedItems){
													var keys = selectedItems.selectedRowKeys,
														hasSelection = keys.length;
													if(hasSelection){
														e.component.option("value", keys[0]); 
														e.component.close();
													}
												}
											});
											return $dataGrid;
										}
									},
									validationRules: [{
										type: "required",
										message: "Please select your department head"
									}]
								},]
						},
						
						
						{	
								itemType: "group",
								caption: "",
								name:" group4",
								colSpan:1,
								colCount : 1,
								items: [
								{dataField:'remarks',colSpan:2,editorType:"dxHtmlEditor",visible: ($scope.mode=='approve') ?true:false,editorOptions: {height: 90,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}},
								{label: {
										text: "Approval Action"
									},
									dataField:"approvalstatus",
									editorType: "dxSelectBox",
									visible: ($scope.mode=='approve') ?true:false,
									editorOptions: { 
										dataSource:$scope.AppAction,  
										valueExpr: 'id',
										displayExpr: 'appaction',
										searchEnabled: true,
										value: ""
									},
									validationRules: [{
										type: "required",
										message: "Action is required"
									}]
								},
						]},
						
						]
					}, 
					{
						itemType: "group"
					},
					{
						itemType: "group",
						caption: "",
						colCount:6,
						items: [{
							itemType: "button",
							horizontalAlignment: "right",
							buttonOptions: {
								text: "Back",
								type: "danger",
								onClick: function(){
									var path = (($scope.mode=='report') || ($scope.mode=='reschedule')) ? "trreport" :"tr";
									$location.path( "/"+path );
								},
								visible: (($scope.mode=='approve'))  ?false:true,
								useSubmitBehavior: false
							}
						},{
							itemType: "button",
							horizontalAlignment: "right",
							buttonOptions: {
								text: "Back",
								type: "danger",
								onClick: function(){
									$scope.trApproval();							
								},
								visible: ($scope.mode=='approve') ?true:false,
								useSubmitBehavior: false
							}
						},{
							itemType: "button",
							horizontalAlignment: "right",
							buttonOptions: {
								text: "Save Update",
								type: "success",
								onClick: function(){
									DevExpress.ui.notify({
										message: "Please wait...!, we are processing your update",
										type: "info",
										displayTime: 1000,
										height: 80,
										position: {
										   my: 'top center', 
										   at: 'center center', 
										   of: window, 
										   offset: '0 0' 
									   }
									});
									$scope.data = $scope.formInstance.option("formData");
									$scope.updateDayoff();
								},
								visible: (($scope.mode=='approve') ||($scope.mode=='reschedule')) ?true:false,
								useSubmitBehavior: false
							}
						},{
							itemType: "button",
							horizontalAlignment: "center",
							buttonOptions: {
								text: "Save as Draft",
								type: "default",
								onClick: function(){
									DevExpress.ui.notify({
										message: "Please wait...!, we are processing your update",
										type: "info",
										displayTime: 1000,
										height: 80,
										position: {
										   my: 'top center', 
										   at: 'center center', 
										   of: window, 
										   offset: '0 0' 
									   }
									});
									$scope.data = $scope.formInstance.option("formData");
									$scope.saveDraft();
									
								},
								visible: (($scope.mode=='approve') ||($scope.mode=='view') ||($scope.mode=='report') ||($scope.mode=='reschedule'))?false:true,
								useSubmitBehavior: false
							}
						},{
							itemType: "button",
							horizontalAlignment: "left",
							buttonOptions: {
								text: "Submit",
								type: "success",
								onClick: function(){
									var result = $scope.formInstance.validate();  
									if (result.isValid) {  
										DevExpress.ui.notify({
											message: "Please wait...!, we are processing your update",
											type: "info",
											displayTime: 3000,
											height: 80,
											position: {
											   my: 'top center', 
											   at: 'center center', 
											   of: window, 
											   offset: '0 0' 
										   }
										});
									} else {
										DevExpress.ui.notify({
											message: "Your form is not complete or has invalid value, please recheck before submit",
											type: "warning",
											displayTime: 3000,
											height: 80,
											position: {
											   my: 'top center', 
											   at: 'center center', 
											   of: window, 
											   offset: '0 0' 
										   }
										});
									}
									$scope.data = $scope.formInstance.option("formData");	
								},
								visible: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule'))?false:true,
								useSubmitBehavior: true
							}
						}]
					},
				],			
			};
		}
	});
	var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('trschedule',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('trschedule',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.departdate = $filter("date")(values.departdate, "yyyy-MM-dd HH:mm")
			values.departtime = $filter("date")(values.departtime, "HH:mm")
			values.arrivingdate = $filter("date")(values.arrivingdate, "yyyy-MM-dd HH:mm")
			values.arrivingtime = $filter("date")(values.arrivingtime, "HH:mm")
			values.tr_id=$scope.Requestid;
            CrudService.Create('trschedule',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.gridComponent.refresh();
			});
		},
		update: function(key, values) {
			values.departdate = $filter("date")(values.departdate, "yyyy-MM-dd HH:mm")
			values.departtime = $filter("date")(values.departtime, "HH:mm")
			values.arrivingdate = $filter("date")(values.arrivingdate, "yyyy-MM-dd HH:mm")
			values.arrivingtime = $filter("date")(values.arrivingtime, "HH:mm")
            CrudService.Update('trschedule',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.gridComponent.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('trschedule',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.gridComponent.refresh();
			});
		}
    });
	var myData = new DevExpress.data.DataSource({
		store: myStore
    });
	var myStore1 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('trticket',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('trticket',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.dateofbirth = $filter("date")(values.dateofbirth, "yyyy-MM-dd HH:mm")
			values.tr_id=$scope.Requestid;
            CrudService.Create('trticket',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid1Component.refresh();
			});
		},
		update: function(key, values) {
			values.dateofbirth = $filter("date")(values.dateofbirth, "yyyy-MM-dd HH:mm")
            CrudService.Update('trticket',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid1Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('trticket',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid1Component.refresh();
			});
		}
    });
	var myData1 = new DevExpress.data.DataSource({
		store: myStore1
    });
	var myStore2 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('trapp',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('trapp',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
			values.tr_id=$scope.Requestid;
            CrudService.Create('trapp',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		update: function(key, values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
            CrudService.Update('trapp',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('trapp',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		}
    });
	var myStore3 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('trhist',$scope.Requestid);         		
		},
		byKey: function(key) {
            //
		},
		insert: function(values) {
			//
		},
		update: function(key, values) {
			//
		},
		remove: function(key) {
			//
		}
    });
	var myData2 = new DevExpress.data.DataSource({
		store: myStore2
    });
	var myData3 = new DevExpress.data.DataSource({
		store: myStore3
    });
	$scope.tabs = [
		{ id:1, TabName : "Schedule", title: 'Travel Schedule (Jadwal Perjalanan)', template: "tab"   },
		{ id:2, TabName : "Ticket", title: 'Detail Ticket', template: "tab1"   },
		{ id:3, TabName : "Approver List", title: 'Approver List', template: "tab2"   },
		{ id:4, TabName : "History Tracking", title: 'History Tracking', template: "tab3"   },
	];
	$scope.showHistory = true;
	$scope.appText = ["No","Yes"];
	$scope.loadPanelVisible = false;
	$scope.region = [{id:'R1',region:"R1"},{id:'R2',region:"R2"}];
	$scope.gridOptions = {
		dataSource: myData,
		allowColumnResizing: true,
		wordWrapEnabled: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
			{dataField:'departdate',validationRules: [{ type: "required" }],width:100,caption: "Depart Date",dataType:"date", format: 'dd/MM/yyyy',editorType: "dxDateBox",editorOptions: {displayFormat:"dd/MM/yyyy",disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},
			{dataField:'departtime',validationRules: [{ type: "required" }],width:50,caption: "Depart Time",dataType:"date",format: 'HH:mm',editorOptions: {displayFormat : "HH:mm", type:'time',disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			{dataField:'departfrom',validationRules: [{ type: "required" }],width:100,caption: "From",dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			{dataField:'arrivingdate',validationRules: [{ type: "required" }],width:100,caption: "Arriving Date",dataType:"date", format: 'dd/MM/yyyy',editorType: "dxDateBox",editorOptions: {displayFormat:"dd/MM/yyyy",disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},
			{dataField:'arrivingtime',validationRules: [{ type: "required" }],width:50,caption: "Arriving Time",format: 'HH:mm',dataType:"date",editorOptions: {displayFormat : "HH:mm", type:'time',disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			{dataField:'arrivingto',validationRules: [{ type: "required" }],width:100,caption: "To",dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			{dataField:'region',validationRules: [{ type: "required" }],width:50,caption: "Region",dataType: "string",lookup: {dataSource: $scope.region,	displayExpr: "region",valueExpr: "id"},editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			{dataField:'reason',width:250,dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},		
		],editing: {
            useIcons:true,
            mode: "cell",
			allowUpdating:(($scope.mode=='view') ||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
			allowAdding:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
			allowDeleting:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
            //allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
            //allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
            form:{colCount: 1,
            },
        },
		onInitialized:function (e){
			$scope.gridComponent = e.component;
		},
		onEditorPreparing: function (e) {  
			$scope.gridComponent = e.component;
		},
		onEditorPreparing: function (e) {  
			$scope.gridComponent = e.component;
			if ((e.dataField == "isapproved") || (e.dataField == "isused")){
                e.editorName = "dxRadioGroup";
                e.editorOptions.layout = "horizontal";
				e.editorOptions.items = $scope.appText;
                //e.editorOptions.switchedOffText = "No";
            }  
		},
		onToolbarPreparing: function(e) {   
            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        $scope.gridComponent.refresh();
                    }
                }
            });
        },
    };
	$scope.grid1Options = {
		dataSource: myData1,
		allowColumnResizing: true,
		wordWrapEnabled: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
			{dataField:'ticketfor',validationRules: [{ type: "required" }],caption: "Ticket For",lookup: {dataSource: [{key:"Employee",val:"Employee"},{key:"Family",val:"Family"},{key:"Guest",val:"Guest"}],valueExpr: "key", displayExpr: "val" },dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			{dataField:'ticketname',validationRules: [{ type: "required" }],caption: "Name",dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			{dataField:'dateofbirth',validationRules: [{ type: "required" }],width:100,caption: "Date of Birth",dataType:"date", format: 'dd/MM/yyyy',editorType: "dxDateBox",editorOptions: {displayFormat:"dd/MM/yyyy",max:Date.now(),disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},
			{dataField:'phonenumber',caption: "Phone Number",dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			{dataField:'gender',validationRules: [{ type: "required" }],caption: "Gender",lookup: {dataSource: [{key:"Male",val:"Male"},{key:"Female",val:"Female"}],valueExpr: "key", displayExpr: "val" },dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			{dataField:'hrremarks',caption: "Remarks / Confirmation from HR (Konfirmasi dari HR)",dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') )?false:true}},
		],editing: {
            useIcons:true,
            mode: "cell",
			allowUpdating:(($scope.mode=='view') ||($scope.mode=='report'))?(($rootScope.isAdmin||($scope.mode=='reschedule'))?true:false):true,
			allowAdding:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin ||($scope.mode=='reschedule'))?true:false):true,
			allowDeleting:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin ||($scope.mode=='reschedule') )?true:false):true,
            form:{colCount: 1,
            },
        },
		onInitialized:function (e){
			$scope.grid1Component = e.component;
		},
		onEditorPreparing: function (e) {  
			$scope.grid1Component = e.component;
		},
		onEditorPreparing: function (e) {  
			$scope.grid1Component = e.component; 
		},
		onToolbarPreparing: function(e) {   
            e.toolbarOptions.items.unshift({					
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        $scope.grid1Component.refresh();
                    }
                }
            });
        },
    };
	$scope.AppType = [{id:0,apptype:"Verification"},{id:1,apptype:"HOD Approval"},{id:2,apptype:"Final Approval"}];

	$scope.empDataSource = {
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
				criteria = {module:'TR',mode:$scope.mode};
                return CrudService.FindData('appr',criteria);
            },
            // byKey:function(key){
                // CrudService.GetById('employee',encodeURIComponent(key)).then(function (emp) {
                    // return emp;
                // });
            // }
        }),
        sort: "id"
    }
	$scope.grid2Options = {
		dataSource: myData2,
		allowColumnResizing: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
			{
						dataField: "approver_id",
						caption: "Employee",
						width: 200,
						allowSorting: false,
						lookup: {
							dataSource: $scope.empDataSource,
							valueExpr: "id",
							displayExpr: "fullname" },
						editCellTemplate: "dropDownBoxEditorTemplatex" },
			{dataField:'approvaldate',width:150,dataType:"date", format:"dd/MM/yyyy",allowEditing:false, visible: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false},
			{dataField:'approvaltype' ,width:200,allowEditing:false,
				lookup: {  
					dataSource:$scope.apptypeDatasource,  
					valueExpr: 'id',
					displayExpr: 'approvaltype'
				}},
			{dataField:'approvalstatus',width:150,allowEditing:false, visible: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false,encodeHtml: false,
			customizeText: function (e) {
					var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
					return rDesc[e.value];
				}},
			//{dataField:'remarks',encodeHtml: false,}
		],
		bindingOptions :{
            "columns[2].lookup.dataSource":"apptypeDatasource"
        },
		editing: {
            useIcons:true,
            mode: "cell",
			allowUpdating: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule'))?(($rootScope.isAdmin)?true:false):true,
			allowAdding:(($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule'))?(($rootScope.isAdmin)?true:false):true,
			allowDeleting:($rootScope.isAdmin)?true:false,
            form:{colCount: 1,
            },
        },
		onInitialized:function (e){
			$scope.grid2Component = e.component;
		},
		onEditorPreparing: function (e) {  
			$scope.grid2Component = e.component;
		},
		onToolbarPreparing: function(e) {
            $scope.dataGrid2 = e.component;
    
            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        $scope.dataGrid2.refresh();
                    }
                }
            });
        },
		
    };
	$scope.grid3Options = {
		dataSource: myData3,
		allowColumnResizing: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		wordWrapEnabled: true,
		columns: [
			{dataField:'date',width:150,dataType: "date",format: 'dd/MM/yyyy HH:mm:ss'},
			{dataField:'fullname',width:200,caption: "Employee",allowEditing:false,dataType: "string"},
			{dataField:'approvaltype',width:150,caption: "Role",allowEditing:false,dataType: "string"},
			{dataField:'actiontype',width:150,caption: "Action",allowEditing:false,encodeHtml: false,
			customizeText: function (e) {
					var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-default'>Created</span>","<span class='mb-2 mr-2 badge badge-pill badge-default'>Save as Draft</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Submitted</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Ask Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
					return rDesc[e.value];
				}},
			{dataField:'remarks',encodeHtml: false}
		],editing: {
            useIcons:true,
			allowUpdating:false,
			allowAdding:false,
			allowDeleting:false,
            form:{colCount: 1,
            },
        },
		onInitialized:function (e){
			$scope.grid3Component = e.component;
		},
		onEditorPreparing: function (e) {  
			$scope.grid3Component = e.component;
		},
		onToolbarPreparing: function(e) {   
            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        $scope.grid3Component.refresh();
                    }
                }
            });
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
	}
	$scope.updateDayoff = function(e){
		if($scope.mode=="reschedule"){
			var data = $scope.formInstance.option("formData");
			var id = data.id;
			delete data;
			criteria = {status:'reschedule',tr_id:$scope.Requestid};
			CrudService.FindData('tr',criteria).then(function (response){
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
					$location.path( "/trreport" );
				}	
			});
		}else{
			//console.log($scope.formInstance.option("formData").approvalstatus);
			if($scope.formInstance.option("formData").approvalstatus==""){
				DevExpress.ui.notify({
					message: "Please select approval action",
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
			}else if($scope.formInstance.option("formData").approvalstatus==3){
				var data = $scope.formInstance.option("formData");
				var date = new Date();
				var d= $filter("date")(date, "yyyy-MM-dd HH:mm")
				data.approvaldate = d;
				data.mode="approve";
				delete data.createddate;
				delete data.employee_id;
				delete data.requeststatus;
				delete data.depthead;
				delete data.fullname;
				delete data.department;
				delete data.createdby;
				delete data.islandtransport;
				delete data.isairtransport;
				delete data.ispersonalvehicle;
				delete data.ispoolcar;
				delete data.isdropoffonly;
				delete data.isuntiljobfinish;
				delete data.jobfinishdate;
				delete data.isbytrain;
				delete data.isother;
				delete data.otherlandtransportdesc;
				delete data.iscommercialairline;
				delete data.iscompanyaircraft;
				delete data.createdby;
				delete data.travelcashadvancepurpose;
				delete data.sppddays;
				delete data.sppdammount;
				delete data.taxidays;
				delete data.taxiammount;
				delete data.accommodationdays;
				delete data.accommodationammount;
				delete data.telephonedays;
				delete data.telephoneammount;
				delete data.otheridrdays;
				delete data.otheridrammount;
				delete data.perdiemammount;
				delete data.otherusdammount;
				delete data.totaladvanceidr;
				delete data.totaladvanceusd;
				delete data.approveddoc;
				CrudService.Update('trapp',data.id,data).then(function (response) {
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
						$location.path( "/trapproval" );
					}
					
				});
			}else{
				criteria = {status:'approver',tr_id:$scope.Requestid};
				CrudService.FindData('trapp',criteria).then(function (response){
					if(response.jml>0){
						var data = $scope.formInstance.option("formData");
						var date = new Date();
						var d= $filter("date")(date, "yyyy-MM-dd HH:mm")
						data.approvaldate = d;
						data.mode="approve";
						delete data.createddate;
						delete data.employee_id;
						delete data.requeststatus;
						delete data.depthead;
						delete data.fullname;
						delete data.department;
						delete data.createdby;
						delete data.islandtransport;
						delete data.isairtransport;
						delete data.ispersonalvehicle;
						delete data.ispoolcar;
						delete data.isdropoffonly;
						delete data.isuntiljobfinish;
						delete data.jobfinishdate;
						delete data.isbytrain;
						delete data.isother;
						delete data.otherlandtransportdesc;
						delete data.iscommercialairline;
						delete data.iscompanyaircraft;
						delete data.createdby;
						delete data.travelcashadvancepurpose;
						delete data.sppddays;
						delete data.sppdammount;
						delete data.taxidays;
						delete data.taxiammount;
						delete data.accommodationdays;
						delete data.accommodationammount;
						delete data.telephonedays;
						delete data.telephoneammount;
						delete data.otheridrdays;
						delete data.otheridrammount;
						delete data.perdiemammount;
						delete data.otherusdammount;
						delete data.totaladvanceidr;
						delete data.totaladvanceusd;
						delete data.approveddoc;
						CrudService.Update('trapp',data.id,data).then(function (response) {
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
								$location.path( "/trapproval" );
							}
							
						});
					}else{
						DevExpress.ui.notify({
							message: "Please add person to do next approval/verification in Approver List tab",
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
				});
			}
		}
	}
	
	$scope.saveDraft = function(e){
		var data = $scope.formInstance.option("formData");
		delete data.fullname;
		delete data.department;
		delete data.approvalstatus;
		data.jobfinishdate= $filter("date")(data.jobfinishdate, "yyyy-MM-dd HH:mm");
		console.log(data);
		CrudService.Update('tr',data.id,data).then(function (response) {
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
				$location.path( "/tr" );
			}
			
		});
	}
	$scope.onFormSubmit = function(e) {
		e.preventDefault();
		criteria = {status:'waiting',username:$scope.formInstance.option("formData").employee_id,id:$scope.Requestid};
		CrudService.FindData('trbyemp',criteria).then(function (response){
			if(response.jml>0){
				DevExpress.ui.notify({
					message: "Cannot add more request, Selected employee still have waiting approval request",
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
				criteria = {status:'approver',tr_id:$scope.Requestid};
				CrudService.FindData('trapp',criteria).then(function (response){
					if(response.jml>0){
						criteria = {status:'approver',tr_id:$scope.Requestid};
						CrudService.FindData('trschedule',criteria).then(function (response){
							if(response.jml>0){
								criteria = {status:'approver',tr_id:$scope.Requestid};
								CrudService.FindData('trticket',criteria).then(function (response){
									if(response.jml>0){
										var data = $scope.formInstance.option("formData");
										data.requeststatus = 1;
										delete data.fullname;
										delete data.department;
										delete data.approvalstatus;
										data.jobfinishdate= $filter("date")(data.jobfinishdate, "yyyy-MM-dd HH:mm");
										CrudService.Update('tr',data.id,data).then(function (response) {
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
												$location.path( "/tr" );
											}
										});
									}else{
										DevExpress.ui.notify({
											message: "Please add ticket detail (mohon lengkapi detail ticket)",
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
								});
							}else{
								DevExpress.ui.notify({
									message: "Please add travel schedule (mohon lengkapi jadwal perjalanan)",
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
						})
					}else{
						DevExpress.ui.notify({
							message: "Please add person to do approval/verification in Approver List tab",
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
				});
			}
		});			 	   
    };
	$scope.initDropDownBoxEditorx = function(data) {
        return {
            dropDownOptions: { width: 500 },
            dataSource: $scope.empDataSource,
            value: data.value,
            valueExpr: "id",
            displayExpr: "fullname",
            contentTemplate: "contentTemplate"
        }
    }
	
	CrudService.GetAll('approvaltype').then(function (resp) {
        $scope.apptypeDatasource=resp;
    });
    $scope.initContentx = function(data, component) {
        return {
            dataSource: $scope.empDataSource,
            remoteOperations: true,
            columns: ["fullname",'approvaltype', "department"],
            hoverStateEnabled: true,
            scrolling: { mode: "virtual" },
            height: 250,
            selection: { mode: "single" },
            selectedRowKeys: [data.value],
            focusedRowEnabled: true,
            focusedRowKey: data.value,
			searchPanel: {
				visible: true,
				width: 240,
				placeholder: "Search..."
			},
				onSelectionChanged: function(selectionChangedArgs) {
                component.option("value", selectionChangedArgs.selectedRowKeys[0]);
                data.setValue(selectionChangedArgs.selectedRowKeys[0]);
                if(selectionChangedArgs.selectedRowKeys.length > 0) {
                    component.close();
                }
            }
        }
    }
	
}]);
})(app || angular.module("kduApp"));