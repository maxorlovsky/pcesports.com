var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var fs = require('fs');
//index = fs.readFileSync(__dirname + '/hs.html');

app.get('/', function(req, res){
	res.sendFile(__dirname + '/hs.html');;
});

io.on('connection', function(socket){
  	io.emit('status', 'Connected');
  	//document.getElementById('status').value = 'Connected';

	socket.on('disconnect', function(){
		io.emit('status', 'Disconnected');
	});

	socket.on('updateIcons', function(data){
		console.log(data);
		var pathToIcons = 'icons';
		var pathToPlayerFolder = data[0];
		var playerFileName = [];

		fs.writeFile(pathToPlayerFolder+'/name.txt', data[1], function(err) {
			if (err) {
				return console.log(err);
			}
		}); 
		
		for(i=0;i<3;++i) {
			fileName = data[2+i]+(data[5+i]===true?'-checked':'')+'.png');
			fs.createReadStream(pathToIcons+'/'+fileName).pipe(fs.createWriteStream(pathToPlayerFolder+'/'+i+'.png'));
		}
	});
});

http.listen(3000, function(){
	console.log('Running');
});