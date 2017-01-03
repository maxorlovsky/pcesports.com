var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var fs = require('fs');
var mysql = require('mysql');
var sql = mysql.createConnection({
	host     : '127.0.0.1',
	user     : 'pcusertest',
	password : 's12WD@#$asdaAD2',
	database : 'pentaclick_dev'
});

sql.connect();

var name = '';

var pce = {
	user: {},

	checkState: function() {
		console.log('state');
		io.emit('fightStatus', pce.user.id);
	},
	authenticate: function() {
		sql.query('SELECT * FROM `participants` WHERE `id` = ? AND `link` = ?', [pce.user.id, pce.user.link], function(err, rows) {
			console.log(err);
			pce.user = rows;
		});
	}
}

io.on('connection', function(socket) {
	var user = {};

  	socket.on('handshake', function(data) {
  		sql.query('SELECT * FROM `participants` WHERE `id` = ? AND `link` = ?', [data.id, data.link], function(err, rows) {
			console.log(err);
			user = rows;
		});
		
  		pce.user = data;

  		pce.authenticate();
  	});

	socket.on('disconnect', function() {
		console.log('disconnected/destroyed');
		//sql.destroy();
	});
});

http.listen(3000, function(){
	console.log('Running');
});

setInterval(pce.checkState, 3000);