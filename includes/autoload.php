<?php 
if ($handle = opendir(LIBS.'class/')) {
    while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != ".." && $file != ".svn" && $file != "class.devis.php") {
			$params = get_file_data_bang(LIBS.'class/'.$file, array('autoload' => "autoload", 'autocreate'=>"autocreate"));
			if($params["autoload"]==="true"){
			   include_once(LIBS.'class/'.$file);
			}
			if($params["autocreate"]==="true"){
				$classToCreate = str_replace(".php","", str_replace("class.","", $file));
				$classToCreate2 = ucfirst($classToCreate);
				${$classToCreate}=new $classToCreate2();	
			}
        }
    }
    closedir($handle);
}
function _cleanup_header_comment_bang($str) {
	return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
}
function get_file_data_bang( $file, $default_headers, $context = '' ) {
	// We don't need to write to the file, so just open for reading.
	$fp = fopen( $file, 'r' );
	// Pull only the first 8kiB of the file in.
	$file_data = fread( $fp, 8192 );
	// PHP will close file handle, but we are good citizens.
	fclose( $fp );
	$all_headers = array_flip($default_headers);
	foreach ( $all_headers as $field => $regex ) {
		preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, ${$field});
		if ( !empty( ${$field} ) )
			${$field} = _cleanup_header_comment_bang( ${$field}[1] );
		else
			${$field} = '';
	}

	$file_data = compact( array_keys( $all_headers ) );
	return $file_data;
}
 ?>