app.factory('query', ['$resource', function query($resource) {
	'use strict';

	return $resource(g.site, //'/nrgs/:language/api/:category/:subquery/:query-:version/:type/',
		{
			ajax: '@ajax'
		},
		{
			get: {
				method: 'GET',
				/*params: {
					version: 'v1'
				}*/
			},
			post: {
				method: 'POST',
				/*params: {
					category: '@category',
					subquery: '@subquery',
					version: 'v1'
				}*/
			}
		}
	);
}]);