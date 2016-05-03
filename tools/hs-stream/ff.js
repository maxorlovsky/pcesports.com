//for files
if (process.argv[2] != 'dev' && process.argv[2] != 'www') {
	console.log('Set environment dev/www');
	return false;
}
else {
	var env = process.argv[2];
}

var socket = require('socket.io-client')('http://'+env+'.pcesports.com:3008');
var fs = require('fs');

// Create a socket (client) that connects to the server
socket
.on('userSelectedData', function(data) {
	var pathToIcons = 'icons';

	for (i=1; i<=2; ++i) {
		var pathToPlayerFolder = i;

		if (data[i] !== null) {
			fs.writeFile(pathToPlayerFolder+'/name.txt', data[i][1], function(err) {
				if (err) {
					return console.log(err);
				}
			}); 
			
			for(j=0;j<3;++j) {
				fileName = data[i][2+j]+(data[i][5+j]===true?'-checked':'')+'.png';
				fs.createReadStream(pathToIcons+'/'+fileName).pipe(fs.createWriteStream(pathToPlayerFolder+'/'+j+'.png'));
			}

			if (data[i][8] != 'undefined') {
				fileName = data[i][8]+'-banned.png';
				fs.createReadStream(pathToIcons+'/'+fileName).pipe(fs.createWriteStream(pathToPlayerFolder+'/b.png'));
			}
		}
		else {
			//Clean
			fs.writeFile(pathToPlayerFolder+'/name.txt', '-', function(err) {
				if (err) {
					return console.log(err);
				}
			}); 
			for(j=0;j<3;++j) {
				fs.createReadStream(pathToIcons+'/-.png').pipe(fs.createWriteStream(pathToPlayerFolder+'/'+j+'.png'));
			}
			fs.createReadStream(pathToIcons+'/-.png').pipe(fs.createWriteStream(pathToPlayerFolder+'/b.png'));
		}
	}
});