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
      var myStore = new DevExpress.data.CustomStore({
        load: function () {
          $scope.isLoaded = true;
          criteria = { filter: "all" };
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
      $scope.$on("initMMF30", function (event, name) {
        $scope.dataGrid.refresh();
      });
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
            width: 200,
          },
          {
            dataField: "fullname",
            caption: "Request For Employee",
            width: 200,
          },
          {
            dataField: "requeststatus",
            encodeHtml: false,
            width: 300,
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

      
    },
  ]);
})(app || angular.module("kduApp"));
