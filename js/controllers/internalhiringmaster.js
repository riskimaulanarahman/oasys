(function (app) {
  app.register.controller('internalhiringmasterCtrl', ['$rootScope', '$scope', '$http', '$interval', '$location', 'CrudService', 'AuthenticationService', function ($rootScope, $scope, $http, $interval, $location, CrudService, AuthenticationService) {
    $scope.ds = {};
    $scope.test = [];
    $scope.disabled = true;
    if ((!$rootScope.isAdmin) && (!$rootScope.viewInternalhiringmaster)) {
      $location.path("/");
    }
    var myStore = new DevExpress.data.CustomStore({
      load: function () {
        $scope.isLoaded = true;
        return CrudService.GetAll('internalhiringmaster').then(function (response) {
          if (response.status == "error") {
            DevExpress.ui.notify(response.message, "error");
          } else {
            return response;
          }
        });
      },

      byKey: function (key) {
        CrudService.GetById('emp', encodeURIComponent(key)).then(function (response) {
          return response;
        });
      },

      insert: function (values) {
        CrudService.Create('internalhiringmaster', values).then(function (response) {
          if (response.status == "error") {
            DevExpress.ui.notify(response.message, "error");
          }
          $scope.dataGrid.refresh();
        });
      },

      update: function (key, values) {
        CrudService.Update('internalhiringmaster', key.id, values).then(function (response) {
          if (response.status == "error") {
            DevExpress.ui.notify(response.message, "error");
          }
          $scope.dataGrid.refresh();
        });

      },
      remove: function (key) {
        CrudService.Delete('internalhiringmaster', key.id).then(function (response) {
          if (response.status == "error") {
            DevExpress.ui.notify(response.message, "error");
          }
          $scope.dataGrid.refresh();
        });
      }
    });

    var myData = new DevExpress.data.DataSource({
      store: myStore
    });
    CrudService.GetAll('div').then(function (resp) {
      $scope.divDatasource = resp;
      //console.log($scope.roles);
    });
    CrudService.GetAll('des').then(function (resp) {
      $scope.desDatasource = resp;
      //console.log($scope.roles);
    });
    CrudService.GetAll('dept').then(function (resp) {
      $scope.deptDatasource = resp;
      //console.log($scope.roles);
    });
    CrudService.GetAll('company').then(function (resp) {
      $scope.compDatasource = resp;
      //console.log($scope.roles);
    });
    CrudService.GetAll('grade').then(function (resp) {
      $scope.gradeDatasource = resp;
      //console.log($scope.roles);
    });
    CrudService.GetAll('level').then(function (resp) {
      $scope.levelDatasource = resp;
      //console.log($scope.roles);
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
      // editing: {
      //   useIcons:true,
      //   allowUpdating:true,
      //   allowAdding:true,
      //   allowDeleting:true,
      //   form:{colCount: 1,
      //   },
      // },
      columns: [
        // {
        //   caption: "Detail",
        //   fixed: true,
        //   fixedPosition: "right",
        //   width: 120,
        //   allowFiltering: false,
        //   allowSorting: false,
        //   formItem: { visible: false},
        //   },
        {
          caption: '#',
          fixed: true,
          formItem: {
            visible: false
          },
          width: 40,
          cellTemplate: function (container, options) {
            container.text(options.rowIndex + 1);
          }
        },
        {
          dataField: "postno",
          editorOptions: {
            disabled: true,
          }
        },
        {
          dataField: "bu",
          caption: "BU",
          sortOrder: "asc",
          // lookup: {
          //   dataSource: $scope.compDatasource,
          //   valueExpr: "id",
          //   displayExpr: "companyname" 
          // }		
        },
        {
          dataField: "department",
          caption: "Department",
          // lookup: {
          //   dataSource: $scope.compDatasource,
          //   valueExpr: "id",
          //   displayExpr: "companyname" 
          // }		
        },
        {
          dataField: "worklocation",
          caption: "Work Location",
          // lookup: {
          //   dataSource: $scope.compDatasource,
          //   valueExpr: "id",
          //   displayExpr: "companyname" 
          // }		
        },
        {
          dataField: "position",
          caption: "Position",
          sortOrder: "asc",
        },
        {
          dataField: "positioncode",
          caption: "Position Code",
          validationRules: [{type: "required"}],
          // dataType: 'string'
        },
        {
          dataField: 'level',
          encodeHtml: false,
          customizeText: function (e) {
            var rDesc = ["", "Mandor", "Asst", "Askep", "Manager", ""];
            return rDesc[e.value];
          }
        },
        {
          dataField: 'expireddate',
          caption: "Expired Date",
          dataType: "date",
          format: "dd/MM/yyyy",
        },
        // {
        //   dataField: "department_id",
        //   caption: "Department",
        //   setCellValue: function(rowData, value) {
        //     rowData.department_id = value;
        //     rowData.division_id = null;
        //     rowData.designation_id = null;
        //   },
        //   lookup: {
        //     dataSource: $scope.deptDatasource,
        //     valueExpr: "id",
        //     displayExpr: "departmentname" 
        //   }		
        // },{
        //   dataField: "division_id",
        //   caption: "Division",
        //   visible:false,
        //   setCellValue: function(rowData, value) {
        //     rowData.division_id = value;
        //     rowData.designation_id = null;
        //   },
        //   lookup: {
        //     dataSource: function (options) {
        //       return {
        //         store: $scope.divDatasource,
        //         filter: options.data ? ["department_id", "=", options.data.department_id] : null
        //       };
        //     },
        //     valueExpr: "id",
        //     displayExpr: "divisionname" 
        //   }		
        // },{
        //   dataField: "designation_id",
        //   caption: "Designation",
        //   lookup: {
        //     dataSource: function (options) {
        //       return {
        //         store: $scope.desDatasource,
        //         filter: options.data ? ["division_id", "=", options.data.division_id] : null
        //       };
        //     },
        //     valueExpr: "id",
        //     displayExpr: "designationname" 
        //   }		
        // },
        // {
        //   dataField: "grade_id",
        //   visible:false,
        //   caption: "Grade",
        //   lookup: {
        //     dataSource: $scope.gradeDatasource,
        //     valueExpr: "id",
        //     displayExpr: "grade" 
        //   }		
        // },
        // {
        //   dataField: "level_id",
        //   caption: "Level",
        //   lookup: {
        //     dataSource: $scope.levelDatasource,
        //     valueExpr: "id",
        //     displayExpr: "level" 
        //   }		
        // },
        // {dataField:'loginname',caption:'User Login'},
        // {dataField:'joindate',visible:false,caption:'Join Date'},
        // {dataField:'gender',visible:false,caption:'Gender'},
        // {dataField:'address',visible:false,caption:'Address'},
        // {dataField:'religion',visible:false,caption:'Religion'},
        // {dataField:'maritalstatus',visible:false,caption:'Marital Status'},
        // {dataField:'isinternationalstaff',dataType: "boolean", showEditorAlways: true ,visible:false,caption:'International Staff'},
      ],
      "export": {
        enabled: true,
        fileName: "ExportGrid",
        allowExportSelectedData: false
      },
      bindingOptions: {
        //"editing.allowUpdating": "allowEdit" ,
        //"editing.allowAdding": "allowAdd" ,
        // "columns[4].lookup.dataSource":"compDatasource",
        // "columns[5].lookup.dataSource":"deptDatasource",
        // "columns[8].lookup.dataSource":"gradeDatasource",
        // "columns[9].lookup.dataSource":"levelDatasource",
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
      selection: {
        mode: "multiple"
      },
      editing: {
        useIcons: true,
        mode: "row",
        allowUpdating: true,
        allowAdding: true,
        //allowUpdating: ($rootScope.isAdmin)?true:false, // Enables editing
        //allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
        // form:{colCount: 1,
        // },
        // popup: {  
        //     title: "Form Data Designation",  
        //     showTitle: true  
        // }, 
      },
      searchPanel: {
        visible: true,
        width: 240,
        placeholder: "Search..."
      },
      scrolling: {
        mode: "infinite"
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
          e.editorOptions.disabled = (typeof e.row.data.department_id !== "number");
        }
        if (e.parentType === "dataRow" && e.dataField === "designation_id") {
          e.editorOptions.disabled = (typeof e.row.data.division_id !== "number");
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
            }
          }
        }, {
          location: "after",
          widget: "dxButton",
          options: {
            hint: "Delete Data",
            bindingOptions: {
              disabled: "disabled"
            },
            icon: "trash",
            onClick: function () {
              // if (!$scope.allowDel) {
              //   DevExpress.ui.notify("You don't have authority to delete data", "error");
              // } else {
                var result = DevExpress.ui.dialog.confirm("Are you sure you want to delete selected?", "Delete row");
                result.done(function (dialogResult) {
                  if (dialogResult) {
                    $.each($scope.dataGrid.getSelectedRowsData(), function () {
                      myStore.remove(this);
                    });
                    $scope.dataGrid.refresh();
                  }
                });
              // }
            }
          }
        }, 
        // {
        //   location: "after",
        //   widget: "dxButton",
        //   options: {
        //     hint: "Add Employee",
        //     icon: "add",
        //     onClick: function () {
        //       if (!$scope.allowAdd) {
        //         DevExpress.ui.notify("You don't have authority to Add data", "error");
        //       } else {
        //         $scope.loadEmployee({}, "add");
        //       }
        //     }
        //   }
        // }
        );
      },
      onContextMenuPreparing: function (e) {
        var dataGrid = e.component;
      },
      onInitialized: function (e) {
        $scope.gridInstance = e.component;
        $scope.ds = e.component.getDataSource();
      },
    };

  }]);
})(app || angular.module("kduApp"));