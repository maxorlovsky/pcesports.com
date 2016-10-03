angular.module('app').directive('externalLink', function() {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			var href = element.href;
			if (href.indexOf('http://') != -1 && href.indexOf('https://') != -1) {
				element.attr('target', '_blank');
			}
		}
	}
});