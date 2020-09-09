(function (app) {
    app.register.controller('mmf30detailCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
        $scope.ds={};
        $scope.test=[];
        $scope.disabled= true;
        $scope.data = [];  
        if (typeof($scope.mode)=="undefined"){
            $location.path( "/" );
        }
	console.log($scope.mode);
	CrudService.GetById('mmf30',$scope.Requestid).then(function(response){
		if(response.status=="autherror"){
			$scope.logout();
		}else{
            $scope.data = response;
            console.log($scope.data);
			if(($scope.mode=='approve')){
				$scope.data.remarks="";
			}

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
            $scope.buyerDataSource = {
                store: new DevExpress.data.CustomStore({
                    key: "employee_id",
                    loadMode: "raw",
                    load: function() {
                        criteria = {module:'MMF30',type:'buyer30',mode:$scope.mode};
                        return CrudService.FindData('appr',criteria);
                    },
                }),
                sort: "employee_id"
            }
            // $scope.buyerDataSource = {
			// 	store: new DevExpress.data.CustomStore({
			// 		key: "id",
			// 		loadMode: "raw",
			// 		load: function() {
			// 			return CrudService.GetAll('emp').then(function (response) {
			// 				if(response.status=="error"){
			// 					DevExpress.ui.notify(response.message,"error");
			// 				}else{
			// 					return response;
			// 				}
			// 			});
			// 		},
			// 	}),
			// 	sort: "id"
			// }
			$scope.deptEmpDataSource = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						criteria = {filter:'bydept',dept:$scope.data.department};
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
            $scope.PRType =[{id:0,prtype:"- Select -"},{id:1,prtype:"Normal PR"},{id:2,prtype:"Urgent PR"},{id:3,prtype:"Minor Purchase"},{id:4,prtype:"Request For Sourcing (RFS) Only"}];
            $scope.ReqType =[{id:0,requisitiontype:"- Select -"},{id:1,requisitiontype:"Stok Item"},{id:2,requisitiontype:"Services"},{id:3,requisitiontype:"Fixed Asset (Requires CAPEX approval)"},{id:4,requisitiontype:"Raw Material"},{id:5,requisitiontype:"Others"}];
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
                    caption: "Request by : "+$scope.data.fullname+" / Dept : "+$scope.data.department,
                    name:"group2",
                    colSpan:2,
                    colCount:2,
                    items: [
                        {
                            dataField:'prtype',
                            editorType: "dxSelectBox",
                            label:{text:"PR Type"},
                            disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                            // validationRules: [{type: "required", message: "Please select RFC Type" }],
                            editorOptions: { 
                                dataSource:$scope.PRType,  
                                valueExpr: 'id',
                                displayExpr: 'prtype',
                                onValueChanged: function(e){
                                    var vis =(e.value==3)?true:false;
                                    $scope.formInstance.itemOption('group2.group4.suppliername', 'visible', vis);
                                    $scope.formInstance.itemOption('group2.group4.suppliername', 'visibleIndex', 0);
                                    $scope.formInstance.updateData('suppliername',  "");
                                    $scope.formInstance.itemOption('group2.group4.supplieraddress', 'visible', vis);
                                    $scope.formInstance.itemOption('group2.group4.supplieraddress', 'visibleIndex', 0);
                                    $scope.formInstance.updateData('supplieraddress',  "");
                                    $scope.formInstance.itemOption('group2.group4.supplieremailfax', 'visible', vis);
                                    $scope.formInstance.itemOption('group2.group4.supplieremailfax', 'visibleIndex', 0);
                                    $scope.formInstance.updateData('supplieremailfax',  "");
                                    $scope.formInstance.itemOption('group2.group4.contractno', 'visible', vis);
                                    $scope.formInstance.itemOption('group2.group4.contractno', 'visibleIndex', 0);
                                    $scope.formInstance.updateData('contractno',  "");
                                    
                                }
                            },
                        },{
                            dataField:'requisitiontype',
                            editorType: "dxSelectBox",
                            label:{text:"Requisition Material"},
                            disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                            // validationRules: [{type: "required", message: "Please select RFC Type" }],
                            editorOptions: { 
                                dataSource:$scope.ReqType,  
                                valueExpr: 'id',
                                displayExpr: 'requisitiontype',
                                onValueChanged: function(e){
                                    var vis =(e.value==5)?true:false;
                                    $scope.formInstance.itemOption('group2.reqisition.requisitionother', 'visible', vis);
                                    $scope.formInstance.itemOption('group2.reqisition.requisitionother', 'visibleIndex', 0);
                                    $scope.formInstance.updateData('requisitionother',  "");
                                    
                                }
                            },
                        },{	
                            itemType: "group",
                            caption: "",
                            name:"un1",
                            colSpan:1,
                            colCount : 1,
                            items: [
                            ]
                            
                        },{	
                            itemType: "group",
                            caption: "",
                            name:"reqisition",
                            colSpan:1,
                            colCount : 1,
                            items: [
                                {
                                    dataField:'requisitionother',
                                    name:'requisitionother',
                                    disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                    // disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                                    label:{
                                        text:"Detail"
                                    },
                                    visible:($scope.data.requisitiontype==5)?true:false,
                                    validationRules: [{
                                        type: "required",
                                        message: "please input Detail"
                                    }]
                                }

                            ]
                            
                        },
                        

                    ]
                    },{	
						itemType: "group",
						name:"group1",
						caption: "",
						colCount : 4,
						colSpan :2,
                        items: 
                        [	{
                                dataField:'prno',
                                label: {
                                    text:"PR No",
                                },
                                disabled: true                        
                            },
                            {
                                dataField:'createddate',
                                editorType: "dxDateBox",
                                label: {text: "Creation Date"},
                                disabled: true,
                                editorOptions: {displayFormat:"dd/MM/yyyy",disabled: false}
                            },
                            {
                                dataField:'costcode',
                                label: {
                                    text:"Cost Code*",
                                },
                                name:'costcode',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'costelement',
                                label: {
                                    text:"Cost Element*",
                                },
                                name:'costelement',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                label: {
                                    text: "Required By"
                                },
                                dataField:"employee_id",
                                editorType: "dxDropDownBox",
                                visible: true,
                                // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
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
                                                if(hasSelection){
                                                    criteria = {status:'pending',username:keys[0],id:$scope.Requestid};
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
                                        criteria = {status:'chemp',employee_id:e.value,mmf30_id:$scope.Requestid};
                                        CrudService.FindData('mmf',criteria).then(function (response){
                                            $scope.grid2Component.refresh();
                                        })
                                    }
                                },
                                validationRules: [{
                                    type: "required",
                                    message: "Please select your direct superior"
                                }]
                            },
                            {
                                dataField:'deliverto',
                                label: {
                                    text:"Deliver To",
                                },
                                name:'deliverto',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'requeststatus',
                                label: {
                                    text: "Request Status"
                                },
                                template: function(data, itemElement) {  
                                    var val = data.editorOptions.value;
                                    $scope.reqStatus = data.editorOptions.value;
                                    val=(val>=0)?val:5;
                                    var rClass = ["mb-2 mr-2 badge badge-pill badge-secondary","mb-2 mr-2 badge badge-pill badge-primary","mb-2 mr-2 badge badge-pill badge-warning","mb-2 mr-2 badge badge-pill badge-success","mb-2 mr-2 badge badge-pill badge-danger","mb-2 mr-2 badge badge-pill badge-alt"];
                                    var rDesc = ["Saved as Draft","Waiting Approval","Require Rework","Approved","Rejected","Not Saved"];
                                    $('<span>').appendTo(itemElement).addClass(rClass[val]).text(rDesc[val]);
                                }
                            },
                            {label: {
                                text: "Department Head"
                            },
                            dataField:"depthead",
                            editorType: "dxDropDownBox",
                            // visible: false,
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
                        },
                        {label: {
                            text: "Buyer"
                        },
                        dataField:"buyer",
                        editorType: "dxDropDownBox",
                        visible: (($scope.data.apprstatuscode==2)) ?true:false,
                        disabled: (($scope.mode=='edit')|| ($scope.mode=='add') || ($scope.mode=='approve')) ?false:true,
                        editorOptions: { 
                            dataSource:$scope.buyerDataSource,  
                            valueExpr: 'employee_id',
                            displayExpr: 'fullname',
                            onValueChanged: function(e){
								console.log(e);
								criteria = {status:'addbuyer',employee_id:e.value,mmf30_id:$scope.Requestid};
								CrudService.FindData('mmf30',criteria).then(function (response){
                                    $scope.grid2Component.refresh();
                                    console.log(e.value + ' & ' + $scope.Requestid);
                                    console.log(response);
								})
							},
                            searchEnabled: true,
                            contentTemplate: function(e){
                                var $dataGrid = $("<div>").dxDataGrid({
                                    dataSource: e.component.option("dataSource"),
                                    columns: [{
                                        dataField:"fullname",width:150
                                    },
                                    // {
                                    //     dataField:"company",width:100
                                    // }, 
                                    {
                                        dataField:"department",width:100
                                    }],
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
                                            console.log(keys);
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
                    },{
                        dataField:'reason',
                        label: {
                            text:"Reason for requisition/purchase",
                        },
                        name:'reason',
                        disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                    },{
                        dataField:'remarksu',
                        label: {
                            text:"Remarks",
                        },
                        name:'remarksu',
                        disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                        validationRules: [{
                            type: "required",
                            message: "Action is required"
                        }]                         
                    },

                        ]
                    },
                    {	
                        itemType: "group",
                        caption: "",
                        name:" group4",
                        colSpan:2,
                        colCount : 4,
                        items: [
                            {
                                dataField:'suppliername',
                                label: {
                                    text:"Supplier Name",
                                },
                                name:'suppliername',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                visible:($scope.data.prtype==3)?true:false
                            },{
                                dataField:'supplieraddress',
                                label: {
                                    text:"Supplier Address",
                                },
                                name:'supplieraddress',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                visible:($scope.data.prtype==3)?true:false
                            },{
                                dataField:'supplieremailfax',
                                label: {
                                    text:"Email / Fax",
                                },
                                name:'supplieremailfax',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                visible:($scope.data.prtype==3)?true:false
                            },{
                                dataField:'contractno',
                                label: {
                                    text:"Contract No**",
                                },
                                name:'contractno',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                visible:($scope.data.prtype==3)?true:false
                            },
                            
                        ]
                    },
                    {	
                        itemType: "group",
                        caption: "",
                        name:" group4",
                        // colSpan:2,
                        colCount : 2,
                        items: [
                            {label:{text:"Comments"},dataField:'proccomments',colSpan:2,editorType:"dxHtmlEditor",visible: (($scope.data.apprstatuscode==3)) ?true:false,editorOptions: {height: 90,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}},
                            {label:{text:"Remarks"},dataField:'remarks',colSpan:2,editorType:"dxHtmlEditor",visible: (($scope.data.apprstatuscode==1)) || (($scope.data.apprstatuscode==2)) ?true:false,editorOptions: {height: 90,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}},
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
						itemType: "group",
						caption: "Action",
						colCount:6,
						items: [{
							itemType: "button",
							horizontalAlignment: "right",
							buttonOptions: {
								text: "Back",
								type: "danger",
								onClick: function(){
									var path = (($scope.mode=='report') || ($scope.mode=='reschedule')) ? "mmf30report" :"mmf30";
									$location.path( "/"+path );
								},
								visible: (($scope.mode=='approve'))  ?false:true,
								useSubmitBehavior: false
							}
                        },
                        {
							itemType: "button",
							horizontalAlignment: "right",
							buttonOptions: {
								text: "Back",
								type: "danger",
								onClick: function(){
									$scope.mmf30Approval();							
								},
								visible: ($scope.mode=='approve') ?true:false,
								useSubmitBehavior: false
							}
                        },
                        {
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
                        },
                        {
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
			return CrudService.GetById('mmf30detail',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('mmf30detail',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.mmf30_id=$scope.Requestid;
            CrudService.Create('mmf30detail',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.gridComponent.refresh();
			});
		},
		update: function(key, values) {
            CrudService.Update('mmf30detail',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.gridComponent.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('mmf30detail',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.gridComponent.refresh();
			});
		}
    });
    var myStore2 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('mmf30app',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('mmf30app',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
			values.mmf30_id=$scope.Requestid;
            CrudService.Create('mmf30app',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		update: function(key, values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
            CrudService.Update('mmf30app',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('mmf30app',key.id).then(function (response) {
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
			return CrudService.GetById('mmf30hist',$scope.Requestid);         		
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
    var myData = new DevExpress.data.DataSource({
		store: myStore
    });
	var myData2 = new DevExpress.data.DataSource({
		store: myStore2
    });
	var myData3 = new DevExpress.data.DataSource({
		store: myStore3
    });

    $scope.tabs = [
		{ id:1, TabName : "Detail", title: 'Detail', template: "tab2"   },
		{ id:2, TabName : "Approver List", title: 'Approver List', template: "tab"   },
		{ id:3, TabName : "History Tracking", title: 'History Tracking', template: "tab1"   },
    ];
    $scope.showHistory = true;
    $scope.loadPanelVisible = false;

    $scope.empDataSource = {
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
				criteria = {module:'MMF30',mode:$scope.mode};
                return CrudService.FindData('appr',criteria);
            },
        }),
        sort: "id"
    }
    $scope.gridOptions = {
		dataSource: myData,
		allowColumnResizing: true,
		wordWrapEnabled: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
			{
                dataField:'materialcode',
                caption: "Material Code",
                dataType: "string",
                editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                }
            },
            {
                dataField:'materialdescr',
                caption: "Description",
                dataType: "string",
                editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                }
            },
            {
                dataField:'partnumber',
                caption: "Part Number",
                dataType: "string",
                editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                }
            },
            {
                dataField:'brandmanufacturer',
                caption: "Brand/Manufacturer",
                dataType: "string",
                editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                }
            },
            {
                dataField:'qty',
                caption: "Qty",
                dataType: "number",
                editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                }
            },
            {
                dataField:'unit',
                caption: "Unit",
                dataType: "string",
                editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                }
            },
            {
                dataField:'currency',
                caption: "Currency",
                dataType: "string",
                editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                }
            },
            {
                dataField:'unitprice',
                caption: "Unit Price",
                dataType: "number",
                editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                }
            },
            {
                dataField:'extendedprice',
                caption: "Extended Price",
                dataType: "number",
                editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                }
            },
            // {
            //     dataField:'remarks',
            //     caption: "Remarks",
            //     dataType: "string",
            //     editorOptions: {
            //         disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
            //     }
            // },
			// {dataField:'dateofbirth',width:100,caption: "Date of Birth",dataType:"date", format: 'dd/MM/yyyy',editorType: "dxDateBox",editorOptions: {displayFormat:"dd/MM/yyyy",max:Date.now(),disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},
			// {dataField:'phonenumber',caption: "Phone Number",dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			// {dataField:'gender',caption: "Gender",lookup: {dataSource: [{key:"Male",val:"Male"},{key:"Female",val:"Female"}],valueExpr: "key", displayExpr: "val" },dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			// {dataField:'hrremarks',caption: "Remarks / Confirmation from HR (Konfirmasi dari HR)",dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') )?false:true}},
		],editing: {
            useIcons:true,
            mode: "cell",
			allowUpdating:(($scope.mode=='view') ||($scope.mode=='report')||($scope.mode=='reschedule'))?(($rootScope.isAdmin)?true:false):true,
			allowAdding:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule'))?(($rootScope.isAdmin)?true:false):true,
			allowDeleting:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule'))?(($rootScope.isAdmin)?true:false):true,
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
    
    $scope.saveDraft = function(e){
		var data = $scope.formInstance.option("formData");
		delete data.fullname;
		delete data.department;
		delete data.approvalstatus;
		delete data.apprstatuscode;
		// data.receivedon = $filter("date")(data.receivedon, "yyyy-MM-dd HH:mm");
		console.log(data);
		CrudService.Update('mmf30',data.id,data).then(function (response) {
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
				$location.path( "/mmf30" );
			}
			
		});
    }

    $scope.updateDayoff = function(e){
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
                var d= $filter("date")(date, "yyyy-MM-dd HH:mm");
                // data.materialreturneddate = $filter("date")(data.materialreturneddate, "yyyy-MM-dd HH:mm");
				data.approvaldate = d;
				data.mode="approve";
				delete data.createddate;
				delete data.employee_id;
				delete data.requeststatus;
				delete data.prtype;
				delete data.requisitiontype;
				delete data.requisitionother;
				delete data.prno;
				delete data.deliverto;
				delete data.costcode;
				delete data.costelement;
				delete data.suppliername;
				delete data.supplieraddress;
				delete data.supplieremailfax;
                delete data.contractno;
                delete data.reason;
                delete data.remarksu;
				
				delete data.depthead;
				// delete data.buyer;
				delete data.apprstatuscode;
				CrudService.Update('mmf30app',data.id,data).then(function (response) {
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
						$location.path( "/mmf30approval" );
					}
					
				});
			}else{
				criteria = {status:'approver',mmf30_id:$scope.Requestid}; 
				CrudService.FindData('mmf30app',criteria).then(function (response){
					if(response.jml>0){
						var data = $scope.formInstance.option("formData");
						var date = new Date();
                        var d= $filter("date")(date, "yyyy-MM-dd HH:mm");
                        // data.materialreturneddate = $filter("date")(data.materialreturneddate, "yyyy-MM-dd HH:mm");
						data.approvaldate = d;
						data.mode="approve";
						delete data.createddate;
                        delete data.employee_id;
                        delete data.requeststatus;
                        delete data.prtype;
                        delete data.requisitiontype;
                        delete data.requisitionother;
                        delete data.prno;
                        delete data.deliverto;
                        delete data.costcode;
                        delete data.costelement;
                        delete data.suppliername;
                        delete data.supplieraddress;
                        delete data.supplieremailfax;
                        delete data.contractno;
                        delete data.reason;
                        delete data.remarksu;
                        // delete data.ReceivedOn;
                        // delete data.proccomments;
                        
                        delete data.depthead;
                        // delete data.buyer;
                        delete data.apprstatuscode;
						CrudService.Update('mmf30app',data.id,data).then(function (response) {
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
								$location.path( "/mmf30approval" );
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
    
    $scope.onFormSubmit = function(e) {
		e.preventDefault();
		criteria = {status:'waiting',username:$scope.formInstance.option("formData").employee_id,id:$scope.Requestid};
		CrudService.FindData('mmf30byemp',criteria).then(function (response){
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
				criteria = {status:'approver',mmf30_id:$scope.Requestid};
				CrudService.FindData('mmf30app',criteria).then(function (response){
					if(response.jml>0){
                        criteria = {status:'approver',mmf30_id:$scope.Requestid};
						CrudService.FindData('mmf30detail',criteria).then(function (response){
							if(response.jml>0){
                                var data = $scope.formInstance.option("formData");
                                data.requeststatus = 1;
                                delete data.fullname;
                                delete data.department;
                                delete data.approvalstatus;
                                delete data.apprstatuscode;
                                data.jobfinishdate= $filter("date")(data.jobfinishdate, "yyyy-MM-dd HH:mm");
                                CrudService.Update('mmf30',data.id,data).then(function (response) {
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
                                        $location.path( "/mmf30" );
                                    }
                                    
                                });
                             }else{
                                DevExpress.ui.notify({
                                    message: "Please add detail (mohon lengkapi detail)",
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
			}
		})
			 	   
    };

    }]);
})(app || angular.module("kduApp"));