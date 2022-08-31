(function (app) {
    app.register.controller('mmfreportCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
        $scope.ds={};
        $scope.test=[];
        $scope.disabled= true;
        if ((!$rootScope.isAdmin) &&  (!$rootScope.viewMMF)){
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
                return CrudService.FindData('mmfapp',criteria).then(function (response) {
                    if(response.status=="error"){
                        DevExpress.ui.notify(response.message,"error");
                    }else{
                        return response;
                    }
                });            		
            },
         
            byKey: function(key) {
                CrudService.GetById('mmfapp',encodeURIComponent(key)).then(function (response) {
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
                values.materialreturneddate = $filter("date")(values.materialreturneddate, "yyyy-MM-dd HH:mm");
                values.action = 'updatereport';
                CrudService.Update('mmf',key.id,values).then(function (response) {
                    if(response.status=="error"){
                         DevExpress.ui.notify(response.message,"error");
                    }
                    $scope.dataGrid.refresh();
                });
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
        //start filter date
        $scope.$on("initMMF", function(event, name) {
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
        //$scope.myData = myData;
        $scope.dataGridOptions = {
            dataSource: myData,
            showColumnLines: true,
            showRowLines: true,
            rowAlternationEnabled: true,
            // allowColumnResizing: true,
            // columnResizingMode: "widget",
            columnAutoWidth: true,
            columnHidingEnabled: true,
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
                                    $scope.loadMMF(options.data,"report",true);
                                })
                                .appendTo(container);
                        }
                    }
                    ,{caption: '#',formItem: { visible: false},width: 40,
                        cellTemplate: function(container, options) {
                            container.text(options.rowIndex +1);
                        }
                    },
                    {dataField:'mmfnumber',caption:"MMF Number", editorOptions: { 
                        disabled: true,
                    }},
                    {dataField:'createddate',caption:"Creation Date",dataType:"date", format:"dd/MM/yyyy", editorOptions: { 
                        disabled: true,
                    }},
                    {dataField:'fullname',caption:"Request For Employee", editorOptions: { 
                        disabled: true,
                    }},
                    {dataField:'requeststatus',encodeHtml: false ,
                        customizeText: function (e) {
                            var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-secondary'>Saved as Draft</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
                            return rDesc[e.value];
                        }, editorOptions: { 
                            disabled: true,
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
                    {dataField:'pono',caption:"PO Number"},
                    {dataField:'materialreturneddate',caption:"Material Returned Date",dataType:"date", format:"dd/MM/yyyy",editorType: "dxDateBox",editorOptions: {displayFormat:"dd/MM/yyyy",disabled: false}},
                    {dataField:'supplierdodnno',caption:"Supplier DO/DN No"},
                    {dataField:'materialdispatchno',caption:"Material Dispatch No"},
                    {dataField:'isrepair',caption:"Repair",dataType: "boolean", showEditorAlways: true },
                    {dataField:'isscrap',caption:"Scrapped",dataType: "boolean", showEditorAlways: true },
                    {dataField:'remarks',encodeHtml: false , editorOptions: { 
                        disabled: true,
                    }},
                    {
                                dataField: "approveddoc",
                                caption:"Approval Doc",
                                width: 100,
                                allowFiltering: false,
                                allowSorting: false,
                                formItem: { visible: false},
                                editorOptions: { 
                                    disabled: true,
                                },
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
                // "editing.allowUpdating": "allowEdit" ,
            },
            masterDetail: {
                enabled: true,
                template: masterDetailTemplate,
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
                mode: "row",
                allowUpdating: true,
                allowAdding:false,
                allowDeleting:false,
                form:{colCount: 1,
                },
                popup: {  
                    title: "Form Data MMF28",  
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
                if (e.dataField == "isfinal"){
                    e.editorName = "dxSwitch";
                    e.editorOptions.switchedOnText = "Yes";
                    e.editorOptions.switchedOffText = "No";
                } 
                if (e.dataField == "isactive"){
                    e.editorName = "dxSwitch";
                    e.editorOptions.switchedOnText = "Yes";
                    e.editorOptions.switchedOffText = "No";
                }
                if(e.row&&e.row.data.requeststatus !== 3) {
                    e.editorOptions.disabled = true;
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
        
        function masterDetailTemplate(_, masterDetailOptions) {
            return $("<div>").dxTabPanel({
              items: [
                {
                  title: "Supporting Document",
                  template: supportingdocdata(masterDetailOptions.data),
                },
                {
                  title: "Approver list",
                  template: approverlist(masterDetailOptions.data),
                },
                {
                  title: "History Tracking",
                  template: history(masterDetailOptions.data),
                },
              ],
            });
          }

          function supportingdocdata(masterDetailData) {
            return function () {
              return $("<div>").dxDataGrid({
                dataSource: new DevExpress.data.DataSource({
                  store: new DevExpress.data.CustomStore({
                    key: "mmf28_id",
                    load: function () {
                      return CrudService.GetById("mmffile", masterDetailData.id);
                    },
                  }),
                }),
                allowColumnResizing: true,
                columnResizingMode: "widget",
                columnAutoWidth: true,
                showBorders: true,
                paging: {
                  pageSize: 5,
                },
                pager: {
                  showPageSizeSelector: false,
                  allowedPageSizes: [5, 10, 20],
                  showInfo: false,
                },
                showBorders: true,
                columns: [
                  {
                    dataField: "file_descr",
                    caption: "File Description",
                    encodeHtml: false,
                    dataType: "string",
                  },
                  {
                    dataField: "file_loc",
                    caption: "FileLocation",
                    width: 100,
                    allowFiltering: false,
                    allowSorting: false,
                    formItem: { visible: false },
                    cellTemplate: function (container, options) {
                      if (options.value != "") {
                        $("<div />")
                          .dxButton({
                            icon: "download",
                            stylingMode: "contained",
                            type: "success",
                            target: "_blank",
                            width: 50,
                            height: 25,
                            onClick: function (e) {
                              window.open(options.value, "_blank");
                            },
                          })
                          .appendTo(container);
                      }
                    },
                  },
                  {
                    dataField: "upload_date",
                    // width:150,
                    caption: "Upload Date",
                    dataType: "date",
                    format: "dd/MM/yyyy HH:mm:ss",
                    editorType: "dxDateBox",
                    editorOptions: {
                      displayFormat: "dd/MM/yyyy HH:mm:ss",
                      disabled: true,
                    },
                  },
                ],
              });
            };
          }
    
          // start approver list and history
    
          $scope.empDataSource = {
            store: new DevExpress.data.CustomStore({
              key: "id",
              loadMode: "raw",
              load: function () {
                criteria = {
                  module: 'MMF',
                  mode: $scope.mode
                };
                return CrudService.FindData('appr', criteria);
              },
            }),
            sort: "id"
          }
    
          CrudService.GetAll('approvaltype').then(function (resp) {
            $scope.apptypeDatasource = resp;
          });
    
          function approverlist(masterDetailData) {
            return function () {
              return $("<div>").dxDataGrid({
                dataSource: new DevExpress.data.DataSource({
                  store: new DevExpress.data.CustomStore({
                    key: "mmf28_id",
                    load: function () {
                      return CrudService.GetById(
                        "mmfapp",
                        masterDetailData.id
                      );
                    },
                  }),
                }),
                allowColumnResizing: true,
                columnResizingMode: "widget",
                columnAutoWidth: true,
                showBorders: true,
                paging: {
                  pageSize: 5,
                },
                pager: {
                  showPageSizeSelector: false,
                  allowedPageSizes: [5, 10, 20],
                  showInfo: false,
                },
                showBorders: true,
                columns: [{
                  dataField: "approver_id",
                  caption: "Employee",
                  width: 200,
                  allowSorting: false,
                  lookup: {
                    dataSource: $scope.empDataSource,
                    valueExpr: "id",
                    displayExpr: "fullname"
                  },
                  editCellTemplate: "dropDownBoxEditorTemplatex"
                }, {
                  dataField: 'approvaldate',
                  width: 150,
                  dataType: "date",
                  format: "dd/MM/yyyy",
                  allowEditing: false,
                }, {
                  dataField: 'approvaltype',
                  width: 200,
                  allowEditing: false,
                  lookup: {
                    dataSource: $scope.apptypeDatasource,
                    valueExpr: 'id',
                    displayExpr: 'approvaltype'
                  }
                }, {
                  dataField: 'approvalstatus',
                  width: 150,
                  allowEditing: false,
                  encodeHtml: false,
                  customizeText: function (e) {
                    var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>", "<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>", "<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>", "<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>", ""];
                    return rDesc[e.value];
                  }
                }, ],
              });
            };
          }
    
          function history(masterDetailData) {
            return function () {
              return $("<div>").dxDataGrid({
                dataSource: new DevExpress.data.DataSource({
                  store: new DevExpress.data.CustomStore({
                    key: "mmf28_id",
                    load: function () {
                      return CrudService.GetById(
                        "mmfhist",
                        masterDetailData.id
                      );
                    },
                  }),
                }),
                allowColumnResizing: true,
                columnResizingMode: "widget",
                columnAutoWidth: true,
                showBorders: true,
                paging: {
                  pageSize: 5,
                },
                pager: {
                  showPageSizeSelector: false,
                  allowedPageSizes: [5, 10, 20],
                  showInfo: false,
                },
                showBorders: true,
                columns: [{
                  dataField: 'date',
                  width: 150,
                  dataType: "date",
                  format: 'dd/MM/yyyy HH:mm:ss'
                }, {
                  dataField: 'fullname',
                  width: 200,
                  caption: "Employee",
                  allowEditing: false,
                  dataType: "string"
                }, {
                  dataField: 'approvaltype',
                  width: 150,
                  caption: "Role",
                  allowEditing: false,
                  dataType: "string"
                }, {
                  dataField: 'actiontype',
                  width: 150,
                  caption: "Action",
                  allowEditing: false,
                  encodeHtml: false,
                  customizeText: function (e) {
                    var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-default'>Created</span>", "<span class='mb-2 mr-2 badge badge-pill badge-default'>Save as Draft</span>", "<span class='mb-2 mr-2 badge badge-pill badge-primary'>Submitted</span>", "<span class='mb-2 mr-2 badge badge-pill badge-warning'>Ask Rework</span>", "<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>", "<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>", ""];
                    return rDesc[e.value];
                  }
                }, {
                  dataField: 'remarks',
                  encodeHtml: false
                }],
              });
            };
          }
    
          // end approver list and history
        
    }]);
    })(app || angular.module("kduApp"));