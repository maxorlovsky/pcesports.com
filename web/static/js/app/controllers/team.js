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