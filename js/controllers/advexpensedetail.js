(function (app) {
app.register.controller('advexpensedetailCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	$scope.data = [];  
    if (typeof($scope.mode)=="undefined"){
		$location.path( "/" );
	}
	var d = new Date();
	CrudService.GetById('advexpense',$scope.Requestid).then(function(response){
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
			console.log($scope.data.bg)
			$scope.allDeptEmpDataSource = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						criteria = {
							filter:'bydeptsamebu',
							dept:$scope.data.department,
							bu:$scope.data.bg
						};
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

			$scope.ExpenseTypes = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						return CrudService.GetAll('expensetype').then(function (response) {
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

			$scope.Currencys = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						return CrudService.GetAll('currency').then(function (response) {
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

			// CrudService.GetAll('currency').then(function (currency) {
			// 	$scope.currencys=currency;
			// 	// console.log($scope.currencys);
			// });

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
			$scope.Region =[{id:0,region:"All Indo,excl.Kaltim,Kaltara,Sulawesi,Papua"},{id:2,region:"Kaltim,Kaltara,Sulawesi"},{id:1,region:"Papua"}];
			$scope.AdvanceForm =[{id:0,paymentform:"- Select -"},{id:1,paymentform:"Payment Req HR"},{id:2,paymentform:"Payment Req OPR"}];
			$scope.Paymentopt =[{id:1,payment:"Cash"},{id:2,payment:"Bank"}];
			$scope.reqStatus = 0;
			$scope.gridSelectedRowKeys =[];

			var suspendValueChagned;
			$scope.Pstart = new Date();
			$scope.Pend = new Date();
			let previousEmployeeId = null;

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
				items: [
					{	
						itemType: "group",
						name: "subgroup",
						caption: "Expense No : "+$scope.data.expenseno,
						colCount : 2,
						colSpan :2,
						items: [
		
						{dataField:'createddate',editorType: "dxDateBox",label: {text: "Creation Date"},
						editorOptions: {
							inputAttr:{ dataintro : 'createddate' },
							readOnly: true,
							displayFormat:"dd/MM/yyyy",
							// disabled: true
						}},
						{
							label: {
								text: "Create for Employee"
							},
							dataField: "employee_id",
							editorType: "dxDropDownBox",
							visible: true,
							// editorOptions: {
							// 	readOnly: (($scope.mode == 'edit') || ($scope.mode == 'add')) ? false : true,
							// 	dataSource: $scope.allDeptEmpDataSource,
							// 	valueExpr: 'id',
							// 	displayExpr: 'fullname',
							// 	searchEnabled: true,
							// 	onInitialized: function (e) {
							// 		previousEmployeeId = e.component.option("value");
							// 	},
							// 	contentTemplate: function (e) {
							// 		return $("<div>").dxDataGrid({
							// 			dataSource: e.component.option("dataSource"),
							// 			columns: [
							// 				{ dataField: "fullname", width: 100 },
							// 				{ dataField: "company", width: 50 },
							// 				{ dataField: "department", width: 200 }
							// 			],
							// 			height: 265,
							// 			selection: { mode: "single" },
							// 			selectedRowKeys: [e.component.option("value")],
							// 			focusedRowEnabled: true,
							// 			focusedRowKey: e.component.option("value"),
							// 			searchPanel: {
							// 				visible: true,
							// 				width: 265,
							// 				placeholder: "Search..."
							// 			},
							// 			onSelectionChanged: function (selectedItems) {
							// 				if (selectedItems.selectedRowKeys.length) {
							// 					e.component.option("value", selectedItems.selectedRowKeys[0]);
							// 					e.component.close();
							// 				}
							// 			}
							// 		});
							// 	},
							// 	onValueChanged: function (e) {
							// 		// 🔄 Reset paymenttype
							// 		const paymenttypeEditor = $scope.formInstance.getEditor('paymenttype');
							// 		if (paymenttypeEditor) paymenttypeEditor.option('value', false);

							// 		// 🔄 Update advanceno dataSource
							// 		$scope.getlessadv = {
							// 			store: new DevExpress.data.CustomStore({
							// 				key: "id",
							// 				loadMode: "raw",
							// 				load: () => CrudService.GetById('listadvance', e.value).then(response => {
							// 					if (response.status === "error") {
							// 						DevExpress.ui.notify(response.message, "error");
							// 						return [];
							// 					}
							// 					return response;
							// 				})
							// 			}),
							// 			sort: "id"
							// 		};

							// 		// 🔍 Cek dan reset perjalanan bisnis jika ada tanggal
							// 		const start = $scope.formInstance.option("formData").startdate;
							// 		const end = $scope.formInstance.option("formData").enddate;

							// 		if (start && end) {
							// 			if (confirm("⚠️ Perhatian: Data perjalanan bisnis akan dihapus. Lanjutkan?")) {
							// 				const departdate = $filter("date")(start, "yyyy-MM-dd HH:mm");
							// 				const returndate = $filter("date")(end, "yyyy-MM-dd HH:mm");

							// 				const criteria = {
							// 					status: 'bisnistrip',
							// 					action: 'reset',
							// 					valstart: departdate,
							// 					valend: returndate,
							// 					advexpense_id: $scope.Requestid,
							// 					employee_id: e.value
							// 				};

							// 				CrudService.FindData('advexpense', criteria).then(response => {
							// 					console.log("Reset perjalanan bisnis berhasil:", response);
							// 					$scope.grid5Component.refresh();
							// 				});

							// 				$scope.formInstance.updateData('startdate', null);
							// 				$scope.formInstance.updateData('enddate', null);

							// 				DevExpress.ui.notify("Perjalanan bisnis berhasil dihapus.", "warning", 3000);
							// 				$scope.formInstance.updateData('advanceno', "");

							// 			} else {
							// 				DevExpress.ui.notify("Aksi dibatalkan. Data perjalanan tetap disimpan.", "info", 3000);
							// 			}
							// 	}

							// 		// 🔍 Trigger pencarian data tambahan
							// 		if ($scope.mode === 'edit' || $scope.mode === 'add') {
							// 			const criteria = {
							// 				status: 'chemp',
							// 				employee_id: e.value,
							// 				advexpense_id: $scope.Requestid,
							// 				mode: $scope.mode
							// 			};
							// 			CrudService.FindData('advexpense', criteria);
							// 		}
							// 	}
							// },
							editorOptions: {
								readOnly: (($scope.mode == 'edit') || ($scope.mode == 'add')) ? false : true,
								dataSource: $scope.allDeptEmpDataSource,
								valueExpr: 'id',
								displayExpr: 'fullname',
								searchEnabled: true,
								onInitialized: function (e) {
									previousEmployeeId = e.component.option("value");
								},
								contentTemplate: function (e) {
									return $("<div>").dxDataGrid({
										dataSource: e.component.option("dataSource"),
										columns: [
											{ dataField: "fullname", width: 100 },
											{ dataField: "company", width: 50 },
											{ dataField: "department", width: 200 }
										],
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
										onSelectionChanged: function (selectedItems) {
											if (selectedItems.selectedRowKeys.length) {
												e.component.option("value", selectedItems.selectedRowKeys[0]);
												e.component.close();
											}
										}
									});
								},
								onValueChanged: function (e) {
									const newValue = e.value;

									// 🔍 Cek dan reset perjalanan bisnis jika ada tanggal
									const start = $scope.formInstance.option("formData").startdate;
									const end = $scope.formInstance.option("formData").enddate;

									if (start && end) {
										if (!confirm("⚠️ Perhatian: Data perjalanan bisnis akan dihapus. Lanjutkan?")) {
											// ❌ Batalkan perubahan dan kembalikan nilai sebelumnya
											e.component.option("value", previousEmployeeId);
											DevExpress.ui.notify("Aksi dibatalkan. Data perjalanan tetap disimpan.", "info", 3000);
											return;
										}

										const departdate = $filter("date")(start, "yyyy-MM-dd HH:mm");
										const returndate = $filter("date")(end, "yyyy-MM-dd HH:mm");

										const criteria = {
											status: 'bisnistrip',
											action: 'reset',
											valstart: departdate,
											valend: returndate,
											advexpense_id: $scope.Requestid,
											employee_id: newValue
										};

										CrudService.FindData('advexpense', criteria).then(response => {
											console.log("Reset perjalanan bisnis berhasil:", response);
											$scope.grid5Component.refresh();
										});

										$scope.formInstance.updateData('startdate', null);
										$scope.formInstance.updateData('enddate', null);
										$scope.formInstance.updateData('advanceno', "");

										DevExpress.ui.notify("Perjalanan bisnis berhasil dihapus.", "warning", 3000);
									}

									// ✅ Simpan nilai baru sebagai nilai sebelumnya
									previousEmployeeId = newValue;

									// 🔄 Reset paymenttype
									const paymenttypeEditor = $scope.formInstance.getEditor('paymenttype');
									if (paymenttypeEditor) paymenttypeEditor.option('value', false);

									// 🔄 Update advanceno dataSource
									$scope.getlessadv = {
										store: new DevExpress.data.CustomStore({
											key: "id",
											loadMode: "raw",
											load: () => CrudService.GetById('listadvance', newValue).then(response => {
												if (response.status === "error") {
													DevExpress.ui.notify(response.message, "error");
													return [];
												}
												return response;
											})
										}),
										sort: "id"
									};

									// 🔍 Trigger pencarian data tambahan
									if ($scope.mode === 'edit' || $scope.mode === 'add') {
										const criteria = {
											status: 'chemp',
											employee_id: newValue,
											advexpense_id: $scope.Requestid,
											mode: $scope.mode
										};
										CrudService.FindData('advexpense', criteria);
									}
								}
							},
							validationRules: [{
								type: "required",
								message: "Please select employee"
							}]
						},
						{
							dataField:'email',
							label: {
								text:"Email",
							},
							name:'email',
							editorOptions: {
								inputAttr:{ dataintro : 'email' },
								readOnly: true
							}
						},
						{
							dataField:'costcenter',
							label: {
								text:"Cost Center",
							},
							name:'costcenter',
							editorOptions: {
								inputAttr:{ dataintro : 'costcenter' },
								readOnly: true
							}
						},
						{
							dataField:'bg',
							label: {
								text:"BU",
							},
							name:'bg',
							editorOptions: {
								inputAttr:{ dataintro : 'bg' },
								readOnly: true
							}
						},
						{
							dataField:'location',
							label: {
								text:"Location",
							},
							name:'location',
							editorOptions: {
								inputAttr:{ dataintro : 'location' },
								readOnly: true
							}
						},
						{
							dataField:'paymenttype',
							name:'paymenttype',
							label:{text:"With Advance ?"},
							visible: true,
							dataType:"boolean",
							editorType: "dxCheckBox",
							editorOptions: { 
								inputAttr:{ dataintro : 'paymenttype' },
								readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
								text:"Yes",
								onValueChanged: function (e) {
								var vis1 =(e.value==1)?true:false;

								$scope.formInstance.itemOption('subgroup.advanceno', 'visible', vis1);

								criteria = {status:'savelessadv',paymenttype:e.value,advexpense_id:$scope.Requestid,employee_id:$scope.data.employee_id};
									CrudService.FindData('advexpense',criteria).then(function (response){
										console.log(response);
								})

								if(e.value==1) {

									$scope.getlessadv = {
										store: new DevExpress.data.CustomStore({
											key: "id",
											loadMode: "raw",
											load: function () {
												return CrudService.GetById('listadvance', $scope.data.employee_id).then(function (response) {
													if (response.status == "error") {
														DevExpress.ui.notify(response.message, "error");
													} else {
														console.log(response);
														return response;
													}
												});
											},
										}),
										sort: "id"
									};

									// Reload the advanceno dataSource
									var advancenoEditor = $scope.formInstance.getEditor('advanceno');
									advancenoEditor.option('dataSource', $scope.getlessadv);
									advancenoEditor.getDataSource().load();
								} else {
									
									$scope.formInstance.updateData('advanceno',  "");

								}
							}
							},
							
						},
						
						]
					},
					{	
						itemType: "group",
						caption: "",
						name:"subgroup",
						colSpan:2,
						colCount : 2,
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
										criteria = {status:'savelessadv',advanceno:e.value,advexpense_id:$scope.Requestid,employee_id:$scope.data.employee_id};
										CrudService.FindData('advexpense',criteria).then(function (response){
											console.log(response);
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
									inputAttr:{ dataintro : 'advanceno' },
									readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                    dataSource:$scope.getlessadvfinal,  
                                    valueExpr: 'advanceno',
                                    // displayExpr: 'advanceno',
									displayExpr: getDisplayExpr,
                                },
								
                            },
							
							
							
						]
						
					},
					{	
						itemType: "group",
						caption: "",
						name:"gdatebox",
						colSpan:2,
						colCount : 2,
						items: [
							{dataField:'startdate',
							name: 'startdate',
							editorType: "dxDateBox",label: {text: "Start Date"},
							editorOptions: {
								inputAttr:{ dataintro : 'startdate' },
								readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
								displayFormat:"dd/MM/yyyy",
								onValueChanged: function(e) {
									if (suspendValueChagned) {
										suspendValueChagned = false;
										return;
									}
									var oldValue = e.previousValue;
									console.log(e.value);
									var start = e.value;
									var end = $scope.formInstance.option("formData").enddate;
									var departdate = $filter("date")(start, "yyyy-MM-dd HH:mm")
									var returndate = $filter("date")(end, "yyyy-MM-dd HH:mm")

									$scope.formInstance.getEditor("enddate").option('value', null);
									if(end !== null) {
										var r = confirm("Detail Bisnis Trip will be deleted");
										if (r == true) {
											criteria = {status:'bisnistrip',action:'reset',valstart:departdate,valend:returndate,advexpense_id:$scope.Requestid,employee_id:$scope.data.employee_id};
											CrudService.FindData('advexpense',criteria).then(function (response){
												console.log(response);
												$scope.grid5Component.refresh();
											})

											e.component.option('value', start);
											txt = "Delete Successed";
										} else {
											txt = "Delete Canceled";
											suspendValueChagned = true; 
											e.component.option('value',  oldValue);
											
										}
										alert(txt);
									} else {
										criteria = {status:'bisnistrip',valstart:departdate,valend:returndate,advexpense_id:$scope.Requestid,employee_id:$scope.data.employee_id};
										CrudService.FindData('advexpense',criteria).then(function (response){
											console.log(response);
										})
									}
									$scope.grid1Component.refresh();

								}
							},
							validationRules: [{
								type: "required",
								message: "Please Due Date"
							}]
							},
							{dataField:'enddate',
							name: 'enddate',
							editorType: "dxDateBox",label: {text: "End Date"},
							editorOptions: {
								inputAttr:{ dataintro : 'enddate' },
								readOnly: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
								displayFormat:"dd/MM/yyyy",
								onValueChanged: function(e) {
									var start = new Date($scope.formInstance.option("formData").startdate);
									var end = new Date(e.value);

									diff  = new Date(end - start);
									days  = diff/1000/60/60/24;
									console.log(start);
									console.log(end);
									console.log(diff);
									console.log(days);

									var departdate = $filter("date")(start, "yyyy-MM-dd HH:mm")
									var returndate = $filter("date")(end, "yyyy-MM-dd HH:mm")
									if(end !== null) {
										criteria = {status:'bisnistrip',action:'add',valdays:days,valstart:departdate,valend:returndate,advexpense_id:$scope.Requestid,employee_id:$scope.data.employee_id};
										CrudService.FindData('advexpense',criteria).then(function (response){
											$scope.grid5Component.refresh();
										})
									}

								}
							},
							validationRules: [{
								type: "required",
								message: "Please Payment Date"
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
							dataField:"superior",
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
								message: "Please select your Superior"
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
						{
							dataField:'reason',label: {
								text:"Reason for request/Remarks",
							},
							disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true   ,
							colSpan:2,editorType:"dxHtmlEditor",editorOptions: {height: 190,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}
						},

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
						{dataField:'remarks',colSpan:2,editorType:"dxHtmlEditor",visible: ($scope.mode=='approve') ?true:false,editorOptions: {height: 90,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}},
						


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
									var path = ($scope.mode=='report') ? "advexpensereport" :"advexpense";
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
									$scope.advexpenseApproval();							
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
				return CrudService.GetById('advexpensedetail',$scope.Requestid);         		
			},
			byKey: function(key) {
				CrudService.GetById('advexpensedetail',encodeURIComponent(key)).then(function (response) {
					return response;
				});
			},
			insert: function(values) {
				values.receiptdate = $filter("date")(values.receiptdate, "yyyy-MM-dd HH:mm")
				values.advexpense_id=$scope.Requestid;
				CrudService.Create('advexpensedetail',values).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid1Component.refresh();
				});
			},
			update: function(key, values) {
				values.receiptdate = $filter("date")(values.receiptdate, "yyyy-MM-dd HH:mm")

				CrudService.Update('advexpensedetail',key.id,values).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid1Component.refresh();
					// $scope.grid2Component.refresh();
				});
			},
			remove: function(key) {
				CrudService.Delete('advexpensedetail',key.id).then(function (response) {
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
				return CrudService.GetById('advexpenseapp',$scope.Requestid);         		
			},
			byKey: function(key) {
				CrudService.GetById('advexpenseapp',encodeURIComponent(key)).then(function (response) {
					return response;
				});
			},
			insert: function(values) {
				values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
				values.advexpense_id=$scope.Requestid;
				CrudService.Create('advexpenseapp',values).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid2Component.refresh();
				});
			},
			update: function(key, values) {
				values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
				CrudService.Update('advexpenseapp',key.id,values).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid2Component.refresh();
				});
			},
			remove: function(key) {
				CrudService.Delete('advexpenseapp',key.id).then(function (response) {
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
				return CrudService.GetById('advexpensehist',$scope.Requestid);         		
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
				return CrudService.GetById('advexpensefile',$scope.Requestid);         		
			},
			byKey: function(key) {
				CrudService.GetById('advexpensefile',encodeURIComponent(key)).then(function (response) {
					return response;
				});
			},
			insert: function(values) {
				values.upload_date = $filter("date")(values.upload_date, "yyyy-MM-dd HH:mm")
				values.advexpense_id=$scope.Requestid;
				values.file_loc =$scope.path;
				CrudService.Create('advexpensefile',values).then(function (response) {
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
				CrudService.Update('advexpensefile',key.id,values).then(function (response) {
					if(response.status=="error"){
						 DevExpress.ui.notify(response.message,"error");
					}
					$scope.grid2Component.refresh();
				});
			},
			remove: function(key) {
				CrudService.Delete('advexpensefile',key.id).then(function (response) {
					if(response.status=="error"){
						 DevExpress.ui.notify(response.message,"error");
					}
					$scope.grid2Component.refresh();
				});
			}
		});
		var myStore5 = new DevExpress.data.CustomStore({
			load: function() {			
				$scope.isLoaded =true;
				return CrudService.GetById('advexpensedetailbt',$scope.Requestid);         		
			},
			byKey: function(key) {
				CrudService.GetById('advexpensedetailbt',encodeURIComponent(key)).then(function (response) {
					return response;
				});
			},
			insert: function(values) {
				values.departdate = $filter("date")(values.departdate, "yyyy-MM-dd HH:mm")
				values.departtime = $filter("date")(values.departtime, "HH:mm")
				values.returndate = $filter("date")(values.returndate, "yyyy-MM-dd HH:mm")
				values.returntime = $filter("date")(values.returntime, "HH:mm")
				values.advexpense_id=$scope.Requestid;
				CrudService.Create('advexpensedetailbt',values).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid1Component.refresh();
				});
			},
			update: function(key, values) {
				values.departdate = $filter("date")(values.departdate, "yyyy-MM-dd HH:mm")
				values.departtime = $filter("date")(values.departtime, "HH:mm")
				values.returndate = $filter("date")(values.returndate, "yyyy-MM-dd HH:mm")
				values.returntime = $filter("date")(values.returntime, "HH:mm")
				CrudService.Update('advexpensedetailbt',key.id,values).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid1Component.refresh();
					// $scope.grid2Component.refresh();
				});
			},
			remove: function(key) {
				CrudService.Delete('advexpensedetailbt',key.id).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.dialog.alert(response.message,"Error");
					}
					$scope.grid1Component.refresh();
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
		var myData5 = new DevExpress.data.DataSource({
			store: myStore5
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
			// customizeText: function(arg) {  
			// 	Globalize.culture().numberFormat.currency.symbol = "Rp";  
			// 	return Globalize.format(arg.value, "c")  
			// },
			columns: [
				{caption: '#',formItem: { visible: false},width: 40,
					cellTemplate: function(container, options) {
						container.text(options.rowIndex +1);
					}
				},
				{
					dataField: "expensetype",
					name:'expensetype',
                    editorType: "dxSelectBox",
                    validationRules: [{type: "required", message: "Please select Expense Type" }],
					lookup: { 
						dataSource:$scope.ExpenseTypes,  
						valueExpr: 'code',
						displayExpr: 'type',
					},
					editorOptions: {
						disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin)?false:true):false,
					},
				},
				{dataField:'purpose',caption:'Purpose / Description',width:150,dataType: "string", 
				validationRules: [{type: "required"}],
				editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin)?false:true):false,
                }},
				{dataField:'receiptdate',width:150,dataType: "date" ,
				format: 'dd/MM/yyyy',editorType: "dxDateBox",
                editorOptions: {
					displayFormat:"dd/MM/yyyy",
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin)?false:true):false,
                }},
				{
					dataField:'amount',
					caption: "Amount", 
					validationRules: [{type: "required"}], 
					width:150,
					// format: "fixedPoint",
					format: {
						type: "fixedPoint",
						precision: 2
					},
                editorOptions: {
					// format: "fixedPoint",
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) || (($scope.data.apprstatuscode==2 && $scope.mode=='approve'))?false:true):false
                }},
				// {dataField: "currency",caption: "currency", lookup: { 
				// 	displayExpr: 'nama',  
				// 	valueExpr: 'nama',
				// 	},setCellValue: function(rowData, value) {
				// 		rowData.currency = value;
				// 		// rowData.approvaltype_id = null;
				// 	},
				// }, 
				{
					dataField: "currency",
					name:'currency',
                    editorType: "dxSelectBox",
					editorOptions: { 
						dataSource:$scope.Currencys,  
						valueExpr: 'nama',
						displayExpr: 'nama',
						disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin)?false:true):false,
					},
				},
				// {dataField:'exchangerate',caption: "Exchange Rate",dataType: "number" ,format: "fixedPoint",
                // editorOptions: {
				// 	format: "fixedPoint",
                //     disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                // }},
				// {dataField:'paymentamount',caption: "Amount in local currency",dataType: "number" ,format: "fixedPoint",
                // editorOptions: {
				// 	format: "fixedPoint",
                //     disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false
                // }},
				// {dataField:'exchangerate'},
				// {dataField:'paymentamount'},
				{dataField:'costcentre', caption: 'Cost Centre',editorOptions: {
					disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin)?false:true):false,
				}},
				{dataField:'country',editorOptions: {
					disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin)?false:true):false,
				}},
				{dataField:'location',validationRules: [{type: "required"}],editorOptions: {
					disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin)?false:true):false,
				}},
				{dataField:'remarks',width:150,dataType: "string" , editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) || (($scope.data.apprstatuscode==2 && $scope.mode=='approve'))?false:true):false
                }},	
			],
			summary: {
				recalculateWhileEditing: true,
				totalItems: [{
					column: "amount",
					summaryType: "sum",
					// valueFormat: "fixedPoint",
					valueFormat: {
						type: "fixedPoint",
						precision: 2
					},
					displayFormat: "Total: {0}",	
				}]
			},
			// bindingOptions :{
			// 	"columns[0].lookup.dataSource":"expensetypes",
			// },
			editing: {
				useIcons:true,
				mode: "cell",
				// allowUpdating:(($scope.mode=='approve') || ($scope.mode=='view') ||($scope.mode=='report') )?(($rootScope.isAdmin)?true:false):true,
				allowAdding:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
				// allowUpdating:(($scope.mode=='approve') || ($scope.mode=='view') ||($scope.mode=='report') )?(($rootScope.isAdmin) || ($scope.data.apprstatuscode==2)?true:false):true,
				allowUpdating(e) {
					return (($scope.mode=='approve') || ($scope.mode=='view') ||($scope.mode=='report') || (e.row.data.expensetype == 'MNP') )?(($rootScope.isAdmin) || ($scope.data.apprstatuscode==2)?true:false):true;
				},
				// allowDeleting:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
				allowDeleting(e) {
					return (($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report') || (e.row.data.expensetype == 'MNP') )?(($rootScope.isAdmin)?true:false):true;
				},
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
					e.editorOptions.uploadUrl= "api.php?action=uploadadvexpensefile&id="+$scope.Requestid;
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

		$scope.grid5Options = {
			dataSource: myData5,
			allowColumnResizing: true,
			wordWrapEnabled: true,
			columnResizingMode : "widget",
			columnMinWidth: 50,
			columnAutoWidth: true,
			// customizeText: function(arg) {  
			// 	Globalize.culture().numberFormat.currency.symbol = "Rp";  
			// 	return Globalize.format(arg.value, "c")  
			// },
			groupPanel: {
				emptyPanelText: 'if you got meals/pocket from your trip, please tick the column breakfast,lunch,dinner or pocket',
				visible: true,
			},
			columns: [
				{caption: '#',formItem: { visible: false},width: 40,
					cellTemplate: function(container, options) {
						container.text(options.rowIndex +1);
					}
				},
				{dataField:'departdate',width:150,dataType: "date" ,
				format: 'dd/MM/yyyy',editorType: "dxDateBox",
                editorOptions: {
					displayFormat:"dd/MM/yyyy",
                    // disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) ?false:true):false
					disabled:true
                }},
				{dataField:'departtime',width:150,dataType: "date" ,
				format: 'HH:mm',editorType: "dxDateBox",
                editorOptions: {
					displayFormat:"HH:mm",
					type:"time",
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) ?false:true):false
                }},
				{dataField:'returndate',width:150,dataType: "date" ,
				format: 'dd/MM/yyyy',editorType: "dxDateBox",
                editorOptions: {
					displayFormat:"dd/MM/yyyy",
                    // disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) ?false:true):false
                    disabled:true
                }},
				{dataField:'returntime',width:150,dataType: "date" ,
				format: 'HH:mm',editorType: "dxDateBox",
                editorOptions: {
					displayFormat:"HH:mm",
					type:"time",
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) ?false:true):false
                }},
				// {
				// 	dataField:'accom',
				// 	dataType: "boolean",
				// },
				{
					dataField:'breakfast',
					dataType: "boolean",
					editorOptions: {
						disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) || (($scope.data.apprstatuscode==2 && $scope.mode=='approve'))?false:true):false
					}
				},
				{
					dataField:'lunch',
					dataType: "boolean",
					editorOptions: {
						disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) || (($scope.data.apprstatuscode==2 && $scope.mode=='approve'))?false:true):false
					}
				},
				{
					dataField:'dinner',
					dataType: "boolean",
					editorOptions: {
						disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) || (($scope.data.apprstatuscode==2 && $scope.mode=='approve'))?false:true):false
					}
				},
				{
					dataField:'pocket',
					dataType: "boolean",
					editorOptions: {
						disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) || (($scope.data.apprstatuscode==2 && $scope.mode=='approve'))?false:true):false
					}
				},
				// {
				// 	dataField:'ispapua',
				// 	caption:'isPapua ?',
				// 	dataType: "boolean",
				// 	editorOptions: {
				// 		disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) || (($scope.data.apprstatuscode==2 && $scope.mode=='approve'))?false:true):false
				// 	}
				// },
				{
					dataField:'ispapua',
					caption:'Region',
					// editorType: "dxSelectBox",
					lookup: { 
						dataSource:$scope.Region,  
						valueExpr: 'id',
						displayExpr: 'region',
						searchEnabled: true,
						value: ""
					},
					validationRules: [{type: "required", message: "Please select Region" }],
					editorOptions: {
						disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) || (($scope.data.apprstatuscode==2 && $scope.mode=='approve'))?false:true):false
					}
				},
				
				{dataField:'remarks',width:150,dataType: "string" , editorOptions: {
                    disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?(($rootScope.isAdmin) || (($scope.data.apprstatuscode==2 && $scope.mode=='approve'))?false:true):false
                }},	
			],
		
			editing: {
				useIcons:true,
				mode: "cell",
				allowUpdating:(($scope.mode=='approve') || ($scope.mode=='view') ||($scope.mode=='report') )?(($rootScope.isAdmin) || ($scope.data.apprstatuscode==2)?true:false):true,
				// allowUpdating:(($scope.mode=='approve') || ($scope.mode=='view') ||($scope.mode=='report') )?(($rootScope.isAdmin)?true:false):true,
				// allowAdding:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
				// allowDeleting:(($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
				allowDeleting: ($rootScope.isAdmin)?true:false, // Enables editing
				allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
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
				$scope.grid5Component = e.component;

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
		
	});

	$scope.calculateStatistics = function () {
		// $scope.dataGrid.getSelectedRowsData().then((rowData) => {
			// let commonDuration = 0;
			// for (let i = 0; i < rowData.length; i += 1) {
			// commonDuration += rowData[i].Task_Due_Date
			// 			- rowData[i].Task_Start_Date;
			// }
			// commonDuration /= MILLISECONDS_IN_DAY;

			// $scope.$apply(() => {
				criteria = {checkmnp:'all',advexpense_id:$scope.Requestid};
				CrudService.FindData('advexpensedetail',criteria).then(function (response){
				const { statistic } = $scope;
				statistic.count = response.jml;
				// statistic.count = rowData.length;
				});
		// });
	};

	$scope.buttonOptionsbt = {
		text: 'Check Amount',
		type: 'default',
		onClick: $scope.calculateStatistics,
	};

	$scope.statistic = {
		count: 0,
	};
	$scope.tabs = [
		{ id:1, TabName : "Detail Expense", title: 'Detail Expense', template: "tab1"   },
		{ id:5, TabName : "Bisnis Trip", title: 'Bisnis Trip', template: "tab5"   },
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
			delete data.paymenttype;

			delete data.createddate;
			delete data.createdby;
			delete data.employee_id;
			delete data.requeststatus;
			delete data.expenseno;
			delete data.name;
			delete data.email;
			delete data.costcenter;
			delete data.bg;
			delete data.location;
			delete data.superior;
			// delete data.startdate;
			// delete data.enddate;
			delete data.reason;
			delete data.apprstatuscode;

			CrudService.Update('advexpenseapp',data.id,data).then(function (response) {
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
					$location.path( "/advexpenseapproval" );
				}
				
			});
		}else{
			criteria = {status:'approver',advexpense_id:$scope.Requestid};
			CrudService.FindData('advexpenseapp',criteria).then(function (response){
				console.log(response.jml);
				if(response.jml>0){
					var data = $scope.formInstance.option("formData");
					var date = new Date();
					var d= $filter("date")(date, "yyyy-MM-dd HH:mm")
					data.approvaldate = d;
					data.mode="approve";
					delete data.paymenttype;

					delete data.createddate;
					delete data.createdby;
					delete data.employee_id;
					delete data.requeststatus;
					delete data.expenseno;
					delete data.name;
					delete data.email;
					delete data.costcenter;
					delete data.bg;
					delete data.location;
					delete data.superior;
					// delete data.startdate;
					// delete data.enddate;
					delete data.reason;
					delete data.apprstatuscode;

					CrudService.Update('advexpenseapp',data.id,data).then(function (response) {
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
							$location.path( "/advexpenseapproval" );
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
		console.log('oke');
		var data = $scope.formInstance.option("formData");
		delete data.fullname;
		delete data.department;
		delete data.approvalstatus;
		// delete data.startdate;
		// delete data.enddate;

		// data.startdate = $filter("date")(data.startdate, "yyyy-MM-dd HH:mm")
		// data.enddate = $filter("date")(data.enddate, "yyyy-MM-dd HH:mm")
		CrudService.Update('advexpense',data.id,data).then(function (response) {
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
				$location.path( "/advexpense" );
			}
			
		});
	}
	$scope.onFormSubmit = function(e) {
		e.preventDefault();
		// criteria = {status:'waiting',username:$scope.formInstance.option("formData").employee_id,id:$scope.Requestid};
		// CrudService.FindData('advexpensebyemp',criteria).then(function (response){
		// 	if(response.jml>0){
		// 		DevExpress.ui.notify({
		// 			message: "Cannot add more request, You still have waiting approval request",
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
			criteria = {status:'approver',advexpense_id:$scope.Requestid};
			CrudService.FindData('advexpenseapp',criteria).then(function (response){
					if(response.jml>0){
						criteria = {status:'approver',advexpense_id:$scope.Requestid};
						CrudService.FindData('advexpensedetail',criteria).then(function (response){
							if(response.jml>0){
										criterias = {status:'approver',advexpense_id:$scope.Requestid};
										
										CrudService.FindData('advexpensedetailbt',criterias).then(function (responsed){
										if(responsed.jml>0){

											criterias = {status:'checktime',advexpense_id:$scope.Requestid};
										
											CrudService.FindData('advexpensedetailbt',criterias).then(function (responsedbt){
											if(responsedbt.jml==0){

												var data = $scope.formInstance.option("formData");
												data.requeststatus = 1;
												delete data.approvalstatus;

												CrudService.Update('advexpense',data.id,data).then(function (response) {
													
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
														$location.path( "/advexpense" );
													}
													
												});
											} else {
												DevExpress.ui.notify({
													message: "Please add detail bisnis trip, first and last (depart time & return time) of the request",
													type: "error",
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
											
									} else {
										DevExpress.ui.notify({
											message: "Please add detail bisnis trip of the request",
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
									message: "Please add detail expense of the request",
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