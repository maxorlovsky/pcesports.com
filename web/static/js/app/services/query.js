app.factory('query', ['$resource', function query($resource) {
	return $resource((g.env=='prod'?g.siteSecure:g.site), {},
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