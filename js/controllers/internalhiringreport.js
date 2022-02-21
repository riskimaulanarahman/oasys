(function (app) {
    app.register.controller("internalhiringreportCtrl", [
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
        if (!$rootScope.isAdmin && !$rootScope.viewInternalhiringreport) {
          $location.path("/");
        }
        var myStore = new DevExpress.data.CustomStore({
          load: function () {
            $scope.isLoaded = true;
            criteria = { status: "all" };
            return CrudService.FindData("internalhiring", criteria).then(function (
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
            CrudService.GetById("internalhiring", encodeURIComponent(key)).then(
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
            // values.materialreturneddate = $filter("date")(
            //   values.materialreturneddate,
            //   "yyyy-MM-dd HH:mm"
            // );
            // values.action = "updatereport";
            CrudService.Update("internalhiring", key.id, values).then(function (
              response
            ) {
              if (response.status == "error") {
                DevExpress.ui.notify(response.message, "error");
              }
              $scope.dataGrid.refresh();
            });
          },
          remove: function (key) {
            CrudService.Delete('internalhiring',key.id).then(function (response) {
                if(response.status=="error"){
                     DevExpress.ui.notify(response.message,"error");
                }
                $scope.dataGrid.refresh();
            });
          },
        });
        // $scope.allowDel = false;
        $scope.$on("initInternalhiring", function (event, name) {
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

        $scope.Companyid = {
          store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
              return CrudService.GetAll('company').then(function (response) {
                if(response.status=="error"){
                  DevExpress.ui.notify(response.message,"error");
                }else{
                  return response;
                }
              });
            },
          }),
          sort: "id"
        }
        $scope.Departmentid = {
          store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
              return CrudService.GetAll('dept').then(function (response) {
                if(response.status=="error"){
                  DevExpress.ui.notify(response.message,"error");
                }else{
                  return response;
                }
              });
            },
          }),
          sort: "id"
        }
        $scope.Loc = {
          store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
              return CrudService.GetAll('loc').then(function (response) {
                if(response.status=="error"){
                  DevExpress.ui.notify(response.message,"error");
                }else{
                  return response;
                }
              });
            },
          }),
          sort: "id"
        }
        $scope.Des = {
          store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
              return CrudService.GetAll('des').then(function (response) {
                if(response.status=="error"){
                  DevExpress.ui.notify(response.message,"error");
                }else{
                  return response;
                }
              });
            },
          }),
          sort: "id"
        }
        $scope.Level = {
          store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
              return CrudService.GetAll('level').then(function (response) {
                if(response.status=="error"){
                  DevExpress.ui.notify(response.message,"error");
                }else{
                  return response;
                }
              });
            },
          }),
          sort: "id"
        }

        
		  	$scope.Status = [{id:0,namastatus:"Canceled"},{id:1,namastatus:"Waiting"},{id:2,namastatus:"Doc. Selection"},{id:3,namastatus:"Assesment"},{id:4,namastatus:"Interview"},{id:5,namastatus:"Approved"},{id:6,namastatus:"Rejected"}];
        //$scope.myData = myData;
        $scope.dataGridOptions = {
          dataSource: myData,
          showColumnLines: true,
          showRowLines: true,
          rowAlternationEnabled: true,
          allowColumnResizing: true,
          columnResizingMode: "widget",
          columnHidingEnabled: true,
          columnsAutoWidth: false,
          columnMinWidth: 80,
          wordWrapEnabled: false,
          showBorders: true,
          height: 600,
          headerFilter: {
            visible: true,
          },
          groupPanel: {
            emptyPanelText: 'Use the context menu of header columns to group data',
            visible: false,
          },
          columns: [
            {
              caption: "#",
              formItem: { visible: false },
              width: 40,
              cellTemplate: function (container, options) {
                container.text(options.rowIndex + 1);
              },
            },
            {
              dataField: "status",
              caption: "Status",
              editorType: "dxSelectBox",
              encodeHtml: false,
              width: 100,
              customizeText: function (e) {
                var rDesc = [
                  "<span class='mb-2 mr-2 badge badge-pill badge-secondary'>Canceled</span>",
                  "<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting</span>",
                  "<span class='mb-2 mr-2 badge badge-pill badge-primary'>Doc. Selection</span>",
                  "<span class='mb-2 mr-2 badge badge-pill badge-warning'>Assesment</span>",
                  "<span class='mb-2 mr-2 badge badge-pill badge-warning'>Interview</span>",
                  "<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>",
                  "<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",
                  "",
                ];
                return rDesc[e.value];
              },
              editorOptions: {
                dataSource: $scope.Status,
                valueExpr: "id",
                displayExpr: "namastatus",
                disabled: false,
              },
              // editorOptions: {
              //   disabled: false,
              // },
            },
            {
              dataField: "lampiran",
              caption: "Letter Approval",
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
            {
                dataField: "createddate",
                caption: "Creation Date",
                dataType: "date",
                format: "dd/MM/yyyy",
                //   width: 200,
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "sapid",
                caption: "SAPID",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "passcode",
                caption: "passcode",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "postno",
                editorOptions: {
                    disabled: true,
                },
                width: 220
            },
            {
                dataField: "fullname",
                caption: "fullname",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "nohp",
                caption: "nohp",
                editorOptions: {
                    disabled: true,
                },
            },
            {
              dataField: "company_id",
              caption: "Company",
              lookup: {
                dataSource: $scope.Companyid,
                valueExpr: "id",
                displayExpr: "companycode",
                disabled: true,
              },
              editorOptions: {
                disabled: true,
              },
              hidingPriority: 0,
            },
            {
              dataField: "department_id",
              caption: "Department",
              lookup: {
                dataSource: $scope.Departmentid,
                valueExpr: "id",
                displayExpr: "departmentname",
                disabled: true,
              },
              editorOptions: {
                disabled: true,
              },
              hidingPriority: 1,
            },
            {
              dataField: "location_id",
              caption: "Location",
              lookup: {
                dataSource: $scope.Loc,
                valueExpr: "id",
                displayExpr: "location",
                disabled: true,
              },
              editorOptions: {
                disabled: true,
              },
              hidingPriority: 2,
            },
            {
              dataField: "designation_id",
              caption: "Designation",
              lookup: {
                dataSource: $scope.Des,
                valueExpr: "id",
                displayExpr: "designationname",
                disabled: true,
              },
              editorOptions: {
                disabled: true,
              },
              hidingPriority: 3,
            },
            {
              dataField: "level_id",
              caption: "Level",
              lookup: {
                dataSource: $scope.Level,
                valueExpr: "id",
                displayExpr: "level",
                disabled: true,
              },
              editorOptions: {
                disabled: true,
              },
              hidingPriority: 3,
            },
            {
                dataField: "dob",
                caption: "Date of Birth",
                dataType: "date",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "age",
                caption: "age",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "joindate",
                caption: "Join Date",
                dataType: "date",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "los",
                caption: "LOS",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "gender",
                caption: "gender",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "education",
                caption: "education",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "educationothers",
                caption: "education others",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "reasonmove",
                caption: "reason move",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "reasondeserve",
                caption: "reason deserve",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "reasoncontribution",
                caption: "reason contribution",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "score1",
                caption: "score 1",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "score2",
                caption: "score 2",
                editorOptions: {
                    disabled: true,
                },
            },
            {
                dataField: "score3",
                caption: "score3",
                editorOptions: {
                    disabled: true,
                },
            },
            
          ],
          export: {
            enabled: true,
            fileName: "internal hiring applyment "+ new Date(),
            allowExportSelectedData: false,
          },
        //   masterDetail: {
        //     enabled: true,
        //     template: masterDetailTemplate,
        //   },
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
            allowUpdating: true,
            allowAdding: false,
            allowDeleting: ($rootScope.isAdmin)?true:false,
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
          // scrolling: {
          //   mode: "infinite",
          // },
          onContentReady: function (e) {
            moveEditColumnToLeft(e.component);
          },
          onCellPrepared: function (e) {

          },
          onEditingStart: function (e) {
            e.component.columnOption("id", "allowEditing", false);
          },
          onEditorPreparing: function (e) {
         
          },
          onEditorPrepared: function (e) {},
          onInitNewRow: function (e) {
          },
          onSelectionChanged: function (data) {
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
  