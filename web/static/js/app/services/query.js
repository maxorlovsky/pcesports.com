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