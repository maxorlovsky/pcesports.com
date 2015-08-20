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
			var message = '';

			if (object.status === 400) {
				if (object.data.errors) {
					angular.forEach(object.data.errors, function(value, key) {
						if (value[0]) {
							message = value[0];
							return;
						}
						else {
							message = GT.vars.lngs.errorMessage;
						}
					});
				}
			}
			else if (object.status === 403) {
				message = object.data.message;
			}
			else if (object.status === 401) {
				message = object.data.message;
			}
			else {
				message = GT.vars.lngs.errorMessage;
			}

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
app.controller('Team', ['$scope', 'query', function ($scope, query) {
	$scope.error = '';
	$scope.button = '';

	$scope.addTeam = function() {
		if ($scope.button) {
            return false;
        }

        $scope.error = '';
        $scope.button = 'alpha';
        
        query.save({
			ajax: 'addTeamz',
			form: $('form').serialize()
		},
		function(data) {
            console.log(data);
			$scope.button = '';
		},
		function(answer) {
			$scope.button = '';
            console.log(answer.data);
            $scope.error = answer.message;
		});
	};
}]);

/*var query = {
    type: 'POST',
    data: {
        ajax: 'addTeam',
        form: $('.profile').serialize()
    },
    success: function(answer) {
        PC.formInProgress = 0;
        data = answer.split(';');
        
        if (data[0] != 1) {
            $('.profile #error p').text(data[1]);
            $('.profile #error').slideDown(1000);
        }
        else {
            window.location.href = data[1];
        }
    }
};
this.ajax(query);*/