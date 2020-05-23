function inc(filename){
	//var body = document.getElementsByTagName('body').item(0);
	var script = document.createElement('script');
	script.src = filename;
	script.language = "JavaSript";
	script.type = "text/javascript";
	//body.appendChild(script);
	document.body.appendChild(script);
	
}
	function PrintDiv() {
		var divToPrint = document.getElementById('divToPrint');
		var popupWin = window.open('', '_blank', 'width=300,height=300');
		popupWin.document.open();
		popupWin.document.write('<html><body onload="window.print()">' + divToPrint.innerHTML + '</html>');
		popupWin.document.close();
	}
	//if ($('#datepicker').length) $("#datepicker").datepicker({ dateFormat: 'yy-mm-dd',showOtherMonths:true });
	// var appversion="1.021";
	// inc("js/controllers/maincontroller.js?v="+appversion);
	// inc("js/controllers/login.js?v="+appversion);
	// inc("js/controllers/user.js?v="+appversion);
	// inc("js/controllers/role.js?v="+appversion);
	// inc("js/controllers/module.js?v="+appversion);
	// inc("js/controllers/userdetail.js?v="+appversion);
	// inc("js/controllers/useraccess.js?v="+appversion);
	// inc("js/controllers/dashboard.js?v="+appversion);
	// inc("js/controllers/company.js?v="+appversion);
	// inc("js/controllers/department.js?v="+appversion);
	// inc("js/controllers/division.js?v="+appversion);
	// inc("js/controllers/designation.js?v="+appversion);
	// inc("js/controllers/employee.js?v="+appversion);
	// inc("js/controllers/employeedetail.js?v="+appversion);
	
	
