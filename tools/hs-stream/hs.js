var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var request = require('request');
var url = require('url');

if (process.argv[2] == 'dev') {
	var env = 'dev';
}
else if (process.argv[2] == 'live') {
	var env = 'www';
}
else {
	console.log('Set environment dev/live');
	return false;
}

io.on('connection', function(socket){
  	io.emit('status', 'Connected');
  	io.emit('updateUserList', stream.users);

	socket.on('disconnect', function(){
		io.emit('status', 'Disconnected');
	});

	socket.on('updateData', function(data){
		stream.users = data;

		io.emit('updateUserList', stream.users);
	});
});

//Initiate
http.listen(3008, function() {
	console.log('Running');
});

var stream = {
	//variables
	users: {
        player1: {
            name: '-',
            class: [0, 0, 0],
            classStatus: [false, false, false],
            ban: 0
        },
        player2: {
            name: '-',
            class: [0, 0, 0],
            classStatus: [false, false, false],
            ban: 0
        }
    }
}