(function (app) {
app.register.controller('userCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService)  {
    $scope.ds={};
    $scope.test=[];
	if (!$rootScope.isAdmin){
		$location.path( "/" );
	}
    var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
            return CrudService.GetAll('user').then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.notify(response.message,"error");
				}else{
					return response;
				}
			});            		
		},
	 
		byKey: function(key) {
            CrudService.GetById('user',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
	 
		insert: function(values) {
            CrudService.Create('user',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		},
	 
		update: function(key, values) {
            CrudService.Update('user',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
            
		},
		remove: function(key) {
			CrudService.Delete('user',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		}											 
    });
    CrudService.GetAll('role').then(function (resp) {
        $scope.dataRolex=resp;
        //console.log($scope.roles);
    });
    $scope.lookupDataSource = {
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
                return CrudService.GetAll('getallrole');
            },
            // byKey:function(key){
                // CrudService.GetById('role',encodeURIComponent(key)).then(function (role) {
                    // return role;
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
    if($rootScope.isLogin){
        initController();
    } 
    // $scope.detailUser = function(id) {
		// $scope.$emit('detailData', id);
	// };
    
    $scope.disabled= true;
    function initController() {
        /*DevExpress.ui.notify({
            message: "User Management \r\n ",
            type: "success",
            displayTime: 5000,
            height: 80,
            position: {
               my: 'top center', 
               at: 'center center', 
               of: window, 
               offset: '0 0' 
           }
        });	*/

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
								$scope.loadUser(options.data);
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
                  ,{dataField:'username',width: 200,fixed: true,fixedPosition: "left"}
                  ,{dataField:'email',width: 200,fixed: true,fixedPosition: "left"}
                  ,{dataField:'password',width: 200,visible: false,fixedPosition: "left"}
                  ,'firstname'
                  ,'lastname'
                  ,{dataField:'isadmin',dataType: "boolean", showEditorAlways: true }
                  ,{dataField: "role_id",
                        caption: "Role",
                        lookup: {  
                            dataSource:$scope.dataRolex,  
                            valueExpr: 'id',
							displayExpr: 'rolename'
                        }
                        
                    }
                  
                  ],
        "export": {
            enabled: true,
            fileName: "ExportGrid",
            allowExportSelectedData: false
        },
        bindingOptions :{
            "columns[8].lookup.dataSource":"dataRolex"
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
                    if (item.dataField === "ExecutiveSummary" ) {
                        item.colSpan = 2;
                    }
                }},
                popup: {  
                    title: "Form Data User",  
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
            if (e.dataField == "isadmin") {
                e.editorName = "dxSwitch";
                e.editorOptions.switchedOnText = "Yes";
                e.editorOptions.switchedOffText = "No";
            }
            if (e.dataField == "issadmin") {
                e.editorName = "dxCheckBox";
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
    
}]);
})(app || angular.module("kduApp"));