!function(){"use strict";window.jQuery?jQuery("#pce-widget").ready(function(){a.initiate()}):console.log("jQuery not found, shutting down");var a={initiate:function(){var b,c,d,e=jQuery("#pce-widget"),f={};if(c=-1!=window.location.href.indexOf("dev")?"http://dev.":-1!=window.location.href.indexOf("test")?"http://test.":"https://www.",-1!=window.location.hostname.indexOf("skillz"))d=c+"pcesports.com/widget/skillzhs";else if(-1!=window.location.hostname.indexOf("unicon"))d=c+"pcesports.com/widget/uniconhs";else if(-1!=window.location.hostname.indexOf("bnb"))d=c+"pcesports.com/widget/bnb";else{if(-1==window.location.hostname.indexOf("pcesports"))return console.log("Your website is not allowed to use Pentaclick eSports widget"),!1;if(-1!=window.location.href.indexOf("skillz"))d=c+"pcesports.com/widget/skillzhs";else if(-1!=window.location.href.indexOf("unicon"))d=c+"pcesports.com/widget/uniconhs";else{if(-1==window.location.href.indexOf("bnb"))return console.log("Please specify platform"),!1;d=c+"pcesports.com/widget/bnb"}}return-1!=window.location.href.indexOf("participant")&&(breakdownGlobal=window.location.href.split("&"),delete breakdownGlobal[0],jQuery.each(breakdownGlobal,function(a,b){void 0!=b&&(breakdown=b.split("="),void 0!=breakdown[1]&&(f[breakdown[0]]=breakdown[1]))}),d+="/participant/"+f.participant+"/"+f.link),e.length<=0?(console.log("Widget frame not found, shutting down"),!1):(e.css({width:"100%",margin:"0 auto"}),e.html("<iframe />"),b=e.find("iframe"),b.css({width:"100%",border:"0"}),b.attr("src",d),void(window.addEventListener?window.addEventListener("message",a.fetchFrameMessage,!1):window.attachEvent("onmessage",a.fetchFrameMessage)))},fetchFrameMessage:function(a){var b="";return a?void(a.data&&(b=a.data.split("="),"height"==b[0]&&jQuery("#pce-widget").find("iframe").height(b[1]))):(console.log("No event data sent"),!1)}}}();