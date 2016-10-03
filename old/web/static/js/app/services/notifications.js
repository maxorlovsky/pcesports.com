app.factory('notification', function notificationFactory() {
	'use strict';

	return {
		error: function(object) {
            return object.data.message;
		},
        form: function(object) {
            var message = '';
            
            angular.forEach(object.data, function (value, key) {
            	if (key != 'status') {
					message += value+"\n";
				}
			});
            
            return message;
        }
	}
});
