<?php

if (empty($db))
{
    set_time_limit(0);
    @error_reporting(E_ALL ^ E_NOTICE);
    @ini_set('display_errors', false);
    @ini_set('html_errors', false);
    @ini_set('error_reporting', E_ALL ^ E_NOTICE);
    
    define('DATALIFEENGINE', true);
    define('ENGINE_DIR', rtrim(dirname(__FILE__), "/") . '/..');
    define('ROOT_DIR', ENGINE_DIR . '/..');
    define('LIC_DOMAIN', '.');
    
    include ENGINE_DIR.'/data/config.php';
    define('DLE_CLASSES' , ENGINE_DIR . (($config['version_id'] > 6.3)?'/classes/':'/inc/'));
    
    
    $PHP_SELF = $config['http_home_url']."index.php";
    
    require_once DLE_CLASSES.'mysql.php';
    require_once ENGINE_DIR.'/data/dbconfig.php';
    require(ENGINE_DIR . '/modules/functions.php');
    
    $db->connect(DBUSER, DBPASS, DBNAME, DBHOST);
}

$_TIME = time()+($config['date_adjust']*60);

$db->query('UPDATE ' . PREFIX . "_job_vacancies SET allow_site=0 WHERE date_exp<$_TIME AND date_exp!=0 AND allow_site=1");
$db->query('UPDATE ' . PREFIX . "_job_resumes SET allow_site=0 WHERE date_exp<$_TIME AND date_exp!=0 AND allow_site=1");

?>