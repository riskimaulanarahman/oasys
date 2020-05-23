app.controller('accmanagerCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService)  {
    $scope.ds={};
    $scope.test=[];
	$scope.formFilterInstance = [];
	$scope.filterData = { };
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
			dataField: "selectEmployee",
			editorType: "dxSelectBox",
			editorOptions: { 
				items: $scope.employee,
				valueExpr: 'id',
				displayExpr: 'fullname',
				searchEnabled: true,  
				showClearButton: true,
			}
		},{
			dataField: "selectModule",
			editorType: "dxSelectBox",
			editorOptions: { 
				items: $scope.modules,
				valueExpr: 'id',
				displayExpr: 'module',
				searchEnabled: true,  
				showClearButton: true,
			}
		}],
		bindingOptions: {
			 'formData': 'filterData',
			"items[0].editorOptions.dataSource": "employee",
			"items[1].editorOptions.dataSource": "modules"			 
		}
	}
    var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
            return CrudService.GetAll('accmanager').then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.notify(response.message,"error");
				}else{
					return response;
				}
			});
		},
	 
		byKey: function(key) {
            CrudService.GetById('accmanager',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
	 
		insert: function(values) {
            CrudService.Create('accmanager',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		},
	 
		update: function(key, values) {
            CrudService.Update('accmanager',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
            
		},
		remove: function(key) {
			CrudService.Delete('accmanager',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		}											 
    });
    CrudService.GetAll('module').then(function (module) {
        $scope.modules=module;
        //console.log($scope.roles);
    });
	CrudService.GetAll('emp').then(function (employee) {
        $scope.employee=employee;
        //console.log($scope.roles);
    });
    $scope.empDataSource = {
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
                return CrudService.GetAll('emp');
            },
            // byKey:function(key){
                // CrudService.GetById('employee',encodeURIComponent(key)).then(function (emp) {
                    // return emp;
                // });
            // }
        }),
        sort: "id"
    }
    var myData = new DevExpress.data.DataSource({
		store: myStore
    });
    $rootScope.$on("loginChanged", function(event, islogin) {
        $rootScope.isLogin = islogin;
        if($rootScope.isLogin){
            initController();
        } 
    });
	$rootScope.$on("dataRefreshing", function(event, data) {
		initController();
    });
    if($rootScope.isLogin){
        initController();
    } 
    // $scope.detailUser = function(id) {
		// $scope.$emit('detailData', id);
	// };
    $scope.showForm= true;
    $scope.disabled= true;
    function initController() {
        if (typeof $scope.dataGrid !== 'undefined'){
			console.log($scope.filterData.selectModule+':'+$scope.filterData.selectEmployee)
			if((typeof $scope.filterData.selectEmployee !== 'undefined') &&(typeof $scope.filterData.selectModule !== 'undefined') && ($scope.filterData.selectModule !== null) && ($scope.filterData.selectEmployee !== null)){
				$scope.dataGrid.filter([['employee_id','=', $scope.filterData.selectEmployee],'and',['module_id','=',  $scope.filterData.selectModule]]);
			}else if((typeof $scope.filterData.selectEmployee !== 'undefined') && ($scope.filterData.selectEmployee !== null)){
				$scope.dataGrid.filter(['employee_id','=', $scope.filterData.selectEmployee]);
			}else if((typeof $scope.filterData.selectModule !== 'undefined') && ($scope.filterData.selectModule !== null)){
				$scope.dataGrid.filter(['module_id','=',  $scope.filterData.selectModule]);
			}else{
				$scope.dataGrid.clearFilter();
			}	
			$scope.dataGrid.refresh();
		}

    };
    $scope.myData = myData;
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
        columnAutoWidth: true,
        showBorders: true,
        height: 600,
        headerFilter: {
            visible: true
        },
        columns: [{
                    caption: "Detail",
                    fixed: true,
                    fixedPosition: "right",
                    width: 60,
                    allowFiltering: false,
                    allowSorting: false,
                    formItem: { visible: false},
                    cellTemplate: function (container, options) {
                        /*
                        $("<div />").dxButton({
                            icon: 'arrowright',
                            onClick: function (e) {
                                DevExpress.ui.notify("The Detail button was clicked");
                                
                            }
                        }).appendTo(container);*/
                        $('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-arrowright dx-button dx-button-success')
                            .text('')
                            .on('dxclick', function () {
                                DevExpress.ui.notify("Loading detail data for "+options.data.id,"info",600);
								//$scope.loadUser(options.data);
								//$scope.detailUser(options.data.id);
                            })
                            .appendTo(container);
                    }
                    }
                  ,{caption: '#',fixed: true,formItem: { visible: false},width: 40,
                        cellTemplate: function(container, options) {
                            container.text(options.rowIndex +1); //$scope.dataGrid.pageIndex() * $scope.dataGrid.pageSize() + options.rowIndex +1
                        }
                   }
                  
                  ,{dataField: "module_id",
                        caption: "Module",
                        lookup: {  
							// dataSource: function(options) {
								// return {
									// store: $scope.roles,
									// filter: options.data ? ["id", "=", options.data.id] : null
								// };
							// },
                            //dataSource:$scope.roles,  
                            displayExpr: 'module',  
                            valueExpr: 'id',
                        }
                        
                    },{
						dataField: "employee_id",
						caption: "Employee",
						width: 150,
						allowSorting: false,
						lookup: {
							dataSource: $scope.empDataSource,
							valueExpr: "id",
							displayExpr: "fullname" },
						editCellTemplate: "dropDownBoxEditorTemplate" },
					
					,{dataField:'allowadd',dataType: "boolean", showEditorAlways: true }
					,{dataField:'allowedit',dataType: "boolean", showEditorAlways: true }
					,{dataField:'allowdelete',dataType: "boolean", showEditorAlways: true }
					,{dataField:'allowview',dataType: "boolean", showEditorAlways: true }
                  
                  ],
        "export": {
            enabled: true,
            fileName: "ExportGrid",
            allowExportSelectedData: false
        },
        bindingOptions :{
            "columns[2].lookup.dataSource":"modules",
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
            mode: "cell",
            allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
            allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
            form:{colCount: 1,
                 customizeItem: function (item) {
                    if (item.dataField === "remarks" ) {
                        item.colSpan = 2;
                    }
                }},
                popup: {  
                    title: "Form Data User Access",  
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
            //e.component.columnOption("password", "allowEditing", false);
			 
			
        },
        onEditorPreparing: function (e) {
            // if (e.dataField == "ExecutiveSummary") {
                // e.editorName = "dxTextArea";
                // e.editorOptions.height = 200;
                // e.editorOptions.inputAttr = { "ck-editor": ""};
                // e.editorOptions.inputAttr = { id: "editor1"};
                $scope.elId = e.id;  
            // }
			if(e.dataField == "password" && e.parentType == "dataRow"){  
				e.editorOptions.disabled = !e.row.inserted;  
			} 
            if ((e.dataField == "allowadd") || (e.dataField == "allowedit")|| (e.dataField == "allowdelete")|| (e.dataField == "allowview")){
                e.editorName = "dxSwitch";
                e.editorOptions.switchedOnText = "Yes";
                e.editorOptions.switchedOffText = "No";
            }  
            $scope.formComponent = e.component;
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
                        if (!$rootScope.isAdmin){
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
    $scope.initDropDownBoxEditor = function(data) {
        return {
            dropDownOptions: { width: 500 },
            dataSource: $scope.empDataSource,
            value: data.value,
            valueExpr: "id",
            displayExpr: "fullname",
            contentTemplate: "contentTemplate"
        }
    }

    $scope.initContent = function(data, component) {
        return {
            dataSource: $scope.empDataSource,
            remoteOperations: true,
            columns: ["sapid","fullname", "department", "designation"],
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