(function (app) {
    app.register.controller('itimaildetailCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService','$filter', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService,$filter)  {
        $scope.ds={};
        $scope.test=[];
        $scope.disabled= true;
        $scope.data = [];  
        if (typeof($scope.mode)=="undefined"){
            $location.path( "/" );
        }
	console.log($scope.mode);
	CrudService.GetById('itimail',$scope.Requestid).then(function(response){
		if(response.status=="autherror"){
			$scope.logout();
		}else{
            $scope.data = response;
			if (($scope.data.formtype==5)){
				$scope.data.membername = Array.isArray($scope.data.membername)?$scope.data.membername:$scope.data.membername.split(",")
				$scope.data.membername = $.map($scope.data.membername, function(value){
					return parseInt(value, 10);
				});
			}
            // $scope.data.department = "";
			if(($scope.mode=='approve')){
				$scope.data.remarks="";
			}

			$scope.allEmpDataSource = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						return CrudService.GetAll('emp').then(function (response) {
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
            // $scope.buyerDataSource = {
            //     store: new DevExpress.data.CustomStore({
            //         key: "employee_id",
            //         loadMode: "raw",
            //         load: function() {
            //             criteria = {module:'itimail',type:'buyer',mode:$scope.mode};
            //             return CrudService.FindData('appr',criteria);
            //         },
            //     }),
            //     sort: "employee_id"
            // }

			$scope.deptEmpDataSource = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						criteria = {filter:'bydept',dept:$scope.data.department};
						return CrudService.FindData('emp',criteria).then(function (response) {
							if(response.status=="error"){
								DevExpress.ui.notify(response.message,"error");
							}else{
								return response;
							}
						});
					}
				}),
				sort: "id"
			}
			$scope.allDeptEmpDataSource = {
				store: new DevExpress.data.CustomStore({
					key: "id",
					loadMode: "raw",
					load: function() {
						criteria = {filter:'bydept2',dept:$scope.data.department};
						return CrudService.FindData('emp',criteria).then(function (response) {
							if(response.status=="error"){
								DevExpress.ui.notify(response.message,"error");
							}else{
								return response;
							}
						});
					}
				}),
				sort: "id"
            }

            $scope.compDatasource = {
                store: new DevExpress.data.CustomStore({
                    key: "companycode",
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
                filter:['isused','1'],
                sort: "companycode"
            }

            $scope.listmod = {
                store: new DevExpress.data.CustomStore({
                    key: "id",
                    loadMode: "raw",
                    load: function() {
                        return CrudService.GetAll('listmod').then(function (response) {
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

            $scope.deptDatasource = {
                store: new DevExpress.data.CustomStore({
                    key: "departmentname",
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
                filter:['isused','1'],
                sort: "departmentname"
            }

            date = new Date().getTime();

            console.log($scope.data);

            $scope.FormType =[{id:0,formtype:"- Select -"},{id:1,formtype:"Exchange - Internet Email"},{id:2,formtype:"Internet Access"},{id:3,formtype:"Increase Mailbox Size"},{id:4,formtype:"RD Web Access"},{id:5,formtype:"Email Group"}];

            $scope.AccessRequested =[{id:0,accessrequested:"- Select -"},{id:1,accessrequested:"Exchange (non-Internet) Email"},{id:2,accessrequested:"Internet Email"},{id:3,accessrequested:"Change Domain"}];
            $scope.AccessType =[{id:0,accesstype:"- Select -"},{id:1,accesstype:"Terminal Server (TS) User Account"},{id:2,accesstype:"Non-TS Account"}];
            $scope.AccountType =[{id:0,accounttype:"- Select -"},{id:1,accounttype:"Permanent"},{id:2,accounttype:"Temporary"}];
            $scope.RequestType =[{id:0,requesttype:"- Select -"},{id:1,requesttype:"Grant Access"},{id:2,requesttype:"Revoke Access"}];
            $scope.EmailQuota =[{id:0,emailquota:"- Select -"},{id:1,emailquota:"250MB"},{id:2,emailquota:"500MB"},{id:3,emailquota:"1000MB"},{id:4,emailquota:"1500MB"},{id:5,emailquota:"2000MB"}];

            if($scope.data.apprstatuscode!==5) {
                $scope.Mailboxsize =[{id:0,mailboxsize:"- Select -"},{id:1,mailboxsize:"256MB"},{id:2,mailboxsize:"512MB"},{id:3,mailboxsize:"1GB"},{id:4,mailboxsize:"1.5GB"},{id:5,mailboxsize:"2GB"}];
                $scope.Incomingsize =[{id:0,incomingsize:"- Select -"},{id:1,incomingsize:"5MB"},{id:2,incomingsize:"10MB"}];
                $scope.Outgoingsize =[{id:0,outgoingsize:"- Select -"},{id:1,outgoingsize:"5MB"},{id:2,outgoingsize:"10MB"}];
            } else {
                $scope.Mailboxsize =[
                    {id:0,mailboxsize:"- Select -"},
                    {id:1,mailboxsize:"256MB"},
                    {id:2,mailboxsize:"512MB"},
                    {id:3,mailboxsize:"1GB"},
                    {id:4,mailboxsize:"1.5GB"},
                    {id:5,mailboxsize:"2GB"},
                    {id:6,mailboxsize:"3GB"},
                    {id:7,mailboxsize:"4GB"},
                    {id:8,mailboxsize:"5GB"},
                    {id:9,mailboxsize:"6GB"},
                    {id:10,mailboxsize:"7GB"},
                    {id:11,mailboxsize:"8GB"},
                    {id:12,mailboxsize:"9GB"},
                    {id:13,mailboxsize:"10GB"},
                ];
                $scope.Incomingsize =[
                    {id:0,incomingsize:"- Select -"},
                    {id:1,incomingsize:"5MB"},
                    {id:2,incomingsize:"10MB"},
                    {id:3,incomingsize:"15MB"},
                    {id:4,incomingsize:"20MB"},
                    {id:5,incomingsize:"25MB"},
                    {id:6,incomingsize:"30MB"},
                    {id:7,incomingsize:"35MB"},
                    {id:8,incomingsize:"40MB"},
                    {id:9,incomingsize:"45MB"},
                    {id:10,incomingsize:"50MB"},
                    {id:11,incomingsize:"55MB"},
                    {id:12,incomingsize:"60MB"},
                    {id:13,incomingsize:"65MB"},
                    {id:14,incomingsize:"70MB"},
                    {id:15,incomingsize:"75MB"},
                    {id:16,incomingsize:"80MB"},
                    {id:17,incomingsize:"85MB"},
                    {id:18,incomingsize:"90MB"},
                    {id:19,incomingsize:"95MB"},
                    {id:20,incomingsize:"100MB"},
                ];
                $scope.Outgoingsize =[
                    {id:0,outgoingsize:"- Select -"},
                    {id:1,outgoingsize:"5MB"},
                    {id:2,outgoingsize:"10MB"},
                    {id:3,outgoingsize:"15MB"},
                    {id:4,outgoingsize:"20MB"},
                    {id:5,outgoingsize:"25MB"},
                    {id:6,outgoingsize:"30MB"},
                    {id:7,outgoingsize:"35MB"},
                    {id:8,outgoingsize:"40MB"},
                    {id:9,outgoingsize:"45MB"},
                    {id:10,outgoingsize:"50MB"},
                    {id:11,outgoingsize:"55MB"},
                    {id:12,outgoingsize:"60MB"},
                    {id:13,outgoingsize:"65MB"},
                    {id:14,outgoingsize:"70MB"},
                    {id:15,outgoingsize:"75MB"},
                    {id:16,outgoingsize:"80MB"},
                    {id:17,outgoingsize:"85MB"},
                    {id:18,outgoingsize:"90MB"},
                    {id:19,outgoingsize:"95MB"},
                    {id:20,outgoingsize:"100MB"},
                ];
            }
            // $scope.EmailDomain = [];
            // console.log($scope.EmailDomain);

            $scope.EmailDomain =[{id:0,emaildomain:"- Select -"},{id:1,emaildomain:"itci-hutani.com"},{id:2,emaildomain:"kalimantan-prima.com"},{id:3,emaildomain:"balikpapanchip.com"},{id:4,emaildomain:"lajudinamika.com"},{id:5,emaildomain:"ptadindo.com"}];
            $scope.ListGroup =[{id:0,listgroup:"- Select -"},{id:1,listgroup:"IHM"},{id:2,listgroup:"KPSI"},{id:3,listgroup:"BCL"},{id:4,listgroup:"LDU"},{id:5,listgroup:"Adindo"}];
            $scope.ListGroupModeration =[{id:0,listgroupmoderation:"- Select -"},{id:1,listgroupmoderation:"Mod-IHM"},{id:2,listgroupmoderation:"Mod-BCL"},{id:3,listgroupmoderation:"Mod-KDU-HRD"},{id:4,listgroupmoderation:"Mod-KF-Head"},{id:5,listgroupmoderation:"Mod-KF-Head2"},{id:6,listgroupmoderation:"Mod-KPSI-Pro"},{id:7,listgroupmoderation:"Mod-KDU-FA"}];
			$scope.AppAction = [{id:1,appaction:"Ask Rework"},{id:2,appaction:"Approve"},{id:3,appaction:"Reject"}];
			$scope.reqStatus = 0;
            $scope.gridSelectedRowKeys =[];
            
			$scope.detailFormOptions = { 
				onInitialized: function(e) {
					$scope.formInstance = e.component;
				},
				onContentReady:function(e){
					$scope.formInstance = e.component;
				},
				readOnly : (($scope.mode=='view')||($scope.mode=='report'))?true:false,
				labelLocation : "top",
				minColWidth  :800,
				colCount : 2,
				formData:$scope.data,	
                items: [
                    {	
                        itemType: "group",
                        caption: "",
                        name:"grouptypeform",
                        colSpan:2,
                        colCount : 3,
                        items: [
                            {
                                dataField:'formtype',
                                editorType: "dxSelectBox",
                                label:{text:"Form Type"},
                                disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                                validationRules: [{type: "required", message: "Please select Form Type" }],
                                editorOptions: { 
                                    dataSource:$scope.FormType,  
                                    valueExpr: 'id',
                                    displayExpr: 'formtype',
                                    onValueChanged: function(e){
                                        var vis1 =(e.value==1)?true:false;
                                        var vis2 =(e.value==2)?true:false;
                                        var vis3 =(e.value==3)?true:false;
                                        var vis4 =(e.value==4)?true:false;
                                        var vis5 =(e.value==5)?true:false;

                                        var visdate =(e.value!==5)?true:false;

                                        criteria = {status:'appcon',formtype:e.value,itimail_id:$scope.Requestid,employee_id:$scope.data.employee_id};
                                        CrudService.FindData('itimail',criteria).then(function (response){
                                            $scope.grid2Component.refresh();
                                            // console.log(e.value + ' & ' + $scope.Requestid);
                                            // console.log(response);
                                        })

                                            $scope.formInstance.itemOption('group2.accessrequested', 'visible', vis1);
                                            // $scope.formInstance.itemOption('group2.accessrequested', 'visibleIndex', 0);
                                            // $scope.formInstance.updateData('accessrequested',  "");
                                            $scope.formInstance.itemOption('group2.accesstype', 'visible', vis1);
                                            // $scope.formInstance.itemOption('group2.accesstype', 'visibleIndex', 0);
                                            // $scope.formInstance.updateData('accesstype',  "");
                                            // $scope.formInstance.itemOption('group2.accounttype', 'visible', vis1);
                                            // $scope.formInstance.itemOption('group2.accounttype', 'visibleIndex', 0);
                                            // $scope.formInstance.updateData('accounttype',  "");
                                            $scope.formInstance.itemOption('group2.emailquota', 'visible', vis1);
                                            // $scope.formInstance.itemOption('group2.emailquota', 'visibleIndex', 0);
                                            // $scope.formInstance.updateData('emailquota',  "");
                                            $scope.formInstance.itemOption('group5.emaildomain', 'visible', vis1);
                                            // $scope.formInstance.itemOption('group5.emaildomain', 'visibleIndex', 0);
                                            // $scope.formInstance.updateData('emaildomain',  "");
                                            $scope.formInstance.itemOption('group5.listgroup', 'visible', vis1);
                                            // $scope.formInstance.itemOption('group5.listgroup', 'visibleIndex', 0);
                                            // $scope.formInstance.updateData('listgroup',  "");
                                            $scope.formInstance.itemOption('group5.listgroupmoderation', 'visible', vis1);
                                            // $scope.formInstance.itemOption('group5.listgroupmoderation', 'visibleIndex', 0);
                                            // $scope.formInstance.updateData('listgroupmoderation',  "");

                                            $scope.formInstance.itemOption('ginetaccess.web1', 'visible', vis2);
                                            $scope.formInstance.itemOption('ginetaccess.web1', 'visibleIndex', 0);
                                            $scope.formInstance.updateData('web1',  "");
                                            $scope.formInstance.itemOption('ginetaccess.web2', 'visible', vis2);
                                            $scope.formInstance.itemOption('ginetaccess.web2', 'visibleIndex', 0);
                                            $scope.formInstance.updateData('web2',  "");

                                            $scope.formInstance.itemOption('gmailbox.newmailboxsize', 'visible', vis3);
                                            $scope.formInstance.itemOption('gmailbox.newmailboxsize', 'visibleIndex', 0);
                                            $scope.formInstance.updateData('newmailboxsize',  "");
                                            $scope.formInstance.itemOption('gmailbox.incomingsize', 'visible', vis3);
                                            $scope.formInstance.itemOption('gmailbox.incomingsize', 'visibleIndex', 0);
                                            $scope.formInstance.updateData('incomingsize',  "");
                                            $scope.formInstance.itemOption('gmailbox.outgoingsize', 'visible', vis3);
                                            $scope.formInstance.itemOption('gmailbox.outgoingsize', 'visibleIndex', 0);
                                            $scope.formInstance.updateData('outgoingsize',  "");

                                            $scope.formInstance.itemOption('grdweb.typeofaccess', 'visible', vis4);
                                            $scope.formInstance.itemOption('grdweb.typeofaccess', 'visibleIndex', 0);
                                            $scope.formInstance.updateData('typeofaccess',  "");

                                            $scope.formInstance.itemOption('gmailgroup.emailgroupname', 'visible', vis5);
                                            $scope.formInstance.itemOption('gmailgroup.membername', 'visible', vis5);

                                            // $scope.formInstance.itemOption('group3.validfrom', 'visible', visdate);
                                            // $scope.formInstance.itemOption('group3.validto', 'visible', visdate);

                                        
                                    }
                                },
                            },
                        ]
                    },
                    {	
						itemType: "group",
						name:"group1",
						caption: "",
						// caption: "Request by : "+$scope.data.fullname+" / Dept : "+$scope.data.department,
						colCount : 3,
						colSpan :2,
                        items: 
                        [	
                            {
                                dataField:'createddate',
                                editorType: "dxDateBox",
                                label: {text: "Creation Date"},
                                disabled: true,
                                editorOptions: {displayFormat:"dd/MM/yyyy",disabled: false}
                            },
                            {
                                label: {
                                    text: "Requested By"
                                },
                                dataField:"employee_id",
                                editorType: "dxDropDownBox",
                                visible: true,
                                // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                disabled: true,
                                editorOptions: { 
                                    dataSource:(($scope.mode=='add')||($scope.mode=='edit'))?$scope.deptEmpDataSource:$scope.allDeptEmpDataSource,    
                                    valueExpr: 'id',
                                    displayExpr: 'fullname',
                                    searchEnabled: true,
                                    contentTemplate: function(e){
                                        var $dataGrid = $("<div>").dxDataGrid({
                                            dataSource: e.component.option("dataSource"),
                                            columns: [{dataField:"fullname",width:100},{dataField:"company",width:50}, {dataField:"department",width:200}],
                                            height: 265,
                                            selection: { mode: "single" },
                                            selectedRowKeys: [e.component.option("value")],
                                            focusedRowEnabled: true,
                                            focusedRowKey: e.component.option("value"),
                                            searchPanel: {
                                                visible: true,
                                                width: 265,
                                                placeholder: "Search..."
                                            },
                                            onSelectionChanged: function(selectedItems){
                                                var keys = selectedItems.selectedRowKeys,
                                                    hasSelection = keys.length;
                                                if(hasSelection){
                                                    criteria = {status:'pending',username:keys[0],id:$scope.Requestid};
                                                    CrudService.FindData('trbyemp',criteria).then(function (response){
                                                        if(response.jml>0){
                                                            DevExpress.ui.notify({
                                                                message: "Cannot add more request, Selected employee still have unsubmitted draft or pending request",
                                                                type: "warning",
                                                                displayTime: 5000,
                                                                height: 80,
                                                                position: {
                                                                my: 'top center', 
                                                                at: 'center center', 
                                                                of: window, 
                                                                offset: '0 0' 
                                                            }
                                                            });
                                                        }else{
                                                            
                                                            e.component.option("value", keys[0]); 
                                                            e.component.close();
                                                        }
                                                    })
                                                }
                                            }
                                        });
                                        return $dataGrid;
                                    },onValueChanged: function(e){
                                        console.log(e);
                                        criteria = {status:'chemp',employee_id:e.value,itimail_id:$scope.Requestid};
                                        CrudService.FindData('itimail',criteria).then(function (response){
                                            $scope.grid2Component.refresh();
                                        })
                                    }
                                },
                                validationRules: [{
                                    type: "required",
                                    message: "Please select your direct superior"
                                }]
                            },
                            {
                                dataField:'department',
                                label: {
                                    text:"Department",
                                },
                                name:'department',
                                disabled: true,                                                    
                            },
                            {
                                dataField:'designation',
                                label: {
                                    text:"Designation",
                                },
                                name:'designation',
                                dataType:"string",
                                disabled: true                           
                            },
                            {
                                dataField:'bgbu',
                                label: {
                                    text:"BG/BU",
                                },
                                name:'bgbu',
                                dataType:"string",
                                disabled: true                           
                            },
                            // {dataField:'bgbu',label:{text:"BG/BU"},disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                            //     editorType: "dxSelectBox",
                            //     editorOptions: {
                            //         dataSource: $scope.compDatasource,
                            //         displayExpr: "companycode",
                            //         valueExpr: "companycode",
                            //         onValueChanged: function(e){
                            //             // var vis =(e.value==4)?true:false;
                            //             var val =(e.value);
                            //             $scope.formInstance.updateData('listgroup',  val);
                                        
                            //         }
                            //     },validationRules: [{
                            //             type: "required",
                            //             message: "Action is required"
                            //         }]
                                
                            // },
                            {
                                dataField:'officelocation',
                                label: {
                                    text:"Office/Location",
                                },
                                name:'officelocation',
                                // disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true     
                                disabled: true,                        
                            },
                            {
                                dataField:'floor',
                                label: {
                                    text:"Floor",
                                },
                                name:'floor',
                                dataType:"number",
                                editorType:"dxNumberBox",
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'phoneext',
                                label: {
                                    text:"Phone (Ext)",
                                },
                                name:'phoneext',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true                            
                            },
                            {
                                dataField:'requeststatus',
                                label: {
                                    text: "Request Status"
                                },
                                template: function(data, itemElement) {  
                                    var val = data.editorOptions.value;
                                    $scope.reqStatus = data.editorOptions.value;
                                    val=(val>=0)?val:5;
                                    var rClass = ["mb-2 mr-2 badge badge-pill badge-secondary","mb-2 mr-2 badge badge-pill badge-primary","mb-2 mr-2 badge badge-pill badge-warning","mb-2 mr-2 badge badge-pill badge-success","mb-2 mr-2 badge badge-pill badge-danger","mb-2 mr-2 badge badge-pill badge-alt"];
                                    var rDesc = ["Saved as Draft","Waiting Approval","Require Rework","Approved","Rejected","Not Saved"];
                                    $('<span>').appendTo(itemElement).addClass(rClass[val]).text(rDesc[val]);
                                }
                            },
                            {
                                label: {
                                text: "Department Head"
                                },
                            dataField:"depthead",
                            editorType: "dxDropDownBox",
                            // visible: false,
                            disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                            editorOptions: { 
                                dataSource:$scope.allEmpDataSource,  
                                valueExpr: 'id',
                                displayExpr: 'fullname',
                                searchEnabled: true,
                                contentTemplate: function(e){
                                    var $dataGrid = $("<div>").dxDataGrid({
                                        dataSource: e.component.option("dataSource"),
                                        columns: [{dataField:"fullname",width:100},{dataField:"company",width:50}, {dataField:"department",width:200}],
                                        height: 265,
                                        selection: { mode: "single" },
                                        selectedRowKeys: [e.component.option("value")],
                                        focusedRowEnabled: true,
                                        focusedRowKey: e.component.option("value"),
                                        searchPanel: {
                                            visible: true,
                                            width: 265,
                                            placeholder: "Search..."
                                        },
                                        onSelectionChanged: function(selectedItems){
                                            var keys = selectedItems.selectedRowKeys,
                                                hasSelection = keys.length;
                                            if(hasSelection){
                                                e.component.option("value", keys[0]); 
                                                e.component.close();
                                            }
                                        }
                                    });
                                    return $dataGrid;
                                }
                            },
                            validationRules: [{
                                type: "required",
                                message: "Please select your department head"
                            }]
                        },

                    ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        name:"group2",
                        colSpan:2,
						colCount:3,
						items: [
                            {
                                dataField:'accessrequested',
                                editorType: "dxSelectBox",
                                label:{text:"Access Requested"},
                                visible:($scope.data.formtype==1)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                editorOptions: { 
                                    dataSource:$scope.AccessRequested,  
                                    valueExpr: 'id',
                                    displayExpr: 'accessrequested',
                                    onValueChanged: function(e){
                                        var dis =(e.value==1)?true:false;

                                        // $scope.formInstance.itemOption('group5.listgroupmoderation').editorOptions.disabled=dis;
                                        

                                        if(e.value == 1) {

                                                    var found = false;

                                                    pos = $scope.EmailDomain.map(function(e){

                                                        if(e.id == 6) {
                                                            found = true;
                                                        }

                                                    });
                                                    if(!found) {
                                                        $scope.EmailDomain.push({id:6,emaildomain:"D1.LCL"});
                                                    }

                                        } else {
                                            var found = false;

                                                    pos = $scope.EmailDomain.map(function(e){

                                                        if(e.id == 6) {
                                                            $scope.EmailDomain.pop({id:6,emaildomain:"D1.LCL"});
                                                        }

                                                    });
                                        }

                                        
                                        $scope.formInstance.itemOption('group5.emaildomain', 'dataSource', $scope.EmailDomain);

                                        
                                    }
                                },
                            },
                            {
                                dataField:'accesstype',
                                editorType: "dxSelectBox",
                                label:{text:"Access Type"},
                                visible:($scope.data.formtype==1)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                validationRules: [{type: "required",message: "Action is required"}],
                                editorOptions: { 
                                    dataSource:$scope.AccessType,  
                                    valueExpr: 'id',
                                    displayExpr: 'accesstype',
                                    // onValueChanged: function(e){
                                    //     var vis =(e.value==4)?true:false;
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visible', vis);
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visibleIndex', 0);
                                    //     $scope.formInstance.updateData('requiredother',  "");
                                        
                                    // }
                                },
                            },
                            // {
                            //     dataField:'accounttype',
                            //     editorType: "dxSelectBox",
                            //     label:{text:"Account Type"},
                            //     visible:($scope.data.formtype==1)?true:false,
                            //     disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                            //     validationRules: [{type: "required",message: "Action is required"}],
                            //     editorOptions: { 
                            //         dataSource:$scope.AccountType,  
                            //         valueExpr: 'id',
                            //         displayExpr: 'accounttype',
                            //         onValueChanged: function(e){
                            //             var dis =(e.value==1)?true:false;
                            //             // $scope.formInstance.itemOption('group3.validfrom').editorOptions.disabled=dis;
                            //             // $scope.formInstance.itemOption('group3.validto').editorOptions.disabled=dis;
                            //             $scope.formInstance.getEditor('validfrom').option('disabled',dis);
                            //             $scope.formInstance.getEditor('validto').option('disabled',dis);
                            //             if(dis) {
                            //                 $scope.formInstance.updateData('validfrom',  $scope.data.createddate);
                            //                 $scope.formInstance.updateData('validto',  "9999-12-31");
                            //             } else {
                            //                 $scope.formInstance.updateData('validfrom',  "");
                            //                 $scope.formInstance.updateData('validto',  "");
                            //             }
                                        
                            //         }
                            //     },
                            // },
                            {
                                dataField:'emailquota',
                                editorType: "dxSelectBox",
                                label:{text:"Email Quota"},
                                visible:($scope.data.formtype==1)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                // validationRules: [{type: "required", message: "Please select RFC Type" }],
                                editorOptions: { 
                                    dataSource:$scope.EmailQuota,  
                                    valueExpr: 'id',
                                    displayExpr: 'emailquota',
                                    // onValueChanged: function(e){
                                    //     var vis =(e.value==4)?true:false;
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visible', vis);
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visibleIndex', 0);
                                    //     $scope.formInstance.updateData('requiredother',  "");
                                        
                                    // }
                                },
                            }
                            
                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        // caption: "Email Domain (select one, if applicable)**:",
                        name:"group5",
                        colSpan:2,
						colCount:3,
						items: [
                            {
                                dataField:'emaildomain',
                                editorType: "dxSelectBox",
                                label:{text:"Email Domain"},
                                visible:($scope.data.formtype==1)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                // validationRules: [{type: "required", message: "Please select RFC Type" }],
                                editorOptions: { 
                                    dataSource:$scope.EmailDomain,  
                                    valueExpr: 'id',
                                    displayExpr: 'emaildomain',
                                    // onValueChanged: function(e){

                                    //     var dis =(e.value==5)?true:false;
                                    //     $scope.formInstance.getEditor('ironportquarantinedetail').option('disabled',dis);
                                        
                                    // }
                                },
                            },
                            {
                                dataField:'listgroup',
                                label: {
                                    text:"List Group",
                                },
                                visible:($scope.data.formtype==1)?true:false,
                                name:'listgroup',
                                dataType:"string",
                                disabled: true                           
                            },
                            {dataField:'listgroupmoderation',name:'listgroupmoderation',label:{text:"List Group Moderation"},visible:($scope.data.formtype==1)?true:false,
                            disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                            editorType: "dxDropDownBox",validationRules: [{type: "required", message: "Please select Activity" }],editorOptions: { 
                                dataSource:$scope.listmod,  
                                valueExpr: 'id',
                                displayExpr: 'mod',
                                searchEnabled: true,
                                contentTemplate: function(e){
                                    var $dataGrid = $("<div>").dxDataGrid({
                                        dataSource: e.component.option("dataSource"),
                                        columns: [{dataField:"mod",width:250}],
                                        height: 265,
                                        selection: { mode: "single" },
                                        selectedRowKeys: [e.component.option("value")],
                                        focusedRowEnabled: true,
                                        focusedRowKey: e.component.option("value"),
                                        searchPanel: {
                                            visible: true,
                                            width: 265,
                                            placeholder: "Search..."
                                        },
                                        onSelectionChanged: function(selectedItems){
                                            var keys = selectedItems.selectedRowKeys,
                                                hasSelection = keys.length;
                                                if(hasSelection){
                                                    e.component.option("value", hasSelection ? keys[0] : null); 
                                                    e.component.close();
                                                }
                                        }
                                    });
                                    return $dataGrid;
                                }
                            }},
                            // {
                            //     dataField:'listgroupmoderation',
                            //     name:'listgroupmoderation',
                            //     editorType: "dxSelectBox",
                            //     label:{text:"List Group Moderation"},
                            //     visible:($scope.data.formtype==1)?true:false,
                            //     disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report'))?true:false,
                            //     // validationRules: [{type: "required", message: "Please select RFC Type" }],
                            //     editorOptions: { 
                            //         dataSource:$scope.ListGroupModeration,  
                            //         valueExpr: 'id',
                            //         displayExpr: 'listgroupmoderation',
                            //         // onValueChanged: function(e){
                            //         //     var vis =(e.value==4)?true:false;
                            //         //     $scope.formInstance.itemOption('group2.requiredother', 'visible', vis);
                            //         //     $scope.formInstance.itemOption('group2.requiredother', 'visibleIndex', 0);
                            //         //     $scope.formInstance.updateData('requiredother',  "");
                                        
                            //         // }
                            //     },
                            // },
                            
                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        // caption: "Email Domain (select one, if applicable)**:",
                        name:"gmailbox",
                        colSpan:2,
						colCount:5,
						items: [
                            {
                                dataField:'newmailboxsize',
                                editorType: "dxSelectBox",
                                label:{text:"New Mailbox Size"},
                                visible:($scope.data.formtype==3)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                // validationRules: [{type: "required", message: "Please select RFC Type" }],
                                editorOptions: { 
                                    dataSource:$scope.Mailboxsize,  
                                    valueExpr: 'id',
                                    displayExpr: 'mailboxsize',
                                    // onValueChanged: function(e){
                                    //     var vis =(e.value==4)?true:false;
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visible', vis);
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visibleIndex', 0);
                                    //     $scope.formInstance.updateData('requiredother',  "");
                                        
                                    // }
                                },
                            },
                            // {
                            //     dataField:'newmailboxsize',
                            //     label: {
                            //         text:"New Mailbox Size",
                            //     },
                            //     visible:($scope.data.formtype==3)?true:false,
                            //     disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                            //     name:'newmailboxsize',
                            //     dataType:"number",
                            //     editorType:"dxNumberBox"
                            // },
                            {
                                dataField:'incomingsize',
                                editorType: "dxSelectBox",
                                label:{text:"Incoming Size"},
                                visible:($scope.data.formtype==3)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                // validationRules: [{type: "required", message: "Please select RFC Type" }],
                                editorOptions: { 
                                    dataSource:$scope.Incomingsize,  
                                    valueExpr: 'id',
                                    displayExpr: 'incomingsize',
                                    // onValueChanged: function(e){
                                    //     var vis =(e.value==4)?true:false;
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visible', vis);
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visibleIndex', 0);
                                    //     $scope.formInstance.updateData('requiredother',  "");
                                        
                                    // }
                                },
                            },
                            // {
                            //     dataField:'incomingsize',
                            //     label: {
                            //         text:"Incoming Size",
                            //     },
                            //     visible:($scope.data.formtype==3)?true:false,
                            //     disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                            //     name:'incomingsize',
                            //     dataType:"number",
                            //     editorType:"dxNumberBox"
                            // },
                            {
                                dataField:'outgoingsize',
                                editorType: "dxSelectBox",
                                label:{text:"Outgoing Size"},
                                visible:($scope.data.formtype==3)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                // validationRules: [{type: "required", message: "Please select RFC Type" }],
                                editorOptions: { 
                                    dataSource:$scope.Outgoingsize,  
                                    valueExpr: 'id',
                                    displayExpr: 'outgoingsize',
                                    // onValueChanged: function(e){
                                    //     var vis =(e.value==4)?true:false;
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visible', vis);
                                    //     $scope.formInstance.itemOption('group2.requiredother', 'visibleIndex', 0);
                                    //     $scope.formInstance.updateData('requiredother',  "");
                                        
                                    // }
                                },
                            },
                            // {
                            //     dataField:'outgoingsize',
                            //     label: {
                            //         text:"Outgoing Size",
                            //     },
                            //     visible:($scope.data.formtype==3)?true:false,
                            //     disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                            //     name:'outgoingsize',
                            //     dataType:"number",
                            //     editorType:"dxNumberBox"
                            // },
                            
                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        // caption: "Email Domain (select one, if applicable)**:",
                        name:"ginetaccess",
                        colSpan:2,
						colCount:3,
						items: [
                            {
                                dataField:'web1',
                                label: {
                                    text:"http://",
                                },
                                visible:($scope.data.formtype==2)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                name:'web1',
                                dataType:"string",
                            },
                            {
                                dataField:'web2',
                                label: {
                                    text:"http://",
                                },
                                visible:($scope.data.formtype==2)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                name:'web2',
                                dataType:"string",
                            },
                            
                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        // caption: "Email Domain (select one, if applicable)**:",
                        name:"grdweb",
                        colSpan:2,
						colCount:3,
						items: [
                            {
                                dataField:'typeofaccess',
                                label: {
                                    text:"TS Access via RD Web RDP to TS",
                                },
                                visible:($scope.data.formtype==4)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                name:'typeofaccess',
                                dataType:"string",
                            },
                            
                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        // caption: "Email Domain (select one, if applicable)**:",
                        name:"gmailgroup",
                        colSpan:2,
						colCount:5,
						items: [
                            {
                                dataField:'emailgroupname',
                                label: {
                                    text:"Email Group Name",
                                },
                                visible:($scope.data.formtype==5)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                name:'emailgroupname',
                                dataType:"string",
                            },
                            {
                                label: {
                                    text: "Member Name"
                                },
                                dataField:"membername",
								colSpan :4,
                                editorType: "dxDropDownBox",
                                visible:($scope.data.formtype==5)?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                editorOptions: { 
                                    dataSource:$scope.allEmpDataSource,  
                                    valueExpr: 'id',
                                    displayExpr: 'fullname',
                                    searchEnabled: true,
                                    contentTemplate: function(e){
										console.log(e.component.option("value"));
										var values = Array.isArray(e.component.option("value"))?e.component.option("value"):e.component.option("value").split(",");
										
                                        var $dataGrid = $("<div>").dxDataGrid({
                                            dataSource: e.component.option("dataSource"),
                                            columns: [{dataField:"fullname",width:100},{dataField:"company",width:50}, {dataField:"department",width:200}],
                                            height: 265,
                                            selection: { mode: "multiple" },
                                            selectedRowKeys: values,
                                            focusedRowEnabled: true,
											value:values,
                                            focusedRowKey: values,
                                            searchPanel: {
                                                visible: true,
                                                width: 265,
                                                placeholder: "Search..."
                                            },
                                            onSelectionChanged: function(selectedItems){
                                                var keys = selectedItems.selectedRowKeys,
                                                    hasSelection = keys.length;
                                                if(hasSelection){
                                                    e.component.option("value", keys); 
                                                    // e.component.close();
                                                    console.log(keys);
    
                                                }
                                            }
                                        });
										dataGrid = $dataGrid.dxDataGrid("instance");  
										//dataGrid.selectRows(lstSelectedValue, false);  

										e.component.on("valueChanged", function (args) {
											valuex =Array.isArray(args.value)?args.value:args.value.split(",");
											this.DefaultValueOne = valuex;  
											this.SelectedValues = valuex;  
											dataGrid.selectRows(valuex, false);  
										});
                                        return $dataGrid;
                                    }
                                }
                            },
                            
                            
                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        name:"group3",
                        colSpan:2,
						colCount:3,
						items: [
                            {
                                dataField:'accounttype',
                                editorType: "dxSelectBox",
                                label:{text:"Account Type"},
                                // visible:($scope.data.formtype==1)?true:false,
                                // disabled: (($scope.mode=='approve')|| ($scope.mode=='view')||($scope.mode=='report') && ($scope.data.apprstatuscode!==1))?true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                validationRules: [{type: "required",message: "Action is required"}],
                                editorOptions: { 
                                    dataSource:$scope.AccountType,  
                                    valueExpr: 'id',
                                    displayExpr: 'accounttype',
                                    onValueChanged: function(e){
                                        var dis =(e.value==1)?true:false;
                                        // $scope.formInstance.itemOption('group3.validfrom').editorOptions.disabled=dis;
                                        // $scope.formInstance.itemOption('group3.validto').editorOptions.disabled=dis;
                                        $scope.formInstance.getEditor('validfrom').option('disabled',dis);
                                        $scope.formInstance.getEditor('validto').option('disabled',dis);
                                        if(dis) {
                                            $scope.formInstance.updateData('validfrom',  $scope.data.createddate);
                                            $scope.formInstance.updateData('validto',  "9999-12-31");
                                        } else {
                                            $scope.formInstance.updateData('validfrom',  "");
                                            $scope.formInstance.updateData('validto',  "");
                                        }
                                        
                                    }
                                },
                            },
                            {
                                dataField:'validfrom',
                                name: 'validfrom',
                                // visible: (($scope.data.formtype==5)) ? false:true,
                                editorType: "dxDateBox",
                                label: {text: "Valid From"},
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                // max: new Date(date + 1000*60*60*24*3),
                                editorOptions: {
                                    displayFormat:"dd/MM/yyyy",
                                    // disabled: (($scope.mode=='add' ) || ($scope.mode=='edit') || ($scope.data.accounttype!==1)) ?false:true,
                                }
                            },
                            {
                                dataField:'validto',
                                name: 'validto',
                                // visible: (($scope.data.formtype==5)) ? false:true,
                                editorType: "dxDateBox",
                                label: {text: "Valid To"},
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true,
                                editorOptions: {
                                    displayFormat:"dd/MM/yyyy",
                                    max: new Date(date + 1000*60*60*24*365),

                                    // disabled: (($scope.mode=='add' ) || ($scope.mode=='edit') || ($scope.data.accounttype!==1)) ?false:true,
                                }
                            }
                            
                        ]
                    },
                    {
						itemType: "group",
                        caption: "",
                        name:"gdeklarasi",
                        colSpan:2,
						colCount:3,
						items: [
                            {
                                dataField:'reason',
                                label: {
                                    text:"Reason for request/Remarks",
                                },
                                // colSpan:2,
                                editorType:"dxHtmlEditor",
                                name:'reason',
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' ) || ($scope.data.apprstatuscode==5)) ?false:true   ,
                                editorOptions: {height: 90,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}                         
                            },
                            {
                                dataField:'isdeclaration',
                                label:{text:"Declaration"},
                                // visible: (($scope.data.apprstatuscode==3) || ($scope.mode=='report')) ? true:false,
                                disabled: (($scope.mode=='edit')|| ($scope.mode=='add' )) ?false:true,
                                dataType:"boolean",
                                editorType: "dxCheckBox",
                                validationRules: [{type: "required",message: "Declaration is required"}],
                                editorOptions: { 
                                    text:"I wish to apply for the services and agree to be bound by the IT Corporate Policies for these services. I also confirm that the information as given above is true and correct",
                                }
                            }
                            
                        ]
                    },
                    {	
                            itemType: "group",
                            caption: "",
                            name:" group4",
                            colSpan:2,
                            colCount : 2,
                            items: [
								{dataField:'remarks',colSpan:2,editorType:"dxHtmlEditor",visible: ($scope.mode=='approve') ?true:false,editorOptions: {height: 90,toolbar: {items: ["undo", "redo", "separator","bold", "italic", "underline"]}}},
								{label: {
										text: "Approval Action"
									},
									dataField:"approvalstatus",
									editorType: "dxSelectBox",
									visible: ($scope.mode=='approve') ?true:false,
									editorOptions: { 
										dataSource:$scope.AppAction,  
										valueExpr: 'id',
										displayExpr: 'appaction',
										searchEnabled: true,
										value: ""
									},
									validationRules: [{
										type: "required",
										message: "Action is required"
									}]
								},
                            ]
                    },
                    {
						itemType: "group",
						caption: "Action",
						colCount:6,
						items: [{
							itemType: "button",
							horizontalAlignment: "right",
							buttonOptions: {
								text: "Back",
								type: "danger",
								onClick: function(){
									var path = (($scope.mode=='report') || ($scope.mode=='reschedule')) ? "itimailreport" :"itimail";
									$location.path( "/"+path );
								},
								visible: (($scope.mode=='approve'))  ?false:true,
								useSubmitBehavior: false
							}
                        },
                        {
							itemType: "button",
							horizontalAlignment: "right",
							buttonOptions: {
								text: "Back",
								type: "danger",
								onClick: function(){
									$scope.itimailApproval();							
								},
								visible: ($scope.mode=='approve') ?true:false,
								useSubmitBehavior: false
							}
                        },
                        {
							itemType: "button",
							horizontalAlignment: "right",
							buttonOptions: {
								text: "Save Update",
								type: "success",
								onClick: function(){
									DevExpress.ui.notify({
										message: "Please wait...!, we are processing your update",
										type: "info",
										displayTime: 1000,
										height: 80,
										position: {
										   my: 'top center', 
										   at: 'center center', 
										   of: window, 
										   offset: '0 0' 
									   }
									});
									$scope.data = $scope.formInstance.option("formData");
									$scope.updateForm();
								},
								visible: (($scope.mode=='approve') ||($scope.mode=='reschedule')) ?true:false,
								useSubmitBehavior: false
							}
                        },
                        {
							itemType: "button",
							horizontalAlignment: "center",
							buttonOptions: {
								text: "Save as Draft",
								type: "default",
								onClick: function(){
									DevExpress.ui.notify({
										message: "Please wait...!, we are processing your update",
										type: "info",
										displayTime: 1000,
										height: 80,
										position: {
										   my: 'top center', 
										   at: 'center center', 
										   of: window, 
										   offset: '0 0' 
									   }
									});
									$scope.data = $scope.formInstance.option("formData");
									$scope.saveDraft();
									
								},
								visible: (($scope.mode=='approve') ||($scope.mode=='view') ||($scope.mode=='report') ||($scope.mode=='reschedule'))?false:true,
								useSubmitBehavior: false
							}
						},{
							itemType: "button",
							horizontalAlignment: "left",
							buttonOptions: {
								text: "Submit",
								type: "success",
								onClick: function(){
									var result = $scope.formInstance.validate();  
									if (result.isValid) {  
										DevExpress.ui.notify({
											message: "Please wait...!, we are processing your update",
											type: "info",
											displayTime: 3000,
											height: 80,
											position: {
											   my: 'top center', 
											   at: 'center center', 
											   of: window, 
											   offset: '0 0' 
										   }
										});
									} else {
										DevExpress.ui.notify({
											message: "Your form is not complete or has invalid value, please recheck before submit",
											type: "warning",
											displayTime: 3000,
											height: 80,
											position: {
											   my: 'top center', 
											   at: 'center center', 
											   of: window, 
											   offset: '0 0' 
										   }
										});
									}
									$scope.data = $scope.formInstance.option("formData");	
								},
								visible: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule'))?false:true,
								useSubmitBehavior: true
							}
						}]
					},
				],			
			};
		}
    });
    var myStore2 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('itimailapp',$scope.Requestid);         		
		},
		byKey: function(key) {
            CrudService.GetById('itimailapp',encodeURIComponent(key)).then(function (response) {
				return response;
			});
		},
		insert: function(values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
			values.itimail_id=$scope.Requestid;
            CrudService.Create('itimailapp',values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		update: function(key, values) {
			values.approvaldate = $filter("date")(values.approvaldate, "yyyy-MM-dd HH:mm")
            CrudService.Update('itimailapp',key.id,values).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		},
		remove: function(key) {
			CrudService.Delete('itimailapp',key.id).then(function (response) {
				if(response.status=="error"){
					 DevExpress.ui.notify(response.message,"error");
				}
				$scope.grid2Component.refresh();
			});
		}
    });
	var myStore3 = new DevExpress.data.CustomStore({
		load: function() {			
            $scope.isLoaded =true;
			return CrudService.GetById('itimailhist',$scope.Requestid);         		
		},
		byKey: function(key) {
            //
		},
		insert: function(values) {
			//
		},
		update: function(key, values) {
			//
		},
		remove: function(key) {
			//
		}
    });
	var myData2 = new DevExpress.data.DataSource({
		store: myStore2
    });
	var myData3 = new DevExpress.data.DataSource({
		store: myStore3
    });

    $scope.tabs = [
		{ id:1, TabName : "Approver List", title: 'Approver List', template: "tab"   },
		{ id:2, TabName : "History Tracking", title: 'History Tracking', template: "tab1"   },
    ];
    $scope.showHistory = true;
    $scope.loadPanelVisible = false;

    $scope.empDataSource = {
        store: new DevExpress.data.CustomStore({
            key: "id",
            loadMode: "raw",
            load: function() {
				criteria = {module:'IT',mode:$scope.mode};
                return CrudService.FindData('appr',criteria);
            },
        }),
        sort: "id"
    }
    
    $scope.grid2Options = {
		dataSource: myData2,
		allowColumnResizing: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		columns: [
			{
						dataField: "approver_id",
						caption: "Employee",
						width: 200,
						allowSorting: false,
						lookup: {
							dataSource: $scope.empDataSource,
							valueExpr: "id",
							displayExpr: "fullname" },
						editCellTemplate: "dropDownBoxEditorTemplatex" },
			{dataField:'approvaldate',width:150,dataType:"date", format:"dd/MM/yyyy",allowEditing:false, visible: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false},
			{dataField:'approvaltype' ,width:200,allowEditing:false,
				lookup: {  
					dataSource:$scope.apptypeDatasource,  
					valueExpr: 'id',
					displayExpr: 'approvaltype'
				}},
			{dataField:'approvalstatus',width:150,allowEditing:false, visible: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report'))?true:false,encodeHtml: false,
			customizeText: function (e) {
					var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-primary'>Waiting Approval</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Require Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
					return rDesc[e.value];
				}},
			//{dataField:'remarks',encodeHtml: false,}
		],
		bindingOptions :{
            "columns[2].lookup.dataSource":"apptypeDatasource"
        },
		editing: {
            useIcons:true,
            mode: "cell",
			allowUpdating: (($scope.mode=='approve') ||($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule'))?(($rootScope.isAdmin)?true:false):true,
			allowAdding:(($scope.mode=='view')||($scope.mode=='report')||($scope.mode=='reschedule'))?(($rootScope.isAdmin)?true:false):true,
			allowDeleting:($rootScope.isAdmin)?true:false,
            form:{colCount: 1,
            },
        },
		onInitialized:function (e){
			$scope.grid2Component = e.component;
		},
		onEditorPreparing: function (e) {  
			$scope.grid2Component = e.component;
		},
		onToolbarPreparing: function(e) {
            $scope.dataGrid2 = e.component;
    
            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        $scope.dataGrid2.refresh();
                    }
                }
            });
        },
		
    };
	$scope.grid3Options = {
		dataSource: myData3,
		allowColumnResizing: true,
		columnResizingMode : "widget",
        columnMinWidth: 50,
        columnAutoWidth: true,
		wordWrapEnabled: true,
		columns: [
			{dataField:'date',width:150,dataType: "date",format: 'dd/MM/yyyy HH:mm:ss'},
			{dataField:'fullname',width:200,caption: "Employee",allowEditing:false,dataType: "string"},
			{dataField:'approvaltype',width:150,caption: "Role",allowEditing:false,dataType: "string"},
			{dataField:'actiontype',width:150,caption: "Action",allowEditing:false,encodeHtml: false,
			customizeText: function (e) {
					var rDesc = ["<span class='mb-2 mr-2 badge badge-pill badge-default'>Created</span>","<span class='mb-2 mr-2 badge badge-pill badge-default'>Save as Draft</span>","<span class='mb-2 mr-2 badge badge-pill badge-primary'>Submitted</span>","<span class='mb-2 mr-2 badge badge-pill badge-warning'>Ask Rework</span>","<span class='mb-2 mr-2 badge badge-pill badge-success'>Approved</span>","<span class='mb-2 mr-2 badge badge-pill badge-danger'>Rejected</span>",""];
					return rDesc[e.value];
				}},
			{dataField:'remarks',encodeHtml: false}
		],editing: {
            useIcons:true,
			allowUpdating:false,
			allowAdding:false,
			allowDeleting:false,
            form:{colCount: 1,
            },
        },
		onInitialized:function (e){
			$scope.grid3Component = e.component;
		},
		onEditorPreparing: function (e) {  
			$scope.grid3Component = e.component;
		},
		onToolbarPreparing: function(e) {   
            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        $scope.grid3Component.refresh();
                    }
                }
            });
        },
    };
	$scope.selectedTab = 0;
	$scope.tabSettings = {
		dataSource: $scope.tabs,
		animationEnabled:true,
		swipeEnabled : false,
		bindingOptions: {
			selectedIndex: 'selectedTab'
		},
    }

    $scope.initDropDownBoxEditorx = function(data) {
        return {
            dropDownOptions: { width: 500 },
            dataSource: $scope.empDataSource,
            value: data.value,
            valueExpr: "id",
            displayExpr: "fullname",
            contentTemplate: "contentTemplate"
        }
    }
	
	CrudService.GetAll('approvaltype').then(function (resp) {
        $scope.apptypeDatasource=resp;
    });
    
    $scope.initContentx = function(data, component) {
        return {
            dataSource: $scope.empDataSource,
            remoteOperations: true,
            columns: ["fullname",'approvaltype','department'],
            hoverStateEnabled: true,
            scrolling: { mode: "virtual" },
            height: 250,
            selection: { mode: "single" },
            selectedRowKeys: [data.value],
            focusedRowEnabled: true,
            focusedRowKey: data.value,
			searchPanel: {
				visible: true,
				width: 240,
				placeholder: "Search..."
			},
				onSelectionChanged: function(selectionChangedArgs) {
                component.option("value", selectionChangedArgs.selectedRowKeys[0]);
                data.setValue(selectionChangedArgs.selectedRowKeys[0]);
                if(selectionChangedArgs.selectedRowKeys.length > 0) {
                    component.close();
                }
            }
        }
    }
    
    $scope.saveDraft = function(e){
		var data = $scope.formInstance.option("formData");
		delete data.fullname;
		// delete data.department;
		delete data.approvalstatus;
		delete data.apprstatuscode;
		// data.requireddate = $filter("date")(data.requireddate, "yyyy-MM-dd HH:mm");
		data.validfrom = $filter("date")(data.validfrom, "yyyy-MM-dd HH:mm");
        data.validto = $filter("date")(data.validto, "yyyy-MM-dd HH:mm");
		console.log(data.membername)
        var selected = data.membername || [];
            data.membername = selected.join();
		console.log(data);
		CrudService.Update('itimail',data.id,data).then(function (response) {
			if(response.status=="error"){
				 DevExpress.ui.notify(response.message,"error");
			}else{
				DevExpress.ui.notify({
					message: "Data has been Updated",
					type: "success",
					displayTime: 2000,
					height: 80,
					position: {
					   my: 'top center', 
					   at: 'center center', 
					   of: window, 
					   offset: '0 0' 
				   }
				});
				$location.path( "/itimail" );
			}
			
		});
    }

    $scope.updateForm = function(e){
			//console.log($scope.formInstance.option("formData").approvalstatus);
			if($scope.formInstance.option("formData").approvalstatus==""){
				DevExpress.ui.notify({
					message: "Please select approval action",
					type: "warning",
					displayTime: 5000,
					height: 80,
					position: {
					   my: 'top center', 
					   at: 'center center', 
					   of: window, 
					   offset: '0 0' 
				   }
				});
			}else if($scope.formInstance.option("formData").approvalstatus==3){
                console.log('a1');
				var data = $scope.formInstance.option("formData");
				var date = new Date();
                var d= $filter("date")(date, "yyyy-MM-dd HH:mm");
                data.validfrom = $filter("date")(data.validfrom, "yyyy-MM-dd HH:mm");
                data.validto = $filter("date")(data.validto, "yyyy-MM-dd HH:mm");
                // data.membername = data.membername.join();
                data.approvaldate = d;
				data.mode="approve";
				delete data.createddate;
                delete data.employee_id;
                delete data.formtype;
                delete data.department;
				delete data.requeststatus;
				delete data.designation;
				delete data.bgbu;
				delete data.officelocation;
				delete data.floor;
				delete data.phoneext;
                delete data.accessrequested;
				delete data.accesstype;
				delete data.accounttype;
				delete data.emailquota;
				delete data.validfrom;
				delete data.validto;
				delete data.emaildomain;
				delete data.listgroup;
				delete data.listgroupmoderation;
				delete data.web1;
				delete data.web2;
				delete data.newmailboxsize;
				delete data.incomingsize;
				delete data.outgoingsize;
				delete data.typeofaccess;
				delete data.emailgroupname;
				delete data.membername;
				delete data.reason;
				delete data.isdeclaration;

				delete data.depthead;
				delete data.apprstatuscode;
				CrudService.Update('itimailapp',data.id,data).then(function (response) {
					if(response.status=="error"){
						DevExpress.ui.notify(response.message,"error");
					}else{
						DevExpress.ui.notify({
							message: "Data has been Updated",
							type: "success",
							displayTime: 2000,
							height: 80,
							position: {
							   my: 'top center', 
							   at: 'center center', 
							   of: window, 
							   offset: '0 0' 
						   }
						});
						$location.path( "/itimailapproval" );
					}
					
				});
			}else{
                console.log('a3');

				criteria = {status:'approver',itimail_id:$scope.Requestid};
				CrudService.FindData('itimailapp',criteria).then(function (response){
					if(response.jml>0){
						var data = $scope.formInstance.option("formData");
						var date = new Date();
                        var d= $filter("date")(date, "yyyy-MM-dd HH:mm");
                        data.validfrom = $filter("date")(data.validfrom, "yyyy-MM-dd HH:mm");
                        data.validto = $filter("date")(data.validto, "yyyy-MM-dd HH:mm");
                        // data.membername = data.membername.join();
						data.approvaldate = d;
                        data.mode="approve";
                        
                        if($scope.data.apprstatuscode!==5) {
                            delete data.accessrequested;
                            delete data.accesstype;
                            delete data.emailquota;
                            delete data.emaildomain;
                            delete data.listgroupmoderation;

                            delete data.web1;
                            delete data.web2;

                            delete data.newmailboxsize;
                            delete data.incomingsize;
                            delete data.outgoingsize;

                            delete data.typeofaccess;

                            delete data.emailgroupname;
                            delete data.membername;
                            
                            delete data.reason;

                            delete data.validfrom;
                            delete data.validto;
                        }
						delete data.createddate;
                        delete data.employee_id;
                        // delete data.formtype;
                        delete data.department;
                        delete data.requeststatus;
                        delete data.designation;
                        delete data.bgbu;
                        delete data.officelocation;
                        delete data.floor;
                        delete data.phoneext;
                        delete data.accounttype;
                        
                        delete data.listgroup;
                        
                        
                        delete data.isdeclaration;

                        delete data.depthead;
				        delete data.apprstatuscode;
						CrudService.Update('itimailapp',data.id,data).then(function (response) {
							if(response.status=="error"){
								DevExpress.ui.notify(response.message,"error");
							}else{
								DevExpress.ui.notify({
									message: "Data has been Updated",
									type: "success",
									displayTime: 2000,
									height: 80,
									position: {
									   my: 'top center', 
									   at: 'center center', 
									   of: window, 
									   offset: '0 0' 
								   }
								});
								$location.path( "/itimailapproval" );
							}
							
						});
					}else{
						DevExpress.ui.notify({
							message: "Please add person to do next approval/verification in Approver List tab",
							type: "warning",
							displayTime: 5000,
							height: 80,
							position: {
							   my: 'top center', 
							   at: 'center center', 
							   of: window, 
							   offset: '0 0' 
						   }
						});
					}
				});
			}
	}
    
    $scope.onFormSubmit = function(e) {
		e.preventDefault();
		// criteria = {status:'waiting',username:$scope.formInstance.option("formData").employee_id,id:$scope.Requestid};
		// CrudService.FindData('itimailbyemp',criteria).then(function (response){
		// 	if(response.jml>0){
		// 		DevExpress.ui.notify({
		// 			message: "Cannot add more request, Selected employee still have waiting approval request",
		// 			type: "warning",
		// 			displayTime: 5000,
		// 			height: 80,
		// 			position: {
		// 			   my: 'top center', 
		// 			   at: 'center center', 
		// 			   of: window, 
		// 			   offset: '0 0' 
		// 		   }
		// 		});
		// 	}else{
				criteria = {status:'approver',itimail_id:$scope.Requestid};
				CrudService.FindData('itimailapp',criteria).then(function (response){
					if(response.jml>0){
                        var data = $scope.formInstance.option("formData");
                        data.requeststatus = 1;
                        delete data.fullname;
                        // delete data.department;
                        delete data.approvalstatus;
                        delete data.apprstatuscode;
                        data.validfrom = $filter("date")(data.validfrom, "yyyy-MM-dd HH:mm");
                        data.validto = $filter("date")(data.validto, "yyyy-MM-dd HH:mm");
                        var selected = data.membername || [];
                            data.membername = selected.join();
                        CrudService.Update('itimail',data.id,data).then(function (response) {
                            if(response.status=="error"){
                                    DevExpress.ui.notify(response.message,"error");
                            }else{
                                DevExpress.ui.notify({
                                    message: "Data has been Updated",
                                    type: "success",
                                    displayTime: 2000,
                                    height: 80,
                                    position: {
                                        my: 'top center', 
                                        at: 'center center', 
                                        of: window, 
                                        offset: '0 0' 
                                    }
                                });
                                $location.path( "/itimail" );
                            }
                            
                        });
					}else{
						DevExpress.ui.notify({
							message: "Please add person to do approval/verification in Approver List tab",
							type: "warning",
							displayTime: 5000,
							height: 80,
							position: {
							   my: 'top center', 
							   at: 'center center', 
							   of: window, 
							   offset: '0 0' 
						   }
						});
					}			
				})
		// 	}
		// })
			 	   
    };

    }]);
})(app || angular.module("kduApp"));