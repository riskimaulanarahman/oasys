(function (app) {
app.register.controller('advpaymentdetailCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	$scope.data = [];  
    if (typeof($scope.mode)=="undefined"){
		$location.path( "/" );
	}
	var d = new Date();
	CrudService.GetById('advpayment',$scope.Requestid).then(function(response){
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
			$scope.allDeptEmpDataSource = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						criteria = {filter:'bydept3',dept:$scope.data.department};
						return CrudService.FindData('emp',criteria);
					}
				}),
				sort: "id"
			}
			$scope.empDataSource = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						criteria = {module:'Advance',mode:$scope.mode};
						return CrudService.FindData('appr',criteria);
					},
				}),
				sort: "id"
			}

			$scope.getlessadv = {
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    loadMode: "raw",
                    load: function() {
                        return CrudService.GetById('listadvance',$scope.data.employee_id).then(function (response) {
                            if(response.status=="error"){
                                DevExpress.ui.notify(response.message,"error");
                            }else{
								console.log(response);
                                return response;
                            }
                        });
                    },
                }),
                
                sort: "id"
            }

			$scope.getlessadvfinal = {
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    loadMode: "raw",
                    load: function() {
                        return CrudService.GetById('listadvancefinal',$scope.data.employee_id).then(function (response) {
                            if(response.status=="error"){
                                DevExpress.ui.notify(response.message,"error");
                            }else{
								console.log(response);
                                return response;
                            }
                        });
                    },
                }),
                
                sort: "id"
            }

			$scope.AppAction = ($scope.data.approvalstep==2)?[{id:1,appaction:"Ask Rework"},{id:2,appaction:"Verify"}]:[{id:1,appaction:"Ask Rework"},{id:2,appaction:"Approve"},{id:3,appaction:"Reject"}];
			$scope.AdvanceForm =[{id:0,paymentform:"- Select -"},{id:1,paymentform:"Payment Req HR"},{id:2,paymentform:"Payment Req OPR"}];
			$scope.Paymentopt =[{id:1,payment:"Cash"},{id:2,payment:"Bank"}];
			$scope.OpsCategory =[{id:1,opscategory:"General"},{id:2,opscategory:"Pajak"},{id:3,opscategory:"PSDH (Pajak Sumber Daya Hutan"}];

			// console.log($scope.Paymentopt);
			$scope.reqStatus = 0;
			$scope.gridSelectedRowKeys =[];

			
			$scope.detailFormOptions = { 
				onInitialized: function(e) {
					$scope.formInstance = e.component;
				},
				onContentReady:function(e){
					$scope.formInstance = e.component;
					console.log($scope.formInstance)
					criteria = {status:'checkform',formtype:$scope.data.paymentform,advpayment_id:$scope.Requestid,employee_id:$scope.data.employee_id};
					console.log($scope.data.paymentform);
					CrudService.FindData('advpayment',criteria).then(function (response){
						var vis =(response.message==200)?true:false;
						if(response.message == 200) {
							// $scope.formInstance.itemOption('group.paymenttype', 'visibleIndex', 0);
							// var lessadv = $scope.formInstance.getEditor('advanceno');
							// lessadv.getDataSource().filter('advanceform','=',e.value);
							// lessadv.getDataSource().load();

							
						} else if(response.message == 404) {
							DevExpress.ui.notify("tidak ada advance","error");
							// $scope.formInstance.itemOption('group.paymenttype', 'visibleIndex', 0);
							$scope.formInstance.itemOption('subgroup.advanceno', 'visible', false);
						} else {
							$scope.formInstance.itemOption('group.paymenttype', 'visible', false);
							$scope.formInstance.updateData('paymenttype',  "");
							$scope.formInstance.itemOption('subgroup.advanceno', 'visible', false);
							$scope.formInstance.updateData('advanceno',  "");

						}
						$scope.formInstance.itemOption('group.paymenttype', 'visible', vis);
						// $scope.formInstance.updateData('paymenttype',  "");

						if($scope.data.paymentform == 2) {
							$scope.formInstance.itemOption('subgroup.opscategory', 'visible', true);
						}

						if($scope.grid2Component) {
							$scope.grid2Component.refresh();
						}
					})
				},
				// bindingOptions :{
				// 	"columns[2].lookup.dataSource":"apptypeDatasource"
				// },
				readOnly : (($scope.mode=='view')||($scope.mode=='report'))?true:false,
				labelLocation : "top",
				minColWidth  :800,
				colCount : 2,
				formData:$scope.data,	
				items: [
					{	
						itemType: "group",
						caption: "Request by : "+$scope.data.fullname+" / Dept : "+$scope.data.department+" / Payment No : "+$scope.data.paymentno,
						colSpan:2,
						colCount : 2,
						items: [
							{
                                dataField:'paymentform',
								name:'paymentform',
                                editorType: "dxSelectBox",
                                label:{text:"Payment Form"},
                                // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                validationRules: [{type: "required",message: "Action is required"}],
                                editorOptions: { 
									inputAttr:{ dataintro : 'paymentform' },
									readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                    dataSource:$scope.AdvanceForm,  
                                    valueExpr: 'id',
                                    displayExpr: 'paymentform',
									// value: "",
									onValueChanged: function(e) {
										criteria = {status:'appform',formtype:e.value,advpayment_id:$scope.Requestid,employee_id:$scope.data.employee_id};
										CrudService.FindData('advpayment',criteria).then(function (response){
											console.log(response);
											var vis =(response.message==200)?true:false;
											if(response.message == 200) {
												

												
											} else if(response.message == 404) {
												DevExpress.ui.notify("tidak ada advance","error");
												// $scope.formInstance.itemOption('group.paymenttype', 'visibleIndex', 0);
												$scope.formInstance.itemOption('subgroup.advanceno', 'visible', false);
											} else {
												$scope.formInstance.itemOption('group.paymenttype', 'visible', false);
												$scope.formInstance.updateData('paymenttype',  "");
												$scope.formInstance.itemOption('subgroup.advanceno', 'visible', false);
												$scope.formInstance.updateData('advanceno',  "");

											}
											$scope.formInstance.itemOption('group.paymenttype', 'visible', vis);
											$scope.formInstance.updateData('paymenttype',  "");
											
											var vis1 =(e.value==2)?true:false;

											$scope.formInstance.itemOption('subgroup.opscategory', 'visible', vis1);
											$scope.formInstance.updateData('opscategory', null);

				
											if($scope.grid2Component) {
												$scope.grid2Component.refresh();
											}
										})
										$('#advformtype').val(e.value);

									}
                                },
								
                            },
							{
                                dataField:'paymenttype',
                                name:'paymenttype',
                                label:{text:"With Advance ?"},
                                // visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
								visible: false,
                                // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                dataType:"boolean",
                                editorType: "dxCheckBox",
                                // validationRules: [{type: "required",message: "Declaration is required"}],
                                editorOptions: { 
									inputAttr:{ dataintro : 'paymenttype' },
									readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                    text:"Yes",
									onValueChanged: function (e) {
									var vis1 =(e.value==1)?true:false;

									$scope.formInstance.itemOption('subgroup.advanceno', 'visible', vis1);
									// $scope.formInstance.itemOption('group.paymenttype', 'visibleIndex', 0);

									criteria = {status:'savelessadv',paymenttype:e.value,advpayment_id:$scope.Requestid,employee_id:$scope.data.employee_id};
									CrudService.FindData('advpayment',criteria).then(function (response){
										console.log(response);
			
									})
									if(e.value==1) {

										var paymentform = $scope.formInstance.getEditor('paymentform');
										console.log(paymentform);
										var lessadv = $scope.formInstance.getEditor('advanceno');
										lessadv.getDataSource().filter('advanceform','=',$scope.data.paymentform);
										lessadv.getDataSource().load();
									} else {
										
										$scope.formInstance.updateData('advanceno',  "");

									}

									// $scope.formInstance.itemOption('subgroup.advanceno', 'visibleIndex', 0);

									// return newValue;
								}
                                },
								
                            },
							
							
							
						]
						
					},{	
						itemType: "group",
						name: "subgroup",
						caption: "",
						colCount : 2,
						colSpan :2,
						items: [
							{
                                dataField:'advanceno',
								name:'advanceno',
                                editorType: "dxSelectBox",
                                label:{text:"Less Advance"},
								visible:($scope.data.paymenttype==1 && $scope.data.requeststatus !=3)?true:false,
                                // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                validationRules: [{type: "required",message: "Action is required"}],
                                editorOptions: { 
									inputAttr:{ dataintro : 'advanceno' },
									readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                    dataSource:$scope.getlessadv,  
                                    valueExpr: 'advanceno',
                                    // displayExpr: 'advanceno',
									displayExpr: getDisplayExpr,
                                    // value: '',
									onValueChanged: function(e) {
										console.log(e.value);
										criteria = {status:'savelessadv',advanceno:e.value,advpayment_id:$scope.Requestid,employee_id:$scope.data.employee_id};
										CrudService.FindData('advpayment',criteria).then(function (response){
											console.log(response.amount);
											// $scope.getadvance = response;
											// 	var lessadv = $scope.formInstance.getEditor('lessadvance');
											// 	lessadv.getDataSource().filter('advanceform','=',e.value);
											// 	lessadv.getDataSource().load();
				
										})

									}
                                },
								
                            },
							{
                                dataField:'advanceno',
								name:'advanceno2',
                                editorType: "dxSelectBox",
                                label:{text:"Less Advance"},
								visible:($scope.data.paymenttype==1 && $scope.data.requeststatus ==3)?true:false,
                                // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                validationRules: [{type: "required",message: "Action is required"}],
                                editorOptions: { 
									inputAttr:{ dataintro : 'advanceno2' },
									readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                    dataSource:$scope.getlessadvfinal,  
                                    valueExpr: 'advanceno',
                                    // displayExpr: 'advanceno',
									displayExpr: getDisplayExpr,
                                    // value: '',
                                },
								
                            },
							{
								dataField:'opscategory',
								name:'opscategory',
								editorType: "dxSelectBox",
								label:{text:"Category"},
								validationRules: [{type: "required",message: "Action is required"}],
								visible:false,
								editorOptions: { 
									readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
									dataSource:$scope.OpsCategory,  
									valueExpr: 'id',
									displayExpr: 'opscategory',
	
									onValueChanged: function(e) {
										criteria = {status:'opscategory',categorytype:e.value,advpayment_id:$scope.Requestid,employee_id:$scope.data.employee_id};
										CrudService.FindData('advpayment',criteria).then(function (response){
											$scope.grid2Component.refresh();
										})
	
									}
								},
								
							},
							{
                                dataField:'payment',
								name:'payment',
                                editorType: "dxSelectBox",
                                label:{text:"Payment Method"},
                                // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                validationRules: [{type: "required",message: "Action is required"}],
                                editorOptions: { 
									inputAttr:{ dataintro : 'payment' },
									readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                    dataSource:$scope.Paymentopt,  
                                    valueExpr: 'id',
                                    displayExpr: 'payment',
                                },
								
                            },
							// {
							// 	label:{text:"Amount : 100"},
							// 	template: "<span>Amount : <b>100</b></span>",
							// 	visible:($scope.data.paymenttype==1 && $scope.data.requeststatus !=3)?true:false,
							// },
							
						{dataField:'createddate',editorType: "dxDateBox",label: {text: "Creation Date"},editorOptions: {inputAttr:{ dataintro : 'createddate' },displayFormat:"dd/MM/yyyy",readOnly: true}},
						
						
						{
							dataField:'beneficiary',
							label: {
								text:"Beneficiary",
							},
							name:'beneficiary',
							// disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
							editorOptions: {
								inputAttr:{ dataintro : 'beneficiary' },
								readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
							}
						},
						{
							dataField:'accountname',
							label: {
								text:"Account Name",
							},
							name:'accountname',
							// disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true         
							editorOptions: {
								inputAttr:{ dataintro : 'accountname' },
								readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
							}                   
						},
						{
							dataField:'bank',
							label: {
								text:"Bank",
							},
							name:'bank',
							// disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true 
							editorOptions: {
								inputAttr:{ dataintro : 'bank' },
								readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
							}                           
						},
						{
							dataField:'accountnumber',
							label: {
								text:"Bank Account No",
							},
							name:'accountnumber',
							// disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true 
							editorOptions: {
								inputAttr:{ dataintro : 'accountnumber' },
								readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
							}                           
						},
						
						
						// {dataField:'remarks',colSpan:2,editorType:"dxHtmlEditor",editorOptions: {height: 190,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}},
						
						
						]
					},
					{	
						itemType: "group",
						caption: "",
						// name:"reqisition",
						colSpan:2,
						colCount : 2,
						items: [
							{dataField:'paymentdate',
							// disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
							editorType: "dxDateBox",label: {text: "Payment Date"},
							editorOptions: {
								inputAttr:{ dataintro : 'paymentdate' },
								readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
								displayFormat:"dd/MM/yyyy",min:Date.now()},
							validationRules: [{
								type: "required",
								message: "Please Payment Date"
							}]},
							{dataField:'duedate',
							// disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
							editorType: "dxDateBox",label: {text: "Due Date"},
							editorOptions: {
								inputAttr:{ dataintro : 'duedate' },
								readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
								displayFormat:"dd/MM/yyyy",min:Date.now()},
							validationRules: [{
								type: "required",
								message: "Please Due Date"
							}]},
							
							
							
						]
						
					},
					{	
						itemType: "group",
						caption: "",
						// name:"reqisition",
						colSpan:1,
						colCount : 1,
						items: [
							{label: {
								text: "Superior"
							},
							dataField:"depthead",
							editorType: "dxDropDownBox",
							visible: true,
							// disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
							editorOptions: { 
								inputAttr:{ dataintro : 'superior' },
								readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
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
											if (hasSelection){
												e.component.option("value", hasSelection ? keys[0] : null); 
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
						{dataField:'requeststatus',label: {text: "Request Status"},template: function(data, itemElement) {  
							var val = data.editorOptions.value;
							$scope.reqStatus = data.editorOptions.value;
							val=(val>=0)?val:5;
							var rClass = ["mb-2 mr-2 badge badge-pill badge-secondary","mb-2 mr-2 badge badge-pill badge-primary","mb-2 mr-2 badge badge-pill badge-warning","mb-2 mr-2 badge badge-pill badge-success","mb-2 mr-2 badge badge-pill badge-danger","mb-2 mr-2 badge badge-pill badge-alt"];
							var rDesc = ["Saved as Draft","Waiting Approval","Require Rework","Approved","Rejected","Waiting Payment","Not Saved"];
							$('<span>').appendTo(itemElement).addClass(rClass[val]).text(rDesc[val]);
						}},
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
						{
							dataField:'remarks',colSpan:2,editorType:"dxHtmlEditor",editorOptions: {height: 190,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}
						},
						// {
						// 	label: {
						// 		text: "I fully aware that this advance must be cleared/settled within seven (7) working days from date of occurent of event. If I fail to comply this, I authorize The Finance Section to deduct my salary without further notification."
						// 	},
						// },
						// {
						// 	label: {
						// 		text: "Saya sepenuhnya memahami bahwa Advance ini harus diselesaikan dalam waktu 7 hari kerja setelah tanggal pelaksanaan kegiatan. Jika Saya tidak dapat memenuhi ketentuan ini, Saya memberikan wewenang kepada bagian keuangan untuk memotong gaji saya tanpa perlu pemberitahuan lebih lanjut."
						// 	},
						// },
						{
							dataField:'isdeclaration',
							label:{text:"Declaration"},
							// visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
							// disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
							dataType:"boolean",
							editorType: "dxCheckBox",
							validationRules: [{type: "required",message: "Declaration is required"}],
							editorOptions: { 
								readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
								text: "I confirm that the information as given is true and correct"
							}
						}


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
									var path = ($scope.mode=='report') ? "advancepaymentreport" :"advancepayment";
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
									$scope.advpaymentApproval();							
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
									$scope.updateAdvance();
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
								visible: (($scope.mode=='approve') ||($scope.mode=='view') ||($scope.mode=='report'))?false:true,
								useSubmitBehavior: false
							}
						},{
							itemType: "button",
							horizontalAlignment: "left",
							buttonOptions: {
								text: "Submit",
								type: "success",
								onClick: function(){
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
									$scope.data = $scope.formInstance.option("formData");	
								},
								visible: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?false:true,
								useSubmitBehavior: true
							}
						}]
					},
				],			
			};
		}

		function getDisplayExpr(item) {
			if (!item) {
			  return '';
			}
		
			return `${item.advanceno} | Amount : ${item.amount}`;
		  }

		var myStore = new DevExpress.data.CustomStore({
			load: function() {			
				$scope.isLoaded =true;
				return CrudService.GetById('advpaymentdetail',$scope.Requestid);         		
			},
			byKey: function(key) {
				CrudService.GetById('advpaymentdetail',encodeURIComponent(key)).then(function (response) {
					return response;
				});
			},
			insert: function(values) {
				values.advpayment_id=$scope.Requestid;

				if($scope.data.paymentform == 0) {
					DevExpress.ui.dialog.alert("Select Payment Form","Error");
				} else if($scope.data.paymentform == 2 && ($scope.data.opscategory == 0 || $scope.data.opscategory == null)) {
					DevExpress.ui.dialog.alert("Select Category","Error");		
				} else {
					CrudService.Create('advpaymentdetail',values).then(function (response) {
						if(response.status=="error"){
							DevExpress.ui.dialog.alert(response.message,"Error");
						}
						$scope.grid1Component.refresh();
					});
				}
			},
			update: function(key, values) {
				CrudService.Update('advpaymentdetail',key.id,values).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid1Component.refresh();
					$scope.grid2Component.refresh();
				});
			},
			remove: function(key) {
				CrudService.Delete('advpaymentdetail',key.id).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid1Component.refresh();
				});
			}
		});
		var myData = new DevExpress.data.DataSource({
			store: myStore
		});
		var myStore2 = new DevExpress.data.CustomStore({
			load: function() {			
				$scope.isLoaded =true;
				return CrudService.GetById('advpaymentapp',$scope.Requestid);         		
			},
			byKey: function(key) {
				CrudService.GetById('advpaymentapp',encodeURIComponent(key)).then(function (response) {
					return response;
				});
			},
			insert: function(values) {
				values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
				values.advpayment_id=$scope.Requestid;
				CrudService.Create('advpaymentapp',values).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid2Component.refresh();
				});
			},
			update: function(key, values) {
				values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
				CrudService.Update('advpaymentapp',key.id,values).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid2Component.refresh();
				});
			},
			remove: function(key) {
				CrudService.Delete('advpaymentapp',key.id).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid2Component.refresh();
				});
			}
		});
		var myStore3 = new DevExpress.data.CustomStore({
			load: function() {			
				$scope.isLoaded =true;
				return CrudService.GetById('advpaymenthist',$scope.Requestid);         		
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
		var myStore4 = new DevExpress.data.CustomStore({
			load: function() {			
				$scope.isLoaded =true;
				return CrudService.GetById('advpaymentfile',$scope.Requestid);         		
			},
			byKey: function(key) {
				CrudService.GetById('advpaymentfile',encodeURIComponent(key)).then(function (response) {
					return response;
				});
			},
			insert: function(values) {
				values.upload_date = $filter("date")(values.upload_date, "yyyy-MM-dd HH:mm")
				values.advpayment_id=$scope.Requestid;
				values.file_loc =$scope.path;
				CrudService.Create('advpaymentfile',values).then(function (response) {
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
				CrudService.Update('advpaymentfile',key.id,values).then(function (response) {
					if(response.status=="error"){
						 DevExpress.ui.notify(response.message,"error");
					}
					$scope.grid2Component.refresh();
				});
			},
			remove: function(key) {
				CrudService.Delete('advpaymentfile',key.id).then(function (response) {
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
		var myData3 = new DevExpress.data.DataSource({
			store: myStore3
		});
		var myData4 = new DevExpress.data.DataSource({
			store: myStore4
		});

		// Globalize.culture().numberFormat.currency.symbol = "Rp";
		$scope.showHistory = true;
		// $scope.appText = ["Yes","No"];
		$scope.loadPanelVisible = false;
		$scope.grid1Options = {
			dataSource: myData,
			allowColumnResizing: true,
			wordWrapEnabled: true,
			columnResizingMode : "widget",
			columnMinWidth: 50,
			columnAutoWidth: true,
			customizeText: function(arg) {  
				Globalize.culture().numberFormat.currency.symbol = "Rp";  
				return Globalize.format(arg.value, "c")  
			},
			columns: [
				{dataField:'description',width:150,dataType: "string" , validationRules: [{ type: "required" }], editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                }},	
				{dataField:'accountcode',caption: "Account Code",width:150,dataType: "string", editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                }},
				{dataField:'amount',caption: "Amount",width:150,dataType: "number" ,format: "fixedPoint",validationRules: [{ type: "required" }],
                editorOptions: {
					format: "fixedPoint",
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) || ($scope.data.apprstatuscode==2)?false:true):false
                }},
				{dataField:'remarks',width:150,dataType: "string" , editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) || ($scope.data.apprstatuscode==2)?false:true):false
                }},	
			],
			summary: {
				recalculateWhileEditing: true,
				totalItems: [{
					column: "amount",
					summaryType: "sum",
					valueFormat: "fixedPoint",
					displayFormat: "Total: {0}",	
				}]
			},
			editing: {
				useIcons:true,
				mode: "row",
				allowUpdating:(($scope.mode=='approve') || ($scope.mode=='view') ||($scope.mode=='report') )?(($rootScope.isAdmin) || ($scope.data.apprstatuscode==2)?true:false):true,
				allowAdding:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
				allowDeleting:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
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
				$scope.grid1Component = e.component;

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
				// allowUpdating: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
				// allowAdding:(($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
				allowUpdating:($rootScope.isAdmin)?true:false,
				allowAdding:($rootScope.isAdmin)?true:false,
				allowDeleting:($rootScope.isAdmin)?true:false,
				form:{colCount: 1,
				},
			},
			onInitialized:function (e){
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
					e.editorOptions.uploadUrl= "api.php?action=uploadadvpaymentfile&id="+$scope.Requestid;
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
		
	});
	$scope.tabs = [
		{ id:1, TabName : "Detail Advance", title: 'Detail Payment & Receipt', template: "tab1"   },
		{ id:4, TabName : "SupportDoc", title: 'Supporting Document', template: "tab4"   },
		{ id:2, TabName : "Approver List", title: 'Approver List', template: "tab2"   },
		{ id:3, TabName : "History Tracking", title: 'History Tracking', template: "tab3"   },
	];
	$scope.selectedTab = 0;
	$scope.tabSettings = {
		dataSource: $scope.tabs,
		animationEnabled:true,
		swipeEnabled : false,
		bindingOptions: {
			selectedIndex: 'selectedTab'
		},
	}
	$scope.updateAdvance = function(e){
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
			delete data.paymentno;
			delete data.paymenttype;
			// delete data.paymentform;
			// delete data.advanceno;
			// delete data.lessadvance;
			delete data.payment;
			delete data.beneficiary;
			delete data.accountName;
			delete data.bank;
			delete data.accountnumber;
			delete data.duedate;
			delete data.paymentdate;
			delete data.isdeclaration;
			delete data.apprstatuscode;


			CrudService.Update('advpaymentapp',data.id,data).then(function (response) {
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
					$location.path( "/advpaymentapproval" );
				}
				
			});
		}else{
			criteria = {status:'approver',advpayment_id:$scope.Requestid};
			CrudService.FindData('advpaymentapp',criteria).then(function (response){
				console.log(response.jml);
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
					delete data.paymentno;
					delete data.paymenttype;
					// delete data.paymentform;
					// delete data.advanceno;
					// delete data.lessadvance;
					delete data.payment;
					delete data.beneficiary;
					delete data.accountName;
					delete data.bank;
					delete data.accountnumber;
					delete data.duedate;
					delete data.paymentdate;
					delete data.isdeclaration;
					delete data.apprstatuscode;


					CrudService.Update('advpaymentapp',data.id,data).then(function (response) {
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
							$location.path( "/advancepaymentapproval" );
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
		var data = $scope.formInstance.option("formData");
		delete data.fullname;
		delete data.department;
		delete data.approvalstatus;
		// delete data.advpaymentform;
		data.duedate = $filter("date")(data.duedate, "yyyy-MM-dd HH:mm")
		data.paymentdate = $filter("date")(data.paymentdate, "yyyy-MM-dd HH:mm")
		//console.log(data);
		CrudService.Update('advpayment',data.id,data).then(function (response) {
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
				$location.path( "/advancepayment" );
			}
			
		});
	}
	$scope.onFormSubmit = function(e) {
		e.preventDefault();
				criteria = {status:'approver',advpayment_id:$scope.Requestid};
				CrudService.FindData('advpaymentapp',criteria).then(function (response){
					if(response.jml>0){
						criteria = {status:'approver',advpayment_id:$scope.Requestid};
						CrudService.FindData('advpaymentdetail',criteria).then(function (response){
							if(response.jml>0){
								var data = $scope.formInstance.option("formData");
								data.requeststatus = 1;
								delete data.approvalstatus;
								data.duedate = $filter("date")(data.duedate, "yyyy-MM-dd HH:mm")
								data.paymentdate = $filter("date")(data.paymentdate, "yyyy-MM-dd HH:mm")
								CrudService.Update('advpayment',data.id,data).then(function (response) {
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
										$location.path( "/advancepayment" );
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
	$scope.initDropDownBoxEditor = function(data) {
        return {
            dropDownOptions: { width: 500 },
            dataSource: $scope.allDeptEmpDataSource,
            value: data.value,
            valueExpr: "id",
            displayExpr: "fullname",
            contentTemplate: "contentTemplate"
        }
    }
	$scope.initDropDownBoxEditorx = function(data) {
        return {
            dropDownOptions: { width: 500 },
            dataSource: $scope.empDataSource,
            value: data.value,
            valueExpr: "id",
            displayExpr: "fullname",
            contentTemplate: "contentTemplatex"
        }
    }
	
	CrudService.GetAll('approvaltype').then(function (resp) {
        $scope.apptypeDatasource=resp;
    });
	$scope.initContent = function(data, component) {
        return {
            dataSource: $scope.allDeptEmpDataSource,
            remoteOperations: true,
            columns: ["fullname","sapid", "department","company"],
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