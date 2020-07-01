(function (app) {
app.register.controller('companyCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService)  {
    $scope.ds={};
	var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			CrudService.checkAccess('Company',$rootScope.curUser.username).then(function (access) {
				$scope.allowEdit = access.allowedit;
				$scope.allowAdd = access.allowadd;
				$scope.allowDel = access.allowdelete;
			});
			var data = CrudService.GetAll('company');
            return data;    		
		}, 
		byKey: function(key) {
            CrudService.GetById('company',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
	 
		insert: function(values) {
			if ($scope.logopath!=""){
				values.logo = $scope.logopath;
			}
			if ($scope.koppath!=""){
				values.kop = $scope.koppath;
			}
            CrudService.Create('company',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		},
	 
		update: function(key, values) {
			if ($scope.logopath!=""){
				values.logo = $scope.logopath;
			}
			if ($scope.koppath!=""){
				values.kop = $scope.koppath;
			}
			values.companyname = values.companyname.trim();
            CrudService.Update('company',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
            
		},
		remove: function(key) {
			CrudService.Delete('company',key.id).then(function (response) {
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
	$scope.myData = myData;
    function moveEditColumnToLeft(dataGrid) {
		dataGrid.columnOption("command:edit", { 
			visibleIndex: -1,
			width: 80 
		});
    }
	$scope.allowEdit = false;
	$scope.allowAdd = false;
	$scope.allowDel = false;
	$scope.disabled= true;
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
				  {caption: '#',fixed: true,formItem: { visible: false},width: 40,
                        cellTemplate: function(container, options) {
                            container.text(options.rowIndex +1); //$scope.dataGrid.pageIndex() * $scope.dataGrid.pageSize() + options.rowIndex +1
                        }
                   }
				  ,{dataField:'companycode',caption:'Company Code',width: 100,fixed: true,fixedPosition: "left"}
                  ,{dataField:'companyname',caption:'Company Name',width: 200,fixed: true,fixedPosition: "left"}
                  ,{dataField:'companyaddress',caption:'Company Address',width: 200,fixed: true,fixedPosition: "left"}
                  ,{dataField:'logofile',caption:"Logo",visible: false}
				  ,{dataField:'kopfile',caption:"Kop",visible: false}
				  ,{
						dataField: "logo",
						caption:"Logo",
						allowFiltering: false,
						allowSorting: false,
						formItem: { visible: false},
						fixed: true,
						cellTemplate: function (container, options) {						
							$("<div>")
								.append($("<img>", {"height":"50px", "src": options.value }))
								.appendTo(container);
						}
					}
					,{
						dataField: "kop",
						caption:"Kop",
						allowFiltering: false,
						allowSorting: false,
						formItem: { visible: false},
						fixed: true,
						cellTemplate: function (container, options) {						
							$("<div>")
								.append($("<img>", {"height":"50px", "src": options.value }))
								.appendTo(container);
						}
					}
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
            //"columns[8].lookup.dataSource":"roles"
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
            mode: "popup",
            //allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
            //allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
            form:{colCount: 1,},
                popup: {  
                    title: "Form Data Company",  
                    showTitle: true  
                }, 
			popup: {
				title: "Entry Data Company",
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
							if($scope.adalogoFile || $scope.adakopFile){
								DevExpress.ui.notify("Please finish your upload or remove any pending file before saving the data","error");
								e.cancel = true;
							} else{
								$scope.gridInstance.saveEditData();
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
							$scope.gridInstance.cancelEditData();
						},text: 'Cancel'
					}
				  }
				]
			}
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
            e.component.columnOption("password", "allowEditing", false);
        },
        onEditorPreparing: function (e) {
            $scope.elId = e.id;  
            $scope.formComponent = e.component;
			$scope.logopath = "";
			$scope.koppath = "";
			if (e.dataField == "logofile") {
				e.editorName = "dxFileUploader";
				e.editorOptions.uploadMode = "useButtons";
				e.editorOptions.name = "cpLogo";
				e.editorOptions.accept = "image/*";
				e.editorOptions.uploadUrl= "api/uploadimage";
				e.editorOptions.onUploaded= function (e) {						
					$scope.logopath = e.request.response;
					$scope.adalogoFile =false;
				}
				e.editorOptions.onValueChanged= function(e){					
					$scope.adalogoFile = (e.value.length==0)?false:true;
				}
			} 
			if (e.dataField == "kopfile") {
				e.editorName = "dxFileUploader";
				e.editorOptions.uploadMode = "useButtons";
				e.editorOptions.name = "cpKop";
				e.editorOptions.accept = "image/*";
				e.editorOptions.uploadUrl= "api/uploadimage";
				e.editorOptions.onUploaded= function (e) {						
					$scope.koppath = e.request.response;
					$scope.adakopFile =false;
				}
				e.editorOptions.onValueChanged= function(e){					
					$scope.adakopFile = (e.value.length==0)?false:true;
				}
			} 
        },
		onEditorPrepared: function (e) {
			if (e.dataField == "companyname") {
				var index = e.row.rowIndex;
				var rm = (typeof(e.value)=="undefined")?"":e.value;
				$scope.gridInstance.cellValue(index, "companyname", rm.trim()+" ");
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
	
}]);
})(app || angular.module("kduApp"));