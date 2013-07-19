$(function($) {
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
        },
	    progressall: function (e, data) {
	        var progress = parseInt(data.loaded / data.total * 100, 10);
	        $('.progress .bar').css(
	            'width',
	            progress + '%'
	        );
	    },
	    add: function (e, data) {
	        var goUpload = true;
	        var uploadFile = data.files[0];
	        console.log(uploadFile.name)
	        if (!(/\.(zip|mp3)$/i).test(uploadFile.name)) {
	            common.notifyError('You must select a zip or a mp3 file');
	            goUpload = false;
	        }

	        if (goUpload == true) {
	            //data.submit();
	        }
	    }
	});
});