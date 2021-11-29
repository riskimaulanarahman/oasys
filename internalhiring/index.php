<!DOCTYPE html>
<html lang="zxx" class="js">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="AppsLand is a powerful App Landing HTML Template with full of customization options and features">
		<link rel="shortcut icon" href="../assets/images/oasys-inverse.png">
		<title>Oasys : Internal Hiring</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="../assets/css/vendor.bundle.css" >
		<link href="../assets/css/stylelanding.css?ver=1.2" rel="stylesheet">
		<link href="../assets/css/theme-royel-teal.css" rel="stylesheet" id="layoutstyle">
	    <link rel='stylesheet' href='../css/dx.common.css' type='text/css'>
	    <link rel='stylesheet' href='../css/dx.light.compact.css' type='text/css'>

        <style>
            .my-swal {
                z-index: 100000000000000 !important;
            }
        </style>

	</head>
	<body class="overflow-scroll">
       	
		<div id="home" class="header-section flex-box-middle section gradiant-background overflow-hidden">
			<div id="particles-js" class="particles-container"></div>
			<div id="navigation" class="navigation is-transparent" data-spy="affix" data-offset-top="5">
				<nav class="navbar navbar-default">
					<div class="container">
						<div class="navbar-header">
							<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#site-collapse-nav" aria-expanded="false">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
							<a class="navbar-brand" href="../">
								<img class="logo logo-light" src="../assets/images/oasys.png" alt="logo" />
								<img class="logo logo-color" src="../assets/images/oasys-inverse.png" alt="logo" />
							</a>
						</div>

						<div class="collapse navbar-collapse font-secondary" id="site-collapse-nav">
							<ul class="nav nav-list navbar-nav navbar-right">
								<li class="demo-dropdown">
									<a href="#home" class="nav-item dropdown-toggle" data-toggle="dropdown">Home</a>
								</li>
                                <li class="demo-dropdown">
									<a href="#jobs" class="nav-item dropdown-toggle" data-toggle="dropdown">Jobs</a>
								</li>
							</ul>
						</div>
					</div>
				</nav>
			</div>
			
			<div class="header-content">
				<div class="container">
					<div class="row">
						<div class="col-md-6">
							<div class="header-texts">
								<h1 class="cd-headline clip is-full-width wow fadeInUp" data-wow-duration=".5s">
									<span>Oasys :</span> 
									<span class="cd-words-wrapper">
										<b class="is-visible">Online</b>
										<b>Approval</b>
										<b>System</b>
									</span>
								</h1>
								<!-- <p class="lead wow fadeInUp" data-wow-duration=".5s" data-wow-delay=".3s">Lihat Status Pengajuan</p> -->
								<ul class="buttons">
                                        <div class="form-row">
                                            <div class="col-md-12">
                                                <div class="position-relative form-group">
                                                    <!-- <input name="isapid" id="isapid" placeholder="SAPID" type="text" class="form-control" value="10070500" autocomplete="off" > -->
                                                    <input name="isapid" id="isapid" placeholder="SAPID" type="text" class="form-control" autocomplete="off" >
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="position-relative form-group">
                                                    <!-- <input name="ipasscode" id="ipasscode" placeholder="Password" type="password" class="form-control" value="123"  autocomplete="off"> -->
                                                    <input name="ipasscode" id="ipasscode" placeholder="Password" type="password" class="form-control" autocomplete="off">
                                                </div>
                                            </div>
                                        </div>
                         
									<li><button class="button wow fadeInUp" id="btn-search" data-wow-duration=".5s" data-wow-delay=".6s">Check Status</button></li>
                                    <small style="color: white;">*Internal Hiring Position is below</small>
								</ul>
							</div>
						</div><!-- .col -->
						<div class="col-md-6 header-mockup">
							<!-- <div class="mockup-screen mockup-screen-one wow fadeInRight" data-wow-duration=".5s"><img src="images/hs-1.png" alt="header screen" /></div>
							<div class="mockup-screen mockup-screen-two wow fadeInRight" data-wow-duration=".5s" data-wow-delay=".3s"><img src="images/hs-2.png" alt="header screen" /></div>
							<div class="mockup-screen mockup-screen-three wow fadeInRight" data-wow-duration=".5s" data-wow-delay=".6s"><img src="images/hs-3.png" alt="header screen" /></div> -->
                            

                        </div>
					</div>
				</div>
			</div>
		</div>
		<div class="feature-boxes" id="jobs">
			<div class="container">
				<div class="row text-center">
                    <!-- <a href="#"> -->
					<div class="col-md-12 col-sm-6">
                        <div class="box wow fadeInUp" data-wow-duration=".5s">
                            <div style="text-align: right;">
                                <a href="../internalhiring/internalhiring.pdf" target="_blank" class="btn btn-warning" style="margin-bottom:5px;"><i class="fa fa-download"></i> Internal Hiring PDF</a><br>
                                <a href="#" target="_blank" class="btn btn-info"><i class="fa fa-download"></i> Letter Approval Template</a><br>
                            </div>
                            <div style="text-align: left;">
                                <b>Kalimantan Fiber</b>
                                <h2>Internal Hiring Position</h2>
                            </div>
                            <div id="popup"></div>
                            <div id="divinternalhiring"></div>
                            <div style="text-align: left; margin-top: 5px; ">
                                <strong>*Notes :</strong>
                                <ul>
                                    <li>- You Can Only apply for one position</li>
                                    <li>- You Can Only apply same position level</li>
                                    <li>- You Can Canceled applyment on (Check Status Form) and apply position again just 3 times</li>
                                </ul>
                            </div>
						</div>
					</div>
                    <!-- </a> -->
				</div>
			</div>
		</div>
		<div class="footer-section section">
			<div class="container">
				<div class="row text-center">
					<div class="col-md-12">
						<ul class="footer-links inline-list">
							<li>Oasys : Internal Hiring © <span id="versionapp">v-</span></li>
                            <button id="btn-close" hidden>close</button>
						</ul>
					</div>
				</div>
			</div>
		</div>
    
<!-- Modal -->
<div class="modal fade" id="modalcheckstatus" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Status Applyment</h5>
      </div>
      <div class="modal-body">
        <div id="divstatus">
            <table class="table table-bordered" id="tbl-status">
                <tr>
                    <th>BU</th>
                    <th>Department</th>
                    <th>Work Location</th>
                    <th>Position</th>
                    <th>Level</th>
                    <th>Expired Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <tr id="td-status">

                </tr>
            </table>
        </div> 
        <hr>
        <h4>History :</h4>
        <div id="divstatushistory">
            <table class="table table-bordered" id="tbl-statushistory">
                <tr>
                    <th>BU</th>
                    <th>Department</th>
                    <th>Work Location</th>
                    <th>Position</th>
                    <th>Level</th>
                    <th>Expired Date</th>
                    <th>Status</th>
                </tr>
                <tbody id="tr-statushistory">

                </tbody>
            </table>
        </div> 
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
		
	</body>
    </html>
    

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>    
<script src="../assets/js/jquery.bundle.js"></script>
<script src="../assets/js/scriptlanding.js"></script>
<script src="service.js"></script>
<script language="JavaScript" src="../js/lib/dx.all.js" type="text/javascript"></script>

<script>
$('#versionapp').text('v1.1');

function internalhiring() {

    var store = new DevExpress.data.CustomStore({
        key: "id",
        load: function() {
            // return sendRequest(apiurl + "/internalhiring","POST",{criteria: 'all'});
            return $.post('/oasys/api/apiinternalhiring', {criteria : 'all'},function(response){
                return response
            },'json')
        },
    });

    function moveEditColumnToLeft(dataGrid) {
        dataGrid.columnOption("command:edit", { 
            visibleIndex: -1,
            width: 80 
        });
    }

    var id = {},
        level = {},
        popup = null,
        popupOptions = {
            width: '80%',
            height: 700,
            contentTemplate: function() {
                return $("<div />").append(

                    // $("<p>Employee Data "+id+"</p>"),
                    // $("<div>").attr("id", "formupload").dxFileUploader({
                    //     uploadMode: "useButtons",
                    //     name: "file",
                    //     uploadUrl: "/api/upload-berkas/"+id+"/suratmasuk",
                    //     accept: "image/*,application/pdf,application/msword,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                    //     onUploaded: function (e) {						
                    //         dataGrid.refresh();
                    //     }
                    // })
                    $("<form>").attr("id","formemployee").dxForm({
                            onInitialized: function(e) {
                                formInstance = e.component;
                                vacantid = id;
                                vacantlevel = level;
                            },
                            colCount: 2,
                            items: [
                            {
                                itemType: 'group',
                                caption: 'Employee Data',
                                items: [
                                    {
                                        dataField: 'sapid',
                                        label: {
                                            text: 'SAPID'
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },
                                    {
                                        dataField: 'passcode',
                                        label: {
                                            text: 'Create Password'
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },
                                    {
                                        itemType: 'button',
                                        horizontalAlignment: 'left',
                                        buttonOptions: {
                                            text: 'Check SAPID Data',
                                            type: 'danger',
                                            onClick: function(){
                                                
                                                var data = formInstance.option("formData");	

                                                var criteria = {criteria : 'find',status : 'checksapid',data:data};
    
                                                $.post('/oasys/api/apiinternalhiring', criteria ,function(response){
                                                    // console.log(response);
                                                    if(response.status == 200) {
                                                        alert(
                                                            'fullname : '+response.data.fullname+'\n'+
                                                            'company : '+response.data.companycode+'\n'+
                                                            'department : '+response.data.department+'\n'+
                                                            'position : '+response.data.designation+'\n'+
                                                            'location : '+response.data.location+'\n'+
                                                            'level : '+response.data.level+'\n'
                                                        );
                                                    } else {
                                                        DevExpress.ui.notify({
                                                            message: "SAPID Not Found",
                                                            type: "error",
                                                            displayTime: 3000,
                                                            height: 80,
                                                            position: {
                                                                my: 'top center', 
                                                                at: 'center center', 
                                                                of: window, 
                                                                offset: '0 0' 
                                                            }
                                                        })
                                                    }
                                                },'json')
                                  
                                            },
                                       
                                        },
                                    },
                                    
                                    
                                ],
                            },
                            {
                                itemType: 'group',
                                caption: 'Letter of Approval from the Department Head',
                                 items: [
										{
											template: function(data, itemElement) {
												itemElement.append($("<div>").attr("id", "lampiran").dxFileUploader({
													multiple: false,
													name:'lampiran',
													accept: '*',
													value: [],
													uploadMode: 'instantly',
													uploadUrl: '../api/uploadlampiran',
													onUploaded: function (e) {						
														var path = e.request.response;
														formInstance.option("formData").lampiran = path
													}
												  }));
											},
											dataField: "lampiran",
											name: "lampiran",
											label: {
												text: "Letter of Approval from the Department Head"
											},
											validationRules: [{
												type: "required"
											}]
										},
                                //     {
                                //         dataField: "lampiran",
                                //         width: 70,
                                //         allowFiltering: false,
                                //         allowSorting: false,
                                //         cellTemplate: cellTemplate,
                                //         editCellTemplate: editCellTemplate
                                //     }
                                    
                                ],
                            }, 
                            {
                                itemType: 'group',
                                caption: 'Personal Data',
                                items: [
                                    // {
                                    //     dataField: 'joindate',
                                    //     label: {
                                    //         text: 'Join Date'
                                    //     },
                                    //     editorType: 'dxDateBox',
                                    //     format: 'yyyy-MM-dd',
                                    //     editorOptions: {
                                    //         displayFormat:"yyyy-MM-dd",
                                    //     },
                                    // },
                                    {
                                        dataField: 'nohp',
                                        label: {
                                            text: 'No Handphone'
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },
                                    {
                                        dataField: 'dob',
                                        label: {
                                            text: 'Date Birth'
                                        },
                                        editorType: 'dxDateBox',
                                        format: 'yyyy-MM-dd',
                                        editorOptions: {
                                            displayFormat:"yyyy-MM-dd",
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },{
                                        dataField: 'gender',
                                        editorType: 'dxSelectBox',
                                        editorOptions: {
                                        dataSource: ['Male','Female'],
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },{
                                        dataField: 'education',
                                        editorType: 'dxSelectBox',
                                        editorOptions: {
                                        dataSource: ['Junior High School',
                                                    'Senior High School',
                                                    'Diploma',
                                                    'Bachelor Degree',
                                                    'Forestry',
                                                    'Agriculture',
                                                    'Economic',
                                                    'Law',
                                                    'Master',
                                                    'Other'
                                                ],
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },
                                    {
                                        dataField: 'educationothers',
                                        label: {
                                            text: 'Education Others'
                                        },
                                    },
                                ],
                            },
                            {
                                itemType: 'group',
                                caption: 'Reason',
                                items: [
                                    {
                                        dataField: 'reasonmove',
                                        name: 'reasonmove',
                                        label: {
                                            text: 'Reason To Move',
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },
                                    {
                                        dataField: 'reasondeserve',
                                        name: 'reasondeserve',
                                        label: {
                                            text: 'Reason why i deserve this position ',
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },
                                    {
                                        dataField: 'reasoncontribution',
                                        name: 'reasoncontribution',
                                        label: {
                                            text: 'Contribution I will make in this position ',
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },
                                ],
                            },
                            {
                                itemType: 'group',
                                caption: 'Score Apprisal',
                                items: [
                                    {
                                        dataField: 'score1',
                                        label: {
                                            text: '2018'
                                        },
                                        editorType: 'dxSelectBox',
                                        editorOptions: {
                                            dataSource: ['A','B+','B','C','D'],
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },
                                    {
                                        dataField: 'score2',
                                        label: {
                                            text: '2019'
                                        },
                                        editorType: 'dxSelectBox',
                                        editorOptions: {
                                            dataSource: ['1','2','3','4','5'],
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },
                                    {
                                        dataField: 'score3',
                                        label: {
                                            text: '2020'
                                        },
                                        editorType: 'dxSelectBox',
                                        editorOptions: {
                                            dataSource: ['1','2','3','4','5'],
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    },
                                ],
                            }, 
                            {
                                itemType: 'group',
                                caption: 'Submit',
                                items: [
                                    {
                                        dataField: 'isdeclaration',
                                        name: 'isdeclaration',
                                        label: {
                                        visible: false,
                                        },
                                        editorType: "dxCheckBox",
                                        editorOptions: {
                                        text: 'I declare that all statements of data and information entered are correct. If further information is needed to complete this registration, I will be willing. This application about mutation of employee and no additional benefits, no raises, and no offers.',
                                        },
                                        validationRules: [{type: "required",message: "this data is required"}],
                                    }, {
                                        itemType: 'button',
                                        horizontalAlignment: 'left',
                                        buttonOptions: {
                                            text: 'Apply',
                                            type: 'success',
                                            onClick: function(){
                                                var result = formInstance.validate();  
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
                                                
                                                // var data = JSON.stringify(formInstance.option("formData"));	
                                                var data = formInstance.option("formData");	
                                                submitform(vacantid,vacantlevel,data);
                                                // alert(JSON.stringify(formInstance.option("formData")));
                                            },
                                            useSubmitBehavior: false,
                                        },
                                    }
                                ],
                            },
                            
                        ],
                    })
                );
            },
            showTitle: true,
            title: "Application Form",
            dragEnabled: false,
            showCloseButton: true,
            closeOnOutsideClick: false
    };

    var showForm = function(vid,vlevel) {
        id = vid;
        level = vlevel;
        console.log('id : '+id+' & level :'+level);

        if(popup) {
            popup.option("contentTemplate", popupOptions.contentTemplate.bind(this));
        } else {
            popup = $("#popup").dxPopup(popupOptions).dxPopup("instance");
        }

        popup.show();
    };

    var dataGrid = $("#divinternalhiring").dxDataGrid({    
        dataSource: store,
        allowColumnReordering: true,
        allowColumnResizing: true,
        columnsAutoWidth: true,
        columnMinWidth: 80,
        wordWrapEnabled: true,
        rowAlternationEnabled: true, 
        showRowLines: true,
        showBorders: true,
        filterRow: { visible: true },
        // filterPanel: { visible: true },
        headerFilter: { visible: true },
        height: 600,
        // selection: {
        //     mode: "multiple"
        // },
        editing: {
            useIcons:true,
            mode: "batch",
            allowAdding: false,
            allowUpdating: false,
            allowDeleting: false,
        },
        scrolling: {
            mode: "infinite"
        },
        columns: [
            {
                caption: "Actions",
                fixed: true,
                fixedPosition: "left",
                width: 120,
                allowFiltering: false,
                allowSorting: false,
                formItem: { visible: false},
                cellTemplate: function (container, options) {
                    $('<div style="padding:2px 15px 2px 15px;" title="View Detail" />').addClass('dx-icon-detailslayout btn-pill btn-shadow btn btn-danger')
                        .text(' Apply')
                        .on('dxclick', function () {
                            // DevExpress.ui.notify("Loading detail data for "+options.data.requestdate,"info",600);
                            // $scope.loadMMF(options.data,"report",true);
                            // alert(options.data.id +'&'+options.data.level);
                            showForm(options.data.id,options.data.level);
                        })
                        .appendTo(container);
                }
            },
            { 
                dataField: "bu",
                sortOrder: "asc",
            },
            'department','worklocation',
            { 
                dataField: "position",
                sortOrder: "asc",
            },
            // 'level',
            {dataField:'level',encodeHtml: false ,
                customizeText: function (e) {
                var rDesc = ["","Mandor","Asst","Askep","Manager",""];
                return rDesc[e.value];
            }},
            {
                dataField: 'expireddate',
                caption: "Expired Date",
                dataType: "date",
                format: "dd/MM/yyyy"
            },

        
        ],
        export: {
            enabled: false,
            fileName: "master-user",
            excelFilterEnabled: true,
            allowExportSelectedData: true
        },
        onInitNewRow: function(e) {  
            // e.data.bulan = new Date().getMonth()+1;
            // e.data.tahun = new Date().getFullYear();
        } ,
        onContentReady: function(e){
            moveEditColumnToLeft(e.component);
        },
        onEditorPreparing: function(e) {

        },
        onToolbarPreparing: function(e) {
            dataGrid = e.component;

            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        dataGrid.refresh();
                    }
                }
            })
        },
    }).dxDataGrid("instance");

    
}

//load
internalhiring();

function submitform(id,level,data) {

    data.joindate = new Date(data.joindate).toLocaleDateString('fr-CA');
    data.dob = new Date(data.dob).toLocaleDateString('fr-CA');

    var criteria = {criteria : 'create',ih_id : id,ih_level : level,data:data};
    
    console.log(criteria)
    $.post('/oasys/api/apiinternalhiringdetail', criteria ,function(response){
        if(response == 200) {
            DevExpress.ui.notify({
                message: "Success Apply Data",
                type: "success",
                displayTime: 3000,
                height: 80,
                position: {
                my: 'top center', 
                at: 'center center', 
                of: window, 
                offset: '0 0' 
            }
            });
            $("#popup").dxPopup("hide");
        } else if(response == 404) {
            DevExpress.ui.notify({
                message: "SAPID Not Found",
                type: "error",
                displayTime: 3000,
                height: 80,
                position: {
                    my: 'top center', 
                    at: 'center center', 
                    of: window, 
                    offset: '0 0' 
                }
            })
            $("#popup").dxPopup("hide");
        } else if(response == 405) {
            DevExpress.ui.notify({
                message: "You Must Apply same level",
                type: "error",
                displayTime: 3000,
                height: 80,
                position: {
                    my: 'top center', 
                    at: 'center center', 
                    of: window, 
                    offset: '0 0' 
                }
            })
            $("#popup").dxPopup("hide");
        } else if(response == 406) {
            DevExpress.ui.notify({
                message: "You Already Apply",
                type: "error",
                displayTime: 3000,
                height: 80,
                position: {
                    my: 'top center', 
                    at: 'center center', 
                    of: window, 
                    offset: '0 0' 
                }
            })
            $("#popup").dxPopup("hide");
        } else if(response == 407) {
            DevExpress.ui.notify({
                message: "Can't apply again because rejected status was found",
                type: "error",
                displayTime: 3000,
                height: 80,
                position: {
                    my: 'top center', 
                    at: 'center center', 
                    of: window, 
                    offset: '0 0' 
                }
            })
            $("#popup").dxPopup("hide");
        } else if(response == 500) {
            DevExpress.ui.notify({
                message: "You already apply 3 times",
                type: "warning",
                displayTime: 3000,
                height: 80,
                position: {
                    my: 'top center', 
                    at: 'center center', 
                    of: window, 
                    offset: '0 0' 
                }
            })
            $("#popup").dxPopup("hide");

        }
    });
}

$('#btn-search').click(function(){
    var sapid = $('#isapid').val();
    var passcode = $('#ipasscode').val();
    var data = {};
    data.sapid = sapid;
    data.passcode = passcode;
    var criteria = {criteria : 'find',status : 'checkstatus', data:data};

    console.log(criteria);
    $.post('/oasys/api/apiinternalhiring', criteria ,function(response){
        console.log(response.data);
        var data = response.data;
        var arraystatus = ["Canceled","Waiting","Doc. Selection","Assesment","Interview","Approved","Rejected",""];
        var arraylevel = ["","Mandor","Asst","Askep","Manager",""];
        if(response.status==200) {
            var detail = response.data.detail;
            $('#modalcheckstatus').modal('show');
            $('#td-status').html('');
            $('#td-status').append(
                '<td>'+detail.bu+'</td>'+
                '<td>'+detail.department+'</td>'+
                '<td>'+detail.worklocation+'</td>'+
                '<td>'+detail.position+'</td>'+
                '<td>'+arraylevel[detail.level]+'</td>'+
                '<td>'+detail.expireddate+'</td>'+
                '<td>'+arraystatus[data.status]+'</td>'
            );
            if(data.status > 0 && data.status < 5) {
                $('#td-status').append(
                    '<td><button class="btn btn-danger" onclick="cancelposition('+data.id+')">Cancel</button></td>'
                );
            }

            //history
            $('#tr-statushistory').html('');
            var criteriahistory = {criteria : 'find',status : 'checkstatushistory', data:data};
            $.post('/oasys/api/apiinternalhiring', criteriahistory ,function(responsehistory){
                var datahistory = responsehistory.data;
                $.each(responsehistory.data,function(x,y) {

                    $('#tr-statushistory').append(
                        '<tr>'+
                        '<td>'+y.bu+'</td>'+
                        '<td>'+y.department+'</td>'+
                        '<td>'+y.worklocation+'</td>'+
                        '<td>'+y.position+'</td>'+
                        '<td>'+arraylevel[y.level]+'</td>'+
                        '<td>'+y.expireddate+'</td>'+
                        '<td>'+arraystatus[y.status]+'</td>'+
                        '</tr>'
                        );
                    })
            },'json')
        } else {
            DevExpress.ui.notify({
                message: "Data Not Found",
                type: "error",
                displayTime: 3000,
                height: 80,
                position: {
                    my: 'top center', 
                    at: 'center center', 
                    of: window, 
                    offset: '0 0' 
                }
            })
        }
    },'json');
    

})

function cancelposition(id) {

    var data = {};
    data.id = id;
    var r = confirm('Apakah anda yakin ?');
    if(r) {
        var criteria = {criteria : 'find',status : 'cancelapplyment', data:data};
        $.post('/oasys/api/apiinternalhiring', criteria ,function(response){
            
            if(response.status==200) {
                DevExpress.ui.notify({
                    message: "Cancel Applyment Success",
                    type: "success",
                    displayTime: 3000,
                    height: 80,
                    position: {
                        my: 'top center', 
                        at: 'center center', 
                        of: window, 
                        offset: '0 0' 
                    }
                })
            } else {
                DevExpress.ui.notify({
                    message: "Error!!",
                    type: "error",
                    displayTime: 3000,
                    height: 80,
                    position: {
                        my: 'top center', 
                        at: 'center center', 
                        of: window, 
                        offset: '0 0' 
                    }
                })
            }
        },'json');
        setTimeout(function(){ window.location.reload(); }, 5000);
    } else {
        alert('Cancel');
    }
}

</script>