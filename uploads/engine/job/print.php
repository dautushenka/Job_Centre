<?php

@session_start();

@error_reporting(E_ALL ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('ROOT_DIR', '../..');
define('ENGINE_DIR', '..');
define("RSS", false);

$member_id = FALSE;
$is_logged = FALSE;
$allow_sql_skin = false;

include ENGINE_DIR.'/data/config.php';
define('DLE_CLASSES' , ENGINE_DIR . (($config['version_id'] > 6.3)?'/classes/':'/inc/'));

if ($config['http_home_url'] == "")
{

	$config['http_home_url'] = explode("engine/car-market/compare.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

$PHP_SELF = $config['http_home_url'] . "index.php";
$_TIME = time()+($config['date_adjust']*60);

require_once DLE_CLASSES.'mysql.php';
require_once ENGINE_DIR.'/data/dbconfig.php';
require(ENGINE_DIR . '/modules/functions.php');
require_once ENGINE_DIR.'/modules/sitelogin.php';
if ($config["lang_".$config['skin']]) { 

     include_once ROOT_DIR.'/language/'.$config["lang_".$config['skin']].'/website.lng';

} else {

     include_once ROOT_DIR.'/language/'.$config['langs'].'/website.lng';

}
$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

check_xss();

require_once DLE_CLASSES . 'templates.class.php';
$tpl = new dle_template;
define('TEMPLATE_DIR', ROOT_DIR.'/templates/' . $config['skin']);
$tpl->dir = TEMPLATE_DIR;

require_once(ENGINE_DIR . "/job/includes.php");

$dbase->Connect(DBHOST, $port = '', DBUSER, DBPASS, DBNAME, $usepconnect = false, COLLATE);

$id = (intval($_REQUEST['id']))?intval($_REQUEST['id']):die('Not Found');
$action = ($_REQUEST['action'])?$_REQUEST['action']:die('Unknow action');
$template->_blank = true;
$template->SetStyleScript(array('{THEME}/job/css/style_user.css'));

switch ($action)
{
    case "vacancy":
        $job->SearchVacancy(array('id' => $id), array('get_count' => 0));
        $template->load('vacancy_print');
        $array = $job->ShowVacancy($id, array('show_edit' => 0));
        break;
        
    case "resume":
        $job->SearchResume(array('id' => $id), array('get_count' => 0));
        $template->load('resume_print');
        $array = $job->ShowResume($id, array('show_edit' => 0));
        break;
        
    case "company":
        $job->SearchCompany(array('id' => $id), array('get_count' => 0));
        $template->load('company_print');
        $array = $job->ShowCompany($id, array('show_edit' => 0));
        break;
        
    default:
        break;
}
$template->Set($array)
         ->Compile('content');

if ($job->member['id'])
{
    $tpl->result['content'] = preg_replace('#\[not-logged\](.*?)\[/not-logged\]#si', '' , $tpl->result['content']);
    $tpl->result['content'] = preg_replace('#\[logged\](.*?)\[/logged\]#si', "\\1" , $tpl->result['content']);
}
else
{
    $tpl->result['content'] = preg_replace('#\[not-logged\](.*?)\[/not-logged\]#si', "\\1" , $tpl->result['content']);
    $tpl->result['content'] = preg_replace('#\[logged\](.*?)\[/logged\]#si', '' , $tpl->result['content']);
}
         
echo $tpl->result['info'] . $tpl->result['content'];

?>