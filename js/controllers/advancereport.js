(function (app) {
  app.register.controller("advancereportCtrl", [
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
      if (!$rootScope.isAdmin && !$rootScope.viewAdvance) {
        $location.path("/");
      }
      var myStore = new DevExpress.data.CustomStore({
        load: function () {
          $scope.isLoaded = true;
          criteria = { filter: "all" };
          return CrudService.FindData("advanceapp", criteria).then(function (
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
          CrudService.GetById("advanceapp", encodeURIComponent(key)).then(
            function (response) {
              return response;
            }
          );
        },
        insert: function (values) {
          // CrudService.Create('advance',values).then(function (response) {
          //     if(response.status=="error"){
          //          DevExpress.ui.notify(response.message,"error");
          //     }
          //     $scope.dataGrid.refresh();
          // });
        },
        update: function (key, values) {
          values.materialreturneddate = $filter("date")(
            values.materialreturneddate,
            "yyyy-MM-dd HH:mm"
          );
          values.action = "updatereport";
          CrudService.Update("advance", key.id, values).then(function (
            response
          ) {
            if (response.status == "error") {
              DevExpress.ui.notify(response.message, "error");
            }
            $scope.dataGrid.refresh();
          });
        },
        remove: function (key) {
          // CrudService.Delete('advance',key.id).then(function (response) {
          //     if(response.status=="error"){
          //          DevExpress.ui.notify(response.message,"error");
          //     }
          //     $scope.dataGrid.refresh();
          // });
        },
      });
      // $scope.allowDel = false;
      $scope.$on("initAdvance", function (event, name) {
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
                  $scope.loadAdvance(options.data, "report", true);
                })
                .appendTo(container);
                if((options.data.approveddoc=='' || options.data.approveddoc==null)  && (options.data.requeststatus=='3')){
                    $('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-repeat  btn-pill btn-shadow btn btn-success')
                        .text('')
                        .on('dxclick', function () {
                            DevExpress.ui.notify("Please Wait...","info");
                            $.ajax({
                                url: 'api/apiadvancepdf?id='+options.data.id,
                                type: 'get',
                                cache: false,
                                success: function(data){
                                    console.log(data);
                                    if(data == 200) {
                                        DevExpress.ui.notify("Success Re-PDF","success");
                                    }else {
                                        alert(data); 
                                    }
                                    $scope.dataGrid.refresh();

                                 },
                                 error: function(data){
                                    var json = $.parseJSON(data);
                                    alert('error '+json);
                                    $scope.dataGrid.refresh();

                                 }
                              });
                        })
                        .appendTo(container);
                }
              // if((options.data.requeststatus=='0') || (options.data.requeststatus=='2')){
              // if((options.data.requeststatus=='3')){
              //     $('<div style="padding:2px 15px 2px 15px;" title="Edit" />').addClass('dx-icon-edit btn-pill btn-shadow btn btn-success')
              //     .text('')
              //     .on('dxclick', function () {
              // if (!$scope.allowEdit){
              //     DevExpress.ui.notify("You don't have authority to edit data","error");
              // } else{
              // $scope.loadMMF(options.data,"editb",true);
              // }
              //     })
              //     .appendTo(container);
              // }else{
              //     $('<div style="padding:2px 15px 2px 15px;"/>').text('').appendTo(container);
              // }
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
          // {dataField:'mmfnumber',caption:"MMF Number", editorOptions: {
          //     disabled: true,
          // }},
          {
            dataField: "createddate",
            caption: "Creation Date",
            dataType: "date",
            format: "dd/MM/yyyy",
            width: 200,
            editorOptions: {
              disabled: true,
            },
          },
          {
            dataField: "advanceno",
            caption: "Advance No",
            width: 200,
            editorOptions: {
              disabled: true,
            },
          },
          {
            dataField: "fullname",
            caption: "Name",
            width: 200,
            editorOptions: {
              disabled: true,
            },
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
            editorOptions: {
              disabled: true,
            },
          },

          {
            dataField: "personholding",
            caption: "Next Approver",
            editorOptions: {
              disabled: true,
            },
          },
          // {dataField:'apprbuyername',caption:"Buyer Name", editorOptions: {
          //     disabled: true,
          // }},
          // {dataField:'apprprocheaddate',caption:"Appr ProcHead Date", editorOptions: {
          //     disabled: true,
          // }},
          // {dataField:'apprbuyerdate',caption:"Appr Buyer Date", editorOptions: {
          //     disabled: true,
          // }},
          // {dataField:'pono',caption:"PO Number"},
          // {dataField:'materialreturneddate',caption:"Material Returned Date",dataType:"date", format:"dd/MM/yyyy",editorType: "dxDateBox",editorOptions: {displayFormat:"dd/MM/yyyy",disabled: false}},
          // {dataField:'supplierdodnno',caption:"Supplier DO/DN No"},
          // {dataField:'materialdispatchno',caption:"Material Dispatch No"},
          // {dataField:'isrepair',caption:"Repair",dataType: "boolean", showEditorAlways: true },
          // {dataField:'isscrap',caption:"Scrapped",dataType: "boolean", showEditorAlways: true },
          {
            dataField: "remarks",
            encodeHtml: false,
            editorOptions: {
              disabled: true,
            },
          },
          {
            dataField: "approveddoc",
            caption: "Approval Doc",
            width: 100,
            allowFiltering: false,
            allowSorting: false,
            formItem: { visible: false },
            editorOptions: {
              disabled: true,
            },
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
        bindingOptions: {
          // "editing.allowUpdating": "allowEdit" ,
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
        editing: {
          useIcons: true,
          mode: "row",
          allowUpdating: false,
          allowAdding: false,
          allowDeleting: false,
          form: { colCount: 1 },
          popup: {
            title: "Form Data access directory",
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
          // if (e.columnIndex == 0 && e.rowType == "data") {
          //     if((e.data.requeststatus!==0) && (e.data.requeststatus!==2) ){
          //         e.cellElement.find(".dx-link-delete").remove();
          //     }
          // }
        },
        onEditingStart: function (e) {
          e.component.columnOption("id", "allowEditing", false);
        },
        onEditorPreparing: function (e) {
          $scope.formComponent = e.component;
          if (e.dataField == "isfinal") {
            e.editorName = "dxSwitch";
            e.editorOptions.switchedOnText = "Yes";
            e.editorOptions.switchedOffText = "No";
          }
          if (e.dataField == "isactive") {
            e.editorName = "dxSwitch";
            e.editorOptions.switchedOnText = "Yes";
            e.editorOptions.switchedOffText = "No";
          }
          if (e.row && e.row.data.requeststatus !== 3) {
            e.editorOptions.disabled = true;
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
              title: "Advance Detail",
              template: detail1(masterDetailOptions.data),
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
                key: "advance_id",
                load: function () {
                  return CrudService.GetById(
                    "advancedetail",
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
            columns: [
              {
                dataField: "description",
                dataType: "string",
              },
              {
                dataField: "accountcode",
                caption: "Account Code",
                dataType: "string",
              },
              {
                dataField: "amount",
                caption: "Amount",
                dataType: "number",
                format: "fixedPoint",
                editorOptions: {
                  format: "fixedPoint",
                },
              },
              {
                dataField: "remarks",
                dataType: "string",
              },
            ],
            summary: {
              totalItems: [
                {
                  column: "amount",
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
                key: "advance_id",
                load: function () {
                  return CrudService.GetById(
                    "advancefile",
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
