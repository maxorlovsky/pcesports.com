app.factory('notification', function notificationFactory() {
	'use strict';

	return {
		error: function(object) {
			var message = '';
            
            return object.data.message;
		}
	}
});
