app.controller('rfcapprovalCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			criteria = ($scope.Filter)?{pending:'true'}:{filter:'all'};
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
        columnAutoWidth: true,
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
						var icon = ($scope.Filter)?'dx-icon-todo':'dx-icon-detailslayout';
                        $('<div style="padding:2px 15px 2px 15px;"/>').addClass(icon+'  btn-pill btn-shadow btn btn-primary')
                            .text('')
                            .on('dxclick', function () {
                                DevExpress.ui.notify("Loading detail data for "+options.data.requestdate,"info",600);
								var  mode=($scope.Filter)?'approve':'view';
								$scope.loadRFC(options.data,mode,$scope.Filter);
                            })
                            .appendTo(container);
                    }
                }
                ,{caption: '#',fixed: true,fixedPosition: "left",formItem: { visible: false},width: 40,
					cellTemplate: function(container, options) {
						container.text(options.rowIndex +1);
					}
                },
				{dataField:'fullname',caption:"Request By",fixed: true, fixedPosition: "left"},
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
				{dataField:'oldcontractno',caption:"Old Contract No"},
				{dataField:'ratetype',caption:"Rate Type"},
				{dataField:'paymentterm',caption:"Payment Term"},
				{dataField:'remarks',encodeHtml: false },
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
            allowExportSelectedData: false
        },
		bindingOptions :{
			//"editing.allowUpdating": "allowEdit" ,
			//"editing.allowAdding": "allowAdd" ,
			//"editing.allowDeleting": "allowDel" ,
			"columns[5].lookup.dataSource":"activityDatasource",
			"columns[13].lookup.dataSource":"contractorDatasource",
			"columns[14].lookup.dataSource":"contractorDatasource",
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