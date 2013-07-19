<?php
$url=$_SERVER["REQUEST_URI"];
$site_url=explode("/",$url);
array_shift($site_url);

if($site_url[0]!=="fr" && $site_url[0]!=="en"){
	DEFINE("SITELANG", "en");
}else{
	DEFINE("SITELANG", $site_url[0]);
}

if(isset($_GET["lang"]) && ($_GET["lang"]==="en_US" || $_GET["lang"]==="fr_FR")){
	$locale=$_GET["lang"];	
}
putenv("LC_ALL=$locale"); // 'true'

if(!empty($locale)){
	$switch = substr($locale, 0,2);
}else{
	$switch = $site_url[0];
}
switch($switch){	
	
	case "fr":
		$locale="fr_FR";
		$local_country="fr-ca";
		$lang_name 	="French (Canada)";
		$localewin = "French_Canada";
	default:
		$locale="en_US";
		$local_country="en-us";	
		$lang_name 	="en_utf8";
		$localewin = "en_utf8";
}

if(isset($_GET["lang"]) && ($_GET["lang"]==="en_US" || $_GET["lang"]==="fr_FR")){
	$locale=$_GET["lang"];	
}

DEFINE("LOCALE_LANG", $locale);
DEFINE("LOCALE_COUNTRY", $local_country);

define('PROJECT_DIR', $_SERVER[DOCUMENT_ROOT]);
define('LOCALE_DIR', PROJECT_DIR .'/local');
define('DEFAULT_LOCALE', 'en_US');

require_once(LIBS.'gettext/gettext.inc');

$supported_locales = array('en_US', 'fr_FR');
$encoding = 'UTF-8';

// gettext setup

T_setlocale(LC_ALL, $locale);
if($locale!="fr_FR")
	setlocale(LC_TIME, $locale, $localewin, $lang_name);
// Set the text domain as 'messages'
$domain = 'messages';

T_bindtextdomain($domain, LOCALE_DIR);
T_bind_textdomain_codeset($domain, $encoding);
T_textdomain($domain);

/*//$locale = $locale.".UTF-8";
DEFINE("LOCALE_LANG", $locale);
DEFINE("LOCALE_COUNTRY", $local_country);

//print_r(LOCALE_LANG);
putenv("LC_ALL=".LOCALE_LANG); //This works 
setlocale(LC_ALL, LOCALE_LANG);
//Sous Windows 
setlocale(LC_ALL, 'english');
$domain = "messages";
$directory = $_SERVER[DOCUMENT_ROOT].'locale';
//echo $directory;
bindtextdomain($domain, './locale/nocache');
bindtextdomain($domain,$directory);
bind_textdomain_codeset($domain, 'UTF-8'); 
textdomain($domain); 

print gettext("In the dashboard"); 
print __("In the dashboard"); 
exit;*/


if(SITELANG!==$_COOKIE["lang"]){
	setcookie ('lang', SITELANG, time()+60*60*24*30,'/', $_SERVER["HTTP_HOST"], '0');
}

?>