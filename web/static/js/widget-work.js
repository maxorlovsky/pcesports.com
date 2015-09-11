(function(){
    'use strict';

    if (window.jQuery) {
        jQuery('#pce-widget').ready(function() {
            widget.initiate();
        });
    }
    else {
        console.log('jQuery not found, shutting down');
    }

    var widget = {
        initiate: function() {
            var widgetElement = jQuery('#pce-widget');
            var iframe;
            var platform;
            var parentUrl;
            var get = {};
            
            if (window.location.href.indexOf('dev') != -1) {
                platform = 'http://dev.';
            }
            else if (window.location.href.indexOf('test') != -1) {
                platform = 'http://test.';
            }
            else {
                platform = 'https://www.';
            }
            
            if (window.location.hostname.indexOf('skillz') != -1) {
                parentUrl = platform+'pcesports.com/widget/skillzhs';
            }
            else if (window.location.hostname.indexOf('unicon') != -1) {
                parentUrl = platform+'pcesports.com/widget/uniconhs';
            }
            else if (window.location.hostname.indexOf('bnb') != -1) {
                parentUrl = platform+'pcesports.com/widget/bnb';
            }
            else if (window.location.hostname.indexOf('pcesports') != -1) {
                if (window.location.href.indexOf('skillz') != -1) {
                    parentUrl = platform+'pcesports.com/widget/skillzhs';
                }
                else if (window.location.href.indexOf('unicon') != -1) {
                    parentUrl = platform+'pcesports.com/widget/uniconhs';
                }
                else if (window.location.href.indexOf('bnb') != -1) {
                    parentUrl = platform+'pcesports.com/widget/bnb';
                }
                else {
                    console.log('Please specify platform');
                    return false;
                }
            }
            else {
                console.log('Your website is not allowed to use Pentaclick eSports widget');
                return false;
            }
            
            if (window.location.href.indexOf('participant') != -1) {
                breakdownGlobal = window.location.href.split('&');
                delete breakdownGlobal[0];
                jQuery.each(breakdownGlobal, function(k, v) {
                    if (v != undefined) {
                        breakdown = v.split('=');
                        if (breakdown[1] != undefined) {
                            get[breakdown[0]] = breakdown[1];
                        }
                    }
                });
                parentUrl += '/participant/'+get.participant+'/'+get.link;
            }

            if (widgetElement.length <= 0) {
                console.log('Widget frame not found, shutting down');
                return false;
            }

            //widget-css
            widgetElement.css({
                width: '100%',
                margin: '0 auto'
            });

            widgetElement.html('<iframe />');
            
            iframe = widgetElement.find('iframe');
            iframe.css({
                width: '100%',
                border: '0',
            });
            iframe.attr('src', parentUrl);
            
            if (window.addEventListener) {
                window.addEventListener("message", widget.fetchFrameMessage, false);
            } else {
                window.attachEvent("onmessage", widget.fetchFrameMessage);
            }
        },
        fetchFrameMessage: function(event) {
            var data = '';
            
            if (!event) {
                console.log('No event data sent');
                return false;
            }
            
            if (event.data) {
                data = event.data.split('=');
                if (data[0] == 'height') {
                    jQuery('#pce-widget').find('iframe').height(data[1]);
                }
            }
        }
    };

})();