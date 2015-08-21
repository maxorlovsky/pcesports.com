var app;

(function(){
	'use strict';

	app = angular.module('pcesports', ['ngResource']);
})();

/*app.constant('config', {
    site: site,
    url: url,
    platformLink: platformLink,
    img: img,
    env: env,
    lang: lang,
    system: system,
    streamer: streamer,
    currency: currentCurrency,
    logged_in: logged_in,
    timezone: timezone,
    loginURL: loginURL,
    userId: userId,
    showOnHomeAssets: showOnHomeAssets,
    streamer: streamer,
    runOff: runOff,
    secValues: secValues,
    loginData: loginData
});*/

/*app.constant('strings', {
    this: string
});*/
/*
app.config(['$routeProvider', function($routeProvider) {
    $routeProvider.
    when('/trade', {
        templateUrl: /template/+system+'/partial/binary.html',
        controller: 'binaryCtrl'
    }).
    otherwise({
        redirectTo: '/trade'
    });
}]);*/
app.factory('notification', function notificationFactory() {
	'use strict';

	return {
		error: function(object) {
            return object.data.message;
		},
        form: function(object) {
            var message = '';
            
            angular.forEach(object.data, function (value, key) {
				message += value+'<br />';
			});
            
            return message;
        }
	}
});

app.factory('query', ['$resource', function query($resource) {
	return $resource(g.site, {},
		{
			save: {
				method: 'POST',
				params: {
					//ajax: '@ajax'
				}
			}
		}
	);
}]);
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
            $scope.error = notification.form(answer);
		});
	};
}]);