(function (app) {
    app.register.controller('advpaymentCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
        $scope.ds={};
        $scope.test=[];
        $scope.disabled= true;
        
        var myStore = new DevExpress.data.CustomStore({
            load: function() {			
                $scope.isLoaded =true;
                return CrudService.GetAll('advpaymentbyemp').then(function (response) {
                    if(response.status=="error"){
                        DevExpress.ui.notify(response.message,"error");
                    }else{
                        return response;
                    }
                });            		
            },
         
            byKey: function(key) {
                CrudService.GetById('advpayment',encodeURIComponent(key)).then(function (response) {
                    return response;
                });
            },
            insert: function(values) {
                CrudService.Create('advpayment',values).then(function (response) {
                    if(response.status=="error"){
                         DevExpress.ui.notify(response.message,"error");
                    }
                    $scope.dataGrid.refresh();
                });
            },
            update: function(key, values) {
                CrudService.Update('advpayment',key.id,values).then(function (response) {
                    if(response.status=="error"){
                         DevExpress.ui.notify(response.message,"error");
                    }
                    $scope.dataGrid.refresh();
                });
            },
            remove: function(key) {
                CrudService.Delete('advpayment',key.id).then(function (response) {
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
                        caption: "Action",
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
                                    $scope.loadAdvpayment(options.data,"view",true);
                                })
                                .appendTo(container);
                            if((options.data.requeststatus=='0') || (options.data.requeststatus=='2')){	
                                $('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-edit btn-pill btn-shadow btn btn-success')
                                .text('')
                                .on('dxclick', function () {
                                    // if (!$scope.allowEdit){
                                        // DevExpress.ui.notify("You don't have authority to edit data","error");
                                    // } else{
                                        $scope.loadAdvpayment(options.data,"edit",true);
                                    // }
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
                    {dataField:'createddate',caption:"Creation Date",dataType:"date", format:"dd/MM/yyyy",width: 200},
                    {dataField:'fullname',caption:"Request For Employee",width: 200},
                    {dataField:'paymentform',caption:"Form Type",encodeHtml: false ,width: 200,
                        customizeText: function (e) {
                            var rDesc = ["","<span class='mb-2 mr-2 badge badge-pill badge-info'>Payment Req HR</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Payment Req OPS</span>",""];
                            return rDesc[e.value];
                    }},
				    {dataField:'paymentno',caption:"Payment No",width: 200},
                    {dataField:'requeststatus',caption:"Request Status",encodeHtml: false ,width: 300,
                        customizeText: function (e) {
                            var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-secondary'>Saved as Draft</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Payment</span>",""];
                            return rDesc[e.value];
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
                allowDeleting:true,
                form:{colCount: 1,
                },
                popup: {  
                    title: "Form Data Advpayment",  
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
                    if((e.data.requeststatus!==0) && (e.data.requeststatus!==2) ){
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
                {						
                    location: "after",
                    widget: "dxButton",
                    options: {
                        hint: "Add New Request",
                        icon: "add",
                        onClick: function() {
                            var date = new Date();
                            var d= $filter("date")(date, "yyyy-MM-dd HH:mm")
                            $scope.loadAdvpayment({createddate:d,username:$rootScope.curUser.username},"add",true);
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