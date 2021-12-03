(function (app) {
app.register.controller('doreportCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	if ((!$rootScope.isAdmin) &&  (!$rootScope.viewDayoff)){
		$location.path( "/" );
	}
    //start filter date
    var date = new Date();
    var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    var endDay = new Date(date.getFullYear(), date.getMonth()+1, 0);
    $scope.filterData = {startDate : $filter("date")(firstDay, 'yyyy-MM-dd'), endDate : $filter("date")(endDay, 'yyyy-MM-dd') };
    $scope.formOptions = {
        readOnly: false,
        showColonAfterLabel: true,
        labelLocation: "left",
        minColWidth: 200,
        colCount: 3,
        showValidationSummary: true,
        onInitialized: function (e) {
            $scope.formFilterInstance = e.component;
        },
        items: [{
            dataField: "startDate",
            editorType: "dxDateBox",
            displayFormat: "yyyy-mm-dd",
            validationRules: [{
                type: "required",
                message: "Date is required"
            }]
        }, {
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
    $scope.showForm = true;

    function initController() {
        $scope.dataGrid.refresh();
    }
    //end filter date
	var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			//start filter date
            criteria = {filter:'all',startDate:$filter("date")($scope.filterData.startDate, 'yyyy-MM-dd'),endDate:$filter("date")($scope.filterData.endDate, 'yyyy-MM-dd')};
            //end filter date
            return CrudService.FindData('doapp',criteria).then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.notify(response.message,"error");
				}else{
					return response;
				}
			});            		
		},
	 
		byKey: function(key) {
            CrudService.GetById('dayoff',encodeURIComponent(key)).then(function (response) {
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
    //start filter date
	$scope.$on("initDO", function(event, name) {
		initController();
    });
    $rootScope.$on("dataRefreshing", function(event, data) {
        initController();
    });
    //end filter date
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
                    width: 120,
                    allowFiltering: false,
                    allowSorting: false,
                    formItem: { visible: false},
                    cellTemplate: function (container, options) {
                        $('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-detailslayout  btn-pill btn-shadow btn btn-primary')
                            .text('')
                            .on('dxclick', function () {
								$scope.loadDayoff(options.data,"report",false);
                            })
                            .appendTo(container);
						if($rootScope.isAdmin && (options.data.requeststatus=='3')){
							$('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-repeat  btn-pill btn-shadow btn btn-success')
								.text('')
								.on('dxclick', function () {
									window.open('api/apipdf?id='+options.data.id, '_blank');
								})
								.appendTo(container);
						}
                    }
                }
                ,{caption: '#',formItem: { visible: false},width: 40,
					cellTemplate: function(container, options) {
						container.text(options.rowIndex +1);
					}
                },
				{dataField:'requestdate',caption: "Creation Date",dataType:"date", format:"dd/MM/yyyy",width: 150},
				{dataField:'fullname',caption: "Request by",width: 150},
				{dataField:'department',caption: "Department",width: 150},
				{dataField:'requeststatus',encodeHtml: false ,width: 200,
					customizeText: function (e) {
						var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-secondary'>Saved as Draft</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
						return rDesc[e.value];
					}},
				{dataField:'remarks',width: '60%',encodeHtml: false },
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