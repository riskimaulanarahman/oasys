(function (app) {
    app.register.controller('itsharefdetailCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
        $scope.ds={};
        $scope.test=[];
        $scope.disabled= true;
        $scope.data = [];  
        $scope.roleapp = [];  
        if (typeof($scope.mode)=="undefined"){
            $location.path( "/" );
        }
    // console.log($scope.data);
    var d = new Date();
	CrudService.GetById('itsharef',$scope.Requestid).then(function(response){
		if(response.status=="autherror"){
			$scope.logout();
		}else{
            $scope.data = response;
			// if (($scope.data.formtype==5)){
			// 	$scope.data.membername = Array.isArray($scope.data.membername)?$scope.data.membername:$scope.data.membername.split(",")
			// 	$scope.data.membername = $.map($scope.data.membername, function(value){
			// 		return parseInt(value, 10);
			// 	});
			// }
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

            $scope.listmod = {
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    loadMode: "raw",
                    load: function() {
                        return CrudService.GetAll('listmod').then(function (response) {
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
                filter:['isused','1'],
                sort: "departmentname"
            }

            date = new Date().getTime();

            //console.log($scope.data);

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

            // $scope.EmailDomain = [];
            // console.log($scope.EmailDomain);

            $scope.EmailDomain =[{id:0,emaildomain:"- Select -"},{id:1,emaildomain:"itci-hutani.com"},{id:2,emaildomain:"kalimantan-prima.com"},{id:3,emaildomain:"balikpapanchip.com"},{id:4,emaildomain:"lajudinamika.com"},{id:5,emaildomain:"ptadindo.com"}];
            $scope.ListGroup =[{id:0,listgroup:"- Select -"},{id:1,listgroup:"IHM"},{id:2,listgroup:"KPSI"},{id:3,listgroup:"BCL"},{id:4,listgroup:"LDU"},{id:5,listgroup:"Adindo"}];
            $scope.ListGroupModeration =[{id:0,listgroupmoderation:"- Select -"},{id:1,listgroupmoderation:"Mod-IHM"},{id:2,listgroupmoderation:"Mod-BCL"},{id:3,listgroupmoderation:"Mod-KDU-HRD"},{id:4,listgroupmoderation:"Mod-KF-Head"},{id:5,listgroupmoderation:"Mod-KF-Head2"},{id:6,listgroupmoderation:"Mod-KPSI-Pro"},{id:7,listgroupmoderation:"Mod-KDU-FA"}];
            if(($scope.data.apprstatuscode==5) || ($scope.data.apprstatuscode==4)) {
            $scope.AppAction = [{id:1,appaction:"Ask Rework"},{id:2,appaction:"Approve"},{id:3,appaction:"Reject"},{id:4,appaction:"Add More Approval"}];
            } else {
            $scope.AppAction = [{id:1,appaction:"Ask Rework"},{id:2,appaction:"Approve"},{id:3,appaction:"Reject"}];

            }
            $scope.AccountType =[{id:0,accounttype:"- Select -"},{id:1,accounttype:"Permanent"},{id:2,accounttype:"Temporary"}];
            
			$scope.reqStatus = 0;
            $scope.gridSelectedRowKeys =[];
            
			$scope.detailFormOptions = { 
				onInitialized: function(e) {
					$scope.formInstance = e.component;
				},
				onContentReady:function(e){
					$scope.formInstance = e.component;
					var vis4 = ($scope.data.formtype==4)?true:false;
					$scope.formInstance.getEditor('accounttype').option('disabled',vis4);
					$scope.formInstance.updateData('validfrom',  new Date());
				},
				readOnly : (($scope.mode=='view')||($scope.mode=='report'))?true:false,
				labelLocation : "top",
				minColWidth  :800,
				colCount : 2,
				formData:$scope.data,
                items: [

                    {	
						itemType: "group",
						name:"group1",
						caption: "Request By : "+$scope.data.fullname,
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
                                visible: false,
                                disabled: true,
                            },
                            {
                                dataField:'department',
                                label: {
                                    text:"Department",
                                },
                                name:'department',
                                disabled: true,                                                    
                            },
                            {
                                dataField:'designation',
                                label: {
                                    text:"Designation",
                                },
                                name:'designation',
                                dataType:"string",
                                disabled: true                           
                            },
                            {
                                dataField:'bgbu',
                                label: {
                                    text:"BG/BU",
                                },
                                name:'bgbu',
                                dataType:"string",
                                disabled: true                           
                            },
                            // {dataField:'bgbu',label:{text:"BG/BU"},disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                            //     editorType: "dxSelectBox",
                            //     editorOptions: {
                            //         dataSource: $scope.compDatasource,
                            //         displayExpr: "companycode",
                            //         valueExpr: "companycode",
                            //         onValueChanged: function(e){
                            //             // var vis =(e.value==4)?true:false;
                            //             var val =(e.value);
                            //             $scope.formInstance.updateData('listgroup',  val);
                                        
                            //         }
                            //     },validationRules: [{
                            //             type: "required",
                            //             message: "Action is required"
                            //         }]
                                
                            // },
                            {
                                dataField:'officelocation',
                                label: {
                                    text:"Office/Location",
                                },
                                name:'officelocation',
                                // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true     
                                disabled: true,                        
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
                            {
                                label: {
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
                        // caption: "Email Domain (select one, if applicable)**:",
                        name:"gfoldername",
                        colSpan:2,
						colCount:3,
						items: [
                            // {
                            //     dataField:'foldername',
                            //     label: {
                            //         text:"folder name",
                            //     },
                            //     // visible:($scope.data.formtype==2)?true:false,
                            //     disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==4) || ($scope.data.apprstatuscode==5)) ?false:true,
                            //     name:'foldername',
                            //     dataType:"string",
                            //     validationRules: [{
                            //         type: "required",
                            //         message: "Please input foldername"
                            //     }]
                            // },
                            
                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        name:"group3",
                        colSpan:2,
						colCount:3,
						items: [
                            {
                                dataField:'accounttype',
								name:'accounttype',
                                editorType: "dxSelectBox",
                                label:{text:"Account Type"},
                                // visible:($scope.data.formtype==1)?true:false,
                                // disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report') && ($scope.data.apprstatuscode!==5))?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==4) || ($scope.data.apprstatuscode==5)) ?false:true,
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
                                            $scope.formInstance.updateData('validfrom',  new Date($scope.data.createddate));
                                            $scope.formInstance.updateData('validto',  new Date("9999-12-31"));
                                        } else {
                                            $scope.formInstance.updateData('validfrom',  new Date());
											if ($scope.data.formtype==4){
												var d = new Date();
												var year = d.getFullYear();
												var month = d.getMonth();
												var day = d.getDate();
												var c = new Date(year + 1, month, day);
												$scope.formInstance.updateData('validto',  c);
											}else{
												$scope.formInstance.updateData('validto',  new Date());
											}
                                        }
                                        
                                    }
                                },
                            },
                            {
                                dataField:'validfrom',
                                name: 'validfrom',
                                // visible: (($scope.data.formtype==5)) ? false:true,
                                editorType: "dxDateBox",
                                label: {text: "Valid From"},
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==4) || ($scope.data.apprstatuscode==5)) ?false:true,
                                // min: new Date(date + 1000*60*60*24*3),
                                editorOptions: {
                                    displayFormat:"dd/MM/yyyy",
									// onValueChanged: function (e) {
									// 		var d = e.value;
									// 		var year = d.getFullYear();
									// 		var month = d.getMonth();
									// 		var day = d.getDate();
									// 		var c = new Date(year + 1, month, day);
									// 		console.log(c)
									// 		$scope.formInstance.getEditor('validto').option('max',c);
									// }
								},
								
                            },
                            {
                                dataField:'validto',
                                name: 'validto',
                                // visible: (($scope.data.formtype==5)) ? false:true,
                                editorType: "dxDateBox",
                                label: {text: "Valid To"},
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==4) || ($scope.data.apprstatuscode==5)) ?false:true,
                                editorOptions: {
                                    displayFormat:"dd/MM/yyyy",
                                    // max: new Date(date + 1000*60*60*24*365),
                                }
                            }
                            
                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        name:"gdeklarasi",
                        colSpan:2,
						colCount:3,
						items: [
                            {
                                dataField:'reason',
                                label: {
                                    text:"Reason for request/Remarks/Folder path",
                                },
                                // colSpan:2,
                                editorType:"dxHtmlEditor",
                                name:'remarks',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==4) || ($scope.data.apprstatuscode==5)) ?false:true   ,
                                // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==4) || ($scope.data.apprstatuscode==5)) ?false:true   ,
                                editorOptions: {height: 90,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}                         
                            },
                            {
                                dataField:'isdeclaration',
                                label:{text:"Declaration"},
                                // visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                dataType:"boolean",
                                editorType: "dxCheckBox",
                                validationRules: [{type: "required",message: "Declaration is required"}],
                                editorOptions: { 
                                    text:"I wish to apply for the services and agree to be bound by the IT Corporate Policies for these services. I also confirm that the information as given above is true and correct",
                                }
                            }
                            
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
									var path = (($scope.mode=='report') || ($scope.mode=='reschedule')) ? "itsharefolderreport" :"itsharefolder";
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
									$scope.itsharefApproval();							
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
									$scope.updateForm();
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

    CrudService.GetById('itsharefdetail',$scope.Requestid).then(function(response){
    //     console.log(response);
        $scope.roleapp = response;
        // console.log($scope.roleapp[0]);
        
        $.each(response,function(x,y) {
            // console.log(y.apprstatuscode);
        });
    }); 

    


    var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
            $scope.load = CrudService.GetById('itsharefdetail',$scope.Requestid);

             

            // console.log($scope.load);

            return $scope.load;
            // return CrudService.GetById('itsharefdetail',$scope.Requestid);   
            // CrudService.GetById('itsharefdetail',$scope.Requestid).then(function(response){
            // //     console.log(response);
            // //     $scope.roleapp = response;
            //     return response
            // })      		
		},
		byKey: function(key) {
            CrudService.GetById('itsharefdetail',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
			values.itsharef_id=$scope.Requestid;
            CrudService.Create('itsharefdetail',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		update: function(key, values) {
			// values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
            CrudService.Update('itsharefdetail',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('itsharefdetail',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		}
    });
    var myStore2 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('itsharefapp',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('itsharefapp',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
			values.itsharef_id=$scope.Requestid;
            CrudService.Create('itsharefapp',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		update: function(key, values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
            CrudService.Update('itsharefapp',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('itsharefapp',key.id).then(function (response) {
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
			return CrudService.GetById('itsharefhist',$scope.Requestid);         		
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
    var myStore4 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('itshareffile',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('itshareffile',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.upload_date = $filter("date")(values.upload_date, "yyyy-MM-dd HH:mm")
			values.itsharef_id=$scope.Requestid;
			values.file_loc =$scope.path;
            CrudService.Create('itshareffile',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid5Component.refresh();
			});
		},
		update: function(key, values) {
			if ($scope.path!=""){
				values.upload_date = $filter("date")(values.upload_date, "yyyy-MM-dd HH:mm");
				values.file_loc =$scope.path;
			}
            CrudService.Update('itshareffile',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid5Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('itshareffile',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid5Component.refresh();
			});
		}
    });
    var myData4 = new DevExpress.data.DataSource({
		store: myStore4
    });

    $scope.tabs = [
        { id:3, TabName : "Detail", title: 'Detail', template: "tab2"   },
		{ id:4, TabName : "SupportDoc", title: 'Supporting Document', template: "tab3"   },
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

    CrudService.GetById('itsharef',$scope.Requestid).then(function(response){
		if(response.status=="autherror"){
			$scope.logout();
		}else{
            $scope.reqtype = [{id:1,rtypeaction:"Create Share Folder"},{id:2,rtypeaction:"Grant Access to Existing Folder"},{id:3,rtypeaction:"Delete Shared Folder"},{id:4,rtypeaction:"Revoke Access from Existing Folder"},{id:5,rtypeaction:"Exclude from Archiving Policy"}];

            $scope.data = response;
            console.log('nih' + $scope.data.apprstatuscode);
            $scope.grid4Options = {
                dataSource: myData,
                allowColumnResizing: true,
                columnResizingMode : "widget",
                columnMinWidth: 50,
                columnAutoWidth: true,
                columns: [
                    {
                        dataField:'foldername',
                        caption: "Folder Name",
                        dataType: "string",
                        // editorOptions: {
                        //     disabled:(($scope.mode=='approve') ||($scope.mode=='view') ||($scope.data.apprstatuscode==3))?true:false
                        // }
                    },
                    {
                        dataField:"requesttype",
                        caption: "Request Type",
                        editorType: "dxSelectBox",
                        editorOptions: { 
                            dataSource:$scope.reqtype,  
                            valueExpr: 'id',
                            displayExpr: 'rtypeaction',
                            searchEnabled: true,
                            value: ""
                        },
                        width: "20%",
                        validationRules: [{
                            type: "required",
                            message: "Please Select Action"
                        }],
                        encodeHtml: false,
                        customizeText: function (e) {
                                var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-warning'>need action</span>",
                                "<span class='mb-2 mr-2 badge badge-pill badge-primary'>Create Share Folder</span>",
                                "<span class='mb-2 mr-2 badge badge-pill badge-primary'>Grant Access to Existing Folder</span>",
                                "<span class='mb-2 mr-2 badge badge-pill badge-danger'>Delete Shared Folder</span>",
                                "<span class='mb-2 mr-2 badge badge-pill badge-danger'>Revoke Access from Existing Folder</span>",
                                "<span class='mb-2 mr-2 badge badge-pill badge-primary'>Exclude from Archiving Policy</span>",
                                ""];
                                return rDesc[e.value];
                        },
                    },
                    {
                        dataField:'grantaccessto',
                        caption: "Grant Access To",
                        dataType: "string",
                        width: "15%",
                        maxLength: 30,
                        editorOptions: {
                            maxLength: 30
                            // disabled:(($scope.mode=='approve') ||($scope.mode=='view') ||($scope.data.apprstatuscode==3))?true:false
                        }
                    },
                    {
                        dataField:'change',
                        caption:'Permission',
                        dataType: "boolean",
                        // editorOptions: {
                        //     disabled:(($scope.mode=='approve') ||($scope.mode=='view') )?true:false
                        // }

                        // editorType: "dxCheckBox",
                        // showEditorAlways: false
                        width: "20%"
                    },
                    // {dataField:'change',dataType: "boolean" },
                    // {
                    //     dataField:'readonly',
                    //     caption: "Read Only",
                    //     dataType: "string",
                    //     editorOptions: {
                    //         disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                    //     }
                    // },
                    // {
                    //     dataField:'change',
                    //     caption: "Change",
                    //     dataType: "string",
                    //     editorOptions: {
                    //         disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                    //     }
                    // },
                ],

                editing: {
                    useIcons:true,
                    // mode: "row",
                    mode: "cell",
                    // allowUpdating: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule')||($scope.data.apprstatuscode==5))?true:false,
                    allowUpdating: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule'))?(($rootScope.isAdmin)||($scope.data.apprstatuscode==4)||($scope.data.apprstatuscode==5)?true:false):true,
                    // allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
                    
                    allowAdding:(($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule'))?(($rootScope.isAdmin)?true:false):true,
                    // allowDeleting:($rootScope.isAdmin)?true:false,
                    allowDeleting:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule'))?(($rootScope.isAdmin)||($scope.data.apprstatuscode==4)||($scope.data.apprstatuscode==5)?true:false):true,
                    
                    form:{colCount: 1,
                    },
                },
                onInitialized:function (e){
                    $scope.grid2Component = e.component;
                },
                // onEditingStart: function(e) {
                //     e.component.columnOption("id", "allowEditing", false); 		
                // },
                onEditorPreparing: function (e) {  
                    $scope.grid2Component = e.component;
                    // if (e.dataField == "readonly"){
                    //     e.editorName = "dxCheckBox";
                    //     e.editorOptions.width = "20%";
                    //     e.editorOptions.switchedOnText = "Yes";
                    //     e.editorOptions.switchedOffText = "No";
                    // } 
                    if (e.dataField == "change"){
                        e.editorName = "dxSwitch";
                        e.editorOptions.width = "20%";
                        e.editorOptions.switchedOnText = "Change";
                        e.editorOptions.switchedOffText = "Read";
                    }	
                },
                // onInitNewRow: function (e) {
                //     e.component.columnOption("id", "allowEditing", false);
                    
                // },
                onSelectionChanged: function(data) {
                    $scope.selectedItems = data.selectedRowsData;
                    $scope.disabled = !$scope.selectedItems.length;
                },
                onRowUpdated: function(e) {        
                    $scope.editors = {};
                },
                onRowInserted: function(e) {
                    $scope.editors = {};
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
        }
    });
    
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
                    var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>",
                    "<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>",
                    "<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>",
                    "<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",
                    "<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>",
                    ""];
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
			// allowAdding:(($scope.mode=='view')||($scope.mode=='report') && ($scope.data.apprstatuscode!==5))?false:true,
			allowAdding:(($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
            allowDeleting:(($rootScope.isAdmin) || ($scope.mode=='approve'))?true:false,
			// allowDeleting:(($scope.mode=='approve')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
            
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
    // console.log($rootScope);
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
                    var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-default'>Created</span>",
                    "<span class='mb-2 mr-2 badge badge-pill badge-default'>Save as Draft</span>",
                    "<span class='mb-2 mr-2 badge badge-pill badge-primary'>Submitted</span>",
                    "<span class='mb-2 mr-2 badge badge-pill badge-warning'>Ask Rework</span>",
                    "<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>",
                    "<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",
                    "<span class='mb-2 mr-2 badge badge-pill badge-warning'>Add More Approval</span>",
                    ""];
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

    $scope.grid5Options = {
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
										$scope.grid5Component.saveEditData();
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
								$scope.grid5Component.cancelEditData();
							},text: 'Cancel'
						}
					  }
					]
				}
        },
		onInitialized:function (e){
			$scope.grid5Component = e.component;
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
				e.editorOptions.uploadUrl= "api.php?action=uploaditshareffile&id="+$scope.Requestid;
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
				$scope.grid5Component.cellValue(index, "file_descr", rm.trim()+" ");
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
						$scope.grid5Component.refresh();
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
		// console.log(data.membername)
        // var selected = data.membername || [];
        //     data.membername = selected.join();
		console.log(data);
		CrudService.Update('itsharef',data.id,data).then(function (response) {
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
				$location.path( "/itsharefolder" );
			}
			
		});
    }

    $scope.updateForm = function(e){
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
                console.log('a1');
				var data = $scope.formInstance.option("formData");
				var date = new Date();
                var d= $filter("date")(date, "yyyy-MM-dd HH:mm");
                data.validfrom = $filter("date")(data.validfrom, "yyyy-MM-dd HH:mm");
                data.validto = $filter("date")(data.validto, "yyyy-MM-dd HH:mm");
                // data.membername = data.membername.join();
                data.approvaldate = d;
				data.mode="approve";
				delete data.createddate;
                delete data.employee_id;
                delete data.formtype;
                delete data.department;
				delete data.requeststatus;
				delete data.designation;
				delete data.bgbu;
				delete data.officelocation;
				delete data.floor;
                delete data.phoneext;
                
                delete data.foldername;
           
				delete data.accounttype;
				delete data.validfrom;
				delete data.validto;
				
				delete data.isdeclaration;
				delete data.reason;

				delete data.depthead;
				delete data.apprstatuscode;
				CrudService.Update('itsharefapp',data.id,data).then(function (response) {
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
						$location.path( "/itsharefolderapproval" );
					}
					
				});
			}else{
                console.log('a3');

				criteria = {status:'approver',itsharef_id:$scope.Requestid};
				CrudService.FindData('itsharefapp',criteria).then(function (response){
					if(response.jml>0){
						var data = $scope.formInstance.option("formData");
						var date = new Date();
                        var d= $filter("date")(date, "yyyy-MM-dd HH:mm");
                        data.validfrom = $filter("date")(data.validfrom, "yyyy-MM-dd HH:mm");
                        data.validto = $filter("date")(data.validto, "yyyy-MM-dd HH:mm");
                        // data.membername = data.membername.join();
						data.approvaldate = d;
                        data.mode="approve";
                        
                        if(($scope.data.apprstatuscode==1) || ($scope.data.apprstatuscode==2) || ($scope.data.apprstatuscode==3)) {
                            // delete data.foldername;

                            delete data.accounttype;

                            delete data.validfrom;
                            delete data.validto;
                            delete data.reason;
                            
                        }
						delete data.createddate;
                        delete data.employee_id;
                        delete data.department;
                        delete data.requeststatus;
                        delete data.designation;
                        delete data.bgbu;
                        delete data.officelocation;
                        delete data.floor;
                        delete data.phoneext;
                        delete data.accounttype;

                        delete data.requesttype;
                        
                        delete data.isdeclaration;

                        delete data.depthead;
				        delete data.apprstatuscode;
						CrudService.Update('itsharefapp',data.id,data).then(function (response) {
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
								$location.path( "/itsharefolderapproval" );
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
		// CrudService.FindData('itsharefbyemp',criteria).then(function (response){
		// 	if(response.jml>0){
		// 		DevExpress.ui.notify({
		// 			message: "Cannot add more request, Selected employee still have waiting approval request",
		// 			type: "warning",
		// 			displayTime: 5000,
		// 			height: 80,
		// 			position: {
		// 			   my: 'top center', 
		// 			   at: 'center center', 
		// 			   of: window, 
		// 			   offset: '0 0' 
		// 		   }
		// 		});
		// 	}else{
				criteria = {status:'approver',itsharef_id:$scope.Requestid};
				CrudService.FindData('itsharefapp',criteria).then(function (response){
					if(response.jml>0){
                        criteria = {status:'approver',itsharef_id:$scope.Requestid};
						CrudService.FindData('itsharefdetail',criteria).then(function (response){
						    if(response.jml>0){
                                var data = $scope.formInstance.option("formData");
                                data.requeststatus = 1;
                                delete data.fullname;
                                // delete data.department;
                                delete data.approvalstatus;
                                delete data.apprstatuscode;
                                data.validfrom = $filter("date")(data.validfrom, "yyyy-MM-dd HH:mm");
                                data.validto = $filter("date")(data.validto, "yyyy-MM-dd HH:mm");
                                CrudService.Update('itsharef',data.id,data).then(function (response) {
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
                                        $location.path( "/itsharefolder" );
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
		// 	}
		// })
			 	   
    };

    }]);
})(app || angular.module("kduApp"));