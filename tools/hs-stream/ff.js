//for files
if (process.argv[2] == 'dev') {
    var env = 'dev';
}
else if  (process.argv[2] == 'live') {
    var env = 'direct';
}
else {
	console.log('Set environment dev/live');
	return false;
}

var socket = require('socket.io-client')('http://'+env+'.pcesports.com:3008');
var fs = require('fs');
var heroes = [
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
];

// Create a socket (client) that connects to the server
socket
.on('updateUserList', function(users) {
	var pathToIcons = 'icons';

	if (!users) {
		return false;
	}

	console.log(users);

	for (i=1; i<=2; ++i) {
		var pathToPlayerFolder = i;
		if (i == 1) {
            data = users.player1;
        }
        else {
            data = users.player2;
        }

		fs.writeFile(pathToPlayerFolder+'/name.txt', data.name, function(err) {
			if (err) {
				return console.log(err);
			}
		}); 
		
		for(j=0;j<3;++j) {
			fileName = heroes[data.class[j]]+(data.classStatus[j]===true?'-checked':'')+'.png';
			fs.createReadStream(pathToIcons+'/'+fileName).pipe(fs.createWriteStream(pathToPlayerFolder+'/'+j+'.png'));
		}

		
		fileName = heroes[data.ban]+'-banned.png';
		fs.createReadStream(pathToIcons+'/'+fileName).pipe(fs.createWriteStream(pathToPlayerFolder+'/b.png'));
		
	}
});