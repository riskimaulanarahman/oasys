(function (app) {
app.register.controller('spklreportCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	if ((!$rootScope.isAdmin) &&  (!$rootScope.viewSPKL)){
		$location.path( "/" );
	}
	var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			criteria = {filter:'all'};
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
	$scope.$on("initSPKL", function(event, name) {
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
									$scope.loadSPKLTKMS(options.data,'report',false);
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