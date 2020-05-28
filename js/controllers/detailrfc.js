(function (app) {
app.register.controller('detailrfcCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	$scope.data = [];
	if (typeof($scope.mode)=="undefined"){
		$location.path( "/" );
	}
	console.log($scope.mode);
	var d = new Date();
	CrudService.GetById('rfc',$scope.Requestid).then(function(response){
		$scope.data = response;
		if(($scope.mode=='approve')){
			$scope.data.remarks="";
		}
		//if($scope.mode!=="add"){
			CrudService.GetById('rfcdetail',$scope.Requestid).then(function (resp) {
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
		$scope.compDatasource = {
			store: new DevExpress.data.CustomStore({
				key: "companycode",
				loadMode: "raw",
				load: function() {
					return CrudService.GetAll('company').then(function (response) {
						if(response.status=="error"){
							DevExpress.ui.notify(response.message,"error");
						}else{
							return response;
						}
					});
				},
			}),
			filter:['isused','1'],
			sort: "companycode"
		}
		$scope.activityDatasource = {
			store: new DevExpress.data.CustomStore({
				key: "id",
				loadMode: "raw",
				load: function() {
					return CrudService.GetAll('rfcactivity').then(function (response) {
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
		$scope.skrateDatasource = {
			store: new DevExpress.data.CustomStore({
				key: "skno",
				loadMode: "raw",
				load: function() {
					return CrudService.GetAll('skrate').then(function (response) {
						if(response.status=="error"){
							DevExpress.ui.notify(response.message,"error");
						}else{
							return response;
						}
					});
				},

			}),
			
			sort: "skno"
		}
		$scope.rfcType =[{id:0,rfctype:"New"},{id:1,rfctype:"Amendment"}];
		$scope.contractorDatasource = {
			store: new DevExpress.data.CustomStore({
				key: "id",
				loadMode: "raw",
				load: function() {
					return CrudService.GetAll('rfccontractor').then(function (response) {
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
		$scope.AppAction = [{id:1,appaction:"Ask Rework"},{id:2,appaction:"Approve"},{id:3,appaction:"Reject"}];
		$scope.reqStatus = 0;
		$scope.gridSelectedRowKeys =[];
		
		$scope.formItems  =[{	
					itemType: "group",
					caption: "RFC Detail",
					name:" group1",
					colCount : 2,
					colSpan :2,
					items: [
					{dataField:'createddate',editorType: "dxDateBox",label: {text: "Create Date"},editorOptions: {displayFormat:"yyyy-MM-dd",disabled: true}},
					{dataField:'requeststatus',label: {text: "Request Status"},template: function(data, itemElement) {  
						var val = data.editorOptions.value;
						$scope.reqStatus = data.editorOptions.value;
						val=(val>=0)?val:5;
						var rClass = ["mb-2 mr-2 badge badge-pill badge-secondary","mb-2 mr-2 badge badge-pill badge-primary","mb-2 mr-2 badge badge-pill badge-warning","mb-2 mr-2 badge badge-pill badge-success","mb-2 mr-2 badge badge-pill badge-danger","mb-2 mr-2 badge badge-pill badge-alt"];
						var rDesc = ["Saved as Draft","Waiting Approval","Require Rework","Approved","Rejected","Not Saved"];
						$('<span>').appendTo(itemElement).addClass(rClass[val]).text(rDesc[val]);
					}},
					{dataField:'companycode',label:{text:"Company Code"},disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
						editorType: "dxSelectBox",
						editorOptions: {
							
							dataSource: $scope.compDatasource,
							displayExpr: "companycode",
							valueExpr: "companycode",
							onValueChanged: function(e){
								console.log(e);
								criteria = {status:'last',companycode:e.value,rfc_id:$scope.Requestid};
								CrudService.FindData('rfc',criteria).then(function (response){
									$scope.formInstance.updateData('rfcno',  response.rfcno);
									$scope.grid3Component.refresh();
								})
							}
						},validationRules: [{
								type: "required",
								message: "Company is required"
							}]
						
					},
					{dataField:'rfcno',label:{text:"RFC No"},disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,validationRules: [{type: "required", message: "RFC No is required" }]},
					{dataField:'activity_id',label:{text:"Activity"},disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,editorType: "dxDropDownBox",validationRules: [{type: "required", message: "Please select Activity" }],editorOptions: { 
							dataSource:$scope.activityDatasource,  
							valueExpr: 'id',
							displayExpr: 'activitydescr',
							searchEnabled: true,
							contentTemplate: function(e){
								var $dataGrid = $("<div>").dxDataGrid({
									dataSource: e.component.option("dataSource"),
									columns: [{dataField:"activitydescr",width:250}],
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
							},onValueChanged: function(e){
								console.log(e);
								criteria = {status:'chactivity',activity:e.value,rfc_id:$scope.Requestid};
								CrudService.FindData('rfc',criteria).then(function (response){
									$scope.formInstance.updateData('isprojectcapex',  response.iscapex);
									$scope.grid3Component.refresh();
								})
							}
						}},
					{
						dataField:'paymentterm',
						editorType: "dxSelectBox",
						label:{text:"Payment Term"},	
						disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
						editorOptions: { items: ['7 days','14 days','30 days']},
						validationRules: [{type: "required", message: "Please Select Payment Term" }]
					},
					{dataField:'periodstart',editorType: "dxDateBox",disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,validationRules: [{type: "required", message: "Please enter Start Date" }],label: {text: "Period Start"},editorOptions: {displayFormat:"yyyy-MM-dd"}},
					{dataField:'periodend',editorType: "dxDateBox",disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,label: {text: "Period End"},
						editorOptions: {
							displayFormat:"yyyy-MM-dd",
							width: "100%",
						},
						validationRules: [{
							type: "required",
							message: "Please enter End Date"
						}, {
							type: "custom",
							reevalute: !0,
							message: "End date should greater or equal than start date",
							validationCallback: function(e) {
								return e.value >= $scope.data.periodstart
							}
						}]
					},
					{dataField:'ratetype',label:{text:"Rate Type"},editorType: "dxSelectBox",disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,validationRules: [{type: "required", message: "Please Select Rate Type" }],
						editorOptions: { items: ['SK','Non SK'],
							onValueChanged: function(e){
								criteria = {status:'chrate',ratetype:e.value,rfc_id:$scope.Requestid};
								CrudService.FindData('rfc',criteria).then(function (response){
									//console.log(response);
									$scope.grid3Component.refresh();
									//$scope.formInstance.updateData('rfcno',  response.rfcno);
								});
								
								if (e.value=='SK'){
									$scope.formInstance.itemOption('group1.skno', 'visible', true);
									$scope.formInstance.itemOption('group1.skno', 'visibleIndex', 9);
									$scope.formInstance.itemOption('group1.rate', 'visible', false);
									$scope.formInstance.updateData('rate',  "");
								}else{
									$scope.formInstance.itemOption('group1.skno', 'visible', false);
									$scope.formInstance.updateData('skno',  "");
									$scope.formInstance.itemOption('group1.rate', 'visible', true);
									$scope.formInstance.itemOption('group1.rate', 'visibleIndex', 9);
								}
								
							}
						}},
					{dataField:'skno',disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,label:{text:"SK Rate No"},visible:($scope.data.ratetype=='SK')?true:false,editorType: "dxDropDownBox",
							validationRules: [{
									type: "required",
									message: "please input SK No"
								}],
							editorOptions: { 
							dataSource:$scope.skrateDatasource,  
							valueExpr: 'skno',
							displayExpr: 'skno',
							searchEnabled: true,
							contentTemplate: function(e){
								var $dataGrid = $("<div>").dxDataGrid({
									dataSource: e.component.option("dataSource"),
									columns: [{dataField:"skno",caption:"SK No",width:180},{dataField:"skdescription",caption:"Description",width:200}],
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
						}},
					{dataField:'rate',disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,label:{text:"Rate"},visible:($scope.data.ratetype=='SK')?false:true,validationRules: [{ type: "required", message: "please input Rate" }],},
					{dataField:'contractor_id',label:{text:"Contractor Recommend 1"},disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,editorType: "dxDropDownBox",editorOptions: { 
							dataSource:$scope.contractorDatasource,  
							valueExpr: 'id',
							displayExpr: 'contractorname',
							searchEnabled: true,
							contentTemplate: function(e){
								var $dataGrid = $("<div>").dxDataGrid({
									dataSource: e.component.option("dataSource"),
									columns: [{dataField:"contractorname",caption:"Contractor"}],
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
						}},
					{dataField:'contractor_id2',label:{text:"Contractor Recommend 2"},disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,editorType: "dxDropDownBox",editorOptions: { 
							dataSource:$scope.contractorDatasource,  
							valueExpr: 'id',
							displayExpr: 'contractorname',
							searchEnabled: true,
							contentTemplate: function(e){
								var $dataGrid = $("<div>").dxDataGrid({
									dataSource: e.component.option("dataSource"),
									columns: [{dataField:"contractorname",caption:"Contractor"}],
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
						}},
					//{dataField:'paymentterm',label:{text:"Payment Term"},dataType:'number',editorType: "dxNumberBox",},
					
					{label: {
							text: "Department Head"
						},
						dataField:"depthead",
						editorType: "dxDropDownBox",
						disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
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
										e.component.option("value", hasSelection ? keys[0] : null); 
										e.component.close();
									}
								});
								return $dataGrid;
							}
						},
						validationRules: [{
							type: "required",
							message: "Please select your department head"
						}]
					},
					
					{dataField:'isprojectcapex',label:{text:"Project / CAPEX Related Activities"},disabled: true,dataType:"boolean",editorType: "dxCheckBox",
						editorOptions: { 
							onValueChanged: function(e){
								var vis2 =(e.value==1)?true:false;
								$scope.formInstance.itemOption('group1.group2.capexno', 'visible', vis2);
								$scope.formInstance.itemOption('group1.group2.capexno', 'visibleIndex', 2);
								$scope.formInstance.itemOption('group1.group2.capexammount', 'visible', vis2);
								$scope.formInstance.itemOption('group1.group2.capexammount', 'visibleIndex', 3);
								$scope.formInstance.itemOption('group1.group2.capexspent', 'visible', vis2);
								$scope.formInstance.itemOption('group1.group2.capexspent', 'visibleIndex', 4);
								$scope.formInstance.itemOption('group1.group2.capexbalance', 'visible', vis2);
								$scope.formInstance.itemOption('group1.group2.capexbalance', 'visibleIndex', 5);
								$scope.formInstance.itemOption('group1.group2.rfcammount', 'visible', vis2);
								$scope.formInstance.itemOption('group1.group2.rfcammount', 'visibleIndex', 6);
								$scope.formInstance.itemOption('group1.group2.balance', 'visible', vis2);
								$scope.formInstance.itemOption('group1.group2.balance', 'visibleIndex', 7);
								$scope.formInstance.updateData('capexno',  "");
								$scope.formInstance.updateData('capexammount',  "0");
								$scope.formInstance.updateData('capexspent',  "0");
								$scope.formInstance.updateData('capexbalance',  "0");
								$scope.formInstance.updateData('rfcammount',  "0");
								$scope.formInstance.updateData('balance',  "0");
							}
						}
					},
					{dataField:'rfctype',editorType: "dxSelectBox",label:{text:"RFC Type"},disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,validationRules: [{type: "required", message: "Please select RFC Type" }],
						editorOptions: { 
							dataSource:$scope.rfcType,  
							valueExpr: 'id',
							displayExpr: 'rfctype',
							onValueChanged: function(e){
								var vis =(e.value==1)?true:false;
								var vis2 = (e.value==2)?true:false;
								$scope.formInstance.itemOption('group1.group2.oldcontractno', 'visible', vis);
								$scope.formInstance.itemOption('group1.group2.oldcontractno', 'visibleIndex', 0);
								$scope.formInstance.updateData('oldcontractno',  "");
								
							}
						},
					},
					{	
						itemType: "group",
						caption: "",
						name:" group2",
						colSpan:2,
						colCount : 1,
						items: [
							{dataField:'oldcontractno',name:'oldcontractno',disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,label:{text:"Old Contract No"},visible:($scope.data.rfctype==1)?true:false,validationRules: [{
								type: "required",
								message: "please input Old Contract No"
							}]},
							{dataField:'capexno',name:'capexno',disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,label:{text:"Capex No"},visible:($scope.data.isprojectcapex==1)?true:false,validationRules: [{
									type: "required",
									message: "please input Capex No"
								}],tabIndex:0},
							{dataField:'capexammount',name:'capexammount',disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,label:{text:"Capex Ammount"},visible:($scope.data.isprojectcapex==1)?true:false,editorType: "dxNumberBox",
								editorOptions: { 
									format: "Rp #,##0.##",
									onValueChanged: function(e){
										var capexbal=e.value - $scope.data.capexspent;
										var balance=e.value - $scope.data.capexspent - $scope.data.rfcammount;
										$scope.formInstance.updateData('capexbalance',  capexbal);
										$scope.formInstance.updateData('balance',  balance);
										console.log($scope.data.balance);
									}
								},
							},
							{dataField:'capexspent',name:'capexspent',disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,label:{text:"Capex Spent"},visible:($scope.data.isprojectcapex==1)?true:false,dataType:'number',editorType: "dxNumberBox",
								editorOptions: { 
									format: "Rp #,##0.##",
									onValueChanged: function(e){
										var capexbal=$scope.data.capexammount - e.value ;
										var balance= $scope.data.capexammount - e.value  - $scope.data.rfcammount;
										$scope.formInstance.updateData('capexbalance',  capexbal);
										$scope.formInstance.updateData('balance',  balance);
									}
								},
							},
							{dataField:'capexbalance',name:'capexbalance',disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,label:{text:"Capex Balance"},visible:($scope.data.isprojectcapex==1)?true:false,dataType:'number',editorType: "dxNumberBox",editorOptions: { disabled:true,format: "Rp #,##0.##",}},
							{dataField:'rfcammount',name:'rfcammount',disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,label:{text:"RFC Ammount"},visible:($scope.data.isprojectcapex==1)?true:false,dataType:'number',editorType: "dxNumberBox",
								editorOptions: { 
									format: "Rp #,##0.##",
									onValueChanged: function(e){
										var balance= $scope.data.capexammount   - $scope.data.capexspent - e.value;
										$scope.formInstance.updateData('balance',  balance);
									}
								},
							},
							{dataField:'balance',name:'balance',disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,label:{text:"Balance after this RFC"},visible:($scope.data.isprojectcapex==1)?true:false,dataType:'number',editorType: "dxNumberBox",editorOptions: { disabled:true,format: "Rp #,##0.##",}},
						]
					},
							{dataField:'remarks',colSpan:2,editorType:"dxHtmlEditor",visibleIndex:22,editorOptions: {height: 90,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}},
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
								var path = ($scope.mode=='report')?"rfcreport":"rfc";
								$location.path( "/"+path );
							},
							visible: ($scope.mode=='approve') ?false:true,
							useSubmitBehavior: false
						}
					},{
						itemType: "button",
						horizontalAlignment: "right",
						buttonOptions: {
							text: "Back",
							type: "danger",
							onClick: function(){
								$location.path( "/rfcapproval" );					
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
								$scope.data = $scope.formInstance.option("formData");
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
								$scope.updateRFC();
							},
							visible: ($scope.mode=='approve') ?true:false,
							useSubmitBehavior: false
						}
					},{
						itemType: "button",
						horizontalAlignment: "center",
						buttonOptions: {
							text: "Save as Draft",
							type: "default",
							onClick: function(){
								$scope.data = $scope.formInstance.option("formData");
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
								$scope.saveDraft();
							},
							visible: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?false:true,
							useSubmitBehavior: false
						}
					},{
						itemType: "button",
						horizontalAlignment: "left",
						buttonOptions: {
							text: "Submit",
							type: "success",
							onClick: function(){
								$scope.data = $scope.formInstance.option("formData");
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
							},
							visible: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?false:true,
							useSubmitBehavior: true
						}
					}]
				},];
		$scope.detailFormOptions = { 
			onContentReady: function(e){
				$scope.formInstance = e.component;
				
				if ($scope.data.rfctype!==2){
					$scope.formInstance.itemOption('group1.capexammount', 'visible', false);
				}
				
			},
			readOnly : (($scope.mode=='view')||($scope.mode=='report'))?true:false,
			labelLocation : "top",
			minColWidth  :800,
			colCount : 2,	
			formData:$scope.data,
			bindingOptions: {
				'items': 'formItems',
			},			
		};
	});

	var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('rfcdetail',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('rfcdetail',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.rfc_id=$scope.Requestid;
            CrudService.Create('rfcdetail',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid1Component.refresh();
			});
		},
		update: function(key, values) {
            CrudService.Update('rfcdetail',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid1Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('rfcdetail',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid1Component.refresh();
			});
		}
    });
	var myData = new DevExpress.data.DataSource({
		store: myStore
    });
	var myStore5 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('rfcterm',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('rfcterm',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.rfc_id=$scope.Requestid;
            CrudService.Create('rfcterm',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid5Component.refresh();
			});
		},
		update: function(key, values) {
            CrudService.Update('rfcterm',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid5Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('rfcterm',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid5Component.refresh();
			});
		}
    });
	var myData5 = new DevExpress.data.DataSource({
		store: myStore5
    });
	
	var myStore2 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('rfcfile',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('rfcfile',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.upload_date = $filter("date")(values.upload_date, "yyyy-MM-dd HH:mm")
			values.rfc_id=$scope.Requestid;
			values.file_loc =$scope.path;
            CrudService.Create('rfcfile',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		update: function(key, values) {
			if ($scope.path!=""){
				values.upload_date = $filter("date")(values.upload_date, "yyyy-MM-dd HH:mm");
				values.file_loc =$scope.path;
			}
            CrudService.Update('rfcfile',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('rfcfile',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		}
    });
	var myData2 = new DevExpress.data.DataSource({
		store: myStore2
    });
	var myStore3 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('rfcapp',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('rfcapp',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
			values.rfc_id=$scope.Requestid;
            CrudService.Create('rfcapp',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid3Component.refresh();
			});
		},
		update: function(key, values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
            CrudService.Update('rfcapp',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid3Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('rfcapp',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid3Component.refresh();
			});
		}
    });
	var myStore4 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('rfchist',$scope.Requestid);         		
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
	var myData3 = new DevExpress.data.DataSource({
		store: myStore3
    });
	var myData4 = new DevExpress.data.DataSource({
		store: myStore4
    });
	$scope.tabs = [
		{ id:1, TabName : "Scope of Work", title: 'Scope of Work', template: "tab1"   },
		{ id:5, TabName : "Other Term", title: 'Other Term & Condition', template: "tab5"   },
		{ id:2, TabName : "SupportDoc", title: 'Supporting Document', template: "tab2"   },
		{ id:3, TabName : "Approver List", title: 'Approver List', template: "tab3"   },
		{ id:4, TabName : "History Tracking", title: 'History Tracking', template: "tab4"   },
	];
	$scope.showHistory = true;
	$scope.appText = ["No","Yes"];
	$scope.loadPanelVisible = false;
	$scope.grid1Options = {
		dataSource: myData,
		allowColumnResizing: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
			{dataField:'description',width:600,wordWrapEnabled:true,caption:'Description of Work',encodeHtml: false,dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}}
			
		],editing: {
            useIcons:true,
            mode: "cell",
			allowUpdating:(($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
			allowAdding:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
			allowDeleting:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
            //allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
            //allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
            form:{colCount: 1,
            },
        },
		onInitialized:function (e){
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
	$scope.grid5Options = {
		dataSource: myData5,
		allowColumnResizing: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
			{dataField:'term',caption:'Other Term & Conditon',width:600,encodeHtml: false,dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}}
			
		],editing: {
            useIcons:true,
            mode: "cell",
			allowUpdating:(($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
			allowAdding:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
			allowDeleting:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
            //allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
            //allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
            form:{colCount: 1,
            },
        },
		onInitialized:function (e){
			$scope.grid5Component = e.component;
		},
		onEditorPreparing: function (e) {  
			$scope.grid5Component = e.component;
		},
		onToolbarPreparing: function(e) {   
            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        $scope.grid5Component.refresh();
                    }
                }
            });
        },
    };
	$scope.empDataSource = {
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
				criteria = {module:'RFC',mode:$scope.mode};
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
	$scope.adaFile =false;
	$scope.grid2Options = {
		dataSource: myData2,
		allowColumnResizing: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
					{dataField:'file_descr',width:250,caption:"File Description",encodeHtml: false,dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},
					
					{
							dataField: "file_loc",
							caption:"FileLocation",
							width: 100,
							allowFiltering: false,
							allowSorting: false,
							formItem: { visible: false},
							cellTemplate: function (container, options) {
								if (options.value!=""){
									$("<div />").dxButton({
										icon: 'download',
										stylingMode: "contained",
										type: "success",
										target : '_blank',
										width: 50,
										height:25,
										onClick: function (e) {
											window.open(options.value, '_blank');
										}
									}).appendTo(container);
								};
							}
						},{dataField:'FileLoc',caption:"Select File Attachment",visible:false},
						{dataField:'upload_date',width:150,caption: "Upload Date",dataType:"date", format: 'dd/MM/yyyy HH:mm:ss',editorType: "dxDateBox",editorOptions: {displayFormat:"dd/MM/yyyy HH:mm:ss",disabled: true}},
			
			
		],editing: {
            useIcons:true,
            mode: "popup",
			allowUpdating:(($scope.mode=='view')||($scope.mode=='report'))?false:true,
			allowAdding:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?false:true,
			allowDeleting:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?false:true,
            //allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
            //allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
            form:{colCount: 2,
            },
			popup: {
					title: "Edit Attachment",
					showTitle: true,
					position: {
						my: "center",
						at: "center",
						of: window
					},
					toolbarItems: [
					  {
						toolbar: 'bottom',
						location: 'after',
						widget: 'dxButton',
						options: {
							onClick: function(e) {	
								if($scope.path==""){
									DevExpress.ui.notify("Please select file attachment and process your upload before saving the data","error");
									e.cancel = true;
								}else{
									if($scope.adaFile){
										DevExpress.ui.notify("Please finish your upload before saving the data","error");
										e.cancel = true;
									} else{
										$scope.grid2Component.saveEditData();
									}
								}
								
							},
							text: 'Save'
						}
					  },
					  {
						toolbar: 'bottom',
						location: 'after',
						widget: 'dxButton',
						options: {
							onClick: function(e) {
								$scope.grid2Component.cancelEditData();
							},text: 'Cancel'
						}
					  }
					]
				}
        },
		onInitialized:function (e){
			$scope.grid2Component = e.component;
		},
		onInitNewRow: function (e) {
				e.data.upload_date = $filter("date")(d, 'yyyy-MM-dd HH:mm:ss');
			},
		onEditorPreparing: function (e) {
			console.log(e.dataField);
				$scope.path = "";
				if (e.dataField == "upload_date" ) {
					e.editorName = "dxDateBox";
					e.editorOptions.displayFormat= "dd/MM/yyyy  HH:mm:ss";
				} 				
				if (e.dataField == "FileLoc") {
					e.editorName = "dxFileUploader";
					e.editorOptions.uploadMode = "useButtons";
					e.editorOptions.name = "myFile";
					e.editorOptions.accept = "image/*,application/pdf,application/msword,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.wordprocessingml.document";
					e.editorOptions.uploadUrl= "api.php?action=uploadrfcfile&id="+$scope.Requestid;
					e.editorOptions.onUploaded= function (e) {						
						$scope.path = e.request.response;
						console.log(e);
						$scope.adaFile =false;
					}
					e.editorOptions.onUploadError= function(e) {
						$scope.path ="";
						DevExpress.ui.notify(e.request.response,"error");
					}
					e.editorOptions.onValueChanged= function(e){					
						$scope.adaFile = (e.value.length==0)?false:true;
					}
				}  
				if (e.dataField == "file_descr") {
					e.editorName = "dxHtmlEditor";
					e.editorOptions.height = 250;
					e.colSpan = 2;
					e.editorOptions.toolbar = {	items: ["bold", "italic", "underline"]	};
				}    				
			},
			onEditorPrepared: function (e) {
				if (e.dataField == "Remarks") {
					var index = e.row.rowIndex;
					var rm = (typeof(e.value)=="undefined")?"":e.value;
					$scope.gridInstance.cellValue(index, "Remarks", rm.trim()+" ");
				}                 
             },
			onToolbarPreparing: function(e) {
				$scope.dataGrid = e.component;		
				e.toolbarOptions.items.unshift(
				{						
					location: "after",
					widget: "dxButton",
					options: {
						hint: "Refresh Data",
						icon: "refresh",
						onClick: function() {
							$scope.grid2Component.refresh();
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
		columns: [
			{
						dataField: "approver_id",
						caption: "Employee",
						width: 250,
						allowSorting: false,
						lookup: {
							dataSource: $scope.empDataSource,
							valueExpr: "id",
							displayExpr: "fullname" },
						editCellTemplate: "dropDownBoxEditorTemplatex" },
			{dataField:'approvaldate',width:150,format: 'dd/MM/yyyy H:m:s',allowEditing:false, visible: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false},
			{dataField:'approvaltype' ,width:100,allowEditing:false,
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
			allowUpdating: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
			allowAdding:(($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
			allowDeleting:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
            form:{colCount: 1,
            },
        },
		onInitialized:function (e){
			$scope.grid3Component = e.component;
		},
		onToolbarPreparing: function(e) {
            $scope.dataGrid3 = e.component;
    
            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        $scope.dataGrid3.refresh();
                    }
                }
            });
        },
		
    };
	$scope.grid4Options = {
		dataSource: myData4,
		allowColumnResizing: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
			{dataField:'date',width:150,dataType: "date",format: 'dd/MM/yyyy H:m:s'},
			{dataField:'fullname',width:250,caption: "Employee",allowEditing:false,dataType: "string"},
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
			$scope.grid4Component = e.component;
		},
		onToolbarPreparing: function(e) {   
            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        $scope.grid4Component.refresh();
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
	$scope.updateRFC = function(e){
		//console.log($scope.formInstance.option("formData").approvalstatus);
		if($scope.data.approvalstatus==""){
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
		}else if($scope.data.approvalstatus==3){
			var data = $scope.data;
			var date = new Date();
			var d= $filter("date")(date, "yyyy-MM-dd HH:mm")
			data.approvaldate = d;
			data.mode="approve";
			delete data.requestdate;
			delete data.employee_id;
			delete data.requeststatus;
			delete data.superior;
			delete data.depthead;
			CrudService.Update('rfcapp',data.id,data).then(function (response) {
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
					$location.path( "/rfcapproval" );
				}
				
			});
		}else{
			criteria = {status:'approver',rfc_id:$scope.Requestid};
			CrudService.FindData('rfcapp',criteria).then(function (response){
				if(response.jml>0){
					var data = $scope.data;
					var date = new Date();
					var d= $filter("date")(date, "yyyy-MM-dd HH:mm")
					data.approvaldate = d;
					data.mode="approve";
					delete data.requestdate;
					delete data.employee_id;
					delete data.requeststatus;
					delete data.superior;
					delete data.depthead;
					CrudService.Update('rfcapp',data.id,data).then(function (response) {
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
							$location.path( "/rfcapproval" );
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
	$scope.saveDraft = function(e){
		var data = $scope.data;
		data.periodstart = $filter("date")(data.periodstart, "yyyy-MM-dd HH:mm");
		data.periodend = $filter("date")(data.periodend, "yyyy-MM-dd HH:mm");
		CrudService.Update('rfc',data.id,data).then(function (response) {
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
				$location.path( "/rfc" );
			}
			
		});
	}
	$scope.onFormSubmit = function(e) {
		e.preventDefault();
		criteria = {status:'approver',rfc_id:$scope.Requestid};
		CrudService.FindData('rfcapp',criteria).then(function (response){
			if(response.jml>0){
				criteria = {status:'approver',rfc_id:$scope.Requestid};
				CrudService.FindData('rfcdetail',criteria).then(function (response){
					if(response.jml>0){
						var data = $scope.data;
						data.periodstart = $filter("date")(data.periodstart, "yyyy-MM-dd HH:mm");
						data.periodend = $filter("date")(data.periodend, "yyyy-MM-dd HH:mm");
						data.requeststatus = 1;
						delete data.approvalstatus;
						CrudService.Update('rfc',data.id,data).then(function (response) {
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
								$location.path( "/rfc" );
							}
							
						});
					}else{
						DevExpress.ui.notify({
							message: "Please add detail of the request",
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
		})	 	   
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