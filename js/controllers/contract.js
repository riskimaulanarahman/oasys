(function (app) {
app.register.controller('contractCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
    $scope.ds={};
    $scope.test=[];
	$scope.disabled= true;
    console.log($rootScope.viewContract)
    if ((!$rootScope.isAdmin) &&  (!$rootScope.viewContract)){
		$location.path( "/" );
	}
    criteria = {status:'allcontract'};
	CrudService.FindData('contract',criteria).then(function (resp){
        $scope.contractDatasource=resp;
    });
    criteria = {status:'allrfc'};
	CrudService.FindData('contract',criteria).then(function (resp){
        $scope.rfcDatasource=resp;
    });
	CrudService.GetAll('rfccontractor').then(function (resp) {
        $scope.contractorDatasource=resp;
    });
    var myStore = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
            CrudService.checkAccess('Contract',$rootScope.curUser.username).then(function (access) {
				$scope.allowEdit = access.allowedit;
				$scope.allowAdd = access.allowadd;
				$scope.allowDel = access.allowdelete;
			});
            return CrudService.GetAll('contract').then(function (response) {
				if(response.status=="error"){
					DevExpress.ui.notify(response.message,"error");
				}else{
					return response;
				}
			});            		
		},
	 
		byKey: function(key) {
		},
		insert: function(values) {
		},
		update: function(key, values) {
		},
		remove: function(key) {
			CrudService.Delete('contractdelete',key.id).then(function (response) {
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
        columnHidingEnabled:true,
        allowColumnResizing: true,
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
								$scope.loadContract(options.data,"view",true);
                            })
                            .appendTo(container);
                        if ($scope.allowEdit && (options.data.contractstatus !=3) ){
                            $('<div style="padding:2px 15px 2px 15px;"/>').addClass('dx-icon-edit btn-pill btn-shadow btn btn-success')
                            .text('')
                            .on('dxclick', function () {
                                $scope.loadContract(options.data,"edit",true);
                            })
                            .appendTo(container);
                        }
                    }
                },
                /*{caption: '#',fixed: true, fixedPosition: "left",formItem: { visible: false},width: 40,
					cellTemplate: function(container, options) {
						container.text(options.rowIndex +1);
					}
                },*/                              
                {dataField:'rfcuser',caption:"User / RFC Creator", },
				{dataField:'contractstatus',caption:"Contact Status",encodeHtml: false ,fixed: true, fixedPosition: "left",formItem: { visible: false},width: 100,
					customizeText: function (e) {
						var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-success'>Active</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Nearly Expired</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Expired</span>","<span class='mb-2 mr-2 badge badge-pill badge-default'>Not Active</span>",""];
						return rDesc[e.value];
					}},
				{dataField:'rfc_id',caption:"RFC No",fixed: true, fixedPosition: "left",width: 150,
                    lookup: {
                        dataSource: $scope.rfcDatasource,
                        valueExpr: "id",
                        displayExpr: "rfcno" 
                    }
                },
                {dataField:'contractno',caption:"Contract No",fixed: true, fixedPosition: "left",width:190,},
                {dataField:'periodstart',caption:"Start Date",  dataType:"date", format:"dd/MM/yyyy",hidingPriority: 10,},
                {dataField:'periodend',caption:"End Date",  dataType:"date", format:"dd/MM/yyyy",hidingPriority: 9,},                
                {dataField:'companycode',caption:"BU", hidingPriority: 7}, 
                {dataField:'contractor_id',caption:"Contractor", hidingPriority: 8,
					lookup: {
						dataSource: $scope.contractorDatasource,
						valueExpr: "id",
						displayExpr: "contractorname" 
					}
                },
                {dataField:'oldcontractno',caption:"Old Contract No",width:190,hidingPriority: 6,
					lookup: {
						dataSource: $scope.contractDatasource,
						valueExpr: "id",
						displayExpr: "contractno" 
					},cellTemplate: function (container, options) {
                        if ((options.data.oldcontractno !=0) && (options.data.oldcontractno !='') && (options.data.oldcontractno != null)){
                        $('<div style="padding:2px 15px 2px 15px;"/>').addClass('btn-pill btn-shadow btn btn-focus')
                            .text(options.displayValue)
                            .on('dxclick', function () {
                                let old = { id : options.value}
								$scope.loadContract(old,"view",true);
                            })
                            .appendTo(container);
                        }
                    }
                },
                {dataField:'newcontractno',caption:"New Contract No",width:190,hidingPriority: 5,
					lookup: {
						dataSource: $scope.contractDatasource,
						valueExpr: "id",
						displayExpr: "contractno" 
					},cellTemplate: function (container, options) {
                        if ((options.data.newcontractno !=0) && (options.data.newcontractno !='') && (options.data.newcontractno != null)){
                        $('<div style="padding:2px 15px 2px 15px;"/>').addClass('btn-pill btn-shadow btn badge-focus')
                            .text(options.displayValue)
                            .on('dxclick', function () {
                                let old = { id : options.value}
								$scope.loadContract(old,"view",true);
                            })
                            .appendTo(container);
                        }
                    }
                },
                {dataField:'activitydescr',caption:"Activity", hidingPriority: 4,},
                {dataField:'description', encodeHtml: false,hidingPriority: 3, },
				{dataField:'createddate',caption:"Created Date",dataType:"date", format:"dd/MM/yyyy h:m",formItem: { visible: false},width: 110,hidingPriority: 2, },
                {dataField:'ratetype',caption:"Rate",hidingPriority: 1, },
                {dataField:'skno',caption:"SK No", hidingPriority: 0, },
				/*{
                    dataField: "contractdocument",
                    caption:"Contract Doc",
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
                        $("<div>")
                            .append($("<a href> Download</a>", {"width":"100%", "href": options.value }))
                            .appendTo(container)
                            .on("click",function(){
                                if (options.value!=""){
                                    $scope.imageAddress = options.value;
                                    $scope.imageDescription ="Initiated By :"+options.data.InitiatedBy;
                                    $scope.imgPopupTitle = "Payment Number : "+options.data.PaymentNumber;
                                    $scope.imgPopupVisible = true;
                                }
                                
                            }
                            );
                    }
                },*/
                ],	
        "export": {
            enabled: true,
            fileName: "ExportGrid",
            allowExportSelectedData: false
        },
		bindingOptions :{
            "columns[3].lookup.dataSource":"rfcDatasource",
            "columns[9].lookup.dataSource":"contractDatasource",
            "columns[10].lookup.dataSource":"contractDatasource",
            "columns[7].lookup.dataSource":"contractorDatasource",
            "editing.allowDeleting": "allowDel" ,
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
            mode: "single"
        },
        editing: {
            useIcons:true,
            mode: "popup",
            allowDeleting: ($rootScope.isAdmin)?true:false, // Enables editing
            //allowAdding: ($rootScope.isAdmin)?true:false, // Enables insertion
            form:{colCount: 2,
            },
            popup: {  
                title: "Form Data Contract Register",  
                showTitle: true,
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
        onSelectionChanged: function(data) {
            $scope.selectedItems = data.selectedRowsData;
            $scope.disabled = !$scope.selectedItems.length;
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
					hint: "Register Contract",
					icon: "add",
					onClick: function() {
						var date = new Date();
						var d= $filter("date")(date, "yyyy-MM-dd HH:mm")
						$scope.loadContract({CreatedDate:d},"add",true);
					}
				}
			}
			/*{
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Delete Data",
                    bindingOptions :{
                        disabled:"disabled"
                    },
                    icon: "trash",
                    onClick: function() {
                        if (!$scope.allowDel){
                            DevExpress.ui.notify("You don't have authority to delete data","error");
                        } else{
                            var result = DevExpress.ui.dialog.confirm("Are you sure you want to delete selected?", "Delete row");
                            result.done(function (dialogResult) {
                                if (dialogResult){
                                    $.each($scope.dataGrid.getSelectedRowsData(), function() {
                                        myStore.remove(this);										
                                    });
                                    $scope.dataGrid.refresh();
                                }
                            });
                        }
                    }
                }
            },*/);
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