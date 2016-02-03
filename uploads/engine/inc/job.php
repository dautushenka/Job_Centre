<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

define('LIC_DOMAIN', /*licadm*/'vashedelo\.biz'/*/licadm*/);
define('DLE_CLASSES' , ENGINE_DIR . (($config['version_id'] > 6.3)?'/classes/':'/inc/'));
@ini_set('zend.ze1_compatibility_mode', 0);

require(ROOT_DIR . '/language/'.$config['langs'].'/job.lng');
require(ENGINE_DIR . '/job/version.php');
require(ENGINE_DIR . '/job/functions.php');

class Licencing
{
	static private $dom = '';
	
	public $domain;
	
	static public $tpl;
	
	public function __construct($domain)
	{
		if (self::$dom)
			self::ErrorMessage();
			
		self::$dom = $domain;
		$this->domain = $domain;
	}
		
	public static function check()
	{
		if (!defined('LIC_DOMAIN'))
			self::ErrorMessage();
			
		if (!eregi(self::$dom, $_SERVER['HTTP_HOST']) && !eregi("localhost", $_SERVER['HTTP_HOST']))
			self::ErrorMessage();
	}
	
	private function ErrorMessage()
	{
		self::sendmail();
		self::$tpl->msg("Error", "Вы используете не лицензионную версию модуля.<br/>За информацией обращайтесь на форум <a href=\"http://forum.kaliostro.net/\" >http://forum.kaliostro.net/</a> или ICQ: 415-74-19");
		exit();
	}
	
	private static function sendmail()
	{
		include_once DLE_CLASSES.'mail.class.php';
		$mail = new dle_mail ($GLOBALS['config']);
		
		$text  = "Лиц домен:" . LIC_DOMAIN . "\n";
		$text .= "Текущей домен: " . $_SERVER['HTTP_HOST'];
		
		$mail->send ("support@kaliostro.net", "Нарушение лицензии", $text);
	}
}
$licence = new Licencing(LIC_DOMAIN);

require_once(ENGINE_DIR . "/Core_modules/Cache.php");
require(ENGINE_DIR . "/job/classes/JobCore.php");
require_once(ENGINE_DIR . "/Core_modules/ExceptionCore.php");
require_once(ENGINE_DIR . "/Core_modules/TemplateAdmin.php");
require_once(ENGINE_DIR . "/Core_modules/Timer.php");
require(ENGINE_DIR . "/job/classes/JobAdmin.php");
require(ENGINE_DIR . "/job/classes/company.php");
require(ENGINE_DIR . "/job/classes/resume.php");
require(ENGINE_DIR . "/job/classes/vacancy.php");
require(ENGINE_DIR . "/job/classes/parse.class.php");
require(ENGINE_DIR . '/job/constants.php');

if (file_exists(ENGINE_DIR . '/data/job_conf.php'))
	require_once (ENGINE_DIR . '/data/job_conf.php');
else 
	msg('error', 'Error', $lang_job['module_not_installed']);

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


try 
{
	
	$tpl = new TemplateAdmin();
	Licencing::$tpl =& $tpl;
	
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
	
	$job = new JobAdmin($job_cfg, $lang_job, $dbase);
	Company::$job = Vacancy::$job = Resume::$job =& $job;
	
	if ($config['version_id'] < 7.5)
	{
		$job->member = array("id"    => $member_db[10],
						 	 "name"  => $member_db[2],
						  	 "email" => $member_db[5],
						  	 "group" => $member_db[1],
						  	 "ip"    => $member_db[15]
							);
	}
	else 
	{
		$job->member = array("id"    => $member_id['user_id'],
						 	 "name"  => $member_id['name'],
						  	 "email" => $member_id['email'],
						  	 "group" => $member_id['user_group'],
						  	 "ip"    => $member_id['logged_ip']
							);
	}
	
	if (get_magic_quotes_gpc() && function_exists('array_map_recursive'))
	{
		array_map_recursive('stripslashes', $_GET);
		array_map_recursive('stripslashes', $_POST);
		array_map_recursive('stripslashes', $_COOKIE);
		array_map_recursive('stripslashes', $_REQUEST);
	}
	
	if ($job->config['general_debug'])
	{
		$dbase->debug = true;
		TemplateAdmin::$Debug_info =& $dbase->query_list;
	}
	$action = (empty($_REQUEST['action']))?'':$_REQUEST['action'];
	$subaction = (empty($_REQUEST['subaction']))?'':$_REQUEST['subaction'];
	$id = (intval($_REQUEST['id']))?intval($_REQUEST['id']):0;
	
	$hidden_array = array("mod" => 'job');
	if ($action)
		$hidden_array["action"] = $action;
					
	$MAIN_URL = $PHP_SELF . "?mod=job";
	$PHP_SELF .= "?mod=job&action=";
	
    if ($config['version_id'] < 9)
    {
        $tpl->SetStyleScript(array($config['http_home_url'] . 'engine/job/admin/style.css'), array('/engine/job/javascript/jquery.js',
																								$config['http_home_url'] . 'engine/job/javascript/job.js'));
    }
    else 
    {
        $tpl->SetStyleScript(array($config['http_home_url'] . 'engine/job/admin/style.css'), array($config['http_home_url'] . 'engine/job/javascript/job.js'));
    }

	
	$tpl->menu(array(
					$job->lang['vacancies']      => array('vacancies', 'vacancy.png'), 
					$job->lang['resumes']        => array('resumes', 'resume.png'), 
					$job->lang['set_city']       => array('cities', 'city.png'), 
					$job->lang['set_specialties'] => array('specialties', 'specialty.png'), 
					$job->lang['companies']      => array('companies', 'company.png'), 
					$job->lang['other_field']    => array('xfields', 'fields.png'),
					$job->lang['settings']       => array('settings', 'settings.png')
					), 
					$PHP_SELF, "/engine/job/images/admin/menu");
					
	$ajax_domain = reset(explode($config['admin_path'], $_SERVER['PHP_SELF']));
					
	$tpl->subhead = <<<SCRIPT
<script type="text/javascript">
var dle_root = '$ajax_domain';
var ajax_url = dle_root + 'engine/job/ajax.php';
var use_country = {$job->use_country};
var use_city = {$job->use_city};
</script>
SCRIPT;
					
	$tpl->footer = FALSE;
	
	
	try 
	{
		switch ($action)
		{
			case "vacancies":
				require(ENGINE_DIR . "/job/admin/vacancies.php");
				break;
				
			case "resumes":
				require(ENGINE_DIR . "/job/admin/resumes.php");
				break;
				
			case "cities":
				require(ENGINE_DIR . "/job/admin/cities.php");
				break;
				
			case "specialties":
				require(ENGINE_DIR . "/job/admin/specialties.php");
				break;
				
			case "companies":
				require(ENGINE_DIR . "/job/admin/companies.php");
				break;
				
			case "fields":
				require(ENGINE_DIR . "/job/admin/fields.php");
				break;
				
			case "xfields":
				require(ENGINE_DIR . "/job/admin/xfields.php");
				break;
				
			case "settings":
				require(ENGINE_DIR . "/job/admin/settings.php");
				break;
				
			case "clearcache":
				Cache::ClearAllCache();
				$tpl->msg($job->lang['clearcache'], $job->lang['clearcache_ok'], $MAIN_URL);
				break;
				
			case "cron":
			    include(ENGINE_DIR . "/job/cron.php");
				Cache::ClearAllCache();
				$tpl->msg($job->lang['action_cron'], $job->lang['action_cron_ok'], $MAIN_URL);
				break;
			/*	
			case "rebuildcounter":
				$dbase->Select('auto_models', array('id'));
				while ($row = $dbase->FetchArray())
				{
					$models[] = $row['id'];
				}
				foreach ($models as $id)
				{
					$count = $dbase->SelectOne('auto_autos', array('count' => 'COUNT(*)'), array('model_id' => $id, "allow_site" => 1));
					$dbase->Update('auto_models', array('auto_num' => $count['count']), array('id' => $id));
				}
				$dbase->Select('auto_marks', array("id"));
				while ($row = $dbase->FetchArray())
				{
					$marks[] = $row['id'];
				}
				foreach ($marks as $id)
				{
					$count = $dbase->SelectOne('auto_models', array('count' => 'SUM(auto_num)'), array("mark_id" => $id));
					$count_other = $dbase->SelectOne('auto_autos', array('count' => 'COUNT(*)'), array('mark_id' => $id, 'model_id' => 0, "allow_site" => 1));
					$dbase->Update('auto_marks', array('auto_num' => $count['count'] + $count_other['count']), array('id' => $id));
				}
				Cache::ClearArrayCache();
				$tpl->msg($job->lang['rebuildcounter'], $job->lang['rebuildcounter_ok'], true);
				break;
				
			case "rebuildphotocount":
				$dbase->Select('auto_autos', array('id'));
				while ($row = $dbase->FetchArray())
				{
					$jobs[] = $row['id'];
				}
				foreach ($jobs as $id)
				{
					$count = $dbase->SelectOne('auto_images', array("count" => "COUNT(*)"), array('auto_id' => $id));
					$dbase->Update('auto_autos', array('photo_count' => $count['count']), array('id' => $id));
				}
				$tpl->msg($job->lang['rebuildcounter'], $job->lang['rebuildcounter_ok'], true);
				break;*/
				
			default:
			    $stats = $job->Stats();
			    
				$tpl->header($job->lang['stats'], true);
				$tpl->OpenTable();
				$tpl->OpenSubtable($job->lang['stats']);
				$tpl->stats(array(
								$job->lang['vacancy_all']       => $stats['vacancy_all'], 
								$job->lang['vacancy_today']     => $stats['vacancy_today'] , 
								$job->lang['vacancy_on_site']   => $stats['vacancy_on_site'], 
								$job->lang['vacancy_no_noder']  => $stats['vacancy_no_noder'], 
								$job->lang['vacancy_no_noder']  => $stats['vacancy_no_noder'], 
								$job->lang['vacancy_max']       => $stats['vacancy_max'], 
								$job->lang['resume_all']        => $stats['resume_all'], 
								$job->lang['resume_today']      => $stats['resume_today'], 
								$job->lang['resume_on_site']    => $stats['resume_on_site'], 
								$job->lang['resume_no_moder']   => $stats['resume_no_moder'], 
								$job->lang['resume_max']        => $stats['resume_max'], 
								$job->lang['company_all']       => $stats['company_all'], 
								$job->lang['company_today']     => $stats['company_today'], 
								$tpl->line 			            => $tpl->line,
								"Версия используемого модуля" => VERSION, 
								"Модуль зарегистрирован на" => "<b><a href=\"http://www." . LIC_DOMAIN . "\" >". LIC_DOMAIN . "<a/></b>", 
								"Страничка поддержки модуля" => "<a href=\"http://www.kaliostro.net/\" ><b><font color=\"green\" >www.kaliostro.net</font><b></a>")
								);
				$tpl->OTable();
				$tpl->OpenForm('', $hidden_array + array('action' => 'clearcache'));
				$tpl->echo = FALSE;
				echo $tpl->row(array($tpl->InputSubmit($job->lang['clearcache']) . $tpl->CloseForm(), 
									 $tpl->OpenForm('', $hidden_array + array('action' => 'cron')) . $tpl->InputSubmit($job->lang['action_cron_btn']) . $tpl->CloseForm(),
//									 $tpl->OpenForm('', $hidden_array + array('action' => 'rebuildphotocount')) . $tpl->InputSubmit($job->lang['rebuildphotocount']) . $tpl->CloseForm()
									 ), false);
				$tpl->echo = TRUE;
				$tpl->CTable();
				$tpl->CloseSubtable();
				$tpl->CloseTable();
		}
	}
	catch (Exception $e)
	{
		$tpl->msg($job->lang['error'], $e->getMessage(), $MAIN_URL);
	}

	$tpl->footer(true, 2007);
	
	
}
catch (Exception $e)
{
	echoheader();
	msg('error', 'Error', $e->getMessage());
	echofooter();
}
?>