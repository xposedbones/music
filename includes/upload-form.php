<form id="fileupload" action="#" method="POST" enctype="multipart/form-data">
    <div class="fileupload-loading"></div>
    <div id="dropzone" class="well fileinput-button"><span class="dropzone dropzone-text"><?php echo T_("(Glisser votre fichier ici)"); ?></span>  <input type="file" name="files[]" multiple></div>
    <div class="span5 fileupload-progress fade">
        <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
            <div class="bar" style="width:0%;"></div>
        </div>
    </div>
   

</form>