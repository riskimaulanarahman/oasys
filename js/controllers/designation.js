app.controller('desCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
    var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			CrudService.checkAccess('Designation',$rootScope.curUser.username).then(function (access) {
				$scope.allowEdit = access.allowedit;
				$scope.allowAdd = access.allowadd;
				$scope.allowDel = access.allowdelete;
			});
            return CrudService.GetAll('des').then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.notify(response.message,"error");
				}else{
					return response;
				}
			});
		},
	 
		byKey: function(key) {
            CrudService.GetById('des',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
	 
		insert: function(values) {
            CrudService.Create('des',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		},
	 
		update: function(key, values) {
            CrudService.Update('des',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
            
		},
		remove: function(key) {
			CrudService.Delete('des',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		}											 
    });

    var myData = new DevExpress.data.DataSource({
		store: myStore
    });
   CrudService.GetAll('div').then(function (resp) {
        $scope.divDatasource=resp;
        //console.log($scope.roles);
    });
	CrudService.GetAll('dept').then(function (resp) {
        $scope.deptDatasource=resp;
        //console.log($scope.roles);
    });
    //$scope.myData = myData;
    function moveEditColumnToLeft(dataGrid) {
		dataGrid.columnOption("command:edit", { 
			visibleIndex: -1,
			width: 80 
		});
    }
	$scope.allowEdit = false;
	$scope.allowAdd = false;
	$scope.allowDel = false;
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
        columns: [
					/*{
                    caption: "Detail",
                    fixed: true,
                    fixedPosition: "right",
                    width: 60,
                    allowFiltering: false,
                    allowSorting: false,
                    formItem: { visible: false},
                    cellTemplate: function (container, options) {
                        $('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-arrowright dx-button dx-button-success')
                            .text('')
                            .on('dxclick', function () {
                                DevExpress.ui.notify("Loading detail data for "+options.data.designationname,"info",600);
                            })
                            .appendTo(container);
                    }
                    }
                  ,*/
				  {caption: '#',fixed: true,formItem: { visible: false},width: 40,
                        cellTemplate: function(container, options) {
                            container.text(options.rowIndex +1);
                        }
                   },{
						dataField: "department_id",
						caption: "Department",
						setCellValue: function(rowData, value) {
							rowData.department_id = value;
							rowData.division_id = null;
						},
						lookup: {
							dataSource: $scope.deptDatasource,
							valueExpr: "id",
							displayExpr: "departmentname" 
						}		
					},{
						dataField: "division_id",
						caption: "Division",
						lookup: {
							dataSource: function (options) {
								return {
									store: $scope.divDatasource,
									filter: options.data ? ["department_id", "=", options.data.department_id] : null
								};
							},
							valueExpr: "id",
							displayExpr: "divisionname" 
						}		
					},
                  {dataField:'designationname',caption:'Designation'}
                  ],
        "export": {
            enabled: true,
            fileName: "ExportGrid",
            allowExportSelectedData: false
        },
		bindingOptions :{
			"editing.allowUpdating": "allowEdit" ,
			"editing.allowAdding": "allowAdd" ,
			"columns[1].lookup.dataSource":"deptDatasource",
			//"editing.allowDeleting": "allowDel" ,
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
        selection: {
            mode: "multiple"
        },
        editing: {
            useIcons:true,
            mode: "row",
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
            },{
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Delete Data",
                    bindingOptions :{
                        disabled:"disabled"
                    },
                    icon: "trash",
                    onClick: function() {
                        if (!$scope.allowDel){
                            DevExpress.ui.notify("You don't have authority to delete data","error");
                        } else{
                            var result = DevExpress.ui.dialog.confirm("Are you sure you want to delete selected?", "Delete row");
                            result.done(function (dialogResult) {
                                if (dialogResult){
                                    $.each($scope.dataGrid.getSelectedRowsData(), function() {
                                        myStore.remove(this);										
                                    });
                                    $scope.dataGrid.refresh();
                                }
                            });
                        }
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