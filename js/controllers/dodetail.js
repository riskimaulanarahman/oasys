(function (app) {
app.register.controller('dodetailCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	$scope.data = [];  
    if (typeof($scope.mode)=="undefined"){
		$location.path( "/" );
	}
	console.log($scope.mode);
	CrudService.GetById('dayoff',$scope.Requestid).then(function(response){
		if(response.status=="autherror"){
			$scope.logout();
		}else{
			$scope.data = response;
			$scope.remaksuser = $scope.data.remarks;
			if(($scope.mode=='approve')){
				$scope.data.remarks="";
			}
			//if($scope.mode!=="add"){
				CrudService.GetById('dodetail',$scope.Requestid).then(function (resp) {
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
						colCount : 2,
						colSpan :2,
						items: [
						{dataField:'requestdate',editorType: "dxDateBox",label: {text: "Creation Date"},editorOptions: {displayFormat:"dd/MM/yyyy",disabled: true}},
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
												criteria = {status:'pending',username:keys[0],id:$scope.Requestid};
												CrudService.FindData('dayoffbyemp',criteria).then(function (response){
													if(response.jml>0){
														DevExpress.ui.dialog.alert("Cannot add more request, Selected employee still have unsubmitted draft or pending request","Error");
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
									criteria = {status:'chemp',employee_id:e.value,dayoff_id:$scope.Requestid};
									CrudService.FindData('dayoff',criteria).then(function (response){
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
						{label: {
								text: "Direct Superior"
							},
							dataField:"superior",
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
								message: "Please select your direct superior"
							}]
						},
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
						},
						{
							template: "<span>Remarks by User : <b>" +$scope.remaksuser+ "</b></span>"
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
						{dataField:'remarks',colSpan:2,editorType:"dxHtmlEditor",editorOptions: {height: 190,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}},

						]
					}, {
						template: "<span><font color='blue'>Approved Weekend / PH Coverage MTD : <b>" +$scope.data.mtd+"</b> day/s, YTD : <b>" +$scope.data.ytd+"</b> day/s</font></span> "
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
									var path = ($scope.mode=='report') ? "doreport" :"dayoff";
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
									$scope.dayoffApproval();							
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
										DevExpress.ui.dialog.alert("Your form is not complete or has invalid value, please recheck before submit","Error");
									}
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
	});
	var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('dodetail',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('dodetail',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.dateworked = $filter("date")(values.startDate, "yyyy-MM-dd HH:mm")
			values.reason = values.text
			values.startDate = $filter("date")(values.startDate, "yyyy-MM-dd HH:mm")
			values.endDate = $filter("date")(values.endDate, "yyyy-MM-dd HH:mm")
			values.dayoff_id=$scope.Requestid;
            CrudService.Create('dodetail',values).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}
				// $scope.scheduleComponent.refresh();
			});
		},
		update: function(key, values) {
			delete values.startdate;
			delete values.enddate;
			values.dateworked = $filter("date")(values.startDate, "yyyy-MM-dd HH:mm")
			values.reason = values.text
			// values.startDate = $filter("date")(values.startDate, "yyyy-MM-dd HH:mm")
			// values.endDate = $filter("date")(values.endDate, "yyyy-MM-dd HH:mm")
			// console.log(values)
            CrudService.Update('dodetail',key.id,values).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}
				// $scope.scheduleComponent.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('dodetail',key.id).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}
				// $scope.scheduleComponent.refresh();
			});
		}
    });
	var myData = new DevExpress.data.DataSource({
		store: myStore
    });
	var myStore2 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('doapp',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('doapp',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
			values.dayoff_id=$scope.Requestid;
            CrudService.Create('doapp',values).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}
				$scope.grid2Component.refresh();
			});
		},
		update: function(key, values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
            CrudService.Update('doapp',key.id,values).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.dialog.alert(response.message,"Error");
				}
				$scope.grid2Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('doapp',key.id).then(function (response) {
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
			return CrudService.GetById('dohist',$scope.Requestid);         		
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
		{ id:4, TabName : "Dayoff Schedule", title: 'Detail Dayoff Schedule', template: "tab4"   },
		// { id:1, TabName : "Dayoff Detail", title: 'Detail Dayoff Request', template: "tab1"   },
		{ id:2, TabName : "Approver List", title: 'Approver List', template: "tab2"   },
		{ id:3, TabName : "History Tracking", title: 'History Tracking', template: "tab3"   },
	];
	$scope.showHistory = true;
	$scope.appText = ["No","Yes"];
	$scope.loadPanelVisible = false;

	function showToast(event, value, type) {
		DevExpress.ui.notify(`${event} "${value}" task`, type, 800);
	}

	$scope.editing = {
		allowAdding: (($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
		allowDeleting: (($scope.mode=='approve') || ($scope.mode=='view')||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
		// allowUpdating: (($scope.mode=='view') ||($scope.mode=='report'))?(($rootScope.isAdmin)?true:false):true,
		allowUpdating: true,
		allowResizing: false,
		allowDragging: false,
	};

	$scope.schedulerOptions = {
		timeZone: 'Asia/Singapore',
		dataSource: myData,
		views: [
			{
				name: '3 Months', type: 'month', intervalCount: 3
			}
		],
		currentView: 'month',
		currentDate: new Date(),
		height: 600,
		bindingOptions: {
			editing: 'editing',
		},
		dataCellTemplate(itemData, itemIndex, itemElement) {
			const date = itemData.startDate;
			const isDisabled = isWeekend(date);
			// const element = $('<div />');
			const element = $(`<div>${itemData.text}</div>`);

			if (isDisabled) {
				element.addClass('disable-date');
			}
	  
			return itemElement.append(element);
		  },
	  
		  dateCellTemplate(itemData, itemIndex, itemElement) {
			const element = $(`<div>${itemData.text}</div>`);
	  
			if (isWeekend(itemData.date)) {
			  element.addClass('disable-date');
			}
	  
			return itemElement.append(element);
		  },
		onAppointmentAdded(e) {
			showToast('Added', e.appointmentData.text, 'success');
			e.component.option("dataSource",myData);
		},
		onAppointmentFormOpening(e) {
			const { form } = e;
			const startDate = new Date(e.appointmentData.startDate);
			if (!isValidAppointmentDate(startDate)) {
				e.cancel = true;
				notifyDisableDate();
			} else if($scope.mode=='view') {
				e.cancel = true;
				// notifyDisableDate();
				DevExpress.ui.notify({ message: 'Cannot Editing This Event', width: 500, shading: true, position:'middle' }, 'warning', 3000, );
			}
			form.option('items', [{
				dataField: 'startDate',
				editorType: 'dxDateBox',
				editorOptions: {
				  min:Date.now(),
				  width: '100%',
				  type: 'date',
				  onValueChanged(args) {
					const valdate = args.value;
					console.log(valdate);
					// form.updateData('endDate', new Date(valdate.getTime() + 60 * 1000 * movieInfo.duration));
					form.updateData('endDate', new Date(valdate).getTime()+ 60 * 1000);
				  },
				  readOnly: (($scope.mode=='approve') ||($scope.mode=='view') ||($scope.mode=='report'))?true:false,
				},
			  }, {
				name: 'endDate',
				dataField: 'endDate',
				editorType: 'dxDateBox',
				editorOptions: {
				  width: '100%',
				  type: 'date',
				  readOnly: true,
				},
			  },
			  {
				label: {
					text: 'Reason',
				},
				name: 'text',
				dataField: 'text',
				editorOptions: {
					readOnly: (($scope.mode=='approve') ||($scope.mode=='view') ||($scope.mode=='report'))?true:false,
				}

			  },
			  {
				label: {
					text: 'Remarks',
				},
				name: 'remarks',
				dataField: 'remarks',
				editorOptions: {
					readOnly: (($scope.mode=='approve') ||($scope.mode=='view') ||($scope.mode=='report'))?true:false,
				}

			  },
			  {
				dataField: 'isapproved',
				editorType: 'dxRadioGroup',
				visible: (($scope.mode=='approve') ||($scope.mode=='view') ||($scope.mode=='report'))?true:false,
				editorOptions: {
				  readOnly: (($scope.mode=='report'))?(($rootScope.isAdmin)?false:true):false,
				  dataSource: [0, 1],
				  itemTemplate(itemData) {
					  console.log(itemData);
					// (itemData == 0) ? 'No' : 'Yes';
					if(itemData == 0) {
						itemData = 'No';
					} else {
						itemData = 'Yes';
					}

					return `${itemData}`;
				  },
				},
			  },
			  ]);
			// applyDisableDatesToDateEditors(e.form);
		},
		onAppointmentAdding(e) {
			if (!isValidAppointment(e.component, e.appointmentData)) {
				e.cancel = true;
				notifyDisableDate();
			}
		},
		onAppointmentUpdated(e) {
			showToast('Updated', e.appointmentData.text, 'info');
			e.component.option("dataSource",myData);
		},
		onAppointmentUpdating(e) {
			if (!isValidAppointment(e.component, e.newData)) {
				e.cancel = true;
				notifyDisableDate();
			}
		},
		onAppointmentDeleted(e) {
			showToast('Deleted', e.appointmentData.text, 'warning');
			e.component.option("dataSource",myData);
		},
		onInitialized: function (e) {
            $scope.schedulerComponent = e.component;
        },
	}

	function notifyDisableDate() {
		DevExpress.ui.notify('Cannot create or move an appointment/event to disabled time/date regions.', 'warning', 1000);
	}

	function isValidAppointment(component, appointmentData) {
		const startDate = new Date(appointmentData.startDate);
		const endDate = new Date(appointmentData.endDate);
		const cellDuration = component.option('cellDuration');
		return isValidAppointmentInterval(startDate, endDate, cellDuration);
	  }
	  
	function isValidAppointmentInterval(startDate, endDate, cellDuration) {
		const edgeEndDate = new Date(endDate.getTime() - 1);
		
		if (!isValidAppointmentDate(edgeEndDate)) {
			return false;
		}
		
		const durationInMs = cellDuration * 60 * 1000;
		const date = startDate;
		while (date <= endDate) {
			if (!isValidAppointmentDate(date)) {
			return false;
			}
			const newDateTime = date.getTime() + durationInMs - 1;
			date.setTime(newDateTime);
		}
		
		return true;
	}

	function isValidAppointmentDate(date) {
		return !isWeekend(date);
	}
	
	function isWeekend(date) {
		const day = date.getDay();

		return day === 0 || day === 1 || day === 2 || day === 3 || day === 4 || day === 5;

	}

	$scope.plusButton = {
		icon: 'refresh',
		onClick() {
			DevExpress.ui.notify('Refresh Data');
			$scope.schedulerComponent.option("dataSource", myData);

		},
	};

	$scope.grid1Options = {
		dataSource: myData,
		allowColumnResizing: true,
		wordWrapEnabled: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
			{dataField:'dateworked',width:100,caption: "Work Date",dataType:"date", format: 'dd/MM/yyyy',editorType: "dxDateBox",editorOptions: {displayFormat:"dd/MM/yyyy",min:Date.now(),disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},
			{dataField:'reason',width:250,dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},
			//{dataField:'achievement',dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
			{dataField:'remarks',width:250,encodeHtml: false,dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},
			{dataField:'isapproved',width:150,caption: "Approved",dataType: "boolean", showEditorAlways: true ,visible: (($scope.mode=='approve') ||($scope.mode=='view') ||($scope.mode=='report'))?true:false },
			{dataField:'isused',width:150,caption: "Used",dataType: "boolean", showEditorAlways: true ,visible: (($scope.mode=='view')||($scope.mode=='report'))?true:false},
			
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
            byKey: function(key) {
                CrudService.GetById('dodetail',encodeURIComponent(key)).then(function (response) {
                    return response;
                });
            },
            insert: function(values) {
                values.dateworked = $filter("date")(values.dateworked, "yyyy-MM-dd HH:mm")
                values.dayoff_id=$scope.Requestid;
                CrudService.Create('dodetail',values).then(function (response) {
                    if(response.status=="error"){
                        DevExpress.ui.dialog.alert(response.message,"Error");
                    }
                    $scope.grid1Component.refresh();
                });
            },
            update: function(key, values) {
                values.dateworked = $filter("date")(values.dateworked, "yyyy-MM-dd HH:mm")
                CrudService.Update('dodetail',key.id,values).then(function (response) {
                    if(response.status=="error"){
                        DevExpress.ui.dialog.alert(response.message,"Error");
                    }
                    $scope.grid1Component.refresh();
                });
            },
            remove: function(key) {
                CrudService.Delete('dodetail',key.id).then(function (response) {
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
                return CrudService.GetById('doapp',$scope.Requestid);         		
            },
            byKey: function(key) {
                CrudService.GetById('doapp',encodeURIComponent(key)).then(function (response) {
                    return response;
                });
            },
            insert: function(values) {
                values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
                values.dayoff_id=$scope.Requestid;
                CrudService.Create('doapp',values).then(function (response) {
                    if(response.status=="error"){
                        DevExpress.ui.dialog.alert(response.message,"Error");
                    }
                    $scope.grid2Component.refresh();
                });
            },
            update: function(key, values) {
                values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
                CrudService.Update('doapp',key.id,values).then(function (response) {
                    if(response.status=="error"){
                        DevExpress.ui.dialog.alert(response.message,"Error");
                    }
                    $scope.grid2Component.refresh();
                });
            },
            remove: function(key) {
                CrudService.Delete('doapp',key.id).then(function (response) {
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
                return CrudService.GetById('dohist',$scope.Requestid);         		
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
        var myStoreScheduler = new DevExpress.data.CustomStore({
            load: function() {			
                $scope.isLoaded =true;
                // return CrudService.GetAll('dobalance');   
                // $scope.isLoaded =true;
                return CrudService.GetAll('dobalance').then(function (response) {
                    if(response.status=="error"){
                        DevExpress.ui.notify(response.message,"error");
                    }else{
                        return response;
                    }
                });         		
            },
            // byKey: function(key) {
                //
            // },
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
        var myDataScheduler = new DevExpress.data.DataSource({
            store: myStoreScheduler
        });
        $scope.tabs = [
            // { id:4, TabName : "Dayoff Schedule", title: 'Detail Dayoff Schedule', template: "tab4"   },
            { id:1, TabName : "Dayoff Detail", title: 'Detail Dayoff Request', template: "tab1"   },
            { id:2, TabName : "Approver List", title: 'Approver List', template: "tab2"   },
            { id:3, TabName : "History Tracking", title: 'History Tracking', template: "tab3"   },
        ];
        $scope.showHistory = true;
        $scope.appText = ["No","Yes"];
        $scope.loadPanelVisible = false;
    
        // const url = 'https://js.devexpress.com/Demos/Mvc/api/SchedulerData';
    
        $scope.schedulerOptions = {
            // timeZone: 'America/Los_Angeles',
            timeZone: 'Asia/Singapore',
            dataSources: myDataScheduler,
            // views: [
            // 	{
            // 		name: '3 Months', type: 'month', intervalCount: 3
            // 	}
            // ],
            views: ['month'],
            currentView: 'month',
            currentDate: new Date(),
            // currentDate: new Date(2021, 2, 28),
            // startDayHour: 9,
            height: 600,
            onInitialized:function (e){
                $scope.scheduleComponent = e.component;
            },
            onEditorPreparing: function (e) {  
                $scope.scheduleComponent = e.component;
            },
            onEditorPreparing: function (e) {  
                $scope.scheduleComponent = e.component;
            },
            onToolbarPreparing: function(e) {   
                e.toolbarOptions.items.unshift({						
                    location: "after",
                    widget: "dxButton",
                    options: {
                        hint: "Refresh Data",
                        icon: "refresh",
                        onClick: function() {
                            $scope.scheduleComponent.refresh();
                        }
                    }
                });
            },
        }
    
        // $scope.schedulerOptions = {
        // 	timeZone: 'America/Los_Angeles',
        // 	dataSource: DevExpress.data.AspNet.createStore({
        // 	key: 'AppointmentId',
        // 	loadUrl: `${url}/Get`,
        // 	insertUrl: `${url}/Post`,
        // 	updateUrl: `${url}/Put`,
        // 	deleteUrl: `${url}/Delete`,
        // 	onBeforeSend(method, ajaxOptions) {
        // 		ajaxOptions.xhrFields = { withCredentials: true };
        // 	},
        // 	}),
        // 	remoteFiltering: true,
        // 	dateSerializationFormat: 'yyyy-MM-ddTHH:mm:ssZ',
        // 	views: ['day', 'workWeek', 'month'],
        // 	currentView: 'day',
        // 	currentDate: new Date(2021, 3, 27),
        // 	startDayHour: 9,
        // 	endDayHour: 19,
        // 	height: 600,
        // 	textExpr: 'Text',
        // 	startDateExpr: 'StartDate',
        // 	endDateExpr: 'EndDate',
        // 	allDayExpr: 'AllDay',
        // 	recurrenceRuleExpr: 'RecurrenceRule',
        // 	recurrenceExceptionExpr: 'RecurrenceException',
        // }
    
        $scope.grid1Options = {
            dataSource: myData,
            allowColumnResizing: true,
            wordWrapEnabled: true,
            columnResizingMode : "widget",
            columnMinWidth: 50,
            columnAutoWidth: true,
            columns: [
                {dataField:'dateworked',width:100,caption: "Work Date",dataType:"date", format: 'dd/MM/yyyy',editorType: "dxDateBox",editorOptions: {displayFormat:"dd/MM/yyyy",min:Date.now(),disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},
                {dataField:'reason',width:250,dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},
                //{dataField:'achievement',dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view'))?true:false}},
                {dataField:'remarks',width:250,encodeHtml: false,dataType: "string",editorOptions: {disabled:(($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false}},
                {dataField:'isapproved',width:150,caption: "Approved",dataType: "boolean", showEditorAlways: true ,visible: (($scope.mode=='approve') ||($scope.mode=='view') ||($scope.mode=='report'))?true:false },
                {dataField:'isused',width:150,caption: "Used",dataType: "boolean", showEditorAlways: true ,visible: (($scope.mode=='view')||($scope.mode=='report'))?true:false},
                
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
                $scope.grid1Component = e.component;
            },
            onEditorPreparing: function (e) {  
                $scope.grid1Component = e.component;
            },
            onEditorPreparing: function (e) {  
                $scope.grid1Component = e.component;
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
                    criteria = {module:'Dayoff',mode:$scope.mode};
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
                allowUpdating: ($rootScope.isAdmin)?true:false,
                allowAdding:($rootScope.isAdmin)?true:false,
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
            //console.log($scope.formInstance.option("formData").approvalstatus);
            if($scope.formInstance.option("formData").approvalstatus==""){
                DevExpress.ui.dialog.alert("Please select approval action","Error");
            }else if($scope.formInstance.option("formData").approvalstatus==3){
                var data = $scope.formInstance.option("formData");
                var date = new Date();
                var d= $filter("date")(date, "yyyy-MM-dd HH:mm")
                data.approvaldate = d;
                data.mode="approve";
                delete data.createddate;
                delete data.datework;
                delete data.employee_id;
                delete data.requeststatus;
                delete data.depthead;
                delete data.fullname;
                delete data.department;
                CrudService.Update('doapp',data.id,data).then(function (response) {
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
                        $location.path( "/doapproval" );
                    }
                    
                });
            }else{
                criteria = {status:'approver',dayoff_id:$scope.Requestid};
                CrudService.FindData('doapp',criteria).then(function (response){
                    if(response.jml>0){
                        var data = $scope.formInstance.option("formData");
                        var date = new Date();
                        var d= $filter("date")(date, "yyyy-MM-dd HH:mm")
                        data.approvaldate = d;
                        data.mode="approve";
                        delete data.createddate;
                        delete data.datework;
                        delete data.employee_id;
                        delete data.requeststatus;
                        delete data.depthead;
                        delete data.fullname;
                        delete data.department;
                        CrudService.Update('doapp',data.id,data).then(function (response) {
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
                                $location.path( "/doapproval" );
                            }
                            
                        });
                    }else{
                        DevExpress.ui.dialog.alert("Please add person to do next approval/verification in Approver List tab","Error");
                    }
                });
            }
            
        }
        
        $scope.saveDraft = function(e){
            var data = $scope.formInstance.option("formData");
            delete data.fullname;
            delete data.department;
            CrudService.Update('dayoff',data.id,data).then(function (response) {
                if(response.status=="error"){
                     DevExpress.ui.dialog.alert(response.message,"error");
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
                    $location.path( "/dayoff" );
                }
                
            });
        }
        $scope.onFormSubmit = function(e) {
            e.preventDefault();
            criteria = {status:'waiting',username:$scope.formInstance.option("formData").employee_id,id:$scope.Requestid};
            CrudService.FindData('dayoffbyemp',criteria).then(function (response){
                if(response.jml>0){
                    DevExpress.ui.dialog.alert("Cannot add more request, Selected employee still have waiting approval request","Error");
                }else{
                    criteria = {status:'approver',dayoff_id:$scope.Requestid};
                    CrudService.FindData('doapp',criteria).then(function (response){
                        if(response.jml>0){
                            criteria = {status:'approver',dayoff_id:$scope.Requestid};
                            CrudService.FindData('dodetail',criteria).then(function (response){
                                if(response.jml>0){
                                    var data = $scope.formInstance.option("formData");
                                    data.requeststatus = 1;
                                    delete data.approvalstatus;
                                    delete data.mtd;
                                    delete data.ytd;
                                    CrudService.Update('dayoff',data.id,data).then(function (response) {
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
                                            $location.path( "/dayoff" );
                                        }
                                        
                                    });
                                }else{
                                    DevExpress.ui.dialog.alert("Please add detail of the request","Error");
                                }
                            })
                        }else{
                            DevExpress.ui.dialog.alert("Please add person to do approval/verification in Approver List tab","Error");
                        }			
                    })
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