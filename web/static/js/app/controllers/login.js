app.controller('Login', ['$scope', 'query', 'notification', function ($scope, query, notification) {
	$scope.errorLogin = '';
	$scope.errorRegistration = '';
	$scope.successRegistration = '';
	$scope.buttonLogin = '';
	$scope.buttonRegistration = '';

	$scope.login = function() {
		if ($scope.buttonLogin) {
            return false;
        }

        $scope.errorLogin = '';
        $scope.buttonLogin = 'alpha';
        
        query.save({
			ajax: 'login',
			email: $scope.emailLogin,
            password: $scope.passwordLogin
		},
		function(answer) {
            location.reload();
		},
		function(answer) {
			$scope.buttonLogin = '';
            $scope.errorLogin = notification.form(answer);
		});
	};

	$scope.register = function() {
		if ($scope.buttonRegistration || !$scope.emailRegistration || !$scope.passwordRegistration) {
            return false;
        }

        $scope.errorRegistration = '';
        $scope.buttonRegistration = 'alpha';
        
        query.save({
			ajax: 'register',
			email: $scope.emailRegistration,
            password: $scope.passwordRegistration,
            captcha: jQuery('#g-recaptcha-response').val()
		},
		function(answer) {
			$scope.emailRegistration = '';
			$scope.passwordRegistration = '';
			$scope.buttonRegistration = '';
            $scope.successRegistration = answer.message;
		},
		function(answer) {
			$scope.buttonRegistration = '';
            $scope.errorRegistration = notification.form(answer);
		});
	};

	$scope.showRegistration = function() {
		jQuery('#login-window .form[name="loginForm"]').slideUp('fast');
		jQuery('#login-window .form[name="registrationForm"]').slideDown('fast');
	};

	$scope.showRestore = function() {
		jQuery('#login-window .form[name="loginForm"]').slideUp('fast');
		jQuery('#login-window .form[name="restoreForm"]').slideDown('fast');
	};

	$scope.backStep = function() {
		jQuery('#login-window .form[name="loginForm"]').slideDown('fast');
		jQuery('#login-window .form[name="registrationForm"]').slideUp('fast');
		jQuery('#login-window .form[name="restoreForm"]').slideUp('fast');
	};
}]);