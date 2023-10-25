(function (app) {
app.register.controller('detailcontractCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	$scope.data = [];
	if (typeof($scope.mode)=="undefined"){
		$location.path( "/" );
	}
	console.log($scope.mode);
	var d = new Date();
	CrudService.GetById('contract',$scope.Requestid).then(function(response){
		$scope.data = response;
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
        $scope.rfcDatasource = {
			store: new DevExpress.data.CustomStore({
				key: "id",
				loadMode: "raw",
				load: function() {
                    criteria = {status:'rfcactive',contract_id:$scope.Requestid};
					return CrudService.FindData('contract',criteria).then(function (response) {
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
		$scope.oldContractDatasource = {
			store: new DevExpress.data.CustomStore({
				key: "id",
				loadMode: "raw",
				load: function() {
                    criteria = {status:'new',contract_id:$scope.Requestid};
					return CrudService.FindData('contract',criteria).then(function (response) {
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
		$scope.formItems  =[{	
					itemType: "group",
					caption: "Register Contract",
					name:" group1",
					colCount : 2,
					colSpan :2,
					items: [
					{dataField:'createddate',editorType: "dxDateBox",label: {text: "Create Date"},editorOptions: {displayFormat:"yyyy-MM-dd",readOnly: true},visible:false},
					{dataField:'rfcuser',label: {text: "User"},editorOptions: {readOnly: true}},
					{dataField:'contractstatus',label: {text: "Contract Status"},template: function(data, itemElement) {  
						var val = data.editorOptions.value;
						$scope.reqStatus = data.editorOptions.value;
						val=(val>=0)?val:5;
						var rClass = ["mb-2 mr-2 badge badge-pill badge-success","mb-2 mr-2 badge badge-pill badge-warning","mb-2 mr-2 badge badge-pill badge-danger","mb-2 mr-2 badge badge-pill badge-default","mb-2 mr-2 badge badge-pill badge-primary","mb-2 mr-2 badge badge-pill badge-alt"];
						var rDesc = ["Active","Nearly Expired","Expired","Not Active"];
						$('<div id="status">').appendTo(itemElement).addClass(rClass[val]).text(rDesc[val]);
					}},
					{dataField:'contractno',label:{text:"Contract No"},validationRules: [{type: "required", message: "Contract is required" }],editorOptions:{readOnly: (($scope.mode=='view')||($scope.mode=='report'))?true:false,}},
					{dataField:'rfc_id',label:{text:"RFC No"},editorType: "dxDropDownBox",validationRules: [{type: "required", message: "Please select RFC" }],editorOptions: { 
                        dataSource:$scope.rfcDatasource,  
                        valueExpr: 'id',
                        displayExpr: 'rfcno',
                        readOnly: (($scope.mode=='view')||($scope.mode=='report'))?true:false,
                        searchEnabled: true,
                        contentTemplate: function(e){
                            var $dataGrid = $("<div>").dxDataGrid({
                                dataSource: e.component.option("dataSource"),
                                columns: [{dataField:"rfcno",width:180,caption:"RFC No"},{dataField:"periodstart",caption:"Start",width:80,dataType:"date", format:"dd/MM/yyyy"},{dataField:"periodend",caption:"End",width:80,dataType:"date", format:"dd/MM/yyyy"},{dataField:"remarks",encodeHtml: false }],
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
                                            e.component.option("value", hasSelection ? keys[0] : null); 
                                            e.component.close();
                                        }
                                }
                            });
                            return $dataGrid;
                        },onValueChanged: function(e){
                            criteria = {status:'chrfc',rfc_id:e.value,contract_id:$scope.Requestid};
                            CrudService.FindData('contract',criteria).then(function (response){
                                $scope.formInstance.updateData('periodstart',  response.periodstart);
                                $scope.formInstance.updateData('periodend',  response.periodend);
                                $scope.formInstance.updateData('contractor_id',  response.contractor_id);
                                $scope.formInstance.updateData('description',  response.remarks);
								var diff = Math.round((new Date(response.periodend) - new Date()) / (1000 * 60 * 60 * 24));
								var stts = (diff<=0)?2:((diff<90)?1:0)
								var rClass = ["mb-2 mr-2 badge badge-pill badge-success","mb-2 mr-2 badge badge-pill badge-warning","mb-2 mr-2 badge badge-pill badge-danger","mb-2 mr-2 badge badge-pill badge-default","mb-2 mr-2 badge badge-pill badge-primary","mb-2 mr-2 badge badge-pill badge-alt"];
								var rDesc = ["Active","Nearly Expired","Expired","Not Active"];
								rClass.forEach(val=>{
									$("#status").removeClass(val);
								})
								$("#status").text(rDesc[stts]);
								$("#status").addClass(rClass[stts]);
								$scope.formInstance.updateData('rfcuser', response.rfcuser);
								$scope.formInstance.updateData('activitydescr', response.activitydescr);
								$scope.formInstance.updateData('companycode', response.companycode);
								$scope.formInstance.updateData('ratetype', response.ratetype);
								$scope.formInstance.updateData('skno', response.skno);
								$scope.formInstance.updateData('description',  response.remarks);
                            })
                        }
                    }},
                    {dataField:'contractor_id',label:{text:"Contractor"},editorType: "dxDropDownBox",validationRules: [{type: "required", message: "Please select Contractor" }],editorOptions: { 
                        readOnly: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                        dataSource:$scope.contractorDatasource,  
                        valueExpr: 'id',
                        displayExpr: 'contractorname',
                        showClearButton:false,
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
                                        if(hasSelection){
                                            e.component.option("value", hasSelection ? keys[0] : null); 
                                            e.component.close();
                                        }
                                }
                            });
                            return $dataGrid;
                        }
                    }},
					{dataField:'oldcontractno',label:{text:"Old Contract"},editorType: "dxDropDownBox",editorOptions: { 
						readOnly: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
						dataSource:$scope.oldContractDatasource,  
						valueExpr: 'id',
						displayExpr: 'contractno',
						showClearButton:true,
						searchEnabled: true,
						contentTemplate: function(e){
							var $dataGrid = $("<div>").dxDataGrid({
								dataSource: e.component.option("dataSource"),
								columns: [{dataField:"contractno",caption:"Contract No",width:200},{dataField:"periodstart",width:80,caption:"Start",dataType:"date", format:"dd/MM/yyyy"},{dataField:"periodend",width:80,caption:"End",dataType:"date", format:"dd/MM/yyyy"},{dataField:"description",encodeHtml: false }],
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
											e.component.option("value", hasSelection ? keys[0] : null); 
											e.component.close();
										}
								}
							});
							return $dataGrid;
						},
						onValueChanged:function(e){
							if (e.value == null){
								$scope.formInstance.updateData('oldcontractno', '');
							}
						}
					}},
					{dataField:'periodstart',editorType: "dxDateBox",validationRules: [{type: "required", message: "Please enter Start Date" }],label: {text: "Period Start"},editorOptions: {displayFormat:"dd/MM/yyyy",readOnly: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,}},
					{dataField:'periodend',editorType: "dxDateBox",label: {text: "Period End"},
						editorOptions: {
							displayFormat:"dd/MM/yyyy",
							width: "100%",
							readOnly: (($scope.mode=='view')||($scope.mode=='report'))?true:false,
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
					]
				},
				{dataField:'companycode',label: {text: "Company"},editorOptions: {readOnly: true}},
				{dataField:'activitydescr',label: {text: "Activity"},editorOptions: {readOnly: true}},
				{dataField:'ratetype',label: {text: "Rate Type"},editorOptions: {readOnly: true}},
				{dataField:'skno',label: {text: "SK No"},editorOptions: {readOnly: true}},
				{
					dataField:'description',colSpan:2,editorType:"dxHtmlEditor",editorOptions: {readOnly: ($scope.mode=='view')?true:false,height: 90,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}
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
							icon: 'back',
							type: "default",
							onClick: function(){
								$location.path( "/contract" );
							},
							visible: true,
							useSubmitBehavior: false
						}
					},{
						itemType: "button",
						horizontalAlignment: "left",
						buttonOptions: {
							text: "Save Data",
							icon: 'save',
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
					},{
						itemType: "button",
						horizontalAlignment: "right",
						buttonOptions: {
							text: "Terminate Contract",
							icon: 'close',
							type: "danger",
							onClick: function(){
								$scope.data = $scope.formInstance.option("formData");
								var result = DevExpress.ui.dialog.confirm("Are you sure you want to terminate the contract?", "Close Contract");
								result.done(function (dialogResult) {
									if (dialogResult){
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
										$scope.closeContract();
									}
								});
								
							},
							visible: ($scope.mode=='edit') ?true:false,
							useSubmitBehavior: false
						}
					},]
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
	var myStore2 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('contractfile',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('contractfile',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.upload_date = $filter("date")(values.upload_date, "yyyy-MM-dd HH:mm")
			values.contract_id=$scope.Requestid;
			values.file_loc =$scope.path;
            CrudService.Create('contractfile',values).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}
				$scope.gridComponent.refresh();
			});
		},
		update: function(key, values) {
			if ($scope.path!=""){
				values.upload_date = $filter("date")(values.upload_date, "yyyy-MM-dd HH:mm");
				values.file_loc =$scope.path;
			}
            CrudService.Update('contractfile',key.id,values).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}
				$scope.gridComponent.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('contractfile',key.id).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}
				$scope.gridComponent.refresh();
			});
		}
    });
	var myData2 = new DevExpress.data.DataSource({
		store: myStore2
    });
	$scope.tabs = [
		{ id:1, TabName : "Contract Documents", title: 'Contract Documents', template: "tab1"   },
	];
	$scope.showDetail = true;
	$scope.loadPanelVisible = false;
	
	$scope.adaFile =false;
	$scope.gridOptions = {
		dataSource: myData2,
		allowColumnResizing: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
					{dataField:'file_descr',width:250,caption:"File Description",encodeHtml: false,dataType: "string",editorOptions: {disabled:(($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?false:true):false}},
					
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
			allowUpdating:( ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
			allowAdding:(($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
			allowDeleting:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
            //allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
            //allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
            form:{colCount: 2,
            },
			popup: {
					title: "Edit File Contract",
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
									DevExpress.ui.dialog.alert("Please select file attachment and process your upload before saving the data","Error");
									e.cancel = true;
								}else{
									if($scope.adaFile){
										DevExpress.ui.dialog.alert("Please finish your upload before saving the data","Error");
										e.cancel = true;
									} else{
										$scope.gridComponent.saveEditData();
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
								$scope.gridComponent.cancelEditData();
							},text: 'Cancel'
						}
					  }
					]
				}
        },
		onInitialized:function (e){
			$scope.gridComponent = e.component;
		},
		onInitNewRow: function (e) {
				e.data.upload_date = $filter("date")(d, 'yyyy-MM-dd HH:mm:ss');
			},
		onEditorPreparing: function (e) {
			$scope.path = "";
			if (e.dataField == "upload_date" ) {
				e.editorName = "dxDateBox";
				e.editorOptions.displayFormat= "dd/MM/yyyy  HH:mm:ss";
			} 				
			if (e.dataField == "FileLoc") {
				e.editorName = "dxFileUploader";
				e.editorOptions.uploadMode = "useButtons";
				e.editorOptions.name = "myFile";
				e.editorOptions.accept = "image/*,application/pdf,application/vnd.ms-outlook,application/msword,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.wordprocessingml.document";
				e.editorOptions.uploadUrl= "api.php?action=uploadcontractfile&id="+$scope.Requestid;
				e.editorOptions.onUploaded= function (e) {						
					$scope.path = e.request.response;
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
			if (e.dataField == "file_descr") {
				var index = e.row.rowIndex;
				var rm = (typeof(e.value)=="undefined")?"":e.value;
				$scope.gridComponent.cellValue(index, "file_descr", rm.trim()+" ");
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
						$scope.gridComponent.refresh();
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
	$scope.closeContract = function(e){
		var data = $scope.formInstance.option("formData");
		CrudService.Update('closecontract',data.id,data).then(function (response) {
			if(response.status=="error"){
				DevExpress.ui.dialog.alert(response.message,"Error");
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
				$location.path( "/contract" );
			}
			
		});
	}
	$scope.onFormSubmit = function(e) {
		e.preventDefault();
        var data = $scope.formInstance.option("formData");
        data.periodstart = $filter("date")(data.periodstart, "yyyy-MM-dd HH:mm");
        data.periodend = $filter("date")(data.periodend, "yyyy-MM-dd HH:mm");
		CrudService.Update('contractupdate',data.id,data).then(function (response) {
            if(response.status=="error"){
                DevExpress.ui.dialog.alert(response.message,"Error");
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
                $location.path( "/contract" );
            }
            
        });	 	   
    };
	
}]);
})(app || angular.module("kduApp"));