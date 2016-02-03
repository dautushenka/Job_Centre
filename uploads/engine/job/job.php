<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}
 
define('DLE_CLASSES' , ENGINE_DIR . (($config['version_id'] > 6.3)?'/classes/':'/inc/'));
define('RSS', false);

if (!$is_logged)
{
	$member_id['user_group'] = 5;
	$member_id['user_id'] = 0;
}


try 
{
	require_once(ENGINE_DIR . "/job/includes.php");
}
catch (Exception $e)
{
	if ($member_id['user_group'] == 1)
		msgbox("Error", $e->getMessage());
	else 
		msgbox("Error", $lang_car['global_error']);
}


if (!$job->config['general_allow_module'] && $job->member['group'] != 1)
{
	msgbox('Error', $job->lang['module_disabled']);
	return ;
}


define("RSS", false);

$action = (empty($_REQUEST['action']))?'':$_REQUEST['action'];
$selected = (empty($_REQUEST['selected']))?array():$_REQUEST['selected'];
$id = (intval($_REQUEST['id']))?intval($_REQUEST['id']):0;

$hidden_array['action'] = $action;

if (!$job->config['general_main_page'])
{
	if ($action == 'main' || !$action)
		$template->TitleSpeedBar($job->lang['name_module']);
	else
		$template->TitleSpeedBar($job->lang['name_module'], $template->GetUrl());
}		
if (!$job->config['general_main_page'])
	$hidden_array["do"] = $job->config['general_name_module'];
	
	
    /*
$template->superhead = <<<JS
<script type="text/javascript">
if (typeof(jQuery) == 'undefined') 
{ 
	jq = document.createElement('script');
    jq.src = '/engine/job/javascript/jquery.js';
    jq.type = 'text/javascript';
	document.getElementsByTagName('head')[0].appendChild(jq);
}
</script>
JS;
*/
	
	
$template->SetStyleScript(array('{THEME}/job/css/style_user.css'), array(		'/engine/job/javascript/job.js',
//                                                                                '/engine/job/javascript/job.js',
                                                                                '/engine/job/javascript/jquery.cookie.js',
																				'/engine/job/javascript/jquery.blockUI.js',
																				));

if (AJAX && !AJAX_INSIDE)
{
    $template->AJAXStyleScript(array($GLOBALS['config']['http_home_url'] . 'templates/' . $config['skin'] . '/job/css/style_user.css'));
}

if ($job->config['general_AJAX'])
{
    $AJAX = <<<JS
$().ready(function()
{
    $('a.inside_ajax').live('click', function()
    {
       BlockContent($('#job-content'));
       $('#job-content').load($(this).attr("href"), {'inside':1}); 
       return false;
    });
    $('a.content_ajax').live('click', function()
    {
        BlockContent($('#dle-content'));
        $('#dle-content').load($(this).attr("href"), function()
        {
            CheckDisable();
        }); 
        return false;
    });
});
JS;
}
else
{
    $AJAX = '';
}
										
$template->subhead = <<<SCRIPT
<script type="text/javascript">
var ajax_url = dle_root + 'engine/job/ajax.php';
var use_country = {$job->use_country};
var use_city  = {$job->use_city};
var allow_no  = '{$job->lang['allow_no']}';
var allow_yes  = '{$job->lang['allow_yes']}';
$AJAX
</script>
SCRIPT;


try {
	
	switch ($action)
	{
		case "list":
			require(ENGINE_DIR . "/job/user/sphere_list.php");
			break;
			
		case "vacancy":
			require(ENGINE_DIR . "/job/user/vacancy.php");
			break;
			
		case "resume":
			require(ENGINE_DIR . "/job/user/resume.php");
			break;
			
		case "vacancy_doadd":
		case "vacancy_save":
		case "vacancy_add":
		case "vacancy_edit":
		case "vacancy_del":
			require(ENGINE_DIR . "/job/user/vacancy_add.php");
			break;
			
		case "resume_doadd":
		case "resume_save":
		case "resume_add":
		case "resume_edit":
		case "resume_del":
			require(ENGINE_DIR . "/job/user/resume_add.php");
			break;
			
		case "vacancy_search":
			require(ENGINE_DIR . "/job/user/vacancy_search.php");
			break;
			
		case "resume_search":
			require(ENGINE_DIR . "/job/user/resume_search.php");
			break;
			
		case "send":
			require(ENGINE_DIR . "/job/user/send_auto.php");
			break;
			
		case "account":
			require(ENGINE_DIR . "/job/user/account.php");
			break;
			
		case "company":
		    require(ENGINE_DIR . "/job/user/company.php");
		    break;
			
		case "company_add":
		case "company_doadd":
		case "company_edit":
		case "company_save":
		case "company_del":
			require(ENGINE_DIR . "/job/user/company_add.php");
			break;
			
		default:
			require(ENGINE_DIR . "/job/user/default.php");
			break;
	}
}
catch (Exception $e)
{
	if ($member_id['user_group'] == 1)
		msgbox("Error", $e->getMessage());
	else 
		msgbox("Error", $lang_job['global_error']);
}

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

$TitleSpeedBar = $template->TitleSpeedBar();


if (AJAX)
{
    $s_navigation = str_replace( array ("'", "&#039;" ), array ("&#039;", "&#039;" ),"<a href=\"{$config['http_home_url']}\">".$config['home_title']."</a> &raquo; ". $TitleSpeedBar['speedbar']);
    
	@header("Content-type: text/css; charset=".$config['charset']);
	echo <<<JS
<script language='JavaScript' type="text/javascript">

	if ( document.getElementById('dle-speedbar') )
	{
	   document.getElementById('dle-speedbar').innerHTML = '{$s_navigation}';
    }
</script>
JS;
	echo $tpl->result['content'];
	exit;
}


if ($job->config['general_debug'] && $dbase->query_list)
{
	$tpl->result['content'] .= "<pre>" . StringLength(print_r($dbase->query_list, true)) . "</pre>";
}

include_once(ENGINE_DIR . "/job/blocks.php");

?>