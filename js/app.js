var app=angular.module('kduApp', ['dx','ui.router','ngMessages', 'ngStorage','oc.lazyLoad','ngRoute'], function($httpProvider) {
  $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
  var param = function(obj) {
    var query = '', name, value, fullSubName, subName, subValue, innerObj, i;
      
    for(name in obj) {
      value = obj[name];
        
      if(value instanceof Array) {
        for(i=0; i<value.length; ++i) {
          subValue = value[i];
          fullSubName = name + '[' + i + ']';
          innerObj = {};
          innerObj[fullSubName] = subValue;
          query += param(innerObj) + '&';
        }
      }
      else if(value instanceof Object) {
        for(subName in value) {
          subValue = value[subName];
          fullSubName = name + '[' + subName + ']';
          innerObj = {};
          innerObj[fullSubName] = subValue;
          query += param(innerObj) + '&';
        }
      }
      else if(value !== undefined && value !== null)
        query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
    }
      
    return query.length ? query.substr(0, query.length - 1) : query;
  };
 
  $httpProvider.defaults.transformRequest = [function(data) {
    return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
  }];
});

if (!String.prototype.padStart) {
    String.prototype.padStart = function padStart(targetLength,padString) {
        targetLength = targetLength>>0; //truncate if number or convert non-number to 0;
        padString = String((typeof padString !== 'undefined' ? padString : ' '));
        if (this.length > targetLength) {
            return String(this);
        }
        else {
            targetLength = targetLength-this.length;
            if (targetLength > padString.length) {
                padString += padString.repeat(targetLength/padString.length); //append to original to ensure we are longer than needed
            }
            return padString.slice(0,targetLength) + String(this);
        }
    };
}
var RouteProvider = function () {
	var scriptPath = "js/controllers/";
	var templatePath = "template/";
	var v= '3.03'
	this.resolve = function (name) {
		var route = {};
		route.templateUrl = templatePath + name + ".html?v="+v;
		route.controller = name + "Ctrl";
		//route.controllerAs = "vm";
		route.resolve = {
			load: ['$q', '$rootScope', function ($q, $rootScope) { return loadController($q, $rootScope, scriptPath + name + ".js?v="+v); }]
		};
		return route;
	};
	var loadController = function ($q, $rootScope, path) {
		var defer = $q.defer();
		$.ajax({
			dataType: "script",
			cache: true,
			url: path
		}).done(function (e) { 
			$rootScope.$apply();
			defer.resolve();
		});
		return defer.promise;
	};
};
app.config(function ($routeProvider, $controllerProvider) {
	var route = new RouteProvider(); 
	$routeProvider
	.when('/', route.resolve("dashboard"))
	.when('/user', route.resolve("user"))
	.when('/role', route.resolve("role"))
	.when('/module', route.resolve("module"))
	.when('/useraccess', route.resolve("useraccess"))
	.when('/dayoff', route.resolve("dayoff"))
	.when('/dodetail', route.resolve("dodetail"))
	.when('/doapproval', route.resolve("doapproval"))
	.when('/rfc', route.resolve("rfc"))
	.when('/detailrfc', route.resolve("detailrfc"))
	.when('/rfcapproval', route.resolve("rfcapproval"))
	.when('/company', route.resolve("company"))
	.when('/department', route.resolve("department"))
	.when('/division', route.resolve("division"))
	.when('/designation', route.resolve("designation"))
	.when('/employee', route.resolve("employee"))
	.when('/detailemployee', route.resolve("detailemployee"))
	.when('/approver', route.resolve("approver"))
	.when('/holiday', route.resolve("holiday"))
	.when('/rfcactivity', route.resolve("rfcactivity"))
	.when('/skrate', route.resolve("skrate"))
	.when('/rfccontractor', route.resolve("rfccontractor"))
	.when('/doreport', route.resolve("doreport"))
	.when('/repdo', route.resolve("repdo"))
	.when('/rfcreport', route.resolve("rfcreport"))
	.when('/tr', route.resolve("tr"))
	.when('/trdetail', route.resolve("trdetail"))
	.when('/trapproval', route.resolve("trapproval"))
	.when('/trreport', route.resolve("trreport"))
	.when('/spkl', route.resolve("spkl"))
	.when('/detailspkl', route.resolve("detailspkl"))
	.when('/spklapproval', route.resolve("spklapproval"))
	.when('/spklreport', route.resolve("spklreport"))
	.when('/spkltms', route.resolve("spkltms"))
	.when('/detailspkltms', route.resolve("detailspkltms"))
	.when('/spkltmsapproval', route.resolve("spkltmsapproval"))
	.when('/spkltmsreport', route.resolve("spkltmsreport"))
	.when('/mmf', route.resolve("mmf"))
	.when('/mmfdetail', route.resolve("mmfdetail"))
	.when('/mmfapproval', route.resolve("mmfapproval"))
	.when('/mmf30', route.resolve("mmf30"))
	.when('/mmf30detail', route.resolve("mmf30detail"))
	.when('/mmf30approval', route.resolve("mmf30approval"))
	.when('/iteie', route.resolve("iteie"))
	.when('/itsharefolder', route.resolve("itsharefolder"))
	.when('/itinetaccess', route.resolve("itinetaccess"))
	.when('/itrdweb', route.resolve("itrdweb"))
	.when('/itmailsize', route.resolve("itmailsize"))
	.when('/itstoragetf', route.resolve("itstoragetf"))
	.otherwise({
		redirectTo: '/'
	});
	app.register =
	{
		controller: $controllerProvider.register
	};
});
/*
function sleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  }
}
*/