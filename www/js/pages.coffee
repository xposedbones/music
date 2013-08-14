Pages = 
	config:
		path:'views/'
		extention:'php'

	loadPage: (page, $container, options)->
		_callback = ()->
			$script = $('#'+page)
			_template =  Handlebars.compile $script.html();
			_html = _template(options);
			$($container).html _html

		if $('#'+page).length
			_callback.call(@);
		else
			$.get @config.path+page+'.'+@config.extention, (data)->
				$script = $('<script id="'+page+'" />').attr('type', 'text/x-handlebars-template').html(data);
				$script.appendTo($('body'));
				_callback.call();
window.Pages = Pages;