(function (app) {
  app.register.controller("trreportCtrl", [
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
      if (!$rootScope.isAdmin && !$rootScope.viewTR) {
        $location.path("/");
      }
      var myStore = new DevExpress.data.CustomStore({
        load: function () {
          $scope.isLoaded = true;
          criteria = { filter: "all" };
          return CrudService.FindData("trapp", criteria).then(function (
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
          CrudService.GetById("trapp", encodeURIComponent(key)).then(function (
            response
          ) {
            return response;
          });
        },
        insert: function (values) {},
        update: function (key, values) {},
        remove: function (key) {},
      });
      $scope.$on("initTR", function (event, name) {
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
      CrudService.GetAll("rfcactivity").then(function (resp) {
        $scope.activityDatasource = resp;
      });
      CrudService.GetAll("rfccontractor").then(function (resp) {
        $scope.contractorDatasource = resp;
      });
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
          visible: true,
        },
        columns: [
          {
            caption: "Detail",
            fixed: true,
            fixedPosition: "left",
            width: 120,
            allowFiltering: false,
            allowSorting: false,
            formItem: { visible: false },
            cellTemplate: function (container, options) {
              $('<div style="padding:2px 15px 2px 15px;"/>')
                .addClass(
                  "dx-icon-detailslayout  btn-pill btn-shadow btn btn-primary"
                )
                .text("")
                .on("dxclick", function () {
                  $scope.loadTR(options.data, "report", false);
                })
                .appendTo(container);
              if (options.data.requeststatus == "3") {
                $(
                  '<div style="padding:2px 15px 2px 15px;" title="Reschedule"/>'
                )
                  .addClass(
                    "dx-icon-repeat btn-pill btn-shadow btn btn-success"
                  )
                  .text("")
                  .on("dxclick", function () {
                    // if (!$scope.allowEdit){
                    // DevExpress.ui.notify("You don't have authority to edit data","error");
                    // } else{
                    $scope.loadTR(options.data, "reschedule", true);
                    // }
                  })
                  .appendTo(container);
              }
            },
          },
          {
            caption: "#",
            fixed: true,
            fixedPosition: "left",
            formItem: { visible: false },
            width: 40,
            cellTemplate: function (container, options) {
              container.text(options.rowIndex + 1);
            },
          },
          {
            dataField: "createddate",
            caption: "Creation Date",
            dataType: "date",
            format: "dd/MM/yyyy",
            width: 90,
          },
          {
            dataField: "fullname",
            caption: "Request By",
            fixed: true,
            fixedPosition: "left",
          },
          {
            dataField: "laststatus",
            caption: "Last Status",
            fixed: true,
            fixedPosition: "left",
          },
          {
            dataField: "personholding",
            caption: "PIC",
            fixed: true,
            fixedPosition: "left",
          },
          {
            dataField: "requeststatus",
            encodeHtml: false,
            fixed: true,
            fixedPosition: "left",
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
                    icon: "exportpdf",
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
        bindingOptions: {
          //"editing.allowUpdating": "allowEdit" ,
          //"editing.allowAdding": "allowAdd" ,
          //"editing.allowDeleting": "allowDel" ,
          //"columns[3].lookup.dataSource":"divDatasource"
        },
        masterDetail: {
          enabled: true,
          template: masterDetailTemplate,
        },
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
        /*selection: {
            mode: "multiple"
        },*/
        editing: {
          useIcons: true,
          mode: "popup",
          allowUpdating: false,
          allowAdding: false,
          allowDeleting: false,
          //allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
          //allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
          form: { colCount: 1 },
          popup: {
            title: "Form Data Designation",
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
              title: "Travel Schedule",
              template: detail1(masterDetailOptions.data),
            },
            {
              title: "Detail Ticket",
              template: detail2(masterDetailOptions.data),
            },
            {
              title: "Supporting Document",
              template: supportingdocdata(masterDetailOptions.data),
            },
          ],
        });
      }

      function detail1(masterDetailData) {
        return function () {
          return $("<div>").dxDataGrid({
            dataSource: new DevExpress.data.DataSource({
              store: new DevExpress.data.CustomStore({
                key: "tr_id",
                load: function () {
                  return CrudService.GetById("trschedule", masterDetailData.id);
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
                dataField: "departdate",
                caption: "Depart Date",
                dataType: "date",
                format: "dd/MM/yyyy",
                editorType: "dxDateBox",
                editorOptions: {
                  displayFormat: "dd/MM/yyyy",
                },
              },
              {
                dataField: "departtime",
                caption: "Depart Time",
                dataType: "date",
                format: "HH:mm",
                editorOptions: {
                  displayFormat: "HH:mm",
                  type: "time",
                },
              },
              {
                dataField: "departfrom",
                caption: "From",
                dataType: "string",
              },
              {
                dataField: "arrivingdate",
                caption: "Arriving Date",
                dataType: "date",
                format: "dd/MM/yyyy",
                editorType: "dxDateBox",
                editorOptions: {
                  displayFormat: "dd/MM/yyyy",
                },
              },
              {
                dataField: "arrivingtime",
                caption: "Arriving Time",
                format: "HH:mm",
                dataType: "date",
                editorOptions: {
                  displayFormat: "HH:mm",
                  type: "time",
                },
              },
              {
                dataField: "arrivingto",
                caption: "To",
                dataType: "string",
              },
              {
                dataField: "region",
                caption: "Region",
                dataType: "string",
              },
              {
                dataField: "reason",
                dataType: "string",
              },
            ],
          });
        };
      }

      function detail2(masterDetailData) {
        return function () {
          return $("<div>").dxDataGrid({
            dataSource: new DevExpress.data.DataSource({
              store: new DevExpress.data.CustomStore({
                key: "tr_id",
                load: function () {
                  return CrudService.GetById("trticket", masterDetailData.id);
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
                dataField: "ticketfor",
                caption: "Ticket For",
                dataType: "string",
              },
              {
                dataField: "ticketname",
                caption: "Name",
                dataType: "string",
              },
              {
                dataField: "dateofbirth",
                width: 100,
                caption: "Date of Birth",
                dataType: "date",
                format: "dd/MM/yyyy",
                editorType: "dxDateBox",
                editorOptions: {
                  displayFormat: "dd/MM/yyyy",
                },
              },
              {
                dataField: "phonenumber",
                caption: "Phone Number",
                dataType: "string",
              },
              {
                dataField: "gender",
                caption: "Gender",
                dataType: "string",
              },
              {
                dataField: "hrremarks",
                caption: "Remarks / Confirmation from HR (Konfirmasi dari HR)",
                dataType: "string",
              },
            ],
          });
        };
      }

      function supportingdocdata(masterDetailData) {
        return function () {
          return $("<div>").dxDataGrid({
            dataSource: new DevExpress.data.DataSource({
              store: new DevExpress.data.CustomStore({
                key: "tr_id",
                load: function () {
                  return CrudService.GetById("trfile", masterDetailData.id);
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
    },
  ]);
})(app || angular.module("kduApp"));
