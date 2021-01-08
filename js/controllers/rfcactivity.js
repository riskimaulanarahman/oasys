(function (app) {
app.register.controller('rfcactivityCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
    var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			CrudService.checkAccess('RFCACtivity',$rootScope.curUser.username).then(function (access) {
				$scope.allowEdit = ($rootScope.isAdmin)?true:access.allowedit;
				$scope.allowAdd = ($rootScope.isAdmin)?true:access.allowadd;
				$scope.allowDel = ($rootScope.isAdmin)?true:access.allowdelete;
			});
            return CrudService.GetAll('rfcactivity').then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.notify(response.message,"error");
				}else{
					return response;
				}
			});            		
		},
	 
		byKey: function(key) {
            CrudService.GetById('rfcactivity',encodeURIComponent(key)).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				return response;
			});
		},
	 
		insert: function(values) {
            CrudService.Create('rfcactivity',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		},
	 
		update: function(key, values) {
            CrudService.Update('rfcactivity',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
            
		},
		remove: function(key) {
			CrudService.Delete('rfcactivity',key.id).then(function (response) {
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
    $rootScope.$on("loginChanged", function(event, islogin) {
        $rootScope.isLogin = islogin;
        if($rootScope.isLogin){
            initController();
        } 
    });
    if($rootScope.isLogin){
        initController();
    }
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
        columns: [{caption: '#',fixed: true,formItem: { visible: false},width: 40,
                        cellTemplate: function(container, options) {
                            container.text(options.rowIndex +1); //$scope.dataGrid.pageIndex() * $scope.dataGrid.pageSize() + options.rowIndex +1
                        }
                   }
				  ,{dataField:'activitycode',caption:"Code"}
				  ,{dataField:'activitydescr',caption:"Description"}
				  ,{dataField:'remarks',caption:"Remarks"}
				  ,{dataField:'ishrrelated',caption: "HR Related Activity",dataType: "boolean", showEditorAlways: true }
				  ,{dataField:'iscapexrelated',caption: "Capex Related Activity",dataType: "boolean", showEditorAlways: true }
				  ,{dataField:'isactive',caption: "Active",dataType: "boolean", showEditorAlways: true }
                  ],
        "export": {
            enabled: true,
            fileName: "ExportGrid",
            allowExportSelectedData: false
        },
		bindingOptions :{
			"editing.allowUpdating": "allowEdit" ,
			"editing.allowAdding": "allowAdd" ,
			"editing.allowDeleting": "allowDel" ,
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
            // allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
            // allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
            form:{colCount: 1,
            },
            popup: {  
                title: "Form Data rfcactivity",  
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
			if ((e.dataField == "isactive") || (e.dataField == "ishrrelated")|| (e.dataField == "iscapexrelated")){
                e.editorName = "dxSwitch";
				e.editorOptions.switchedOnText = "Yes";
				e.editorOptions.switchedOffText = "No";
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