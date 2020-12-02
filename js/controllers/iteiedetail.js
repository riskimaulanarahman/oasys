(function (app) {
    app.register.controller('iteiedetailCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
        $scope.ds={};
        $scope.test=[];
        $scope.disabled= true;
        $scope.data = [];  
        if (typeof($scope.mode)=="undefined"){
            $location.path( "/" );
        }
	console.log($scope.mode);
	CrudService.GetById('iteie',$scope.Requestid).then(function(response){
		if(response.status=="autherror"){
			$scope.logout();
		}else{
            $scope.data = response;
            // $scope.data.department = "";
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
            // $scope.buyerDataSource = {
            //     store: new DevExpress.data.CustomStore({
            //         key: "employee_id",
            //         loadMode: "raw",
            //         load: function() {
            //             criteria = {module:'iteie',type:'buyer',mode:$scope.mode};
            //             return CrudService.FindData('appr',criteria);
            //         },
            //     }),
            //     sort: "employee_id"
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

            $scope.deptDatasource = {
                store: new DevExpress.data.CustomStore({
                    key: "departmentname",
                    loadMode: "raw",
                    load: function() {
                        return CrudService.GetAll('dept').then(function (response) {
                            if(response.status=="error"){
                                DevExpress.ui.notify(response.message,"error");
                            }else{
                                return response;
                            }
                        });
                    },
                }),
                // filter:['isused','1'],
                sort: "departmentname"
            }

            console.log($scope.data);

            var today = new Date();
            var dd = today.getDate();

            var mm = today.getMonth()+1; 
            var yyyy = today.getFullYear();
            if(dd<10) 
            {
                dd='0'+dd;
            } 

            if(mm<10) 
            {
                mm='0'+mm;
            } 

            today = yyyy+'-'+mm+'-'+dd;

            $scope.AccessRequested =[{id:0,accessrequested:"- Select -"},{id:1,accessrequested:"Exchange (non-Internet) Email"},{id:2,accessrequested:"Internet Email"},{id:3,accessrequested:"Change Domain"}];
            $scope.AccessType =[{id:0,accesstype:"- Select -"},{id:1,accesstype:"Terminal Server (TS) User Account"},{id:2,accesstype:"Non-TS Account"}];
            $scope.AccountType =[{id:0,accounttype:"- Select -"},{id:1,accounttype:"Permanent"},{id:2,accounttype:"Temporary"}];
            $scope.RequestType =[{id:0,requesttype:"- Select -"},{id:1,requesttype:"Grant Access"},{id:2,requesttype:"Revoke Access"}];
            $scope.EmailQuota =[{id:0,emailquota:"- Select -"},{id:1,emailquota:"250MB"},{id:2,emailquota:"500MB"},{id:3,emailquota:"1000MB"},{id:4,emailquota:"1500MB"},{id:5,emailquota:"2000MB"}];
            // $scope.EmailDomain = [];
            // console.log($scope.EmailDomain);

            $scope.EmailDomain =[{id:0,emaildomain:"- Select -"},{id:1,emaildomain:"itci-hutani.com"},{id:2,emaildomain:"kalimantan-prima.com"},{id:3,emaildomain:"balikpapanchip.com"},{id:4,emaildomain:"lajudinamika.com"},{id:5,emaildomain:"ptadindo.com"}];
            $scope.ListGroup =[{id:0,listgroup:"- Select -"},{id:1,listgroup:"IHM"},{id:2,listgroup:"KPSI"},{id:3,listgroup:"BCL"},{id:4,listgroup:"LDU"},{id:5,listgroup:"Adindo"}];
            $scope.ListGroupModeration =[{id:0,listgroupmoderation:"- Select -"},{id:1,listgroupmoderation:"Mod-IHM"},{id:2,listgroupmoderation:"Mod-BCL"},{id:3,listgroupmoderation:"Mod-KDU-HRD"},{id:4,listgroupmoderation:"Mod-KF-Head"},{id:5,listgroupmoderation:"Mod-KF-Head2"},{id:6,listgroupmoderation:"Mod-KPSI-Pro"},{id:7,listgroupmoderation:"Mod-KDU-FA"}];
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
						caption: "",
						// caption: "Request by : "+$scope.data.fullname+" / Dept : "+$scope.data.department,
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
                                        criteria = {status:'chemp',employee_id:e.value,iteie_id:$scope.Requestid};
                                        CrudService.FindData('iteie',criteria).then(function (response){
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
                                dataField:'department',
                                label: {
                                    text:"Requested Department",
                                },
                                name:'department',
                                disabled: true,                                                    
                            },
                            {
                                dataField:'name',
                                label: {
                                    text:"Name",
                                },
                                name:'name',
                                dataType:"string",
                                validationRules: [{type: "required",message: "Action is required"}],
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'employeeid',
                                label: {
                                    text:"Employee ID",
                                },
                                name:'employeeid',
                                dataType:"string",
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            // {
                            //     dataField:'department2',
                            //     label: {
                            //         text:"Department",
                            //     },
                            //     name:'department',
                            //     disabled: false,                                                    
                            // },
                            {dataField:'departmentuser',label:{text:"Department"},disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                editorType: "dxSelectBox",
                                editorOptions: {
                                    dataSource: $scope.deptDatasource,
                                    displayExpr: "departmentname",
                                    valueExpr: "departmentname",
                                    searchEnabled: true,
                                    // onValueChanged: function(e){
                                    //     console.log(e);
                                    //     criteria = {status:'last',companycode:e.value,rfc_id:$scope.Requestid};
                                    //     CrudService.FindData('rfc',criteria).then(function (response){
                                    //         $scope.formInstance.updateData('rfcno',  response.rfcno);
                                    //         $scope.grid3Component.refresh();
                                    //     })
                                    // }
                                },validationRules: [{
                                        type: "required",
                                        message: "Action is required"
                                    }]
                                
                            },
                            {
                                dataField:'designation',
                                label: {
                                    text:"Designation",
                                },
                                name:'designation',
                                dataType:"string",
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            // {
                            //     dataField:'bgbu',
                            //     label: {
                            //         text:"BG/BU",
                            //     },
                            //     name:'bgbu',
                            //     validationRules: [{type: "required",message: "Action is required"}],
                            //     disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            // },
                            {dataField:'bgbu',label:{text:"BG/BU"},disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                editorType: "dxSelectBox",
                                editorOptions: {
                                    dataSource: $scope.compDatasource,
                                    displayExpr: "companycode",
                                    valueExpr: "companycode",
                                    onValueChanged: function(e){
                                        // var vis =(e.value==4)?true:false;
                                        var val =(e.value);
                                        $scope.formInstance.updateData('listgroup',  val);
                                        
                                    }
                                },validationRules: [{
                                        type: "required",
                                        message: "Action is required"
                                    }]
                                
                            },
                            {
                                dataField:'officelocation',
                                label: {
                                    text:"Office/Location",
                                },
                                name:'officelocation',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'floor',
                                label: {
                                    text:"Floor",
                                },
                                name:'floor',
                                dataType:"number",
                                editorType:"dxNumberBox",
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'phoneext',
                                label: {
                                    text:"Phone (Ext)",
                                },
                                name:'phoneext',
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

                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        name:"group2",
                        colSpan:2,
						colCount:3,
						items: [
                            {
                                dataField:'isvip',
                                label:{text:"VIP ?"},
                                // visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                dataType:"boolean",
                                editorType: "dxCheckBox",
                                // validationRules: [{type: "required",message: "Declaration is required"}],
                                editorOptions: { 
                                    text:"Yes",
                                }
                            },
                            {
                                dataField:'accesstype',
                                editorType: "dxSelectBox",
                                label:{text:"Access Type"},
                                disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                                validationRules: [{type: "required",message: "Action is required"}],
                                editorOptions: { 
                                    dataSource:$scope.AccessType,  
                                    valueExpr: 'id',
                                    displayExpr: 'accesstype',
                                    // onValueChanged: function(e){
                                    //     var vis =(e.value==4)?true:false;
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visible', vis);
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visibleIndex', 0);
                                    //     $scope.formInstance.updateData('requiredother',  "");
                                        
                                    // }
                                },
                            },
                            {
                                dataField:'accounttype',
                                editorType: "dxSelectBox",
                                label:{text:"Account Type"},
                                disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                                validationRules: [{type: "required",message: "Action is required"}],
                                editorOptions: { 
                                    dataSource:$scope.AccountType,  
                                    valueExpr: 'id',
                                    displayExpr: 'accounttype',
                                    onValueChanged: function(e){
                                        var dis =(e.value==1)?true:false;
                                        // $scope.formInstance.itemOption('group3.validfrom').editorOptions.disabled=dis;
                                        // $scope.formInstance.itemOption('group3.validto').editorOptions.disabled=dis;
                                        $scope.formInstance.getEditor('validfrom').option('disabled',dis);
                                        $scope.formInstance.getEditor('validto').option('disabled',dis);
                                        if(dis) {
                                            $scope.formInstance.updateData('validfrom',  $scope.data.createddate);
                                            $scope.formInstance.updateData('validto',  "9999-12-31");
                                        } else {
                                            $scope.formInstance.updateData('validfrom',  today);
                                            $scope.formInstance.updateData('validto',  today);
                                        }
                                        
                                    }
                                },
                            },
                            
                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        name:"group3",
                        colSpan:2,
						colCount:2,
						items: [
                            {
                                dataField:'validfrom',
                                // visible: (($scope.data.apprstatuscode==3)) ? true:false,
                                editorType: "dxDateBox",
                                label: {text: "Valid From"},
                                // max: new Date(date + 1000*60*60*24*3),
                                editorOptions: {
                                    displayFormat:"dd/MM/yyyy",
                                    disabled: (($scope.mode=='add' ) || ($scope.data.accounttype!==1)) ?false:true,
                                }
                            },
                            {
                                dataField:'validto',
                                // visible: (($scope.data.apprstatuscode==3)) ? true:false,
                                editorType: "dxDateBox",
                                label: {text: "Valid To"},
                                // max: new Date(date + 1000*60*60*24*3),
                                editorOptions: {displayFormat:"dd/MM/yyyy",disabled: (($scope.data.accounttype!==1)|| ($scope.mode=='add' )) ?false:true}
                            }
                            
                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        name:"group5",
                        colSpan:2,
						colCount:3,
						items: [
                            // {
                            //     dataField:'listgroup',
                            //     editorType: "dxSelectBox",
                            //     label:{text:"List Group"},
                            //     disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                            //     // validationRules: [{type: "required", message: "Please select RFC Type" }],
                            //     editorOptions: { 
                            //         dataSource:$scope.ListGroup,  
                            //         valueExpr: 'id',
                            //         displayExpr: 'listgroup',
                            //         // onValueChanged: function(e){
                            //         //     var vis =(e.value==4)?true:false;
                            //         //     $scope.formInstance.itemOption('group2.requiredother', 'visible', vis);
                            //         //     $scope.formInstance.itemOption('group2.requiredother', 'visibleIndex', 0);
                            //         //     $scope.formInstance.updateData('requiredother',  "");
                                        
                            //         // }
                            //     },
                            // },
                            {
                                dataField:'listgroup',
                                label: {
                                    text:"List Group",
                                },
                                name:'listgroup',
                                dataType:"string",
                                disabled: true                           
                            },
                            {
                                dataField:'reason',
                                label: {
                                    text:"Reason for request/Remarks",
                                },
                                name:'reason',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            // {
                            //     dataField:'isdeclaration',
                            //     label:{text:"Declaration"},
                            //     // visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
                            //     disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                            //     dataType:"boolean",
                            //     editorType: "dxCheckBox",
                            //     validationRules: [{type: "required",message: "Declaration is required"}],
                            //     editorOptions: { 
                            //         text:"I wish to apply for the services and agree to be bound by the IT Corporate Policies for these services. I also confirm that the information as given above is true and correct",
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
									var path = (($scope.mode=='report') || ($scope.mode=='reschedule')) ? "iteiereport" :"iteie";
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
									$scope.iteieApproval();							
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
			return CrudService.GetById('iteieapp',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('iteieapp',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
			values.iteie_id=$scope.Requestid;
            CrudService.Create('iteieapp',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		update: function(key, values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
            CrudService.Update('iteieapp',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('iteieapp',key.id).then(function (response) {
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
			return CrudService.GetById('iteiehist',$scope.Requestid);         		
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
				criteria = {module:'IT',mode:$scope.mode};
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
            columns: ["fullname",'approvaltype','department'],
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
		// delete data.department;
		delete data.approvalstatus;
		delete data.apprstatuscode;
		// data.requireddate = $filter("date")(data.requireddate, "yyyy-MM-dd HH:mm");
		data.validfrom = $filter("date")(data.validfrom, "yyyy-MM-dd HH:mm");
		data.validto = $filter("date")(data.validto, "yyyy-MM-dd HH:mm");
		console.log(data);
		CrudService.Update('iteie',data.id,data).then(function (response) {
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
				$location.path( "/iteie" );
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
                data.validfrom = $filter("date")(data.validfrom, "yyyy-MM-dd HH:mm");
		        data.validto = $filter("date")(data.validto, "yyyy-MM-dd HH:mm");
                data.approvaldate = d;
				data.mode="approve";
				delete data.createddate;
				delete data.employee_id;
				delete data.name;
				delete data.employeeid;
				delete data.designation;
				delete data.requeststatus;
				delete data.bgbu;
				delete data.officelocation;
				delete data.floor;
				delete data.phoneext;
                delete data.department;
                delete data.departmentuser;
				delete data.isvip;
				delete data.accesstype;
				delete data.accounttype;
				delete data.validfrom;
				delete data.validto;
				delete data.listgroup;
				delete data.reason;

				delete data.depthead;
				delete data.apprstatuscode;
				CrudService.Update('iteieapp',data.id,data).then(function (response) {
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
						$location.path( "/iteieapproval" );
					}
					
				});
			}else{
				criteria = {status:'approver',iteie_id:$scope.Requestid};
				CrudService.FindData('iteieapp',criteria).then(function (response){
					if(response.jml>0){
						var data = $scope.formInstance.option("formData");
						var date = new Date();
                        var d= $filter("date")(date, "yyyy-MM-dd HH:mm");
                        data.validfrom = $filter("date")(data.validfrom, "yyyy-MM-dd HH:mm");
		                data.validto = $filter("date")(data.validto, "yyyy-MM-dd HH:mm");
						data.approvaldate = d;
						data.mode="approve";
						delete data.createddate;
                        delete data.employee_id;
                        delete data.name;
				        delete data.employeeid;
                        delete data.designation;
                        delete data.requeststatus;
                        delete data.bgbu;
                        delete data.officelocation;
                        delete data.floor;
                        delete data.phoneext;
                        delete data.department;
                        delete data.departmentuser;
                        delete data.isvip;
                        delete data.accesstype;
                        delete data.accounttype;
                        delete data.validfrom;
                        delete data.validto;
                        delete data.listgroup;
                        delete data.reason;

                        delete data.depthead;
				        delete data.apprstatuscode;
						CrudService.Update('iteieapp',data.id,data).then(function (response) {
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
								$location.path( "/iteieapproval" );
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
		CrudService.FindData('iteiebyemp',criteria).then(function (response){
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
				criteria = {status:'approver',iteie_id:$scope.Requestid};
				CrudService.FindData('iteieapp',criteria).then(function (response){
					if(response.jml>0){
                        var data = $scope.formInstance.option("formData");
                        data.requeststatus = 1;
                        delete data.fullname;
                        // delete data.department;
                        delete data.approvalstatus;
                        delete data.apprstatuscode;
                        data.validfrom = $filter("date")(data.validfrom, "yyyy-MM-dd HH:mm");
		                data.validto = $filter("date")(data.validto, "yyyy-MM-dd HH:mm");
                        CrudService.Update('iteie',data.id,data).then(function (response) {
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
                                $location.path( "/iteie" );
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
			}
		})
			 	   
    };

    }]);
})(app || angular.module("kduApp"));