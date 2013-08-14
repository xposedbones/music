$(function($) {
	$("#album-listing ul").isotope({
		   masonry: {
			    columnWidth: 128
			  }
	});
	
	$("#upload").click(function(){
		$('.form_holder').slideToggle();
	});
	$('#fileupload').fileupload({
		url: '/uploads/',
        done: function (e, data) {
            var uploadFile = data.files[0];
	        if ((/\.(zip)$/i).test(uploadFile.name)) {
	            $.ajax({
					url: "/includes/extract-songs.php",
					dataType: "json",
					data:{ajax:true},
					success: function(response){
						
					}
				});
	        }
	        $("header").removeClass("uploading");
	        $(".fileupload-progress").slideUp();

        },
	    progressall: function (e, data) {
	        var progress = parseInt(data.loaded / data.total * 100, 10);
	        $('.progress .bar').css(
	            'height',
	            progress + '%'
	        );
	        $("#upload .upload-progress").text('Uploading... ' + progress + ' %');
	    },
	    add: function (e, data) {
	        var goUpload = true;
	        var uploadFile = data.files[0];
	        console.log(uploadFile.name)
	        if (!(/\.(zip|mp3)$/i).test(uploadFile.name)) {
	            //common.notifyError('You must select a zip or a mp3 file');
	            goUpload = false;
	        }

	        if (goUpload == true) {
	            //data.submit();
	            $("#start").click(function(e){

	            	e.preventDefault();
	            	data.submit();
	            	
	            	//$("header").addClass("uploading");
	            	//$(".fileupload-progress").slideDown();
	            	$('.form_holder').slideToggle();
	            });
	        }
	    },
	    
	});
	/*$("#menu a").click(function(e){
		e.preventDefault();
		var href=$(this).attr('href');
		window.location.href = "#!/"+href;
		$.ajax({
			url: "/views/"+href,
			dataType: 'html',
			data:{65412 : true},
			success: function(response){
				$("#content").html(response);
			}
		});
	});*/
	
	$('body').on('click','[data-action]', function(e){
		var event_param = $(this).attr('data-action').split('|')
		var event_type = event_param[0]

		switch(event_type){
			case 'albums-listing':
				var albumsTest = []
				for(var i=0; i<30; ++i){
					var types = ['', 'huge', 'med'];
					var _type = Math.floor(Math.random()*types.length);
					var album = {
						type:types[_type],
						image_url:'http://0.static.wix.com/media/cec8b8_bcbae9b705b89190a82a0dd200a03348.jpg_512',
						artist:'Macklemore & Ryan Lewis',
						name:'The Heist'
					}
					albumsTest.push(album)
				}
				Pages.loadPage('albums-listing', '#content', {
					albums:albumsTest
				})
				break;
		}
	})
	
	$('a[data-action="albums-listing"]').click();
	

});