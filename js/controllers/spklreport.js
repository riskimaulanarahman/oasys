(function (app) {
app.register.controller('spklreportCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	if ((!$rootScope.isAdmin) &&  (!$rootScope.viewSPKL)){
		$location.path( "/" );
	}
    //start filter date
    var date = new Date();
    var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    var endDay = new Date(date.getFullYear(), date.getMonth()+1, 0);
    $scope.filterData = {startDate : $filter("date")(firstDay, 'yyyy-MM-dd'), endDate : $filter("date")(endDay, 'yyyy-MM-dd') };
    $scope.formOptions = {
        readOnly: false,
        showColonAfterLabel: true,
        labelLocation: "left",
        minColWidth: 200,
        colCount: 3,
        showValidationSummary: true,
        onInitialized: function (e) {
            $scope.formFilterInstance = e.component;
        },
        items: [{
            dataField: "startDate",
            editorType: "dxDateBox",
            displayFormat: "yyyy-mm-dd",
            validationRules: [{
                type: "required",
                message: "Date is required"
            }]
        }, {
            dataField: "endDate",
            editorType: "dxDateBox",
            displayFormat: "yyyy-mm-dd",
            validationRules: [{
                type: "required",
                message: "Date is required"
            }]
        }],
        bindingOptions: {
            'formData': 'filterData',
        }
    }
    $scope.showForm = true;

    function initController() {
        $scope.dataGrid.refresh();
    }
    //end filter date
	var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			//start filter date
            criteria = {filter:'all',startDate:$filter("date")($scope.filterData.startDate, 'yyyy-MM-dd'),endDate:$filter("date")($scope.filterData.endDate, 'yyyy-MM-dd')};
            //end filter date
            return CrudService.FindData('spklapp',criteria).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.notify(response.message,"error");
				}else{
					return response;
				}
			});            		
		},
	 
		byKey: function(key) {
            CrudService.GetById('spklapp',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
            
		},
		update: function(key, values) {
            
		},
		remove: function(key) {
			
		}
    });
    //start filter date
	$scope.$on("initSPKL", function(event, name) {
		initController();
    });
    $rootScope.$on("dataRefreshing", function(event, data) {
        initController();
    });
    //end filter date
	var myData = new DevExpress.data.DataSource({
		store: myStore
    });
	function moveEditColumnToLeft(dataGrid) {
		dataGrid.columnOption("command:edit", { 
			visibleIndex: -1,
			width: 80 
		});
    }
    $scope.dataGridOptions = {
        dataSource: myData,
        showColumnLines: true,
        showRowLines: true,
        rowAlternationEnabled: true,
        allowColumnResizing: true,
        columnResizingMode: "widget",
        columnAutoWidth: false,
        showBorders: true,
        height: 600,
        headerFilter: {
            visible: true
        },
        columns: [{
                    caption: "Detail",
                    fixed: true,
                    fixedPosition: "left",
                    width: 120,
                    allowFiltering: false,
                    allowSorting: false,
                    formItem: { visible: false},
                    cellTemplate: function (container, options) {
                        $('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-detailslayout  btn-pill btn-shadow btn btn-primary')
                            .text('')
                            .on('dxclick', function () {
								if ((options.data.tmsreqstatus==0)|| (options.data.tmsreqstatus=='0')){
									$scope.loadSPKL(options.data,'report',false);
								}else{
									$scope.loadSPKLTMS(options.data,'report',false);
								}
                            })
                            .appendTo(container);
							if($rootScope.isAdmin && (options.data.tmsreqstatus=='3')){
							$('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-repeat  btn-pill btn-shadow btn btn-success')
								.text('')
								.on('dxclick', function () {
									window.open('api/apispkltmspdf?id='+options.data.id, '_blank');
								})
								.appendTo(container);
						}
                    }
                }
                ,{caption: '#',fixed: true,fixedPosition: "left",formItem: { visible: false},width: 40,
					cellTemplate: function(container, options) {
						container.text(options.rowIndex +1);
					}
                },
				{dataField:'createddate',caption:"Creation Date",dataType:"date", format:"dd/MM/yyyy",width: 90},
				{dataField:'fullname',caption:"Request By",fixed: true, fixedPosition: "left"},
				{dataField:'spklstatus',caption:"SPKL Status", fixedPosition: "left"},
				{dataField:'otstatus',caption:"OT Status", fixedPosition: "left"},
				{dataField:'personholding',caption:"PIC", fixedPosition: "left"},
				{dataField:'requeststatus',caption:"SPKL Status",encodeHtml: false , fixedPosition: "left",
					customizeText: function (e) {
						var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-secondary'>Saved as Draft</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
						return rDesc[e.value];
					}},
				{dataField:'tmsreqstatus',caption:"OT Status",encodeHtml: false , fixedPosition: "left",
					customizeText: function (e) {
						var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-secondary'>Not yet Submitted</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
						return rDesc[e.value];
					}},
				{
							dataField: "approveddoc",
							caption:"SPKL Doc",
							width: 100,
							allowFiltering: false,
							allowSorting: false,
							formItem: { visible: false},
							cellTemplate: function (container, options) {
								
								if ((options.value!="") && (options.value)){
									$("<div />").dxButton({
										icon: 'exportpdf',
										stylingMode: "contained",
										type: "success",
										target : '_blank',
										width: 50,
										onClick: function (e) {
											window.open(options.value, '_blank');
										}
									}).appendTo(container);
								}
							}
						},
				{
							dataField: "approvedtmsdoc",
							caption:"OT Doc",
							width: 100,
							allowFiltering: false,
							allowSorting: false,
							formItem: { visible: false},
							cellTemplate: function (container, options) {
								
								if ((options.value!="") && (options.value)){
									$("<div />").dxButton({
										icon: 'exportpdf',
										stylingMode: "contained",
										type: "success",
										target : '_blank',
										width: 50,
										onClick: function (e) {
											window.open(options.value, '_blank');
										}
									}).appendTo(container);
								}
							}
						},
                ],	
        "export": {
            enabled: true,
            fileName: "ExportGrid",
            allowExportSelectedData: false
        },
		bindingOptions :{
			//"editing.allowUpdating": "allowEdit" ,
			//"editing.allowAdding": "allowAdd" ,
			//"editing.allowDeleting": "allowDel" ,
            //"columns[3].lookup.dataSource":"divDatasource"
        },
        masterDetail: {
            enabled: true,
            template: masterDetailTemplate,
        },
        columnChooser: {
            enabled: true
        },
        loadPanel: {
            enabled: true
        }, 
        columnFixing: { 
            enabled: true
        },
        paging: {
            pageSize: 10
        },
        pager: {
            showPageSizeSelector: false,
            allowedPageSizes: [5, 10, 20],
            showInfo: false
        },
        /*selection: {
            mode: "multiple"
        },*/
        editing: {
            useIcons:true,
            mode: "popup",
			allowUpdating: false,
			allowAdding:false,
			allowDeleting:false,
            //allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
            //allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
            form:{colCount: 1,
            },
            popup: {  
                title: "Form Data Designation",  
                showTitle: true  
            }, 
        },
        searchPanel: {
            visible: true,
            width: 240,
            placeholder: "Search..."
        },
        scrolling: {
            mode: "infinite"
        },
        onContentReady: function(e){
            moveEditColumnToLeft(e.component);
        },
        onEditingStart: function(e) {
            e.component.columnOption("id", "allowEditing", false);
        },
        onEditorPreparing: function (e) { 
            $scope.formComponent = e.component;
			if(e.parentType === "dataRow" && e.dataField === "division_id") {
                e.editorOptions.disabled = (typeof e.row.data.department_id !== "number");
            }
			if(e.parentType === "dataRow" && e.dataField === "designation_id") {
                e.editorOptions.disabled = (typeof e.row.data.division_id !== "number");
            }
        },
        onEditorPrepared: function(e) {
        },
        onInitNewRow: function (e) {
            e.component.columnOption("id", "allowEditing", false);
        },
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
            $scope.dataGrid = e.component;
    
            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        $scope.dataGrid.refresh();
                    }
                }
            });
        },
        onContextMenuPreparing: function (e) {
            var dataGrid = e.component;
        } ,
        onInitialized: function(e) {
            $scope.gridInstance = e.component;
            $scope.ds = e.component.getDataSource();
        },                             
    }; 

    function masterDetailTemplate(_, masterDetailOptions) {
        return $("<div>").dxTabPanel({
          items: [
            {
              title: "Detail SPKL/Employee List",
              template: detail1(masterDetailOptions.data),
            },
            {
              title: "Approver list",
              template: approverlist(masterDetailOptions.data),
            },
            {
              title: "History Tracking",
              template: history(masterDetailOptions.data),
            },
          ],
        });
      }

      $scope.empDataSource = {
        store: new DevExpress.data.CustomStore({
          key: "id",
          loadMode: "raw",
          load: function () {
            criteria = {
              module: 'SPKL',
              mode: $scope.mode
            };
            return CrudService.FindData('appr', criteria);
          },
        }),
        sort: "id"
      }

      $scope.allDeptEmpDataSource = {
          store: new DevExpress.data.CustomStore({
              key: "id",
              loadMode: "raw",
              load: function () {
                  criteria = {
                      filter: 'byreport',
                  };
                  return CrudService.FindData('emp', criteria);
              }
          }),
          sort: "id"
      }

    //   CrudService.GetAll('emp').then(function (resp) {
    //     $scope.allDeptEmpDataSource = resp;
    //   });

      CrudService.GetAll('approvaltype').then(function (resp) {
        $scope.apptypeDatasource = resp;
      });

      function detail1(masterDetailData) {
        return function () {
          return $("<div>").dxDataGrid({
            dataSource: new DevExpress.data.DataSource({
              store: new DevExpress.data.CustomStore({
                key: "spkl_id",
                load: function () {
                  return CrudService.GetById("spkldetail", masterDetailData.id);
                },
              }),
            }),
            allowColumnResizing: true,
            columnResizingMode: "widget",
            columnAutoWidth: true,
            showBorders: true,
            paging: {
              pageSize: 5,
            },
            pager: {
              showPageSizeSelector: false,
              allowedPageSizes: [5, 10, 20],
              showInfo: false,
            },
            showBorders: true,
            columns: [
                {
                    dataField: 'isapproved',
                    width: 110,
                    caption: "Approved",
                    dataType: "boolean",
                    showEditorAlways: true,
                }, {
                    dataField: "employee_id",
                    caption: "Employee",
                    width: 200,
                    allowSorting: false,
                    visible: true,
                    lookup: {
                        dataSource: $scope.allDeptEmpDataSource,
                        valueExpr: "id",
                        displayExpr: "fullname"
                    },
                    validationRules: [{
                        type: "required"
                    }],
                    editCellTemplate: "dropDownBoxEditorTemplate"
                }, {
                    dataField: 'sapid',
                    width: 90,
                    dataType: "string",
                    editorOptions: {
                        disabled: true
                    },
                    formItem: {
                        visible: false
                    }
                }, {
                    dataField: 'position',
                    caption: "Position",
                    width: 150,
                    dataType: "string",
                    editorOptions: {
                        disabled: true
                    },
                    formItem: {
                        visible: false
                    }
                }, {
                    dataField: 'estimatenormalhours',
                    validationRules: [{
                        type: "required"
                    }],
                    caption: "Normal Hours Estimate (hrs)",
                    width: 80,
                    dataType: "number",
                    editorOptions: {
                        disabled: (($scope.mode == 'approve') || ($scope.mode == 'view') || ($scope.mode == 'report')) ? true : false
                    }
                }, {
                    dataField: 'estimateovertimehours',
                    validationRules: [{
                        type: "required"
                    }],
                    caption: "Overtime Hours Estimate (hrs)",
                    width: 80,
                    dataType: "number",
                    editorOptions: {
                        disabled: (($scope.mode == 'approve') || ($scope.mode == 'view') || ($scope.mode == 'report')) ? true : false
                    }
                }, {
                    dataField: 'target',
                    validationRules: [{
                        type: "required"
                    }],
                    caption: "Target Work",
                    encodeHtml: false,
                    // width: 250,
                    dataType: "string",
                    editorOptions: {
                        disabled: (($scope.mode == 'approve') || ($scope.mode == 'view') || ($scope.mode == 'report')) ? true : false
                    }
                },
				
            ],
          });
        };
      }

      // start approver list and history

      function approverlist(masterDetailData) {
        return function () {
          return $("<div>").dxDataGrid({
            dataSource: new DevExpress.data.DataSource({
              store: new DevExpress.data.CustomStore({
                key: "spkl_id",
                load: function () {
                  return CrudService.GetById(
                    "spklapp",
                    masterDetailData.id
                  );
                },
              }),
            }),
            allowColumnResizing: true,
            columnResizingMode: "widget",
            columnAutoWidth: true,
            showBorders: true,
            paging: {
              pageSize: 5,
            },
            pager: {
              showPageSizeSelector: false,
              allowedPageSizes: [5, 10, 20],
              showInfo: false,
            },
            showBorders: true,
            columns: [{
              dataField: "approver_id",
              caption: "Employee",
              width: 200,
              allowSorting: false,
              lookup: {
                dataSource: $scope.empDataSource,
                valueExpr: "id",
                displayExpr: "fullname"
              },
              editCellTemplate: "dropDownBoxEditorTemplatex"
            }, {
              dataField: 'approvaldate',
              width: 150,
              dataType: "date",
              format: "dd/MM/yyyy",
              allowEditing: false,
            }, {
              dataField: 'approvaltype',
              width: 200,
              allowEditing: false,
              lookup: {
                dataSource: $scope.apptypeDatasource,
                valueExpr: 'id',
                displayExpr: 'approvaltype'
              }
            }, {
              dataField: 'approvalstatus',
              width: 150,
              allowEditing: false,
              encodeHtml: false,
              customizeText: function (e) {
                var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>", "<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>", "<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>", "<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>", ""];
                return rDesc[e.value];
              }
            }, ],
          });
        };
      }

      function history(masterDetailData) {
        return function () {
          return $("<div>").dxDataGrid({
            dataSource: new DevExpress.data.DataSource({
              store: new DevExpress.data.CustomStore({
                key: "spkl_id",
                load: function () {
                  return CrudService.GetById(
                    "spklhist",
                    masterDetailData.id
                  );
                },
              }),
            }),
            allowColumnResizing: true,
            columnResizingMode: "widget",
            columnAutoWidth: true,
            showBorders: true,
            paging: {
              pageSize: 5,
            },
            pager: {
              showPageSizeSelector: false,
              allowedPageSizes: [5, 10, 20],
              showInfo: false,
            },
            showBorders: true,
            columns: [{
              dataField: 'date',
              width: 150,
              dataType: "date",
              format: 'dd/MM/yyyy HH:mm:ss'
            }, {
              dataField: 'fullname',
              width: 200,
              caption: "Employee",
              allowEditing: false,
              dataType: "string"
            }, {
              dataField: 'approvaltype',
              width: 150,
              caption: "Role",
              allowEditing: false,
              dataType: "string"
            }, {
              dataField: 'actiontype',
              width: 150,
              caption: "Action",
              allowEditing: false,
              encodeHtml: false,
              customizeText: function (e) {
                var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-default'>Created</span>", "<span class='mb-2 mr-2 badge badge-pill badge-default'>Save as Draft</span>", "<span class='mb-2 mr-2 badge badge-pill badge-primary'>Submitted</span>", "<span class='mb-2 mr-2 badge badge-pill badge-warning'>Ask Rework</span>", "<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>", "<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>", ""];
                return rDesc[e.value];
              }
            }, {
              dataField: 'remarks',
              encodeHtml: false
            }],
          });
        };
      }

      // end approver list and history

}]);
})(app || angular.module("kduApp"));