app.controller('userDetailCtrl', ['$rootScope','$scope', '$http', '$interval','$location','CrudService','AuthenticationService', function($rootScope,$scope, $http, $interval,$location,CrudService,AuthenticationService)  {
	$scope.data = [];  
    $scope.detailFormOptions = { 
		readOnly : true,
		labelLocation : "left",
		minColWidth  :300,
		colCount : 2,
        bindingOptions: {
            formData: "data",
        }
    };
	
	console.log($scope.Userid);
	// $rootScope.$on('loaddetailData', function(event, id) {
		// });
	CrudService.GetById('user',$scope.Userid).then(function(response){
		$scope.data = response;
	})
	
	$scope.backbuttonOptions = {
		type: "success",
		text: "Back",
        onClick: function(){	
			$scope.dataUser();
        }
    };

}]);