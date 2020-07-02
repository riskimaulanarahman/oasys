(function (app) {
app.register.controller('approverCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	if (!$rootScope.viewApprover){
		$location.path( "/" );
	}
    var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			CrudService.checkAccess('Approver',$rootScope.curUser.username).then(function (access) {
				$scope.allowEdit = access.allowedit;
				$scope.allowAdd = access.allowadd;
				$scope.allowDel = access.allowdelete;
			});
            return CrudService.GetAll('appr').then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.notify(response.message,"error");
				}else{
					return response;
				}
			});            		
		},
		byKey: function(key) {
            CrudService.GetById('appr',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
            CrudService.Create('appr',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		},
		update: function(key, values) {
            CrudService.Update('appr',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
            
		},
		remove: function(key) {
			CrudService.Delete('appr',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		}											 
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
    $scope.AppType = [{id:0,apptype:"Verification"},{id:1,apptype:"HOD Approval"},{id:2,apptype:"Final Approval"}];
    function moveEditColumnToLeft(dataGrid) {
		dataGrid.columnOption("command:edit", { 
			visibleIndex: -1,
			width: 80 
		});
    }
    CrudService.GetAll('approvaltype').then(function (resp) {
        $scope.apptypeDatasource=resp;
        //console.log($scope.roles);
    });
	CrudService.GetAll('module').then(function (module) {
        $scope.modules=module;
        //console.log($scope.roles);
    });
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
                        
                        $("<div />").dxButton({
                            icon: 'arrowright',
                            onClick: function (e) {
                                DevExpress.ui.notify("The Detail button was clicked");
                                
                            }
                        }).appendTo(container);
                        $('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-arrowright dx-button dx-button-success')
                            .text('')
                            .on('dxclick', function () {
                                DevExpress.ui.notify("Loading detail data for "+options.data.id,"info",600);
								//$scope.loadUser(options.data);
								//$scope.detailUser(options.data.id);
                            })
                            .appendTo(container);
                    }
                    },*/
                  {caption: '#',fixed: true,formItem: { visible: false},width: 40,
                        cellTemplate: function(container, options) {
                            container.text(options.rowIndex +1); //$scope.dataGrid.pageIndex() * $scope.dataGrid.pageSize() + options.rowIndex +1
                        }
                   },
					{dataField: "module",caption: "Module", lookup: { 
                            displayExpr: 'module',  
                            valueExpr: 'module',
                        },setCellValue: function(rowData, value) {
							rowData.module = value;
							rowData.approvaltype_id = null;
						},
					},                      
					{
						dataField: "employee_id",
						caption: "Employee",
						width: 150,
						allowSorting: false,
						lookup: {
							dataSource: $scope.empDataSource,
							valueExpr: "id",
							displayExpr: "fullname" },
						editCellTemplate: "dropDownBoxEditorTemplate" },
					
					{dataField:'approvaltype_id' ,
					caption: "ApprovalType",
						lookup: {  
							dataSource: function (options) {
								console.log(options.data);
								return {
									store: $scope.apptypeDatasource,
									filter: options.data ? ["module", "=", options.data.module] : null
								};
							},
							valueExpr: 'id',
							displayExpr: 'approvaltype'
						}},
					{dataField: "sequence",caption: "Sequence",  },  
					{dataField:'isfinal',dataType: "boolean", showEditorAlways: true }
                  
                  ],
        "export": {
            enabled: true,
            fileName: "ExportGrid",
            allowExportSelectedData: false
        },
        bindingOptions :{
			"editing.allowUpdating": "allowEdit" ,
			"editing.allowAdding": "allowAdd" ,
			//"editing.allowDeleting": "allowDel" ,
			"columns[1].lookup.dataSource":"modules"
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
            //e.component.columnOption("password", "allowEditing", false);
			 
			
        },
        onEditorPreparing: function (e) { 
            $scope.formComponent = e.component;
			if (e.dataField == "isfinal"){
                e.editorName = "dxSwitch";
                e.editorOptions.switchedOnText = "Yes";
                e.editorOptions.switchedOffText = "No";
            }  
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
})(app || angular.module("kduApp"));