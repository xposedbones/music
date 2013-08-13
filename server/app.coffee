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


		init: (config)->
				# Load libs only on init
				if config?
						@config = @_mergeOptions @config config

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

				@express.use express.bodyParser()
				@express.use express.cookieSession()

				@express.use (err, req, res, next)->
				  console.error(err.stack);
				  res.send(500, 'Oops ! Something went super wrong.');

				@redisWorker = @redis.createClient(App.config.redisPort, App.config.redisHost)
				
				@io.on 'connection', (socket)->
					console.log 'connected'


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


		_handleTwilioCall: (method, req,  res) ->
			switch method
				when 'call'
					console.log 'call'
					file = App.config.twilio.responses.intro
					if req.query.Digits?

						cities = [null, 'laval', 'montreal', 'longueuil', 'quebec'];
						voteData = 
							identity: req.query.From
							time: new Date().getTime()
							type:'call'

						App._registerVote(cities[req.query.Digits], voteData)
						file = App.config.twilio.responses.outro

					App.fs.readFile file, (err, data)->
						if(err)
							res.writeHead '500'
							return res.end('Error loading xml file')
						res.setHeader 'Content-Type', 'text/xml'
						res.writeHead '200'
						res.end data;
				else
					res.writeHead '404'
					res.end 'Method not found'


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

		_updateCityStats: (city)->
			App.redisWorker.zcard 'maireacademie:votes:'+city, (err, callVotes)->
				request = require("request");
				request 'http://api.facebook.com/restserver.php?method=links.getStats&urls=http://'+city+'.maireacademie.ca/&format=json', (error, response, body)->
					data = JSON.parse(body);
					App.stats[city].votes = callVotes
					App.stats[city].likes = data[0].like_count
					App.stats[city].total = callVotes + data[0].like_count

					App.io.sockets.emit 'stats', {city:city, stats:App.stats[city]};

			#http://api.facebook.com/restserver.php?method=links.getStats&urls=http://montreal.maireacademie.ca/&format=json

		_handleHttpRequest: (req, res) ->

				allowedHosts = ['www.maireacademie.ca', 'laval.maireacademie.ca', 'montreal.maireacademie.ca', 'longueuil.maireacademie.ca', 'quebec.maireacademie.ca', '342da8a6.ngrok.com', 'localhost'];

				if false && allowedHosts.indexOf(req.headers.host) == -1
					res.setHeader 'Location', 'http://'+allowedHosts[0]
					res.writeHead '302'
					return res.end '';

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