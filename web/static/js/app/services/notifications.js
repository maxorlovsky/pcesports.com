app.factory('notification', function notificationFactory() {
	'use strict';

	return {
		error: function(object) {
			var message = '';

			if (object.status === 400) {
				if (object.data.errors) {
					angular.forEach(object.data.errors, function(value, key) {
						if (value[0]) {
							message = value[0];
							return;
						}
						else {
							message = GT.vars.lngs.errorMessage;
						}
					});
				}
			}
			else if (object.status === 403) {
				message = object.data.message;
			}
			else if (object.status === 401) {
				message = object.data.message;
			}
			else {
				message = GT.vars.lngs.errorMessage;
			}

			return message;
		}
	}
});
