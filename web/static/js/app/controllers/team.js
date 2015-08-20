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