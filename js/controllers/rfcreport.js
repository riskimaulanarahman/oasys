(function (app) {
app.register.controller('rfcreportCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	if ((!$rootScope.isAdmin) &&  (!$rootScope.viewRFC)){
		$location.path( "/" );
	}
	$scope.formFilterInstance = [];
	var date = new Date();
	var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
	var endDay = new Date(date.getFullYear(), date.getMonth()+1, 0);
	$scope.filterData = {startDate : $filter("date")(firstDay, 'yyyy-MM-dd'), endDate : $filter("date")(endDay, 'yyyy-MM-dd') };
	$scope.formOptions = {
		readOnly: false,
		showColonAfterLabel: true,
		labelLocation: "left",
		minColWidth: 200,
		colCount:3,
		showValidationSummary: true,
		onInitialized: function(e) {
			$scope.formFilterInstance = e.component;
		},items: [{
                dataField: "startDate",
                editorType: "dxDateBox",
				displayFormat: "yyyy-mm-dd",
                validationRules: [{
                    type: "required",
                    message: "Date is required"
                }]
            },{
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
	function initController() {
		$scope.dataGrid.refresh();
	}
	$scope.showForm= true;
	var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			criteria = {filter:'all',startDate:$filter("date")($scope.filterData.startDate, 'yyyy-MM-dd'),endDate:$filter("date")($scope.filterData.endDate, 'yyyy-MM-dd')};
            return CrudService.FindData('rfcapp',criteria).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.notify(response.message,"error");
				}else{
					return response;
				}
			});            		
		},
	 
		byKey: function(key) {
            CrudService.GetById('rfcapp',encodeURIComponent(key)).then(function (response) {
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
	$scope.$on("initRFC", function(event, name) {
		$scope.dataGrid.refresh();
    });
	$rootScope.$on("dataRefreshing", function(event, data) {
		initController();
    });
	var myData = new DevExpress.data.DataSource({
		store: myStore
    });
	function moveEditColumnToLeft(dataGrid) {
		dataGrid.columnOption("command:edit", { 
			visibleIndex: -1,
			width: 80 
		});
    }
	CrudService.GetAll('rfcactivity').then(function (resp) {
        $scope.activityDatasource=resp;
    });
	CrudService.GetAll('rfccontractor').then(function (resp) {
        $scope.contractorDatasource=resp;
    })
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
								$scope.loadRFC(options.data,'report',false);
                            })
                            .appendTo(container);
                    }
                }
                ,{caption: '#',fixed: true,fixedPosition: "left",formItem: { visible: false},width: 40,
					cellTemplate: function(container, options) {
						container.text(options.rowIndex +1);
					}
                },
				{dataField:'createddate',caption:"Creation Date",dataType:"date", format:"dd/MM/yyyy",width: 90},
				{dataField:'fullname',caption:"Request By",fixed: true, fixedPosition: "left"},
				{dataField:'laststatus',caption:"Last Status",fixed: true, fixedPosition: "left"},
				{dataField:'personholding',caption:"PIC",fixed: true, fixedPosition: "left"},
				{dataField:'requeststatus',encodeHtml: false ,fixed: true, fixedPosition: "left",
					customizeText: function (e) {
						var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-secondary'>Saved as Draft</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
						return rDesc[e.value];
					}},
				{dataField:'rfcno',caption:"RFC No",fixed: true, fixedPosition: "left"},
				{dataField:'activity_id',caption:"Activity",
					lookup: {
						dataSource: $scope.activityDatasource,
						valueExpr: "id",
						displayExpr: "activitydescr" 
					}},
				{dataField:'periodstart',caption:"Period Start",dataType:"date", format:"dd/MM/yyyy",width: 90},
				{dataField:'periodend',caption:"Period End",dataType:"date", format:"dd/MM/yyyy",width: 90},
				{dataField:'contractor_id',caption:"Contractor 1",
					lookup: {
						dataSource: $scope.contractorDatasource,
						valueExpr: "id",
						displayExpr: "contractorname" 
					}},
				{dataField:'contractor_id2',caption:"Contractor 2",
					lookup: {
						dataSource: $scope.contractorDatasource,
						valueExpr: "id",
						displayExpr: "contractorname" 
					}},
				{dataField:'companycode',caption:"BU"},
				{dataField:'ratetype',caption:"Rate Type"},
				{
							dataField: "approveddoc",
							caption:"Approval Doc",
							width: 100,
							allowFiltering: false,
							allowSorting: false,
							formItem: { visible: false},
							cellTemplate: function (container, options) {
								
								if ((options.value!="") && (options.value)){
									$("<div />").dxButton({
										icon: 'download',
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
            allowExportSelectedData: false,
			customizeExcelCell: function(options) {
                var gridCell = options.gridCell;
                if(!gridCell) {
                    return;
                }

                if(gridCell.rowType === 'data') {
                    if(gridCell.data.OrderDate < new Date(2014, 2, 3)) {
                        options.font.color = '#AAAAAA';
                    }
                    if((gridCell.column.dataField === 'requeststatus') ){
						if(options.value.includes("Approved")) {
							options.value = "APPROVED"
							options.backgroundColor = '#3ac47d';
						} else if(options.value.includes("Saved")) {
							options.value = "SAVED AS DRAFT"
							options.backgroundColor = '#6c757d';
						} else if(options.value.includes("Waiting")) {
							options.value = "WAITING APPROVAL"
							options.backgroundColor = '#3f6ad8';
						} else if(options.value.includes("Rejected")) {
							options.value = "REJECTED"
							options.backgroundColor = '#d92550';
						} else if(options.value.includes("Require")) {
							options.value = "REQUIRE REWORK"
							options.backgroundColor = '#f7b924';
						}else{
							options.value = ""
						}
					}
                }
			}
        },
		bindingOptions :{
			//"editing.allowUpdating": "allowEdit" ,
			//"editing.allowAdding": "allowAdd" ,
			//"editing.allowDeleting": "allowDel" ,
			"columns[8].lookup.dataSource":"activityDatasource",
			"columns[11].lookup.dataSource":"contractorDatasource",
			"columns[12].lookup.dataSource":"contractorDatasource",
            //"columns[3].lookup.dataSource":"divDatasource"
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
}]);
})(app || angular.module("kduApp"));