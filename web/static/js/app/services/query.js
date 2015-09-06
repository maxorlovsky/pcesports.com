app.factory('query', ['$resource', function query($resource) {
	return $resource((g.env=='dev'?g.site:g.siteSecure), {},
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