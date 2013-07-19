<?php
/*
autoload: true
autocreate: true
*/
if (function_exists('mysql_set_charset') === false) { 
     /** 
      * Sets the client character set. 
      * 
      * Note: This function requires MySQL 5.0.7 or later. 
      * 
      * @see http://www.php.net/mysql-set-charset 
      * @param string $charset A valid character set name 
      * @param resource $link_identifier The MySQL connection 
      * @return TRUE on success or FALSE on failure 
      */ 
    function mysql_set_charset($charset, $link_identifier = null) { 
        if ($link_identifier == null) { 
            return mysql_query('SET NAMES "'.$charset.'"'); 
        } else { 
            return mysql_query('SET NAMES "'.$charset.'"', $link_identifier); 
        } 
    } 
}
class Bd{
	var $r;
	var $qr;
	
	/*Function connect (Connection to the database)*/
	/*
	$db=sBDD,$host=sHOST,$user=sUSER,$pass=sPWD
	*/
	function connect($args=array()){
		$args_default=array(
			"db" => sBDD,
			"host" => sHOST,
			"user" => sUSER,
			"pass" => sPWD,
			"charset" => "utf8",
		);
		$new_args=bng_parse_args($args,$args_default);
		extract($new_args);
		
		$link = mysql_connect($host, $user, $pass);
		mysql_select_db($db, $link);
		mysql_set_charset($charset,$link); 
		$this->link = $link;
	}
	
	/*Function r (return the query)*/
	function r(){
		echo $this->r;	
	}
	function show_query(){
		echo $this->request;	
	}
	
	/*Function query (Handed query)*/
	function query($query){
		$this->r=$query;
	}
	
	/*Function select (Create a Query - SELECT)*/
	function select($table,$cols=array()){
		if(!empty($cols[0])){
			$s='';
			foreach($cols as $l => $val){
				$s .= $val.', ';	
			}
			$s = substr($s, 0, -2);
		}else{
			$s = '*';	
		}
		$this->r = 'SELECT '.$s.' FROM '.$table;	
	}
	
	
	function selectr($args){
		// Example
		// array(
		// 	"table"=>"devis_types_achats",
		// 	"db_column"=>array(
		// 		"id",
		// 		"title"
		// 	),
		// 	"order"=>array(
		// 		array(
		// 			"value"=>"title",
		// 			"type"=>"ASC"
		// 		)
		// 	)
		// );

		global $bd;
		extract($args, EXTR_SKIP);
		
		$cols="";
		$vals="";

		if($db_column=="*"){
			$cols="*";
		}else{
			foreach($db_column as $entry => $val){
				$cols.="".$val.", ";	
			}
			$cols = substr($cols, 0, -2);
		}
		
		if(!empty($order)){
			foreach($order as $k => $v){
				$o .= $v["value"]." ".$v["type"].", ";
			}
			$o = substr($o, 0, -2);
			if(!empty($o)){
				$norder = "ORDER BY ".$o;
			}
		}
		
		if(!empty($limit)){
			$limit = "LIMIT ".$limit;
		}
		if(!empty($where)){
			foreach($where as $wh => $where_elem){
				if($where_elem["condition"]=="REGEXP"){
					$where_elem["value"] = stripslashes($where_elem["value"]);
				}
				$where_r.="".$wh." ".$where_elem["condition"]." "."'".mysql_real_escape_string($where_elem["value"])."' AND ";	
			}
			$where_r = substr($where_r, 0, -5);
		}

		if(!empty($where_r)){
			$nwhere = "WHERE ".$where_r;
		}
		
		
		$this->request = $r = 'SELECT '.$cols.' FROM '.$table.' '.$nwhere.' '.$norder.' '.$limit;

		$bd->loadr($r);
		
		//$error = mysql_errno();
		$result = $bd->result();
		
		
		//return array("result"=>$result, "error"=>$error);
		return $result;
		
	}
	
	/*Function addCondition (Add a condition to the Query)*/
	function addCondition($cond){
		$this->r = $this->r.' WHERE '.$cond; 	
	}
	
	/*Function limit (Add a limit to the Query)*/
	function limit($limit){
		$this->r = $this->r.' LIMIT '.$limit;	
	}
	
	/*Function order (Add a order to the Query)*/
	function order($order, $sort='ASC'){
		$this->r = $this->r.' ORDER BY '.$order.' '.$sort;	
	}
	
	/*Function load (Execute the query)*/
	function load(){
		$this->r = $this->result = mysql_query($this->r, $this->link);
	}
	
	function loadr($query){
		$this->r = $this->result = mysql_query($query, $this->link);
	}
	
	function error(){
		return mysql_errno($this->link);
	}

	function numrows(){
		return mysql_num_rows($this->result);
	}
	
	/*Function Result (Return the result of the query)*/
	function result($type="O"){
		switch($type){
			case"a":
				while($l[]=mysql_fetch_assoc($this->r)){
				}
				array_pop($l);
			break;
			default:
				while($l[]=mysql_fetch_object($this->r)){
				}
				array_pop($l);
				foreach($l as $ligne => $valeur){
					foreach($valeur as $ligne2 => $val){
						$valeur->$ligne2=$val;
					}
				}
			
		}
		

		return $l;
	}
	
	function insert($args){
		global $bd;
		extract($args, EXTR_SKIP);
		
		$cols="";
		$vals="";
		foreach($db_column as $entry => $val){
			$cols.="`".$entry."`, ";	
			$vals.="'".mysql_real_escape_string($val)."', ";
		}
		$cols = substr($cols, 0, -2);
		$vals = substr($vals, 0, -2);
		
		$vals = str_replace('\"NOW()\"', 'NOW()', $vals);
		
		$this->request = $r = 'INSERT INTO `'.$table.'` ('.$cols.') VALUES ('.$vals.')';
		//exit();
		$bd->query($r);
		$bd->load();
		$id = mysql_insert_id();
		$error = mysql_errno();
		
		return array("id"=>$id, "error"=>$error);
		
	}
	
	function bd_copy($args){
		global $bd;
		extract($args, EXTR_SKIP);
		
		$cols="";
		$vals="";
		$array_cols=array();
		foreach($db_column as $entry => $val){
			$array_cols[]=$val;
			$cols.="`".$val.'`, ';	
		}
		$col_temp = $cols = substr($cols, 0, -2);
		
		//$vals = str_replace('\"NOW()\"', 'NOW()', $vals);
		$q='SELECT '.$cols.' from `'.$table.'` where '.$copyFrom;
		$bd->query($q);
		$bd->load();
		$copied=$bd->result();

		foreach ($copied as $k => $v) {

			$array_vals=array();
			$vals="";
			$cols = $col_temp;
			foreach($copied[$k] as $entry => $val){
				$array_vals[]=mysql_real_escape_string($val);
			}

			if(is_array($new_vals) && !empty($new_vals)){
				foreach ($new_vals as $nvals => $nvalue) {
					if(!strstr($cols, $nvals)){
						$cols.=",`".$nvals."`";
						$array_vals[]=$nvalue;
					}else{
						$key = array_search($nvals,$array_cols);
						$array_vals[$key]=$nvalue;
					}
				}
			}

			foreach ($array_vals as $array_val => $array_v) {
				$vals.="'".$array_v."', ";
			}

			$vals = substr($vals, 0, -2);
			$r = 'INSERT INTO `'.$table.'` ('.$cols.') VALUES ('.$vals.')';
			//exit();
			$bd->query($r);
			$bd->load();
			$id[] = mysql_insert_id();
			$error[] = mysql_errno();

		}

		return array("id"=>$id, "error"=>$error);
		
	}
	
	function update($args){
		global $bd;
		
		extract($args, EXTR_SKIP);
		
		$cols="";
		foreach($db_column as $entry => $val){
			if(is_array($val)){
				$val = implode(",",$val);
			}
			$cols.="`".$entry."`='".mysql_real_escape_string($val)."', ";	
		}
		foreach($where as $wh => $where_elem){
			$where_r.="`".$wh."`".$where_elem["condition"]."'".mysql_real_escape_string($where_elem["value"])."' AND ";	
		}
		
		$cols = substr($cols, 0, -2);
		$where_r = substr($where_r, 0, -5);
		
		
		$this->request = $r = 'UPDATE `'.$table.'` SET '.$cols.' WHERE '.$where_r;
		//exit();
		
		$bd->query($r);
		$bd->load();
		$id = mysql_insert_id();
		$error = mysql_errno();
		
		return array("id"=>$id, "error"=>$error);
		
	}
	
	function delete($args){
		global $bd;
		
		extract($args, EXTR_SKIP);
		
		$cols="";
		
		foreach($where as $wh => $where_elem){
			$where_r.="".$wh.$where_elem["condition"]."'".$where_elem["value"]."' AND ";	
		}
		
		$cols = substr($cols, 0, -2);
		$where_r = substr($where_r, 0, -5);
		
		$r = 'DELETE FROM `'.$table.'` WHERE '.$where_r;

		$bd->query($r);
		$bd->load();
		$error = mysql_errno();
		
		$id = mysql_insert_id();
		$error = mysql_errno();
		
		return array("id"=>$id, "error"=>$error);
		
	}
	
	
	/*Function insert_id (return the insert id)*/
	function insert_id(){
		return mysql_insert_id();
	}

}
?>