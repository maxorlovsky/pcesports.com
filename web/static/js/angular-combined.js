'use strict';

var app = angular.module('pcesports', ['ngResource']);

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
app.factory('query', ['$resource', function($resource){
	return $resource(
		g.site,// /:id
		{
			id: '@id'
		},
		{
			post: {
				method: 'POST',
				params: { phoneId:'phones' },
				isArray: true
			}
		}
	);
}]);
app.controller('Team', ['$scope', 'query', function ($scope, query) {
	$scope.formInProgress = 0;
	$scope.error = '';
	$scope.button = '';

	$scope.addTeam = function() {
		if ($scope.formInProgress == 1) {
            return false;
        }

        $scope.formInProgress = 1;
        $scope.error = '';
        $scope.button = 'alpha';
        
        query.post({
        	ajax: 'addTeam',
        	form: $('form').serialize()
    	},
    	function() {
    		$scope.button = '';
    	},
    	function() {
    		$scope.button = '';
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
        $('#addTeam').removeClass('alpha');
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