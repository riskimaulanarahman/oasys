(function (app) {
    app.register.controller('mmfdetailCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
        $scope.ds={};
        $scope.test=[];
        $scope.disabled= true;
        $scope.data = [];  
        if (typeof($scope.mode)=="undefined"){
            $location.path( "/" );
        }
    console.log($scope.mode);
    var d = new Date();
	CrudService.GetById('mmf',$scope.Requestid).then(function(response){
		if(response.status=="autherror"){
			$scope.logout();
		}else{
			$scope.data = response;
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
                        criteria = {module:'MMF',type:'buyer',mode:$scope.mode};
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
			
			console.log($scope.data);
            $scope.RequiredType =[{id:0,requiredtype:"- Select -"},{id:1,requiredtype:"Repair"},{id:2,requiredtype:"Servicing"},{id:3,requiredtype:"Calibration"},{id:4,requiredtype:"Others"}];
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
						name:"group1",
						caption: "Request by : "+$scope.data.fullname+" / Dept : "+$scope.data.department,
						colCount : 3,
						colSpan :2,
                        items: 
                        [	
                            {
                                dataField:'createddate',
                                editorType: "dxDateBox",
                                label: {text: "Creation Date"},
                                disabled: true,
                                editorOptions: {displayFormat:"dd/MM/yyyy",disabled: false}
                            },
                            {
                                label: {
                                    text: "Requested By"
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
                                        criteria = {status:'chemp',employee_id:e.value,mmf28_id:$scope.Requestid};
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
                                dataField:'telpno',
                                label: {
                                    text:"Telp No",
                                },
                                name:'telpno',
                                dataType:"string",
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            // {
                            //     dataField:'requeststatus',
                            //     label: {
                            //         text: "Request Status"
                            //     },
                            //     template: function(data, itemElement) {  
                            //         var val = data.editorOptions.value;
                            //         $scope.reqStatus = data.editorOptions.value;
                            //         val=(val>=0)?val:5;
                            //         var rClass = ["mb-2 mr-2 badge badge-pill badge-secondary","mb-2 mr-2 badge badge-pill badge-primary","mb-2 mr-2 badge badge-pill badge-warning","mb-2 mr-2 badge badge-pill badge-success","mb-2 mr-2 badge badge-pill badge-danger","mb-2 mr-2 badge badge-pill badge-alt"];
                            //         var rDesc = ["Saved as Draft","Waiting Approval","Require Rework","Approved","Rejected","Not Saved"];
                            //         $('<span>').appendTo(itemElement).addClass(rClass[val]).text(rDesc[val]);
                            //     }
                            // },
                            {
                                dataField:'mmfnumber',
                                label: {
                                    text:"MMF No",
                                },
                                disabled: true                            
                            },
                            {
                                dataField:'wonumber',
                                label: {
                                    text:"Work Order No",
                                },
                                name:'wonumber',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                               
                            },
                            {
                                dataField:'chargecode',
                                label: {
                                    text:"Charge Code",
                                },
								name:'chargecode',
								validationRules: [{
									type: "required",
									message: "Action is required"
								}],
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                       
                            },
                            {
                                dataField:'materialdispatch',
                                label: {
                                    text:"Material Dispatch No",
								},
								validationRules: [{
									type: "required",
									message: "Action is required"
								}],
                                name:'materialdispatch',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'requireddate',
                                editorType: "dxDateBox",
                                label: {text: "Required By (Date)"},
								disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
								validationRules: [{
									type: "required",
									message: "Action is required"
								}],
                                editorOptions: {displayFormat:"dd/MM/yyyy",disabled: false}
                            },
                            {
                                dataField:'materialcode',
                                label: {
                                    text:"Material Code",
                                },
                                name:'materialcode',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'materialdescr',
                                label: {
                                    text:"Material Description",
                                },
								name:'materialdescr',
								validationRules: [{
									type: "required",
									message: "Action is required"
								}],
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'symptomps',
                                label: {
                                    text:"Symptoms (Problem)",
                                },
                                name:'symptomps',
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
                        // {label: {
                        //     text: "Buyer"
                        // },
                        // dataField:"buyer",
                        // editorType: "dxDropDownBox",
                        // visible: (($scope.data.apprstatuscode==2)) ?true:false,
                        // disabled: (($scope.mode=='edit')|| ($scope.mode=='add') || ($scope.mode=='approve')) ?false:true,
                        // editorOptions: { 
                        //     dataSource:$scope.buyerDataSource,  
                        //     valueExpr: 'employee_id',
                        //     displayExpr: 'fullname',
                        //     onValueChanged: function(e){
						// 		console.log(e);
						// 		criteria = {status:'addbuyer',employee_id:e.value,mmf28_id:$scope.Requestid};
						// 		CrudService.FindData('mmf',criteria).then(function (response){
                        //             $scope.grid2Component.refresh();
                        //             console.log(e.value + ' & ' + $scope.Requestid);
                        //             console.log(response);
						// 		})
						// 	},
                        //     searchEnabled: true,
                        //     contentTemplate: function(e){
                        //         var $dataGrid = $("<div>").dxDataGrid({
                        //             dataSource: e.component.option("dataSource"),
                        //             columns: [{
                        //                 dataField:"fullname",width:150
                        //             },
                        //             // {
                        //             //     dataField:"company",width:100
                        //             // }, 
                        //             {
                        //                 dataField:"department",width:100
                        //             }],
                        //             height: 265,
                        //             selection: { mode: "single" },
                        //             selectedRowKeys: [e.component.option("value")],
                        //             focusedRowEnabled: true,
                        //             focusedRowKey: e.component.option("value"),
                        //             searchPanel: {
                        //                 visible: true,
                        //                 width: 265,
                        //                 placeholder: "Search..."
                        //             },
                        //             onSelectionChanged: function(selectedItems){
                        //                 var keys = selectedItems.selectedRowKeys,
                        //                     hasSelection = keys.length;
                        //                     console.log(keys);
                        //                 if(hasSelection){
                        //                     e.component.option("value", keys[0]); 
                        //                     e.component.close();
                        //                 }
                        //             }
                        //         });
                        //         return $dataGrid;
                        //     }
                        // },
                        // validationRules: [{
                        //     type: "required",
                        //     message: "Please select your Buyer"
                        // }]
                    // }

                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        name:"group2",
                        colSpan:2,
						colCount:2,
						items: [
                            {
                                dataField:'requiredtype',
                                editorType: "dxSelectBox",
                                label:{text:"Required"},
                                disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
								// validationRules: [{type: "required", message: "Please select RFC Type" }],
								validationRules: [{
									type: "required",
									message: "Action is required"
								}],
                                editorOptions: { 
                                    dataSource:$scope.RequiredType,  
                                    valueExpr: 'id',
                                    displayExpr: 'requiredtype',
                                    onValueChanged: function(e){
                                        var vis =(e.value==4)?true:false;
                                        $scope.formInstance.itemOption('group2.group7.requiredother', 'visible', vis);
                                        $scope.formInstance.itemOption('group2.group7.requiredother', 'visibleIndex', 0);
                                        $scope.formInstance.updateData('requiredother',  "");
                                        
                                    }
                                },
                            },{
                                dataField:'instruction',
                                label: {
                                    text:"Instruction",
                                },
                                name:'instruction',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            

                        ]
					},
                    {
						itemType: "group",
                        caption: "",
                        name:"group7",
                        colSpan:2,
						colCount:2,
						items: [
                            {
                                dataField:'requiredother',
                                name:'requiredother',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                // disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                                label:{
                                    text:"Pls Specify"
                                },
                                visible:($scope.data.requiredtype==4)?true:false,
                                validationRules: [{
                                    type: "required",
                                    message: "please input Specify"
                                }]
                            },
                            

                        ]
                    },
                    {	
						itemType: "group",
						caption: "Chemical Content",
						name:"group3",
						colSpan:1,
						colCount : 1,
						items: [
							{
                                dataField:'ishazardouschemical',
                                label:{text:"",visible:false},
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                dataType:"boolean",
                                editorType: "dxCheckBox",
                                editorOptions: { 
                                    text:"Hazardous Chemical",
                                    dataSource:$scope.ishazardouschemical,  
                                    valueExpr: 'id',
                                    displayExpr: 'ishazardouschemical',
                                    onValueChanged: function(e){
                                        var vis =(e.value==1)?true:false;
                                        $scope.formInstance.itemOption('group3.group4.hazchemicalname', 'visible', vis);
                                        $scope.formInstance.itemOption('group3.group4.hazchemicalname', 'visibleIndex', 0);
                                        $scope.formInstance.updateData('hazchemicalname',  "");
                                        $scope.formInstance.itemOption('group3.group4.isdecontaminated', 'visible', vis);
                                        $scope.formInstance.itemOption('group3.group4.isdecontaminated', 'visibleIndex', 0);
                                        $scope.formInstance.updateData('isdecontaminated',  "");
                                        $scope.formInstance.itemOption('group3.group4.isnotcontaminated', 'visible', vis);
                                        $scope.formInstance.itemOption('group3.group4.isnotcontaminated', 'visibleIndex', 0);
                                        $scope.formInstance.updateData('isnotcontaminated',  "");
                                        $scope.formInstance.itemOption('group3.group4.isnonhazardous', 'visible', vis);
                                        $scope.formInstance.itemOption('group3.group4.isnonhazardous', 'visibleIndex', 0);
                                        $scope.formInstance.updateData('isnonhazardous',  "");
                                        $scope.formInstance.itemOption('group3.group4.isnonchemical', 'visible', vis);
                                        $scope.formInstance.itemOption('group3.group4.isnonchemical', 'visibleIndex', 0);
                                        $scope.formInstance.updateData('isnonchemical',  "");
                                        if (e.value==0){
                                            $scope.formInstance.itemOption('group3.group4.group5.notcontaminatedreason', 'visible', vis);
                                            $scope.formInstance.itemOption('group3.group4.group5.notcontaminatedreason', 'visibleIndex', 0);
                                            $scope.formInstance.updateData('notcontaminatedreason',  "");
                                            $scope.formInstance.itemOption('group3.group4.group5.nonhazchemicalname', 'visible', vis);
                                            $scope.formInstance.itemOption('group3.group4.group5.nonhazchemicalname', 'visibleIndex', 0);
                                            $scope.formInstance.updateData('nonhazchemicalname',  "");
                                        }
                                        
                                        
                                    }
                                }
                            },{	
								itemType: "group",
								caption: "",
								name:"group4",
								colSpan:1,
								colCount : 1,
								items: [
                                    {
                                        dataField:'hazchemicalname',
                                        label: {
                                            text:"Chemcial Name",
                                        },
                                        visible:($scope.data.ishazardouschemical==1)?true:false,
                                        name:'hazchemicalname',
                                        disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                        validationRules: [{
                                            type: "required",
                                            message: "Please select your department head"
                                        }]                            
                                    }

                                ]
                                
							},{	
								itemType: "group",
								caption: "",
								name:"group4",
								colSpan:1,
								colCount : 1,
								items: [
                                    {
                                        dataField:'isdecontaminated',
                                        label:{text:"",visible:false},
                                        disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                        visible:($scope.data.ishazardouschemical==1)?true:false,
                                        dataType:"boolean",
                                        editorType: "dxCheckBox",
                                        editorOptions: { 
                                            text:"Decontaminated",
                                        }
                                    },{
                                        dataField:'isnotcontaminated',
                                        label:{text:"",visible:false},
                                        disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                        visible:($scope.data.ishazardouschemical==1)?true:false,
                                        dataType:"boolean",
                                        editorType: "dxCheckBox",
                                        editorOptions: { 
                                            text:"Not Contaminated",
                                            onValueChanged: function(e){
												var vis_notcontaminated =(e.value==1)?true:false;
												$scope.formInstance.itemOption('group3.group4.group5.notcontaminatedreason', 'visible', vis_notcontaminated);
												$scope.formInstance.itemOption('group3.group4.group5.notcontaminatedreason', 'visibleIndex', 5);
                                                $scope.formInstance.updateData('notcontaminatedreason',  "");
											}
                                        }
                                    },{
                                        dataField:'isnonhazardous',
                                        label:{text:"",visible:false},
                                        disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                        visible:($scope.data.ishazardouschemical==1)?true:false,
                                        dataType:"boolean",
                                        editorType: "dxCheckBox",
                                        editorOptions: { 
                                            text:"Non-hazardous Chemical",
                                            onValueChanged: function(e){
												var vis_isnonhazardous =(e.value==1)?true:false;
												$scope.formInstance.itemOption('group3.group4.group5.nonhazchemicalname', 'visible', vis_isnonhazardous);
												$scope.formInstance.itemOption('group3.group4.group5.nonhazchemicalname', 'visibleIndex', 5);
												$scope.formInstance.updateData('nonhazchemicalname',  "");
											}
                                        }
                                    },{
                                        dataField:'isnonchemical',
                                        label:{text:"",visible:false},
                                        disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                        visible:($scope.data.ishazardouschemical==1)?true:false,
                                        dataType:"boolean",
                                        editorType: "dxCheckBox",
                                        editorOptions: { 
                                            text:"No Chemical Involved",
                                        }
                                    },{	
                                        itemType: "group",
                                        caption: "",
                                        name:"group5",
                                        colSpan:1,
                                        colCount : 1,
                                        items: [
                                            {
                                                dataField:'notcontaminatedreason',
                                                label: {
                                                    text:"Reason (Not Contaminated)",
                                                },
                                                visible:($scope.data.isnotcontaminated==1)?true:false,
                                                name:'notcontaminatedreason',
                                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                                validationRules: [{
                                                    type: "required",
                                                    message: "Please select your department head"
                                                }]                         
                                            },{
                                                dataField:'nonhazchemicalname',
                                                label: {
                                                    text:"Chemical Name (Non-hazardous Chemical)",
                                                },
                                                visible:($scope.data.isnonhazardous==1)?true:false,
                                                name:'nonhazchemicalname',
                                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                                validationRules: [{
                                                    type: "required",
                                                    message: "Please select your department head"
                                                }]                         
                                            }
        
                                        ]
                                        
                                    },

                                ]
                                
							},
                        ]
                    },
                    {	
						itemType: "group",
						caption: "",
						name:"group6",
                        colSpan:1,
						colCount : 1,
						items: [
							{
								dataField:'estimatecost',
								editorType: "dxNumberBox",
                                label: {
                                    text:"Estimation Cost (IDR)",
                                },
								name:'estimatecost',
								editorOptions: {
									format: "#,##0.##",
									value: 0,
									min: 0
								},
                                visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
                                // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            // {
                            //     dataField:'pono',
                            //     label: {
                            //         text:"PO No",
                            //     },
                            //     name:'pono',
                            //     // visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
                            // },
                            // {
                            //     dataField:'materialreturneddate',
                            //     // visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
                            //     editorType: "dxDateBox",
                            //     label: {text: "Material Returned Date (From Vendor)"},
                            //     editorOptions: {displayFormat:"dd/MM/yyyy",disabled: false}
                            // },{
                            //     dataField:'supplierdodnno',
                            //     label: {
                            //         text:"Supplier DO/DN No",
                            //     },
                            //     name:'supplierdodnno',
                            //     // visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
                            //     // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            // },
                            // {
                            //     dataField:'materialdispatchno',
                            //     label: {
                            //         text:"Material Dispatch No",
                            //     },
                            //     name:'materialdispatchno',
                            //     // visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
                            //     // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            // },{
                            //     dataField:'isrepair',
                            //     label:{text:"",visible:false},
                            //     // visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
                            //     // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                            //     dataType:"boolean",
                            //     editorType: "dxCheckBox",
                            //     editorOptions: { 
                            //         text:"Repair",
                            //     }
                            // },{
                            //     dataField:'isscrap',
                            //     label:{text:"",visible:false},
                            //     // visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
                            //     // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                            //     dataType:"boolean",
                            //     editorType: "dxCheckBox",
                            //     editorOptions: { 
                            //         text:"Scrapped",
                            //     }
                            // }

						]
                    },
                    {	
                            itemType: "group",
                            caption: "",
                            name:" group4",
                            colSpan:2,
                            colCount : 2,
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
										value: "",
										onValueChanged: function(e){
											var vis =(e.value==2 && $scope.data.apprstatuscode==2)?true:false;
											$scope.formInstance.itemOption('group4.group9.buyer', 'visible', vis);

											if(e.value!==2 || $scope.data.apprstatuscode==2){
												// $scope.formInstance.itemOption('buyer').editorOptions.disabled=dis;
												$scope.formInstance.updateData('buyer',  "");
											}
											// $scope.formInstance.itemOption('group4.group9.buyer', 'disabled', dis);
											// $scope.formInstance.itemOption('group5.listgroupmoderation').editorOptions.disabled=dis;
											// $scope.formInstance.updateData('buyer',  "");
											
										}
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
						caption: "",
						name:"group9",
						colSpan:2,
						colCount : 2,
						items: [
							{label: {
								text: "Buyer"
							},
							dataField:"buyer",
							editorType: "dxDropDownBox",
							visible: (($scope.data.apprstatuscode==2) && $scope.data.approvalstatus==2) ?true:false,
							disabled: (($scope.mode=='edit')|| ($scope.mode=='add') || ($scope.mode=='approve')) ?false:true,
							editorOptions: { 
								dataSource:$scope.buyerDataSource,  
								valueExpr: 'employee_id',
								displayExpr: 'fullname',
								onValueChanged: function(e){
									console.log(e);
									criteria = {status:'addbuyer',employee_id:e.value,mmf28_id:$scope.Requestid};
									CrudService.FindData('mmf',criteria).then(function (response){
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
							// validationRules: [{
							//     type: "required",
							//     message: "Please select your Buyer"
							// }]
						}
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
									var path = (($scope.mode=='report') || ($scope.mode=='reschedule')) ? "mmfreport" :"mmf";
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
									$scope.mmfApproval();							
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
    var myStore2 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('mmfapp',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('mmfapp',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
			values.mmf28_id=$scope.Requestid;
            CrudService.Create('mmfapp',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		update: function(key, values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
            CrudService.Update('mmfapp',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('mmfapp',key.id).then(function (response) {
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
			return CrudService.GetById('mmfhist',$scope.Requestid);         		
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

    var myStore4 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('mmffile',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('mmffile',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.upload_date = $filter("date")(values.upload_date, "yyyy-MM-dd HH:mm")
			values.mmf28_id=$scope.Requestid;
			values.file_loc =$scope.path;
            CrudService.Create('mmffile',values).then(function (response) {
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
            CrudService.Update('mmffile',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('mmffile',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		}
    });
	var myData4 = new DevExpress.data.DataSource({
		store: myStore4
    });

    $scope.tabs = [
        { id:3, TabName : "SupportDoc", title: 'Supporting Document', template: "tab2"   },
		{ id:1, TabName : "Approver List", title: 'Approver List', template: "tab"   },
		{ id:2, TabName : "History Tracking", title: 'History Tracking', template: "tab1"   },
    ];
    $scope.showHistory = true;
    $scope.loadPanelVisible = false;

    $scope.empDataSource = {
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
				criteria = {module:'MMF',mode:$scope.mode};
                return CrudService.FindData('appr',criteria);
            },
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
    $scope.grid4Options = {
		dataSource: myData4,
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
				e.editorOptions.uploadUrl= "api.php?action=uploadmmffile&id="+$scope.Requestid;
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
			if (e.dataField == "file_descr") {
				var index = e.row.rowIndex;
				var rm = (typeof(e.value)=="undefined")?"":e.value;
				$scope.grid2Component.cellValue(index, "file_descr", rm.trim()+" ");
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
		data.requireddate = $filter("date")(data.requireddate, "yyyy-MM-dd HH:mm");
		data.materialreturneddate = $filter("date")(data.materialreturneddate, "yyyy-MM-dd HH:mm");
		console.log(data);
		CrudService.Update('mmf',data.id,data).then(function (response) {
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
				$location.path( "/mmf" );
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
			}else if($scope.formInstance.option("formData").approvalstatus==3 || $scope.formInstance.option("formData").approvalstatus==1){
				var data = $scope.formInstance.option("formData");
				var date = new Date();
                var d= $filter("date")(date, "yyyy-MM-dd HH:mm");
                data.materialreturneddate = $filter("date")(data.materialreturneddate, "yyyy-MM-dd HH:mm");
				data.approvaldate = d;
				data.mode="approve";
				delete data.createddate;
				delete data.employee_id;
				delete data.wonumber;
				delete data.mmfnumber;
				delete data.requeststatus;
				delete data.telpno;
				delete data.chargecode;
				delete data.materialdispatch;
				delete data.requireddate;
				delete data.materialcode;
				delete data.materialdescr;
				delete data.symptomps;
				delete data.requiredtype;
				delete data.requiredother;
				delete data.instruction;
				delete data.ishazardouschemical;
				delete data.hazchemicalname;
				delete data.isdecontaminated;
				delete data.isnotcontaminated;
				delete data.notcontaminatedreason;
				delete data.isnonhazardous;
				delete data.nonhazchemicalname;
				delete data.isnonchemical;
				// delete data.materialdispatchno;
				// delete data.isrepair;
				// delete data.isscrap;
				// delete data.estimatecost;
				delete data.pono;
				delete data.materialreturneddate;
				delete data.supplierdodnno;
				delete data.action;
				delete data.depthead;
				// delete data.buyer;
				delete data.apprstatuscode;
				CrudService.Update('mmfapp',data.id,data).then(function (response) {
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
						$location.path( "/mmfapproval" );
					}
					
				});
			}else{
				criteria = {status:'approver',mmf28_id:$scope.Requestid};
				CrudService.FindData('mmfapp',criteria).then(function (response){
					if(response.jml>0){
						var data = $scope.formInstance.option("formData");
						var date = new Date();
                        var d= $filter("date")(date, "yyyy-MM-dd HH:mm");
                        data.materialreturneddate = $filter("date")(data.materialreturneddate, "yyyy-MM-dd HH:mm");
						data.approvaldate = d;
						data.mode="approve";
						delete data.createddate;
                        delete data.employee_id;
                        delete data.wonumber;
                        delete data.mmfnumber;
                        delete data.requeststatus;
                        delete data.telpno;
                        delete data.chargecode;
                        delete data.materialdispatch;
                        delete data.requireddate;
                        delete data.materialcode;
                        delete data.materialdescr;
                        delete data.symptomps;
                        delete data.requiredtype;
                        delete data.requiredother;
                        delete data.instruction;
                        delete data.ishazardouschemical;
                        delete data.hazchemicalname;
                        delete data.isdecontaminated;
                        delete data.isnotcontaminated;
                        delete data.notcontaminatedreason;
                        delete data.isnonhazardous;
                        delete data.nonhazchemicalname;
                        delete data.isnonchemical;
                        // delete data.materialdispatchno;
                        // delete data.isrepair;
                        // delete data.isscrap;
                        // delete data.estimatecost;
                        delete data.pono;
                        delete data.materialreturneddate;
                        delete data.supplierdodnno;
                        delete data.action;
                        delete data.depthead;
                        // delete data.buyer;
				        delete data.apprstatuscode;
						CrudService.Update('mmfapp',data.id,data).then(function (response) {
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
								$location.path( "/mmfapproval" );
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
		// criteria = {status:'waiting',username:$scope.formInstance.option("formData").employee_id,id:$scope.Requestid};
		// CrudService.FindData('mmfbyemp',criteria).then(function (response){
			// if(response.jml>0){
			// 	DevExpress.ui.notify({
			// 		message: "Cannot add more request, Selected employee still have waiting approval request",
			// 		type: "warning",
			// 		displayTime: 5000,
			// 		height: 80,
			// 		position: {
			// 		   my: 'top center', 
			// 		   at: 'center center', 
			// 		   of: window, 
			// 		   offset: '0 0' 
			// 	   }
			// 	});
			// }else{
				criteria = {status:'approver',mmf28_id:$scope.Requestid};
				CrudService.FindData('mmfapp',criteria).then(function (response){
					if(response.jml>0){
                        var data = $scope.formInstance.option("formData");
                        data.requeststatus = 1;
                        delete data.fullname;
                        delete data.department;
                        delete data.approvalstatus;
                        delete data.apprstatuscode;
                        // data.jobfinishdate= $filter("date")(data.jobfinishdate, "yyyy-MM-dd HH:mm");
                        CrudService.Update('mmf',data.id,data).then(function (response) {
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
                                $location.path( "/mmf" );
                            }
                            
                        });
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
			// }
		// })
			 	   
    };

    }]);
})(app || angular.module("kduApp"));