cookies =
	read: (key='')->
		regex = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)')
		result = regex.exec(document.cookie)
		if result?
			return result[1]		
		return false

	write: (c_name, value = '', exdays=360)->
		exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays)
		c_value=escape(value)
		if exdays?
			c_value =  c_value + "; expires="+exdate.toUTCString()
		document.cookie=c_name + "=" + c_value
		return true;
window.app.cookies = cookies;