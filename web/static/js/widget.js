!function(){"use strict";window.jQuery?jQuery("#pce-widget").ready(function(){a.initiate()}):console.log("jQuery not found, shutting down");var a={games:["leagueoflegends","hearthstone"],initiate:function(){var b,c,d,e=jQuery("#pce-widget"),f={},g=this.getProject();if(g===!1)return!1;c=this.getPlatform()+"pcesports.com/widget"+g;var d=this.getGame();return d!==!1&&(c+="/"+d,-1!=window.location.href.indexOf("participant")&&(breakdownGlobal=window.location.href.split("&"),delete breakdownGlobal[0],jQuery.each(breakdownGlobal,function(a,b){void 0!=b&&(breakdown=b.split("="),void 0!=breakdown[1]&&(f[breakdown[0]]=breakdown[1]))}),c+="/participant/"+f.participant+"/"+f.link)),e.length<=0?(console.log("Widget frame not found, shutting down"),!1):(e.css({width:"100%",margin:"0 auto"}),e.html("<iframe />"),b=e.find("iframe"),b.css({width:"100%",border:"0"}),b.attr("src",c),void(window.addEventListener?window.addEventListener("message",a.fetchFrameMessage,!1):window.attachEvent("onmessage",a.fetchFrameMessage)))},getGame:function(){var a=!1;return jQuery.each(this.games,function(b,c){return-1!=window.location.href.indexOf(c)?void(a=c):void 0}),a},getProject:function(){var a;return-1!=window.location.hostname.indexOf("skillz")?a="/skillzhs":-1!=window.location.hostname.indexOf("unicon")?a="/uniconhs":-1!=window.location.hostname.indexOf("bnb")?a="/bnb":-1!=window.location.hostname.indexOf("pcesports")&&(-1!=window.location.href.indexOf("skillz")?a="/skillzhs":-1!=window.location.href.indexOf("unicon")?a="/uniconhs":-1!=window.location.href.indexOf("bnb")&&(a="/bnb")),a?a:(console.log("Your website is not allowed to use Pentaclick eSports widget"),!1)},getPlatform:function(){var a;return a=-1!=window.location.href.indexOf("dev")?"http://dev.":-1!=window.location.href.indexOf("test")?"http://test.":"https://www."},fetchFrameMessage:function(a){var b="";return a?void(a.data&&(b=a.data.split("="),"height"==b[0]&&jQuery("#pce-widget").find("iframe").height(b[1]))):(console.log("No event data sent"),!1)}}}();