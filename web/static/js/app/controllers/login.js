app.controller('Login', ['$scope', 'query', 'notification', function ($scope, query, notification) {
	$scope.error = '';
	$scope.button = '';

	$scope.login = function() {
		if ($scope.button) {
            return false;
        }

        $scope.error = '';
        $scope.button = 'alpha';
        
        query.save({
			ajax: 'login',
			form: $('loginForm').serialize()
		},
		function(answer) {
            window.location.href = answer.url;
		},
		function(answer) {
			$scope.button = '';
            $scope.error = notification.form(answer);
		});
	};
}]);