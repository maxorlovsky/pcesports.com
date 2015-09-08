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