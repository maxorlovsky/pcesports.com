angular.module('app').controller('MainController', ['$scope', '$location', 'LINKS', function ($scope, $location, LINKS) {
	$scope.links = LINKS;
	$scope.loaded = 0;
	$scope.year = new Date().getFullYear();
	$scope.parentObject = {
		sidebar: 0,
		currentPage: '/'
	};
	
	$scope.$on('$viewContentLoaded', function(){
	    $scope.loaded = 1;
	});

	$scope.gotoContent = function() {
		$anchorScroll('container');
	}
}]);