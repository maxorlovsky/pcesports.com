app.controller('Restore', ['$scope', 'query', 'notification', function ($scope, query, notification) {
	$scope.error = '';

	$scope.restore = function() {
		if ($scope.button || !$scope.code || !$scope.email || !$scope.password || !$scope.passwordRepeat) {
           return false;
        }

        $scope.form.repeat_password.$setValidity("match", true);
        if ($scope.password != $scope.passwordRepeat) {
        	$scope.form.repeat_password.$setValidity("match", false);
        }

        $scope.error = '';
        $scope.button = 'alpha';
        
        query.save({
			ajax: 'restorePasswordCode',
			email: $scope.email,
            code: $scope.code,
            password: $scope.password,
            passwordRepeat: $scope.passwordRepeat,
            captcha: jQuery('#g-recaptcha-response').val()
		},
		function(answer) {
            window.location.href = answer.url;
		},
		function(answer) {
			$scope.button = '';
            $scope.error = notification.form(answer);
            grecaptcha.reset();
		});
	};
}]);