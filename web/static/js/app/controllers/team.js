app.controller('Team', ['$scope', 'query', 'notification', function ($scope, query, notification) {
	$scope.error = '';
	$scope.success = '';
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
            window.location.href = answer.url;
		},
		function(answer) {
			$scope.button = '';
            $scope.error = notification.form(answer);
		});
	};

	$scope.editTeam = function() {
		if ($scope.button) {
            return false;
        }

        $scope.error = '';
        $scope.success = '';
        $scope.button = 'alpha';
        
        query.save({
			ajax: 'editTeam',
			form: $('form').serialize()
		},
		function(answer) {
			$scope.button = '';
            $scope.success = answer.message;
		},
		function(answer) {
			$scope.button = '';
            $scope.error = notification.form(answer);
		});
	};
}]);