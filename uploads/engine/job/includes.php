<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}


@ini_set('zend.ze1_compatibility_mode', 0);

require(ENGINE_DIR . '/job/functions.php');
include_lng('job', 'lang_job');

if (file_exists(ENGINE_DIR . '/data/job_conf.php'))
	require_once (ENGINE_DIR . '/data/job_conf.php');
else 
{
	msgbox('Error', $lang_car['module_not_installed']);
	return 0;
}

if (!class_exists('Licencing'))
{
    class Licencing
    {
    	static private $dom;
    	
    	public $domain;
    	
    	public function __construct($domain)
    	{
    		self::$dom = $domain;
    		$this->domain = $domain;
    	}
    		
    	public static function check()
    	{
    		
    	}
    }
    $licence = new Licencing(LIC_DOMAIN);
}

require(ENGINE_DIR . '/job/version.php');
require_once(ENGINE_DIR . "/Core_modules/Cache.php");
require_once(ENGINE_DIR . "/Core_modules/ExceptionCore.php");
require(ENGINE_DIR . "/job/classes/JobCore.php");
require(ENGINE_DIR . "/job/classes/JobUser.php");
require_once(ENGINE_DIR . "/Core_modules/TemplateUser.php");
require_once(ENGINE_DIR . "/Core_modules/Timer.php");
require(ENGINE_DIR . "/job/classes/company.php");
require(ENGINE_DIR . "/job/classes/resume.php");
require(ENGINE_DIR . "/job/classes/vacancy.php");
require(ENGINE_DIR.'/job/constants.php');
require_once ENGINE_DIR . '/job/classes/Fields.php';

Cache::$array_cache_path = ENGINE_DIR . "/job/cache/array/";
Cache::$HTML_cache_path = ENGINE_DIR . "/job/cache/";
				
$exc = new ExceptionErrorHandler('All');
if ($job_cfg['general_debug'])
{
	ExceptionErrorHandler::$log_type = 'show';
	ExceptionDataBase::$log_type = 'show';
	ExceptionAllError::$log_type = 'show';
}
else
{
	ExceptionErrorHandler::$log_type = 'file';
	ExceptionErrorHandler::$log_file = ENGINE_DIR . "/job/logs/HandlerErrors.log";
	ExceptionDataBase::$log_type = 'file';
	ExceptionDataBase::$log_file = ENGINE_DIR . "/job/logs/database.log";
	ExceptionAllError::$log_type = 'file';
	ExceptionAllError::$log_file = ENGINE_DIR . "/job/logs/errors.log";
}

$template = new TemplateUser($tpl, 'job/');
$timer = new Timer($_TIME);

if ($db->mysql_extend == 'MySQLi')
{
	require_once(ENGINE_DIR . "/Core_modules/MySQLi_DLE.php");
	$dbase = new MySQLi_DLE($db, $timer, $TABLES, PREFIX . "_");
}
else 
{
	require_once(ENGINE_DIR . "/Core_modules/MySQL_DLE.php");
	$dbase = new MySQL_DLE($db, $timer, $TABLES, PREFIX . "_");
}
if (AJAX)
	$dbase->Connect(DBHOST, $port = '', DBUSER, DBPASS, DBNAME, $usepconnect = false, COLLATE);

$job = new JobUser($job_cfg, $lang_job, $dbase);
$job->tpl =& $template;
Company::$job = Vacancy::$job = Resume::$job =& $job;

if ($job->config['general_debug'])
{
	$dbase->debug = true;
}


if ($is_logged && $member_id)
{
    $job->member = array("id"    => $member_id['user_id'],
    					 "name"  => $member_id['name'],
    					 "email" => $member_id['email'],
    				     "group" => $member_id['user_group'],
    					 "ip"    => $_SERVER['REMOTE_ADDR']
    					);
}
else 
{
    $job->member = array("id"    => 0,
					     "name"  => '',
					     "email" => '',
				         "group" => 5,
					     "ip"    => $_SERVER['REMOTE_ADDR']
					);
}

define("MODER_DEL", (CheckGroupAllow('general_moderator_del'))?TRUE:FALSE);					
define("MODER_EDIT", (CheckGroupAllow('general_moderator_edit'))?TRUE:FALSE);					
		
					
$template->use_alt_url = $job->config['general_mod_rewrite'];

if ($job->config['general_main_page'])
{
	$template->main_alt_url = substr($config['http_home_url'], 0, strlen($config['http_home_url']) - 1);
	$template->main_url = $PHP_SELF;
}
else 
{
	$template->main_alt_url = $config['http_home_url'] . $job->config['general_name_module'];
	$template->main_url = $PHP_SELF . "?do=" . $job->config['general_name_module'];
}

$template->alt_url_array = array(
								"action" => '/',
								"company_id" => 'company/',
								"vacancy" => 'vacancy',
								"apage" => '/',
								"account" => '/',
								"company_id" => '/',
								"page" => '/page',
								"print" => '/print',
								"rss" => '/rss',
								);
								
$template->safe_blocks = array("logged", "not-logged");

if (get_magic_quotes_gpc() && function_exists('array_map_recursive'))
{
	array_map_recursive('stripslashes', $_GET);
	array_map_recursive('stripslashes', $_POST);
	array_map_recursive('stripslashes', $_COOKIE);
	array_map_recursive('stripslashes', $_REQUEST);
}

$job->sort_array = array( 'cost' => $job->lang['sort_cost'],
						   'race' => $job->lang['sort_race'],
						   'date' => $job->lang['sort_date'],
					       'year' => $job->lang['sort_year'],
						);

$job->subsort_array = array( 'ASC' => $job->lang['subsort_ASC'], 'DESC' => $job->lang['subsort_DESC']);

?>