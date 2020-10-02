(function (app) {
    app.register.controller('mmf30reportCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
        $scope.ds={};
        $scope.test=[];
        $scope.disabled= true;
        if ((!$rootScope.isAdmin) &&  (!$rootScope.viewMMF30)){
            $location.path( "/" );
        }
        var myStore = new DevExpress.data.CustomStore({
            load: function() {			
                $scope.isLoaded =true;
                criteria = {filter:'all'};
                return CrudService.FindData('mmf30app',criteria).then(function (response) {
                    if(response.status=="error"){
                        DevExpress.ui.notify(response.message,"error");
                    }else{
                        return response;
                    }
                });            		
            },
         
            byKey: function(key) {
                CrudService.GetById('mmf30app',encodeURIComponent(key)).then(function (response) {
                    return response;
                });
            },
            insert: function(values) {
                // CrudService.Create('mmf',values).then(function (response) {
                //     if(response.status=="error"){
                //          DevExpress.ui.notify(response.message,"error");
                //     }
                //     $scope.dataGrid.refresh();
                // });
            },
            update: function(key, values) {
                // CrudService.Update('mmf',key.id,values).then(function (response) {
                //     if(response.status=="error"){
                //          DevExpress.ui.notify(response.message,"error");
                //     }
                //     $scope.dataGrid.refresh();
                // });
            },
            remove: function(key) {
                // CrudService.Delete('mmf',key.id).then(function (response) {
                //     if(response.status=="error"){
                //          DevExpress.ui.notify(response.message,"error");
                //     }
                //     $scope.dataGrid.refresh();
                // });
            }
        });
        // $scope.allowDel = false;
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
        //$scope.myData = myData;
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
                        caption: "Actions",
                        fixed: true,
                        fixedPosition: "left",
                        width: 120,
                        allowFiltering: false,
                        allowSorting: false,
                        formItem: { visible: false},
                        cellTemplate: function (container, options) {
                            $('<div style="padding:2px 15px 2px 15px;" title="View Detail" />').addClass('dx-icon-detailslayout btn-pill btn-shadow btn btn-primary')
                                .text('')
                                .on('dxclick', function () {
                                    DevExpress.ui.notify("Loading detail data for "+options.data.requestdate,"info",600);
                                    $scope.loadMMF30(options.data,"report",true);
                                })
                                .appendTo(container);
                            // if((options.data.requeststatus=='3')){
                            //     // if((options.data.requeststatus=='0') || (options.data.requeststatus=='2')){
                            //     $('<div style="padding:2px 15px 2px 15px;" title="Edit" />').addClass('dx-icon-edit btn-pill btn-shadow btn btn-success')
                            //     .text('')
                            //     .on('dxclick', function () {
                            //         // if (!$scope.allowEdit){
                            //         //     DevExpress.ui.notify("You don't have authority to edit data","error");
                            //         // } else{
                            //             $scope.loadMMF30(options.data,"edit",true);
                            //         // }
                            //     })
                            //     .appendTo(container);
                            // }else{
                            //     $('<div style="padding:2px 15px 2px 15px;"/>').text('').appendTo(container);
                            // }
                        }
                    }
                    ,{caption: '#',formItem: { visible: false},width: 40,
                        cellTemplate: function(container, options) {
                            container.text(options.rowIndex +1);
                        }
                    },
                    {dataField:'prno',caption:"PR Number", editorOptions: { 
                        disabled: true,
                    }},
                    {dataField:'createddate',caption:"Creation Date",dataType:"date", format:"dd/MM/yyyy",width: 200},
                    {dataField:'fullname',caption:"Request For Employee",width: 200},
                    {dataField:'requeststatus',encodeHtml: false ,width: 300,
                        customizeText: function (e) {
                            var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-secondary'>Saved as Draft</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
                            return rDesc[e.value];
                        }},
                    
                    {dataField:'personholding',caption:"Next Approver", editorOptions: { 
                        disabled: true,
                    }},
                    {dataField:'apprbuyername',caption:"Buyer Name", editorOptions: { 
                        disabled: true,
                    }},
                    {dataField:'apprprocheaddate',caption:"Appr ProcHead Date", editorOptions: { 
                        disabled: true,
                    }},
                    {dataField:'apprbuyerdate',caption:"Appr Buyer Date", editorOptions: { 
                        disabled: true,
                    }},
                    {dataField:'remarks',encodeHtml: false },
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
                                }
                            },
                    ],	
            "export": {
                enabled: true,
                fileName: "ExportGrid",
                allowExportSelectedData: false
            },
            bindingOptions :{
    
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
            editing: {
                useIcons:true,
                mode: "popup",
                allowUpdating: false,
                allowAdding:false,
                allowDeleting:false,
                form:{colCount: 1,
                },
                popup: {  
                    title: "Form Data MMF30",  
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
                // if (e.columnIndex == 0 && e.rowType == "data") {
                //     if((e.data.requeststatus!==0) && (e.data.requeststatus!==2) ){
                //         e.cellElement.find(".dx-link-delete").remove();
                //     }
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