<?php

define('DATALIFEENGINE', true);
define('ROOT_DIR', '../..');
define('ENGINE_DIR',dirname (__FILE__) . "/../");

error_reporting(7);
ini_set('display_errors', true);
ini_set('html_errors', false);

include ENGINE_DIR.'/data/config.php';
define('DLE_CLASSES' , ENGINE_DIR . (($config['version_id'] > 6.3)?'/classes/':'/inc/'));

if ($config['http_home_url'] == "") 
{
	$config['http_home_url'] = explode("engine/rss.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
}

if (isset ( $_COOKIE['dle_skin'] ) and $_COOKIE['dle_skin'] != '' && @is_dir ( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'] ))
	$config['skin'] = $_COOKIE['dle_skin'];

require_once DLE_CLASSES . 'mysql.php';
include_once ENGINE_DIR . '/data/dbconfig.php';
include_once ENGINE_DIR . '/modules/functions.php';
require_once DLE_CLASSES . 'templates.class.php';

if (isset($config["lang_" . $config['skin']]) and $config["lang_" . $config['skin']] != '')
    require ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . "/website.lng";
else
    require(ROOT_DIR . "/language/".$config['langs']."/website.lng");
    
$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

define('LIC_DOMAIN', '.');
define("RSS", true);
$_GET['action'] = 'resume';

check_xss ();

$PHP_SELF = $config['http_home_url']."index.php";
$_TIME = time()+($config['date_adjust']*60);
$tpl = new dle_template;
$tpl->dir =  ROOT_DIR.'/templates/'.$config['skin'];
define('TEMPLATE_DIR', $tpl->dir);

require_once(ENGINE_DIR . "/job/includes.php");

if ($config['site_offline'] == "yes") die ("The site in offline mode");
if (!$job->config['general_RSS']) die("RSS canal was disabled");

$dbase->Connect(DBHOST, '', DBUSER, DBPASS, DBNAME, false, COLLATE);

require(ENGINE_DIR . "/job/user/resume.php");

header('Content-type: application/xml');
echo<<<XML
<?xml version="1.0" encoding="windows-1251"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
<title>{$config['home_title']}</title>
<link>{$template->main_alt_url}/</link>
<language>ru</language>
<description>{$config['home_title']}</description>
<generator>Job Centre v1.0.0alpha</generator>

XML;



echo $tpl->result['rss'];

echo'</channel></rss>';
?>