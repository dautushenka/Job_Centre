<?php

@session_start();

@error_reporting(E_ALL ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('ROOT_DIR', '../..');
define('ENGINE_DIR', '..');

$member_id = FALSE;
$is_logged = FALSE;
$allow_sql_skin = false;

include ENGINE_DIR.'/data/config.php';

define('DLE_CLASSES' , ENGINE_DIR . (($config['version_id'] > 6.3)?'/classes/':'/inc/'));

if ($config['http_home_url'] == "")
{

	$config['http_home_url'] = explode("engine/job/ajax.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

if (isset ( $_COOKIE['dle_skin'] ) and $_COOKIE['dle_skin'] != '' && @is_dir ( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'] ))
	$config['skin'] = $_COOKIE['dle_skin'];

$PHP_SELF = $config['http_home_url']."index.php";
$_TIME = time()+($config['date_adjust']*60);

require_once DLE_CLASSES . 'mysql.php';
require_once ENGINE_DIR.'/data/dbconfig.php';
require(ENGINE_DIR . '/modules/functions.php');
require_once ENGINE_DIR.'/modules/sitelogin.php';

if (isset($config["lang_" . $config['skin']]) and $config["lang_" . $config['skin']] != '')
    require ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . "/website.lng";
else
    require(ROOT_DIR . "/language/".$config['langs']."/website.lng");
    
$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

check_xss();

require_once DLE_CLASSES.'templates.class.php';
$tpl = new dle_template;
$tpl->dir = ROOT_DIR.'/templates/'.$config['skin'];

require_once(ENGINE_DIR . "/job/includes.php");

$action = (empty($_REQUEST['action']))?'':$_REQUEST['action'];
$id = (intval($_REQUEST['id']))?intval($_REQUEST['id']):0;
$search = (empty($_REQUEST['search']))?false:true;

switch ($action)
{
	case 'GetSpecialties': 
		foreach ($job->GetSpecialties($id, ($search)?'search':'') as $id=>$name)
		{
            $spec = new stdClass();
            $spec->id = $id;
            $spec->name = $name;
			$result[] = $spec;
		}
		header('Content-Type: application/json; charset="' . $config['charset'] . '"');
		echo php2js($result);
		exit();
		break;
		
	case 'GetCities':
		foreach ($job->GetCities($id, ($search)?'search':'') as $id=>$name)
		{
            $city = new stdClass();
            $city->id = $id;
            $city->name = $name;
			$result[] = $city;
		}
		header('Content-Type: application/json; charset="' . $config['charset'] . '"');
		echo php2js($result);
		exit();
		break;
		
	case "GetCityEdit":
		require(ENGINE_DIR . "/Core_modules/TemplateAdmin.php");
		$tpl = new TemplateAdmin();
		$tpl->echo = FALSE;
		$PHP_SELF = $config['http_home_url']."admin.php?mod=job&action=cities&subaction=";
		$result = $tpl->OTable(array(), 'style="margin-left:30px;width:95%" id="city_' . $id . '"');
		foreach ($job->GetCities($id) as $id=>$name)
		{
			$result .= $tpl->row(array('width="30px" align="center"' => "", $id, $name, "[<a href=\"{$PHP_SELF}edit&type=city&id=$id\">{$job->lang['action_edit']}</a>][<a OnClick=\"if (confirm('{$job->lang['del_confirm_city']}'))return true; else return false;\" href=\"{$PHP_SELF}del&type=city&id=$id\">{$job->lang['action_del']}</a>]"), false, false, "id=\"$id\"");
		}
		$result .= $tpl->CTable();
		header('Content-Type: text/html; charset="' . $config['charset'] . '"');
		echo $result;
		break;
		
	case "GetSpecialtiesEdit":
		require(ENGINE_DIR . "/Core_modules/TemplateAdmin.php");
		$tpl = new TemplateAdmin();
		$tpl->echo = FALSE;
		$PHP_SELF = $config['http_home_url']."admin.php?mod=job&action=specialties&subaction=";
		$result = $tpl->OTable(array(), 'style="margin-left:30px;width:95%" id="specialty_' . $id . '"');
		foreach ($job->GetSpecialties($id) as $id=>$name)
		{
			$result .= $tpl->row(array('width="30px" align="center"' => "", $id, $name, "[<a href=\"{$PHP_SELF}edit&type=specialty&id=$id\">{$job->lang['action_edit']}</a>][<a OnClick=\"if (confirm('{$job->lang['del_confirm_specialty']}'))return true; else return false;\" href=\"{$PHP_SELF}del&type=specialty&id=$id\">{$job->lang['action_del']}</a>]"), false, false, "id=\"$id\"");
		}
		$result .= $tpl->CTable();
		header('Content-Type: text/html; charset="' . $config['charset'] . '"');
		echo $result;
		break;
		
	case "DelImage":
		$job_id = (empty($_REQUEST['auto_id']))?0:$_REQUEST['auto_id'];
		header('Content-Type: text/html; charset="' . $config['charset'] . '"');
		echo $job->DelPhoto($job_id, $id);
		break;
		
	case "CheckLogin":
		require(ENGINE_DIR . "/job/ajax_registration.php");
		break;
		
	case "company_admin":
	    header('Content-Type: text/html; charset="' . $config['charset'] . '"');
	    if (empty($_REQUEST['q']))
	    {
	        return '';
	    }
	    
	    $name = convert_unicode($_REQUEST['q'], $config['charset']);
	    
	    $name = addcslashes($dbase->EscapeString($name), '_%');
	    $resourse = $dbase->DirectQuery('SELECT name FROM ' . USERPREFIX . "_users WHERE name LIKE '$name%'");
	    
	    while ($row = $dbase->FetchArray($resourse))
	    {
	        echo $row['name'] . "\n";
	    }
	    exit;
	    break;
	    
	case "specialty":
	    header('Content-Type: text/html; charset="' . $config['charset'] . '"');
	    if (empty($_REQUEST['term']))
	    {
	        return '';
	    }
	    $name = convert_unicode($_REQUEST['term'], $config['charset']);
	    $sphere = empty($_REQUEST['sphere'])?0:intval($_REQUEST['sphere']);
	    
	    $name = addcslashes($dbase->EscapeString($name), '_%');
	    $dbase->SetWhere('name', $name, 'LIKE', 'job_specialties');
	    
	    if ($sphere)
	    {
	        $dbase->SetWhere('sphere_id', $sphere, '=', 'job_specialties');
	    }
	    $resourse = $dbase->Select('job_specialties', array('name'));
	    
        $return = array();
	    while ($row = $dbase->FetchArray($resourse))
	    {
            $return[] = mb_convert_encoding( $row['name'], "UTF-8", 'windows-1251');
//            $return[] = convert_unicode($row['name'], "utf-8");
//	        echo $row['name'] . "\n";
	    }
	    
        print json_encode($return);
	    exit;
	    break;
		
	case "send_mail":
		$error = false; $data = array_map_recursive('urldecode', UrlParse($_REQUEST['data']));

		include_once DLE_CLASSES.'mail.class.php';
		$mail = new dle_mail ($config);
		
		if (!$is_logged)
		{
			if (!auto_check_email($data['from_email']))
				$error = true;
				
			if (!$data['from_name'])
				$error = true;
				
			$mail->from = $data['from_email'];
		}
		else 
			$mail->from = $member_id['email'];

		if (!auto_check_email($data['user_email']) || strlen($data['text']) < 10 || strlen($data['subj']) < 5)
			$error = true;
			
		if ($error)
			die("Error");
			
		$mail->send ($data['user_email'], $data['subj'], $data['text']);
		if ($mail->send_error) die("Error");
		echo "ok";
		break;
		
	case "email_auto":
		$error = false; $data = array_map_recursive('urldecode', UrlParse($_REQUEST['data']));
		
		if (!$id)
			$error = true;
		else 
			$email = $base->SelectOne('auto_autos', array("email"), array("id" => $id));
			
		if (empty($email['email']))
			$error = true;

		include_once DLE_CLASSES.'mail.class.php';
		$mail = new dle_mail ($config);
		
		if (!$is_logged)
		{
			if (!auto_check_email($data['from_email']))
				$error = true;
				
			if (!$data['from_name'])
				$error = true;
				
			$mail->from = $data['from_email'];
		}
		else 
			$mail->from = $member_id['email'];

		if (strlen($data['text']) < 10 || strlen($data['subj']) < 5)
			$error = true;
			
		if ($error)
			die("Error");
			
		$mail->send ($email['email'], $data['subj'], $data['text']);
		if ($mail->send_error) die("Error");
		echo "ok";
		break;
		
	case "allow_site":
		$allow = (intval($_REQUEST['allow']))?1:0;
		if (!in_array($_REQUEST['type'], array('vacancy', 'resume')))
		{
		    exit;
		}
		if ((MODER_EDIT || MODER_DEL) && $id)
		{
		    if ($_REQUEST['type'] == "vacancy")
		    {
		        $dbase->Update('job_vacancies', array("allow_site" => $allow), array('id' => $id));
		    }
		    else
		    {
		        $dbase->Update('job_resumes', array("allow_site" => $allow), array('id' => $id));
		    }
			Cache::ClearHTMLCache();
		}
		break;
		
	default:
		echo "Unknown Action";
		break;
}
exit;
?>