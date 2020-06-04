(function (app) {
app.register.controller('repdoCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
	$scope.formFilterInstance = [];
	var date = new Date();
	var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
	var endDay = new Date(date.getFullYear(), date.getMonth()+1, 0);
	$scope.filterData = {startDate : $filter("date")(firstDay, 'yyyy-MM-dd'), endDate : $filter("date")(endDay, 'yyyy-MM-dd') };
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
                dataField: "startDate",
                editorType: "dxDateBox",
				displayFormat: "yyyy-mm-dd",
                validationRules: [{
                    type: "required",
                    message: "Date is required"
                }]
            },{
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
	$scope.showForm= true;
	function initController() {
		$scope.dataGrid.refresh();
		// if (typeof $scope.dataGrid !== 'undefined'){
			// if((typeof $scope.filterData.startDate !== 'undefined') &&(typeof $scope.filterData.endDate !== 'undefined') && ($scope.filterData.startDate !== null) && ($scope.filterData.endDate !== null)){
				// $scope.dataGrid.filter([['dateworked','>=', $scope.filterData.startDate],'and',['dateworked','=<',  $scope.filterData.endDate]]);
			// }else{
				// $scope.dataGrid.clearFilter();
			// }	
			// $scope.dataGrid.refresh();
		// }
	}
	var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			criteria = {detail:'true',startDate:$filter("date")($scope.filterData.startDate, 'yyyy-MM-dd'),endDate:$filter("date")($scope.filterData.endDate, 'yyyy-MM-dd')};
            return CrudService.FindData('dodetail',criteria).then(function (response) {
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
	$scope.$on("initRepDO", function(event, name) {
		initController();
		//$scope.dataGrid.refresh();
    });
	$rootScope.$on("dataRefreshing", function(event, data) {
		initController();
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
        columns: [
                ,{caption: '#',formItem: { visible: false},width: 40,
					cellTemplate: function(container, options) {
						container.text($scope.gridInstance.pageIndex() * $scope.gridInstance.pageSize()+ options.rowIndex +1);
					}
                },
				{dataField:'dateworked',caption: "Work Date",dataType:"date", format:"dd/MM/yyyy",width: 80},
				{dataField:'fullapproveddate',caption: "Full Approved Date",dataType:"date", format:"dd/MM/yyyy",width: 80},
				{dataField:'sapid',caption: "SAP ID",width: 70},
				{dataField:'name',caption: "Name"},
				{dataField:'department',caption: "Department"},
				{dataField:'position',caption: "Position",width: 150},
				{dataField:'bu',caption: "Business Group",width: 50},
				{dataField:'superior',caption: "Superior"},
				{dataField:'depthead',caption: "Dept Head"},
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
            pageSize: 20
        },
        pager: {
            showPageSizeSelector: false,
            allowedPageSizes: [ 20,50],
            showInfo: true,
			showNavigationButtons:true,
			visible:true
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
        /*scrolling: {
            mode: "infinite"
        },*/
        onContentReady: function(e){
            moveEditColumnToLeft(e.component);
			// var visibleRowsCount = e.component.totalCount();  
			// var pageSize = e.component.pageSize(); 
			// var totalCount = e.component.option('dataSource').length;
			// console.log(e.component.option('dataSource'));
			// if (visibleRowsCount>0){
				// e.component.option('pager.infoText', 'Displaying ' + visibleRowsCount + ' of '+totalCount+' records'); 
			// }else{
				// e.component.option('pager.infoText', 'Displaying 0 records');
			// }
			
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