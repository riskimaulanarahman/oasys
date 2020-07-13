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
            $scope.RequiredType =[{id:0,requiredtype:"- Select -"},{id:1,requiredtype:"Repair"},{id:2,requiredtype:"Servicing"},{id:3,requiredtype:"Calibratior"},{id:4,requiredtype:"Others"}];
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
                            {
                                dataField:'telpno',
                                label: {
                                    text:"Tel No",
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
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'materialdispatchno',
                                label: {
                                    text:"Material Dispatch No",
                                },
                                name:'materialdispatchno',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'requireddate',
                                editorType: "dxDateBox",
                                label: {text: "Required By (Date)"},
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
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'symptomps',
                                label: {
                                    text:"Symtoms (Problem)",
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
                                editorOptions: { 
                                    dataSource:$scope.RequiredType,  
                                    valueExpr: 'id',
                                    displayExpr: 'requiredtype',
                                    onValueChanged: function(e){
                                        var vis =(e.value==4)?true:false;
                                        $scope.formInstance.itemOption('group2.requiredother', 'visible', vis);
                                        $scope.formInstance.itemOption('group2.requiredother', 'visibleIndex', 0);
                                        $scope.formInstance.updateData('requiredother',  "");
                                        
                                    }
                                },
                            },{
                                dataField:'requiredother',
                                name:'requiredother',
                                disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                                label:{
                                    text:"Pls Specify"
                                },
                                visible:($scope.data.requiredtype==4)?true:false,
                                validationRules: [{
                                    type: "required",
                                    message: "please input Specify"
                                }]
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
						caption: "To be completed by Procurement",
						name:"group6",
						colSpan:1,
						colCount : 1,
						items: [
                            {
                                dataField:'materialdispatchno',
                                label: {
                                    text:"Material Dispatch No",
                                },
                                name:'materialdispatchno',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },{
                                dataField:'isrepair',
                                label:{text:"",visible:false},
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                dataType:"boolean",
                                editorType: "dxCheckBox",
                                editorOptions: { 
                                    text:"Repair",
                                }
                            },{
                                dataField:'isscrap',
                                label:{text:"",visible:false},
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                dataType:"boolean",
                                editorType: "dxCheckBox",
                                editorOptions: { 
                                    text:"Scrapped",
                                }
                            },{
                                dataField:'estimatecost',
                                label: {
                                    text:"Estimation Cost",
                                },
                                name:'estimatecost',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },{
                                dataField:'pono',
                                label: {
                                    text:"PO No",
                                },
                                name:'pono',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },{
                                dataField:'materialreturneddate',
                                editorType: "dxDateBox",
                                label: {text: "Material Returned Date"},
                                editorOptions: {displayFormat:"dd/MM/yyyy",disabled: false}
                            },{
                                dataField:'supplierdodnno',
                                label: {
                                    text:"Supplier DO/DN No",
                                },
                                name:'supplierdodnno',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
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
                        // {
						// 	itemType: "button",
						// 	horizontalAlignment: "right",
						// 	buttonOptions: {
						// 		text: "Back",
						// 		type: "danger",
						// 		onClick: function(){
						// 			$scope.mmfApproval();							
						// 		},
						// 		visible: ($scope.mode=='approve') ?true:false,
						// 		useSubmitBehavior: false
						// 	}
                        // },
                        // {
						// 	itemType: "button",
						// 	horizontalAlignment: "right",
						// 	buttonOptions: {
						// 		text: "Save Update",
						// 		type: "success",
						// 		onClick: function(){
						// 			DevExpress.ui.notify({
						// 				message: "Please wait...!, we are processing your update",
						// 				type: "info",
						// 				displayTime: 1000,
						// 				height: 80,
						// 				position: {
						// 				   my: 'top center', 
						// 				   at: 'center center', 
						// 				   of: window, 
						// 				   offset: '0 0' 
						// 			   }
						// 			});
						// 			$scope.data = $scope.formInstance.option("formData");
						// 			$scope.updateDayoff();
						// 		},
						// 		visible: (($scope.mode=='approve') ||($scope.mode=='reschedule')) ?true:false,
						// 		useSubmitBehavior: false
						// 	}
                        // },
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
    
    $scope.saveDraft = function(e){
		var data = $scope.formInstance.option("formData");
		delete data.fullname;
		delete data.department;
		delete data.approvalstatus;
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
    }]);
})(app || angular.module("kduApp"));