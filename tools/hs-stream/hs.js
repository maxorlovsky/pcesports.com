var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var fs = require('fs');
var request = require('request');

io.on('connection', function(socket){
  	io.emit('status', 'Connected');
  	io.emit('updateUserList', stream.usersFromServer);
  	io.emit('userSelectedData', stream.users);

	socket.on('disconnect', function(){
		io.emit('status', 'Disconnected');
	});

	socket.on('updateIcons', function(data){
		var pathToIcons = 'icons';
		var pathToPlayerFolder = data[0];
		var playerFileName = [];

		stream.users[pathToPlayerFolder] = data;

		io.emit('userSelectedData', stream.users);

		/*fs.writeFile(pathToPlayerFolder+'/name.txt', data[1], function(err) {
			if (err) {
				return console.log(err);
			}
		}); 
		
		for(i=0;i<3;++i) {
			fileName = data[2+i]+(data[5+i]===true?'-checked':'')+'.png';
			fs.createReadStream(pathToIcons+'/'+fileName).pipe(fs.createWriteStream(pathToPlayerFolder+'/'+i+'.png'));
		}

		if (data[8] != 'undefined') {
			fileName = data[8]+'-banned.png';
			fs.createReadStream(pathToIcons+'/'+fileName).pipe(fs.createWriteStream(pathToPlayerFolder+'/b.png'));
		}*/
	});

	socket.on('cleanUser', function(num) {
		stream.users[num] = null;
		io.emit('userSelectedData', stream.users);
	});
});

//Initiate
http.listen(3000, function() {
	stream.fetchUsers();

	setInterval(stream.fetchUsers, 5000); //rerun to fetch list every 15 seconds, it's localhost so should be fine

	console.log('Running');
});

var stream = {
	//variables
	users: [null, null, null],
	usersFromServer: {},
	hsListLink: 'http://dev.pcesports.com/run/hslist/haosdi012',
	request: null,
	heroes: [
        '',
        'warrior',
        'hunter',
        'mage',
        'warlock',
        'shaman',
        'rogue',
        'druid',
        'paladin',
        'priest'
    ],

	//functions
	fetchUsers: function() {
		if (stream.request !== null) {
			stream.request.abort();
		}

		stream.request = request(stream.hsListLink, function (error, response, body) {
			if (error || response.statusCode !== 200) {
				console.log('Call request error');
				return false;
			}

			if (JSON.stringify(stream.usersFromServer) !== JSON.stringify(body)) {
				//List updated, then giving this part to frontend and updating the link

				//Update global variable
				stream.usersFromServer = body;

				//Sending data to frontend
				io.emit('updateUserList', stream.usersFromServer);

				var userDataUpdateRequired = false;

				//Classes/Bans updates, figuring out and updating current users data
				for(i=1; i<=2; ++i) {
					if (stream.users[i] !== null) {
						obj = JSON.parse(body);
						for (var k in obj) {
							v = obj[k];
							if (v.name == stream.users[i][1]) {
								stream.users[i][2] = stream.heroes[v.contact_info.hero1];
								stream.users[i][3] = stream.heroes[v.contact_info.hero2];
								stream.users[i][4] = stream.heroes[v.contact_info.hero3];
								stream.users[i][8] = stream.heroes[v.contact_info.ban];

								userDataUpdateRequired = true;
							}
						}
					}
				}

				//Sending data to frontend, only if bans or classes were updated for that user
				if (userDataUpdateRequired === true) {
					io.emit('userSelectedData', stream.users);
				}
			}

			stream.request = null;
		});
	}
}