App = 
		io: null
		redis:require('redis')
		redisWorker: null
		fs:null
		httpServer:null
		express:null
		config:
			port:8080
			redisHost:'top.30mars.ca'
			redisPort:6379
			redisDatabase: 2
			wwwPath:'./../www/'

		tempUserSessions:
			session1:
				name:'Alex',
				id: 'test'
			whatever:
				name:'BENOIT',
				id: 'test'
			whenever:
				name:'Alex',
				id: 'test'

		trendingAlbums:[
			{
				type:'med',
				image_url:'http://0.static.wix.com/media/cec8b8_bcbae9b705b89190a82a0dd200a03348.jpg_512',
				artist:'Macklemore & Ryan Lewis',
				name:'The Heist'
			},
			{
				type:'',
				image_url:"http://userserve-ak.last.fm/serve/_/88057565/Believe.png",
				artist:'Cher',
				name:'Believe'
			},
			{
				type:'huge',
				image_url:"http://userserve-ak.last.fm/serve/_/90656285/Gold+PNG.png",
				artist:'Sir Sly',
				name:'Gold [ep]'
			},
			{
				type:'med',
				image_url:"http://userserve-ak.last.fm/serve/_/84554553/Enema+of+the+State+6a00e54f9153e08833016766c18afa.jpg",
				artist:'Blink 182',
				name:'Enema Of The State'
			},

		]

		init: (config)->
				# Load libs only on init
				#if config?
				#		@config = @_mergeOptions @config config

				@fs = require('fs');
				express = require('express')
				@express = express.call(this);
				@httpServer = require('http').createServer(@express);
				@httpServer.listen(@config.port)
				@io = require('socket.io').listen(@httpServer);
				@io.set('log level', 1);

				@express.get '/api/*', @_handleAPICalls
				@express.post '/api/*', @_handleAPICalls
				@express.get '/*', @_handleHttpRequest
				#@express.post '/upload/', @_handleUpload

				@express.post('/upload/', (req, res) ->
				    console.log(JSON.stringify(req.files));
				);

				@express.use express.bodyParser({uploadDir:'/tmp/'});
				#@express.use express.cookieSession()

				@express.use (err, req, res, next)->
				  console.error(err.stack);
				  res.send(500, 'Oops ! Something went super wrong.');

				@redisWorker = @redis.createClient(App.config.redisPort, App.config.redisHost)
				
				@io.on 'connection', (socket)->
					socket.on 'authenticate', (data)->
						if App.tempUserSessions[data]? #FIX ME, HACK, NOT FINAL
							socket.emit 'userData', App.tempUserSessions[data];

					socket.on 'getTrendingAlbums', (callback)->
						callback(App.trendingAlbums);


		_handleUpload: (req, res) ->
			console.log req;
			if req.files?
				for key of req.files
					console.log req.files


		_handleAPICalls: (req, res) ->
			parts = req.url.split('?')[0].split('/');
			if parts.length < 4
				res.writeHead '500'
				res.end 'API calls expect at least a module/parameter combo.'
				return;
			module = parts[2];
			method = parts[3];
			
			switch module
				when "vote"
					voteData = 
						identity: req.connection.remoteAddress
						type:'like'

					if req.headers['X-Real-IP']?
						voteData.identity = req.headers['X-Real-IP'];

					result = App._registerVote method, voteData;
					if result
						res.writeHead '200'
						return res.end JSON.stringify {status:'ok', value:result}
					else
						res.writeHead '500'
						return res.end JSON.stringify {status:'failed'}
				else
					res.writeHead '404'
					res.end 'Module ' + module + ' not found'


		_registerVote: (city, data) ->
			cities = 
				laval : 0
				montreal : 1
				longueuil : 2
				quebec : 3

			if not city? || not cities[city]?
				return false

			key = 'votes:'+city
			console.log('New vote for '+city+' from '+data.identity);

			sendData = 
				city:city
				type:data.type

			if data.type == 'call'
				data.identity = (data.identity+'').split(',')[0]
				sendData.tel = data.identity.substr(0, data.identity.length-2);
			

			App.io.sockets.emit 'vote', sendData;
			App.tickerData.push(sendData);
			if App.tickerData.length > App.tickerMaxLength
				App.tickerData.splice 0, (App.tickerData.length - App.tickerMaxLength)

			if data.type == 'call'
				return App.redisWorker.zadd 'maireacademie:votes:'+city, new Date().getTime(), JSON.stringify data;
			else
				return App.redisWorker.zadd 'maireacademie:votes:fb:'+city, new Date().getTime(), JSON.stringify data;

		

		_handleHttpRequest: (req, res) ->

				# allowedHosts = ['www.maireacademie.ca', 'laval.maireacademie.ca', 'montreal.maireacademie.ca', 'longueuil.maireacademie.ca', 'quebec.maireacademie.ca', '342da8a6.ngrok.com', 'localhost'];

				# if false && allowedHosts.indexOf(req.headers.host) == -1
				# 	res.setHeader 'Location', 'http://'+allowedHosts[0]
				# 	res.writeHead '302'
				# 	return res.end '';

				file = req.url.split('?')[0];
				file = if file == '/' then 'index.php' else file;
				file = file.split('..').join('');

				path = __dirname + '/' + App.config.wwwPath + file;

				App.fs.readFile path, (err, data)->
						if(err)
								res.writeHead '500'
								return res.end('Error loading '+file)
						res.writeHead '200'
						res.end data;


App.init();