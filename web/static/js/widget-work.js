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
        games: ['leagueoflegends', 'hearthstone'],
        
        initiate: function() {
            var widgetElement = jQuery('#pce-widget');
            var iframe;
            var url;
            var get = {};
            var game;
            var project = this.getProject();
            
            if (project === false) {
                return false;
            }
            
            url = this.getPlatform() + 'pcesports.com/widget' + project;
            
            var game = this.getGame();
            if (game !== false) {
                url += '/' + game;
                
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
                    url += '/participant/'+get.participant+'/'+get.link;
                }
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
            iframe.attr('src', url);
            
            if (window.addEventListener) {
                window.addEventListener("message", widget.fetchFrameMessage, false);
            } else {
                window.attachEvent("onmessage", widget.fetchFrameMessage);
            }
        },
        getGame: function() {
            jQuery.each(this.games, function(key, value) {
                if (window.location.href.indexOf(value) != -1) {
                    return value;
                }
            });
            
            return false;
        },
        getProject: function() {
            var url;
            
            if (window.location.hostname.indexOf('skillz') != -1) {
                url = '/skillzhs';
            }
            else if (window.location.hostname.indexOf('unicon') != -1) {
                url = '/uniconhs';
            }
            else if (window.location.hostname.indexOf('bnb') != -1) {
                url = '/bnb';
            }
            else if (window.location.hostname.indexOf('pcesports') != -1) {
                if (window.location.href.indexOf('skillz') != -1) {
                    url = '/skillzhs';
                }
                else if (window.location.href.indexOf('unicon') != -1) {
                    url = '/uniconhs';
                }
                else if (window.location.href.indexOf('bnb') != -1) {
                    url = '/bnb';
                }
            }
            
            if (url) {
                return url;
            }
            
            console.log('Your website is not allowed to use Pentaclick eSports widget');
            return false;
        },
        getPlatform: function() {
            if (window.location.href.indexOf('dev') != -1) {
                platform = 'http://dev.';
            }
            else if (window.location.href.indexOf('test') != -1) {
                platform = 'http://test.';
            }
            else {
                platform = 'https://www.';
            }
            
            return platform;
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