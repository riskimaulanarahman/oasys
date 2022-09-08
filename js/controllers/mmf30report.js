(function (app) {
  app.register.controller("mmf30reportCtrl", [
    "$rootScope",
    "$scope",
    "$http",
    "$interval",
    "$location",
    "CrudService",
    "AuthenticationService",
    "$filter",
    function (
      $rootScope,
      $scope,
      $http,
      $interval,
      $location,
      CrudService,
      AuthenticationService,
      $filter
    ) {
      $scope.ds = {};
      $scope.test = [];
      $scope.disabled = true;
      if (!$rootScope.isAdmin && !$rootScope.viewMMF30) {
        $location.path("/");
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
          CrudService.FindData('mmf30detail',{filter:'all',startDate:$filter("date")($scope.filterData.startDate, 'yyyy-MM-dd'),endDate:$filter("date")($scope.filterData.endDate, 'yyyy-MM-dd')}).then(function (resp) {
            $scope.mmf30detail = resp;
          });
      }
      $scope.mmf30detail=[]
      const getMMFDetail = (id) => {}
        $scope.mmf30detail.filter((data) => data.mmf30_id === id);
      //end filter date
      var myStore = new DevExpress.data.CustomStore({
        load: function () {
          $scope.isLoaded = true;
          //start filter date
          criteria = {filter:'all',startDate:$filter("date")($scope.filterData.startDate, 'yyyy-MM-dd'),endDate:$filter("date")($scope.filterData.endDate, 'yyyy-MM-dd')};
          //end filter date
          return CrudService.FindData("mmf30app", criteria).then(function (
            response
          ) {
            if (response.status == "error") {
              DevExpress.ui.notify(response.message, "error");
            } else {
              return response;
            }
          });
        },

        byKey: function (key) {
          CrudService.GetById("mmf30app", encodeURIComponent(key)).then(
            function (response) {
              return response;
            }
          );
        },
        insert: function (values) {

        },
        update: function (key, values) {

        },
        remove: function (key) {

        },
      });
      //start filter date
      $scope.$on("initMMF30", function (event, name) {
          initController();
      });
      $rootScope.$on("dataRefreshing", function(event, data) {
          initController();
      });
      //end filter date
      var myData = new DevExpress.data.DataSource({
        store: myStore,
      });
      function moveEditColumnToLeft(dataGrid) {
        dataGrid.columnOption("command:edit", {
          visibleIndex: -1,
          width: 80,
        });
      }
      //$scope.myData = myData;
      $scope.masterRows=[]
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
          visible: true,
        },
        columns: [
          {
            caption: "Actions",
            fixed: true,
            fixedPosition: "left",
            width: 120,
            allowFiltering: false,
            allowSorting: false,
            formItem: { visible: false },
            cellTemplate: function (container, options) {
              $(
                '<div style="padding:2px 15px 2px 15px;" title="View Detail" />'
              )
                .addClass(
                  "dx-icon-detailslayout btn-pill btn-shadow btn btn-primary"
                )
                .text("")
                .on("dxclick", function () {
                  DevExpress.ui.notify(
                    "Loading detail data for " + options.data.requestdate,
                    "info",
                    600
                  );
                  $scope.loadMMF30(options.data, "report", true);
                })
                .appendTo(container);
            },
          },
          {
            caption: "#",
            formItem: { visible: false },
            width: 40,
            cellTemplate: function (container, options) {
              container.text(options.rowIndex + 1);
            },
          },
          {
            dataField: "prno",
            caption: "PR Number",
            editorOptions: {
              disabled: true,
            },
          },
          {
            dataField: "createddate",
            caption: "Creation Date",
            dataType: "date",
            format: "dd/MM/yyyy",
            // width: 200,
          },
          {
            dataField: "fullname",
            caption: "Request For Employee",
            // width: 200,
          },
          {
            dataField: "requeststatus",
            encodeHtml: false,
            // width: 300,
            customizeText: function (e) {
              var rDesc = [
                "<span class='mb-2 mr-2 badge badge-pill badge-secondary'>Saved as Draft</span>",
                "<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>",
                "<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>",
                "<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>",
                "<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",
                "",
              ];
              return rDesc[e.value];
            },
          },

          {
            dataField: "personholding",
            caption: "Next Approver",
            editorOptions: {
              disabled: true,
            },
          },
          {
            dataField: "apprbuyername",
            caption: "Buyer Name",
            editorOptions: {
              disabled: true,
            },
          },
          {
            dataField: "apprprocheaddate",
            caption: "Appr ProcHead Date",
            editorOptions: {
              disabled: true,
            },
          },
          {
            dataField: "apprbuyerdate",
            caption: "Appr Buyer Date",
            editorOptions: {
              disabled: true,
            },
          },
          { dataField: "remarks", encodeHtml: false },
          {
            dataField: "approveddoc",
            caption: "Approval Doc",
            width: 100,
            allowFiltering: false,
            allowSorting: false,
            formItem: { visible: false },
            cellTemplate: function (container, options) {
              if (options.value != "" && options.value) {
                $("<div />")
                  .dxButton({
                    icon: "download",
                    stylingMode: "contained",
                    type: "success",
                    target: "_blank",
                    width: 50,
                    onClick: function (e) {
                      window.open(options.value, "_blank");
                    },
                  })
                  .appendTo(container);
              }
            },
          },
        ],
        export: {
          enabled: true,
          fileName: "ExportGrid",
          allowExportSelectedData: false,
        },
        masterDetail: {
          enabled: true,
          template: masterDetailTemplate,
        },
        bindingOptions: {},
        columnChooser: {
          enabled: true,
        },
        loadPanel: {
          enabled: true,
        },
        columnFixing: {
          enabled: true,
        },
        paging: {
          pageSize: 10,
        },
        pager: {
          showPageSizeSelector: false,
          allowedPageSizes: [5, 10, 20],
          showInfo: false,
        },
        editing: {
          useIcons: true,
          mode: "popup",
          allowUpdating: false,
          allowAdding: false,
          allowDeleting: false,
          form: { colCount: 1 },
          popup: {
            title: "Form Data MMF30",
            showTitle: true,
          },
        },
        searchPanel: {
          visible: true,
          width: 240,
          placeholder: "Search...",
        },
        scrolling: {
          mode: "infinite",
        },
        onContentReady: function (e) {
          moveEditColumnToLeft(e.component);
        },
        onCellPrepared: function (e) {
        },
        onEditingStart: function (e) {
          e.component.columnOption("id", "allowEditing", false);
        },
        onEditorPreparing: function (e) {
          $scope.formComponent = e.component;
          if (e.parentType === "dataRow" && e.dataField === "division_id") {
            e.editorOptions.disabled =
              typeof e.row.data.department_id !== "number";
          }
          if (e.parentType === "dataRow" && e.dataField === "designation_id") {
            e.editorOptions.disabled =
              typeof e.row.data.division_id !== "number";
          }
        },
        onEditorPrepared: function (e) {},
        onInitNewRow: function (e) {
          e.component.columnOption("id", "allowEditing", false);
        },
        onSelectionChanged: function (data) {
          $scope.selectedItems = data.selectedRowsData;
          $scope.disabled = !$scope.selectedItems.length;
        },
        onRowUpdated: function (e) {
          $scope.editors = {};
        },
        onRowInserted: function (e) {
          $scope.editors = {};
        },
        onToolbarPreparing: function (e) {
          $scope.dataGrid = e.component;

          e.toolbarOptions.items.unshift({
            location: "after",
            widget: "dxButton",
            options: {
              hint: "Refresh Data",
              icon: "refresh",
              onClick: function () {
                $scope.dataGrid.refresh();
              },
            },
          });
        },
        onContextMenuPreparing: function (e) {
          var dataGrid = e.component;
        },
        onInitialized: function (e) {
          $scope.gridInstance = e.component;
          $scope.ds = e.component.getDataSource();
        },
        onExporting: function(e) { 
          e.component.beginUpdate();
          e.component.columnOption('ID', 'visible', true);
          var workbook = new ExcelJS.Workbook(); 
          var worksheet = workbook.addWorksheet('MMF30');

          DevExpress.excelExporter.exportDataGrid({
            component: e.component,
            worksheet: worksheet,
            autoFilterEnabled: true,
            topLeftCell: { row: 2, column: 2 },
            customizeCell: ({ gridCell, excelCell }) => {
              if(gridCell.rowType === 'data') {
                if(!gridCell) {
                  return;
                }
                if((gridCell.column.dataField === 'requeststatus') ){
                  if(gridCell.value===0) {
                    excelCell.value = "Saved as Draft"
                    excelCell.fill = { type: 'pattern', pattern: 'solid', fgColor:{argb :"e4e7ea"} };
                  } else if(gridCell.value===1) {
                    excelCell.value = "Waiting Approval"
                    excelCell.fill = { type: 'pattern', pattern: 'solid', fgColor:{argb :"49b6d6"}  };
                  } else if(gridCell.value===3) {
                    excelCell.value = "Require Rework"
                    excelCell.fill = { type: 'pattern', pattern: 'solid', fgColor:{argb :"f59c1a"}   };
                  } else if(gridCell.value===4) {
                    excelCell.value = "Approved"
                    excelCell.fill = { type: 'pattern', pattern: 'solid', fgColor:{argb :"00acac"}  };
                  }else{
                    excelCell.value = ""
                  }
                }
              }
              if ( gridCell.column.dataField === "prno" && gridCell.rowType === "data" ) {
                $scope.masterRows.push({
                  rowIndex: excelCell.fullAddress.row + 1,
                  data: gridCell.data
                });
              }
            }
          }).then((cellRange)=> {
            const borderStyle = { style: "thin", color: { argb: "FF7E7E7E" } };
            let offset = 0;
            
            const insertRow = (index, offset, outlineLevel) => {
              const currentIndex = index + offset;
              const row = worksheet.insertRow(currentIndex, [], "n");
    
              for (var j = worksheet.rowCount + 1; j > currentIndex; j--) {
                worksheet.getRow(j).outlineLevel = worksheet.getRow(j - 1).outlineLevel;
              }
              row.outlineLevel = outlineLevel;
              return row;
            };
            for (var i = 0; i < $scope.masterRows.length; i++) {
              let rowIndex = $scope.masterRows[i].rowIndex
              let row = insertRow(rowIndex + i, offset++, 1);
              let columnIndex = cellRange.from.column + 2;
              
              Object.assign(row.getCell(columnIndex), {
                value: '> MMF No : '+$scope.masterRows[i].data.prno+ " Detail Data",
                fill: {
                  type: "pattern",
                  pattern: "solid",
                  fgColor: { argb: "BEDFE6" }
                }
              });
              worksheet.mergeCells(row.number, columnIndex, row.number, columnIndex+10);
    
              const columns = ["materialcode","materialdescr","partnumber","brandmanufacturer","qty","unit","currency","unitprice","extendedprice","remarks" ];
    
              row = insertRow(rowIndex+ i, offset++, 1);
              columns.forEach((columnName, currentColumnIndex) => {
                Object.assign(row.getCell(columnIndex + currentColumnIndex), {
                  value: columnName,
                  font: { bold: true },
                  fill: {
                    type: "pattern",
                    pattern: "solid",
                    fgColor: { argb: "BEDFE6" }
                  },
                  border: {
                    bottom: borderStyle,
                    left: borderStyle,
                    right: borderStyle,
                    top: borderStyle
                  }
                });
              });
                getMMFDetail($scope.masterRows[i].data.id).forEach((detail, index) => {
                  row = insertRow(rowIndex+i, offset++, 1);
                  columns.forEach((columnName, currentColumnIndex) => {
                    Object.assign(row.getCell(columnIndex + currentColumnIndex), {
                      value: detail[columnName],
                      fill: {
                        type: "pattern",
                        pattern: "solid",
                        fgColor: { argb: "BEDFE6" }
                      },
                      border: {
                        bottom: borderStyle,
                        left: borderStyle,
                        right: borderStyle,
                        top: borderStyle
                      }
                    });
                  });
                });
                offset--;
              
            }
          }).then(function() {
            workbook.xlsx.writeBuffer().then(function(buffer) {
              saveAs(new Blob([buffer], { type: 'application/octet-stream' }), 'MMF30Report.xlsx');
            });
            e.component.columnOption('ID', 'visible', false);
            e.component.endUpdate();
          });

          e.cancel = true;
        }
      };
      function masterDetailTemplate(_, masterDetailOptions) {
        return $("<div>").dxTabPanel({
          items: [
            {
              title: "Detail",
              template: detaildata(masterDetailOptions.data),
            },
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

      function detaildata(masterDetailData) {
        return function () {
          return $("<div>").dxDataGrid({
            dataSource: new DevExpress.data.DataSource({
                store: new DevExpress.data.CustomStore({
                    key: "mmf30_id",
                    load: function() {
                        return CrudService.GetById('mmf30detail',masterDetailData.id)
                    }
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
                "materialcode","materialdescr","partnumber","brandmanufacturer","qty","unit","currency",
                {
                    dataField: "unitprice",
                    format: "fixedPoint",
                    editorOptions: {
                        format: "fixedPoint",
                    }
                },
                {
                    dataField: "extendedprice",
                    format: "fixedPoint",
                    editorOptions: {
                        format: "fixedPoint",
                    }
                },
                "remarks"
            ],
            summary: {
              totalItems: [
                {
                  column: "extendedprice",
                  summaryType: "sum",
                  valueFormat: {
                    format: "currency",
                    precision: 2,
                  },
                },
              ],
            },
          });
        };
      }

      function supportingdocdata(masterDetailData) {
        return function () {
          return $("<div>").dxDataGrid({
            dataSource: new DevExpress.data.DataSource({
                store: new DevExpress.data.CustomStore({
                    key: "mmf30_id",
                    load: function() {
                        return CrudService.GetById('mmf30file',masterDetailData.id)
                    }
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
                    dataField:'file_descr',
                    caption:"File Description",
                    encodeHtml: false,
                    dataType: "string",
                },
                {
                    dataField: "file_loc",
                    caption:"FileLocation",
                    width: 100,
                    allowFiltering: false,
                    allowSorting: false,
                    formItem: { visible: false},
                    cellTemplate: function (container, options) {
                        if (options.value!=""){
                            $("<div />").dxButton({
                                icon: 'download',
                                stylingMode: "contained",
                                type: "success",
                                target : '_blank',
                                width: 50,
                                height:25,
                                onClick: function (e) {
                                    window.open(options.value, '_blank');
                                }
                            }).appendTo(container);
                        };
                    }
                },
                {
                    dataField:'upload_date',
                    // width:150,
                    caption: "Upload Date",
                    dataType:"date", 
                    format: 'dd/MM/yyyy HH:mm:ss',
                    editorType: "dxDateBox",
                    editorOptions: {
                        displayFormat:"dd/MM/yyyy HH:mm:ss",
                        disabled: true
                    }
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
              module: 'MMF30',
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
                key: "mmf30_id",
                load: function () {
                  return CrudService.GetById(
                    "mmf30app",
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
                key: "mmf30_id",
                load: function () {
                  return CrudService.GetById(
                    "mmf30hist",
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

      
    },
  ]);
})(app || angular.module("kduApp"));
