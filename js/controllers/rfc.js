app.controller('rfcCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	CrudService.GetAll('rfcactivity').then(function (resp) {
        $scope.activityDatasource=resp;
    });
	CrudService.GetAll('rfccontractor').then(function (resp) {
        $scope.contractorDatasource=resp;
    });
    var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
            return CrudService.GetAll('rfcbyemp').then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.notify(response.message,"error");
				}else{
					return response;
				}
			});            		
		},
	 
		byKey: function(key) {
            CrudService.GetById('rfc',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
            CrudService.Create('rfc',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		},
		update: function(key, values) {
            CrudService.Update('rfc',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('rfc',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.dataGrid.refresh();
			});
		}
    });
	$scope.allowDel = false;
    var myData = new DevExpress.data.DataSource({
		store: myStore
    });
    //$scope.myData = myData;
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
                    fixedPosition: "left",
                    width: 120,
                    allowFiltering: false,
                    allowSorting: false,
                    formItem: { visible: false},
                    cellTemplate: function (container, options) {
                        $('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-detailslayout btn-pill btn-shadow btn btn-primary')
                            .text('')
                            .on('dxclick', function () {
                                DevExpress.ui.notify("Loading detail data for "+options.data.requestdate,"info",600);
								$scope.loadRFC(options.data,"view",true);
                            })
                            .appendTo(container);
						if((options.data.requeststatus=='0') || (options.data.requeststatus=='2')){	
							$('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-edit btn-pill btn-shadow btn btn-success')
                            .text('')
                            .on('dxclick', function () {
								// if (!$scope.allowEdit){
									// DevExpress.ui.notify("You don't have authority to edit data","error");
								// } else{
									$scope.loadRFC(options.data,"edit",true);
								// }
                            })
                            .appendTo(container);
						}else{
							$('<div style="padding:2px 15px 2px 15px;"/>').text('').appendTo(container);
						}
                    }
                }
                ,{caption: '#',fixed: true, fixedPosition: "left",formItem: { visible: false},width: 40,
					cellTemplate: function(container, options) {
						container.text(options.rowIndex +1);
					}
                },
				{dataField:'createddate',caption:"Creation Date",fixed: true, fixedPosition: "left",dataType:"date", format:"dd/MM/yyyy h:m:ss"},
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
				{dataField:'remarks',width: 300,encodeHtml: false },
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
								/*$("<div>")
									.append($("<a href> Download</a>", {"width":"100%", "href": options.value }))
									.appendTo(container)
									.on("click",function(){
										if (options.value!=""){
											$scope.imageAddress = options.value;
											$scope.imageDescription ="Initiated By :"+options.data.InitiatedBy;
											$scope.imgPopupTitle = "Payment Number : "+options.data.PaymentNumber;
											$scope.imgPopupVisible = true;
										}
										
									}
									)*/;
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
			"columns[5].lookup.dataSource":"activityDatasource",
			"columns[11].lookup.dataSource":"contractorDatasource",
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
        /*selection: {
            mode: "multiple"
        },*/
        editing: {
            useIcons:true,
            mode: "popup",
			allowUpdating: false,
			allowAdding:false,
			allowDeleting:true,
            //allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
            //allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
            form:{colCount: 1,
            },
            popup: {  
                title: "Form Data RFC Approval",  
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
		onCellPrepared: function(e) {
			if (e.columnIndex == 0 && e.rowType == "data") {
				if(e.data.requeststatus!==0){
					e.cellElement.find(".dx-link-delete").remove();
				}
			}
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
            },
			/*{
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
            },*/
			{						
				location: "after",
				widget: "dxButton",
				options: {
					hint: "Add New Request",
					icon: "add",
					onClick: function() {
						var date = new Date();
						var d= $filter("date")(date, "yyyy-MM-dd HH:mm")
						$scope.loadRFC({CreatedDate:d,username:$rootScope.curUser.username},"add",true);
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