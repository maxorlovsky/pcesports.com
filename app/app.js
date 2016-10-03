angular.module('app', ['ngRoute', 'ngAnimate'])
.constant('LINKS', [
		{url: '/new', value: 'Home', controller: 'HomeController', icon: 'home'},
		{url: '/game/leagueoflegends', value: 'League of Legends', controller: 'LolController', icon: 'lol'},
		{url: '/game/hearthstone', value: 'Hearthstone', controller: 'HsController', icon: 'hs'},
		//{url: '/games/overwatch', value: 'Overwatch', controller: 'OwController'},
		//{url: '/game/lol', value: 'CS:GO', controller: 'CsController'},
		{url: '/about', value: 'About', controller: 'AboutController', icon: 'about'},
		{url: '/contact', value: 'Contact', controller: 'ContactController', icon: 'contact'},
		{url: '/login', value: 'Login', controller: 'ProfileController', nav: false},
])
.config(['$routeProvider', '$locationProvider', 'LINKS', function($routeProvider, $locationProvider, LINKS) {
	var file = '';
	
	angular.forEach(LINKS, function(value, key) {
		//Checking if this is activeable link
		//If controller exists
		//If this is not external link
		if (value.url !== false && value.controller && !value.external) {
			//Changing slash to minus, for files to load properly
			file = value.url.replace(/\//g, '-').substring(1);

			//Home URL, exception
			if (value.url == '/new') {
				file = 'home';
			}

			//Adding to router
			$routeProvider.when(value.url, {
				templateUrl: 'new/app/pages/' + file + '/view.html',
				controller: value.controller
			});
		}
	});
	$routeProvider.otherwise({ redirectTo: '/new' });
	
	//Enabling html5 mode to not use hashtag
	$locationProvider.html5Mode(true);
}]);

//Scroll fix, on changing location we just hitting page up
angular.module('app').run(['$rootScope', '$anchorScroll', function($rootScope, $anchorScroll) {
	$rootScope.$on('$routeChangeSuccess', function() {
		$anchorScroll();
	});
}]);