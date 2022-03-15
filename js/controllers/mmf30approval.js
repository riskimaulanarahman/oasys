(function (app) {
    app.register.controller('mmf30approvalCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
        $scope.ds={};
        $scope.test=[];
        $scope.disabled= true;
        var myStore = new DevExpress.data.CustomStore({
            load: function() {			
                $scope.isLoaded =true;
                criteria = {pending:'true'};
                return CrudService.FindData('mmf30app',criteria).then(function (response) {
                    if(response.status=="error"){
                        DevExpress.ui.notify(response.message,"error");
                    }else{
                        return response;
                    }
                });            		
            },
         
            byKey: function(key) {
                CrudService.GetById('mmf30',encodeURIComponent(key)).then(function (response) {
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
        $scope.$on("initMMF30", function(event, name) {
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
            columnAutoWidth: true,
            showBorders: true,
            height: 600,
            headerFilter: {
                visible: true
            },
            columns: [{
                        caption: "Action",
                        fixed: true,
                        fixedPosition: "left",
                        width: 120,
                        allowFiltering: false,
                        allowSorting: false,
                        formItem: { visible: false},
                        cellTemplate: function (container, options) {
                            if((options.data.requeststatus=='1')){	
                            var icon = 'dx-icon-todo';
                            $('<div style="padding:2px 15px 2px 15px;"/>').addClass(icon+'  btn-pill btn-shadow btn btn-primary')
                                .text('')
                                .on('dxclick', function () {
                                    DevExpress.ui.notify("Loading detail data for "+options.data.requestdate,"info",600);
                                    var  mode='approve';
                                    $scope.loadMMF30(options.data,mode,$scope.Filter);
                                })
                                .appendTo(container);
                            }else{
                                $('<div style="padding:2px 15px 2px 15px;"/>').text('').appendTo(container);
                            }
                        }
                    }
                    ,{caption: '#',formItem: { visible: false},width: 40,
                        cellTemplate: function(container, options) {
                            container.text(options.rowIndex +1);
                        }
                    },
                    {dataField:'createddate',caption: "Creation Date",dataType:"date", format:"dd/MM/yyyy",width: 150},
                    {dataField:'prno',caption: "PR No",width: 250},
                    {dataField:'fullname',caption: "Request by",width: 150},
                    {dataField:'department',caption: "Department",width: 150},
                    {dataField:'requeststatus',encodeHtml: false ,width: 200,
                        customizeText: function (e) {
                            var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-secondary'>Saved as Draft</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
                            return rDesc[e.value];
                        }},
                    {dataField:'reason', caption:"Reason for requisition/purchase",encodeHtml: false },
                    {dataField:'remarks',width: '60%',encodeHtml: false },
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