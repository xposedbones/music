Socket = 
	socket:null
	config:
		endpoint = '/'

	init:()->
		@socket = io.connect @config.endpoint
		@socket.on 'connect', (e)->
			Socket.authenticate();

		@socket.on 'userData', (userData)->
			window.app.user = userData;
			alert 'hey, ' + userData.name;
			
	authenticate:()->
		sessionCookie = app.cookies.read('session_key');
		sessionCookie = 'whatever';
		if sessionCookie
			Socket.socket.emit 'authenticate', sessionCookie
		else
			Socket.socket.emit 'login',
				user: 'test',
				pass: 'test'

window.app.Socket = Socket;