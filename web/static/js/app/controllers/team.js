app.controller('Team', ['$scope', 'query', 'notification', function ($scope, query, notification) {
	$scope.error = '';
	$scope.button = '';

	$scope.addTeam = function() {
		if ($scope.button) {
            return false;
        }

        $scope.error = '';
        $scope.button = 'alpha';
        
        query.save({
			ajax: 'addTeam',
			form: $('form').serialize()
		},
		function(answer) {
            window.location.href = answer.data.url;
		},
		function(answer) {
			$scope.button = '';
            $scope.error = notification(answer);
		});
	};
}]);