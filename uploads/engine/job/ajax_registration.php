<?php

require_once ENGINE_DIR.'/modules/functions.php';
require_once ENGINE_DIR.'/classes/parse.class.php';

$parse = new ParseFilter();

if (!function_exists('convert_unicode'))
{
	function convert_unicode($t, $to = 'windows-1251')
	{
		$to = strtolower( $to );
	
		if( $to == 'utf-8' ) {
			
			return urldecode( $t );
		
		} else {
			
			if( function_exists( 'iconv' ) ) $t = iconv( "UTF-8", $to . "//IGNORE", $t );
			else $t = "The library iconv is not supported by your server";
		
		}
	
		return urldecode( $t );
	}

}

function check_name($name)
{
	global $lang, $db;

	$stop = '';

	if (strlen($name) > 20)
	{
		 
            $stop .= $lang['reg_err_3'];
	}
	if (preg_match("/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/",$name))
	{
		 
            $stop .= $lang['reg_err_4'];
	}
	if (empty($name))
	{
		 
            $stop .= $lang['reg_err_7'];
	}
	if (!$stop)
	{

		$replace_word = array ('e' => '[eå¸]', 'r' => '[rã]', 't' => '[tò]', 'y' => '[yó]','u' => '[uè]','i' => '[i1l!]','o' => '[oî0]','p' => '[pð]','a' => '[aà]','s' => '[s5]','w' => 'w','q' => 'q','d' => 'd','f' => 'f','g' => '[gä]','h' => '[hí]','j' => 'j','k' => '[kê]','l' => '[l1i!]','z' => 'z','x' => '[xõ%]','c' => '[cñ]','v' => '[vuè]','b' => '[bâü]','n' => '[nïë]','m' => '[mì]','é' => '[éèu]','ö' => 'ö','ó' => '[óy]','å' => '[åe¸]','í' => '[íh]','ã' => '[ãr]','ø' => '[øwù]','ù' => '[ùwø]','ç' => '[ç3ý]','õ' => '[õx%]','ú' => '[úü]','ô' => 'ô','û' => '(û|ü[i1l!]?)','â' => '[âb]','à' => '[àa]','ï' => '[ïn]','ð' => '[ðp]','î' => '[îo0]','ë' => '[ën]','ä' => 'ä','æ' => 'æ','ý' => '[ý3ç]','ÿ' => '[ÿ]','÷' => '[÷4]','ñ' => '[ñc]','ì' => '[ìm]','è' => '[èué]','ò' => '[òt]','ü' => '[üb]','á' => '[á6]','þ' => '(þ|[!1il][oî0])','¸' => '[¸åe]','1' => '[1il!]','2' => '2','3' => '[3çý]','4' => '[4÷]','5' => '[5s]','6' => '[6á]','7' => '7','8' => '8','9' => '9','0' => '[0îo]','_' => '_','#' => '#','%' => '[%x]','^' => '[^~]','(' => '[(]',')' => '[)]','=' => '=','.' => '[.]','-' => '-','[' => '[\[]');
		$name=strtolower($name);
		$search_name=strtr($name, $replace_word);

		$db->query ("SELECT name FROM " . USERPREFIX . "_users WHERE LOWER(name) REGEXP '[[:<:]]{$search_name}[[:>:]]' OR name = '$name'");

        if ($db->num_rows() > 0)
        {
			$stop .= $lang['reg_err_20'];
		}
	}

	if (!$stop) return false; else return $stop;
}



$name  = $db->safesql(trim(htmlspecialchars($parse->process(convert_unicode($_GET['name'], $config['charset'])))));
$allow = check_name($name);

if (!$allow)
	echo 'true';
else
	echo 'false';

exit;
?>