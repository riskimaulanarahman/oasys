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
	
	
