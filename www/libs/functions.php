<?php
$starttime = microtime();
$startarray = explode(" ", $starttime);
$starttime = $startarray[1] + $startarray[0];


global $menuarray,$autorized_pages;
$menuarray=array("","entreprise","contact","projet","commande","archive","facturation","devis","prospection");

$authorized_pages=array("ajouter.php","editer.php","aide.php","logout.php","preferences.php");



if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}

if(!function_exists('json_decode')){
	function json_decode($json, $assoc=false)
	{
	 $comment = false;
	 $out = '$x=';
	  
	 for ($i=0; $i<strlen($json); $i++)
	 {
	  if (!$comment)
	  {
	   if (($json[$i] == '{') || ($json[$i] == '['))       $out .= ' array(';
	   else if (($json[$i] == '}') || ($json[$i] == ']'))   $out .= ')';
	   else if ($json[$i] == ':')    $out .= '=>';
	   else                         $out .= $json[$i];         
	  }
	  else $out .= $json[$i];
	  if ($json[$i] == '"' && $json[($i-1)]!="\\")    $comment = !$comment;
	 }
	 
	 eval($out . ';');
	 return $x;
	}
}

if(!function_exists('stripslashes_deep') && !function_exists('bng_parse_str') && !function_exists('bng_parse_args')){
	function stripslashes_deep($value) {
		if ( is_array($value) ) {
			$value = array_map('stripslashes_deep', $value);
		} elseif ( is_object($value) ) {
			$vars = get_object_vars( $value );
			foreach ($vars as $key=>$data) {
				$value->{$key} = stripslashes_deep( $data );
			}
		} else {
			$value = stripslashes($value);
		}
	
		return $value;
	}
	function bng_parse_str( $string, &$array ) {
		parse_str( $string, $array );
		if ( get_magic_quotes_gpc() )
			$array = stripslashes_deep( $array );
		//$array = apply_filters( 'wp_parse_str', $array );
	}
	function bng_parse_args( $args, $defaults = '' ) {
		if ( is_object( $args ) )
			$r = get_object_vars( $args );
		elseif ( is_array( $args ) )
			$r =& $args;
		else
			bng_parse_str( $args, $r );
	
		if ( is_array( $defaults ) )
			return array_merge( $defaults, $r );
		return $r;
	}
}

/****************** NEW Fonction Prospection ***************************/


function validate($elem, $dontValidate=array()){
		foreach($elem as $l => $val){
			if(empty($val) || $val===''){
				
				if(!empty($dontValidate))	{
					if(in_array($l, $dontValidate)){
						continue;
					}
				}
				return 'empty_all';
			}

			if($l==="email" && isset($elem['email']) && !preg_match('/^[a-zA-Z][\w\.-]*[a-zA-Z0-9]@[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]$/', $elem['email'])){
				return 'invalid_email';
			}
			if($l==="password" && isset($elem['password']) && strlen($elem['password'])<4){
				return 'invalid_password';	
			}
			if($l==="passwordrepeat" && isset($elem['password']) && isset($elem['passwordrepeat']) && $elem['password']!==$elem['passwordrepeat']){
				return 'invalid_password_combination';	
			}
		}
		return '';
	}

if(!function_exists('pageTitle')){
	function pageTitle($titre=NULL){
		if(!empty($titre)){
			$newtitre=$titre." - ";
		}
		echo $newtitre.htmlentities(TITRE." - ".COMPAGNIE);
	}
}

function refresh_contact_logs($prospectid, $page=0, $limit=10){
	global $bd;
	global $perm;
	if($limit!=0){
		//echo "---".$_SESSION["page"];
		/*if(!isset($_SESSION["page"])){
			$_SESSION["page"]=0;
		}
		if($page==-1){
			$page=$_SESSION["page"];	
		}
		if($page>$_SESSION["page"]){
			$_SESSION["page"]=$page;
		}*/
		$page = $page*$limit;
		$limit= "LIMIT $page,$limit";
	}else{
		$limit ="";	
	}
	
	$r="SELECT client, responsable FROM prospects WHERE idprospect=".$prospectid;
	$bd->query($r);
	$bd->load();
	$prospect=$bd->result();
	$prospect=$prospect[0];
	
	if($prospect->client==="true" || (in_array("clientis",$perm) && $prospect->responsable==="Bang Marketing")){
		$disable_bool=true;
	}else{
		$disable_bool=false;
	}
	
	$r="SELECT * FROM prospects_contact_log WHERE prospectid=$prospectid ORDER BY date DESC, logid DESC $limit";
	$bd->query($r);
	$bd->load();
	
	$logs = $bd->result();
	
	/*print_r($logs);
	exit();*/
	
	$logs_list="";
	foreach($logs as $log => $log_value){
		$logs_list.='
		<div>
			<form action="/prospection/log/edit_log.php" method="post" class="ajaxForm">
				<table>
					<tr>
						<td class="date-log">
							<input type="text" name="date_contact" class="datepicker" value="'.$log_value->date.'" '.($disable_bool===true?"disabled=\"disabled\"":null).'/>
						</td>
						<td class="note-log">
							<textarea name="note_contact" class="expandingArea" '.($disable_bool===true?"disabled=\"disabled\"":null).'>'.$log_value->note.'</textarea>
						</td>
						';
						if($disable_bool!==true){
						$logs_list.='
						<td class="btns">
							<input type="hidden" name="prospectid" value="'.$prospectid.'" />
							<input type="hidden" name="logid" value="'.$log_value->logid.'" />
							<button type="submit" class="edit" name="edit"><span>Éditer</span></button>
							'.(in_array('effacer',$perm)?'<button type="submit" class="delete" name="delete"><span>Supprimer</span></button>':null).'
						</td>';
						}
				$logs_list.='
					</tr>
				</table>
			</form>
		</div>
		';
	}
	return $logs_list;
}

function zerofill ($num, $zerofill = 4)
{
	return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
}

function zerodelete($num)
{
	return intval($num);
}


function search($array, $key, $value){
	$results = array();
	
	if (is_array($array)){
		if ($array[$key] == $value)
			$results[] = $array;
	
		foreach ($array as $subarray)
			$results = array_merge($results, search($subarray, $key, $value));
	}
	return $results;
}

/*if(!function_exists('str_split')) {
  function str_split($string, $split_length = 1) {
    $array = explode("\r\n", chunk_split($string, $split_length));
    array_pop($array);
    return $array;
  }
}
function frdate($format='j F Y',$date=False){
    $date = (is_numeric($date)?$date:time());
 
    $m=array(1=>'janvier','février','mars','avril','mai','juin',
    'juillet','août','septembre','octobre','novembre','décembre');
    $ms=array(1=>'janv','fév','mars','avr','mai','juin',
    'juil','août','sept','oct','nov','déc');
    $md=array(1=>'Janvier','Février','Mars','Avril','Mai','Juin',
    'Juillet','Août','Septembre','Octobre','Novembre','Décembre');
    $d=array(0=>'Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi');
    $ds=array(0=>'Dim','Lun','Mar','Mer','Jeu','Ven','Sam');
 
    $f = str_split($format);
    for($i=0;$i<count($f);$i++){
        if($f[$i]!="\\"){
            switch($f[$i]){
                case 'F':
                    $f[$i] = $md[date('n',$date)];
                    break;
                case 'f':
                    $f[$i] = $m[date('n',$date)];
                    break;
                case 'M':
                    $f[$i] = $ms[date('n',$date)];
                    break;
                case 'l':
                    $f[$i] = $d[date('w',$date)];
                    break;
                case 'D':
                    $f[$i] = $ds[date('w',$date)];
                    break;
                default:
                    $f[$i] = date($f[$i],$date);
            }
 
        }else{
            $f[$i]='';
            $i++;
        }
    }
    return implode('',$f);
}*/


/*****TEST PAGINATION*****/
function wp_parse_args( $args, $defaults = '' ) {
	if ( is_object( $args ) )
		$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
		$r =& $args;
	else
		wp_parse_str( $args, $r );

	if ( is_array( $defaults ) )
		return array_merge( $defaults, $r );
	return $r;
}
function wp_parse_str( $string, &$array ) {
	parse_str( $string, $array );
	if ( get_magic_quotes_gpc() )
		$array = stripslashes_deep( $array );
	//$array = apply_filters( 'wp_parse_str', $array );
}

function paginate_links( $args = '' ) {
$defaults = array(
	'base' => '%_%', // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
	'format' => '?page=%#%', // ?page=%#% : %#% is replaced by the page number
	'total' => 1,
	'current' => 0,
	'show_all' => false,
	'prev_next' => true,
	'prev_text' => '&laquo; Previous',
	'next_text' => 'Next &raquo;',
	'end_size' => 1,
	'mid_size' => 2,
	'type' => 'plain',
	'add_args' => false, // array of query args to add
	'add_fragment' => ''
);

$args = wp_parse_args( $args, $defaults );
extract($args, EXTR_SKIP);

// Who knows what else people pass in $args
$total = (int) $total;
if ( $total < 2 )
	return;
$current  = (int) $current;
$end_size = 0  < (int) $end_size ? (int) $end_size : 1; // Out of bounds?  Make it the default.
$mid_size = 0 <= (int) $mid_size ? (int) $mid_size : 2;
$add_args = is_array($add_args) ? $add_args : false;
$r = '';
$page_links = array();
$n = 0;
$dots = false;

if ( $prev_next && $current && 1 < $current ) :
	$link = str_replace('%_%', /*2 == $current ? '' : */$format, $base);

	$link = str_replace('%#%', $current - 1, $link);
	
	if ( $add_args )
		$link = add_query_arg( $add_args, $link );
	$link .= $add_fragment;
	$page_links[] = "<a class='prev page-numbers' href='".$link."'>$prev_text</a>";
endif;
for ( $n = 1; $n <= $total; $n++ ) :
	//$n_display = number_format_i18n($n);
	$n_display = (int)$n;
	if ( $n == $current ) :
		$page_links[] = "<span class='page-numbers current'>$n_display</span>";
		$dots = true;
	else :
		if ( $show_all || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
			$link = str_replace('%_%', /*1 == $n ? '' :*/ $format, $base);
			$link = str_replace('%#%', $n, $link);
			if ( $add_args )
				$link = add_query_arg( $add_args, $link );
			$link .= $add_fragment;
			$page_links[] = "<a class='page-numbers' href='".$link."'>$n_display</a>";
			$dots = true;
		elseif ( $dots && !$show_all ) :
			$page_links[] = "<span class='page-numbers dots'>...</span>";
			$dots = false;
		endif;
	endif;
endfor;
if ( $prev_next && $current && ( $current < $total || -1 == $total ) ) :
	$link = str_replace('%_%', $format, $base);
	$link = str_replace('%#%', $current + 1, $link);
	if ( $add_args )
		$link = add_query_arg( $add_args, $link );
	$link .= $add_fragment;
	$page_links[] = "<a class='next page-numbers' href='".$link."'>$next_text</a>";
endif;
switch ( $type ) :
	case 'array' :
		return $page_links;
		break;
	case 'list' :
		$r .= "<ul class='page-numbers'>\n\t<li>";
		$r .= join("</li>\n\t<li>", $page_links);
		$r .= "</li>\n</ul>\n";
		break;
	default :
		$r = join("\n", $page_links);
		break;
endswitch;
return $r;
}

function add_query_arg() {
	$ret = '';
	if ( is_array( func_get_arg(0) ) ) {
		if ( @func_num_args() < 2 || false === @func_get_arg( 1 ) )
			$uri = $_SERVER['REQUEST_URI'];
		else
			$uri = @func_get_arg( 1 );
	} else {
		if ( @func_num_args() < 3 || false === @func_get_arg( 2 ) )
			$uri = $_SERVER['REQUEST_URI'];
		else
			$uri = @func_get_arg( 2 );
	}

	if ( $frag = strstr( $uri, '#' ) )
		$uri = substr( $uri, 0, -strlen( $frag ) );
	else
		$frag = '';

	if ( preg_match( '|^https?://|i', $uri, $matches ) ) {
		$protocol = $matches[0];
		$uri = substr( $uri, strlen( $protocol ) );
	} else {
		$protocol = '';
	}

	if ( strpos( $uri, '?' ) !== false ) {
		$parts = explode( '?', $uri, 2 );
		if ( 1 == count( $parts ) ) {
			$base = '?';
			$query = $parts[0];
		} else {
			$base = $parts[0] . '?';
			$query = $parts[1];
		}
	} elseif ( !empty( $protocol ) || strpos( $uri, '=' ) === false ) {
		$base = $uri . '?';
		$query = '';
	} else {
		$base = '';
		$query = $uri;
	}

	wp_parse_str( $query, $qs );
	$qs = urlencode_deep( $qs ); // this re-URL-encodes things that were already in the query string
	if ( is_array( func_get_arg( 0 ) ) ) {
		$kayvees = func_get_arg( 0 );
		$qs = array_merge( $qs, $kayvees );
	} else {
		$qs[func_get_arg( 0 )] = func_get_arg( 1 );
	}

	foreach ( (array) $qs as $k => $v ) {
		if ( $v === false )
			unset( $qs[$k] );
	}

	$ret = build_query( $qs );
	$ret = trim( $ret, '?' );
	$ret = preg_replace( '#=(&|$)#', '$1', $ret );
	$ret = $protocol . $base . $ret . $frag;
	$ret = rtrim( $ret, '?' );

	return $ret;
}
function urlencode_deep($value) {
	$value = is_array($value) ? array_map('urlencode_deep', $value) : urlencode($value);
	return $value;
}
function build_query( $data ) {
	return _http_build_query( $data, null, '&', '', false );
}
function _http_build_query($data, $prefix=null, $sep=null, $key='', $urlencode=true) {
	$ret = array();

	foreach ( (array) $data as $k => $v ) {
		if ( $urlencode)
			$k = urlencode($k);
		if ( is_int($k) && $prefix != null )
			$k = $prefix.$k;
		if ( !empty($key) )
			$k = $key . '%5B' . $k . '%5D';
		if ( $v === NULL )
			continue;
		elseif ( $v === FALSE )
			$v = '0';

		if ( is_array($v) || is_object($v) )
			array_push($ret,_http_build_query($v, '', $sep, $k, $urlencode));
		elseif ( $urlencode )
			array_push($ret, $k.'='.urlencode($v));
		else
			array_push($ret, $k.'='.$v);
	}

	if ( NULL === $sep )
		$sep = ini_get('arg_separator.output');

	return implode($sep, $ret);
}


/**
Cette function retourne soit l'accronyme ou le nom de l'entreprise
@param nom : Le nom de l'entreprise
@param accro: L'accronyme de l'entreprise
@return L'accronyme si non vide, sinon le nom
*/
function get_entreprise_accronyme($nom, $accro){
	$ret = "";
	if(!empty($accro)){
		$ret = $accro;
	}else{
		$ret=$nom;
	}
	return $ret;

}

/**
Cette function retourne soit l'accronyme ou le nom de l'entreprise en lien avec un hint
@param nom : Le nom de l'entreprise
@param accro: L'accronyme de l'entreprise
@return L'accronyme si non vide, sinon le nom
*/
function get_entreprise_accronyme_hint($nom, $accro){
	$args=array(
			"text"=>get_entreprise_accronyme($nom, $accro),
			"link"=>"",
			"target"=>"",
			"length"=>25,
			"ellipsis"=>'<span class="ellipsis hint--top" data-hint="'.$nom.'">(&hellip;)</span>',
			"bycaracters"=>true
		);
		$entre_name = custom_generate_excerpt($args);

		if($entre_name==$accro){
			$entre_name='<span class="ellipsis hint--top" data-hint="'.$nom.'">'.$entre_name.'</span>';
		}

		return $entre_name;
}

/**
Cette function retourner la clé d'encryption
**/
function cryptionKey(){
	return "bang-marketing";
}


function custom_generate_excerpt($args) {
	//$text, $link="", $length = 55, $ellipsis = '&nbsp;[&hellip;]'
		$args_default=array(
			"text"=>"",
			"link"=>"",
			"target"=>"",
			"length"=>55,
			"ellipsis"=>"&nbsp;[&hellip;]",
			"bycaracters"=>false
		);
  		$args = bng_parse_args($args, $args_default);
		extract($args);
        $content = $text;//strip_tags($text);
		//$content = preg_replace( '/s+/', ' ', $content );
		if(!$bycaracters){
	        $words = explode( ' ', $content );
	        if ( count( $words ) > $length ){
	            $excerpt = implode( ' ', array_slice( $words, 0, $length ) );
	            $exc = true;	
			}else{
	            $excerpt = $content;
			}
		}else{
			if(strlen($content)>$length){
				$excerpt = mb_substr($content, 0, $length, "UTF-8");
				//print_r($excerpt);
				$exc = true;
			}else{
				 $excerpt = $content;
			}
		}

		if($exc){
			if(!empty($link)){
				$ellipsis = '&nbsp;<a href="'.$link.'"'.(!empty($target)?' target="'.$target.'"':null).'>'.$ellipsis.'</a>';
			}
			$excerpt.= $ellipsis;
		}
    return $excerpt;
}


/**
Function get_field()
*/
function get_field($field_name, $id, $type, $create=true, $inc_attr=null){
	global $bd;
	//print_r($type."<br>");
	$r="SELECT * FROM metas WHERE meta_key='field_".$field_name."' AND elem_id='0.1' LIMIT 1";

	$bd->loadr($r);
	$field=$bd->result();
	// print_r($field[0]);
	$field = $field_obj = $field[0];
//
//exit();
	if(!$create){
		$r="SELECT * FROM metas WHERE meta_key='".$field_name."' AND elem_id='".$id."' AND elem_type='".$type."'";
		$bd->loadr($r);
		$field_infos=$bd->result();
	}

    
	$field= json_decode($field->meta_value, true);
	
	$field = $field[$field_name];
	// print_r($field);

	$options=array();
	if(!empty($field["options"])){
		if($field["empty_option"]==true){
			$options[""]=" ";
		}
		
		if($field["options"]["type"]=="relationship"){
			$args = $field["options"]["relation"];

			//print_r($args);
			$relations=$bd->selectr($args);
			//$bd->show_query();
			
			foreach ($relations as $rel) {
				$value="";
				$val_elem = $field["options"]["text_col"];
				if(is_array($val_elem)){
					foreach ($val_elem as $va => $v) {
						$value.=$rel->{$v}." ";
					}
				}else{
					$value = $rel->{$val_elem};
				}
				$options[$rel->{$field["options"]["value_col"]}]=$value;
			}
			
		}else{

			foreach ($field["options"] as $key => $value) {
				$options[$key]=$value;
			}
		}
		if(!$create){
			$temp = $field_infos[0]->meta_value;
			$temp_array = @json_decode($temp,true);
			if(is_array($temp_array)){
				$temp = $temp_array;
				$field_infos[0]->meta_value = $temp;
			}else{
				$field_infos[0]->meta_value = array("value"=>$temp, "text"=>$options[$temp]);
			}
		}
	}
	if(empty($field["attributes"])){
		$field["attributes"]=array();
	}
	if(!empty($inc_attr) && is_array($inc_attr)){
		$field["attributes"] = array_merge($field["attributes"], $inc_attr);
	}
	if($create){
		if(!empty($options)){
			$obj = new $field["type"]($field["label"], $field["name"], $options, $field["attributes"]);
		}else{
			//print_r($field);
			$obj = new $field["type"]($field["label"], $field["name"], $field["attributes"]);
		}
		return $obj;
	}else{
		// print_r($field["type"]);
		if(is_array($field_infos[0]->meta_value) && $field["type"]=="Element_Radio"){
			$field_infos[0]->meta_value = $field_infos[0]->meta_value["value"];
		}
		// print_r($field_infos);
		return array($field_obj->meta_key=>$field_infos[0]->meta_value);
	}
}

/**
Function get_field_value()
*/
function get_field_value($field_name, $id, $type){

	return get_field($field_name, $id, $type, false);
}

function save_field($args){
	global $bd;

	$args_default=array(
		"val"=>"",
		"field_name"=>"",
		"id"=>"",
		"type"=>""
	);
	$args = bng_parse_args($args, $args_default);
	extract($args);

	$r="SELECT * FROM metas WHERE meta_key='".str_replace("field_", "", $field_name)."' AND elem_id='".$id."' AND elem_type='".$type."'";
	$bd->loadr($r);
	$field=$bd->result();

	//print_r($val);
	if(is_array($val)){
		$valeur = json_encode($val);
	}else{
		$valeur = $val;
	}
	//print_r("- //// - ".$valeur);

	if(empty($field[0])){
		$args = array(
			"table"=>"metas",
			"db_column"=>array(
				"elem_id"=>$id,
				"elem_type"=>$type,
				"meta_key"=>str_replace("field", "", $field_name),
				"meta_value"=>$field_name

			)
		);
		$bd->insert($args);
		$args = array(
			"table"=>"metas",
			"db_column"=>array(
				"elem_id"=>$id,
				"elem_type"=>$type,
				"meta_key"=>str_replace("field_", "", $field_name),
				"meta_value"=>$valeur
			)
		);
		$bd->insert($args);

	}else{
		$args = array(
			"table"=>"metas",
			"db_column"=>array(
				"meta_value"=>$valeur
			),
			"where"=>array(
				"elem_id"=>array(
					"value"=>$id,
					"condition"=>"="
				),
				"elem_type"=>array(
					"value"=>$type,
					"condition"=>"="
				),
				"meta_key"=>array(
					"value"=>str_replace("field_", "", $field_name),
					"condition"=>"="
				)
			)
		);
		$bd->update($args);
	}
	

	
	//$bd->show_query();
}


function delete_fields($id, $type){
	global $bd;

	$args = array(
		"table"=>"metas",
		"where"=>array(
			"elem_id"=>array(
				"value"=>$id,
				"condition"=>"="
			),
			"elem_type"=>array(
				"value"=>$type,
				"condition"=>"="
			)
			
		)
	);

	$bd->delete($args);
}

function copy_fields($id, $type, $new_id){
	global $bd;

	$args=array(
		"table"=>"metas",
		"db_column"=>array(
			"elem_type",
			"meta_key",
			"meta_value"
		),
		'copyFrom'=>"elem_id=".$id." AND meta_key!='is_template' AND meta_key!='_is_template'",
		"copy_all"=>true
	);
	$args["new_vals"]=array(
		"elem_id"=>$new_id
	);
	$copy_element = $bd->bd_copy($args);

	$args=array(
		"table"=>"metas",
		"db_column"=>array(
			"elem_id"=>$new_id
		),
		"where"=>array(
			"elem_id"=>array(
				"value"=>0,
				"condition"=>"="
			)
		)
	);
	$bd->update($args);
}

function curPageURL() {
	 $pageURL = 'http';
	 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	 $pageURL .= "://";
	 if ($_SERVER["SERVER_PORT"] != "80") {
	  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]."/";
	 } else {
	  $pageURL .= $_SERVER["SERVER_NAME"]."/";
	 }
	 return $pageURL;
	}


function num_to_letter($num, $uppercase = FALSE){

	if(is_numeric($num)){
		$num -= 1;
		$letter = chr(($num % 26) + 97);
		$letter .= (floor($num/26) > 0) ? str_repeat($letter, floor($num/26)) : '';
		return ($uppercase ? strtoupper($letter) : $letter);
	}else{
		return ord(strtolower($num)) - 96;
	}
}

function get_format(){
	$format = "%e %B %Y %H:%m";
	if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
	    $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
	}

	return $format;
}


/**
Cette function retourne la valeur de l'option de la base de donné
@args : Array(
			"name",
			"date"
		)
@return L'accronyme si non vide, sinon le nom
*/
function get_option($args){
	global $bd;
	$args_default = array(
		"date"=>date("Y-m-d")
	);
	extract(bng_parse_args($args, $args_default));

	if(!isset($name)){
		return "Aucune option choisie";
	}

	$r = "SELECT value FROM options WHERE name='".$name."'";
	$option = $bd->resultr($r);

	$array_option = @json_decode($option[0]->value);
	//If it is a array then there's condition to make else it's a regular value
	if(is_array($array_option)){
		//If value is array and contain a from date
		if(isset($array_option[0]->from)){
			foreach ($array_option as $elem) {
				// We return the good value by checking (from & to) date
				if(($date>=$elem->from && empty($elem->to)) || ($date>=$elem->from && $date<$elem->to) || ($date<=$elem->to && empty($elem->from))){
					return $elem->value;
				}
				//print_r($elem);
			}
		}
	}

	return $option[0]->value;
}

?>