<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

$PHP_SELF .= "&action=settings";
$save_con = (empty($_REQUEST['save_con']))?array():$_REQUEST['save_con'];

if ($subaction == "save")
{
	$job->config = array_merge($job->config, $save_con);
}

$tpl->echo = FALSE;

require(ENGINE_DIR . "/job/admin/settings_array.php");
						
$tpl->echo = TRUE;
		
if ($subaction == "save")
{
	$errors = array();
	foreach ($settings_array as $settings)
	{
		foreach ($settings as $setting)
		{
			if ($setting['regexp'])
			{
				if (is_array($save_con[$setting['name']]))
				{
					foreach ($save_con[$setting['name']] as $value)
					{
						if (!preg_match($setting['regexp'], $value))
							$errors[] = $setting['title'];
					}
				}
				elseif (!preg_match($setting['regexp'], $save_con[$setting['name']]))
					$errors[] = $setting['title'];
			}
		}
	}
	
	if (file_exists(ENGINE_DIR.'/data/job_conf.php') && !is_writable(ENGINE_DIR.'/data/job_conf.php'))
	{
	    $errors[] = $job->lang['settings_file_not_writable'];
	}
	elseif (!file_exists(ENGINE_DIR.'/data/job_conf.php') && !is_writable(ENGINE_DIR.'/data'))
	{
	    $errors[] = $job->lang['settings_dir_not_writable'];
    }
	
	if (!$errors)
	{
    	if($job->member['group'] != 1){ $tpl->msg($lang['opt_denied'], $lang['opt_denied']); }
		
		$save_con['version_id'] = $job->config['version_id'];
		$save_con['use_country'] = $job->config['use_country'];
		$save_con['use_city'] = $job->config['use_city'];
		
	    $handler = fopen(ENGINE_DIR.'/data/job_conf.php', "w");
	    fwrite($handler, "<?PHP \n//Job Configurations\n\n\$job_cfg = array(\n\n");
	       
	    save_conf($save_con);
	    fwrite($handler, ");\n\n?>");
	    fclose($handler);
	    		
	    Cache::ClearAllCache();
	    $tpl->msg("info", $lang['opt_sysok'], $PHP_SELF);
	}
}


$JScript = <<<JS
<script type="text/javascript">
$(document).ready(function()
{
	$("#setting").find("#general").show();
	$("#submenu").find("#general").css("border", "1px solid");
	$("#setting input:text").css("text-align", "center");
	$("#submenu").find("a").click(function()
	{
		$("#submenu").find("a").css("border", "");
		$(this).css("border", "1px solid");
		$("#setting tr[id]").hide();
		id = $(this).attr("id");
		$("#setting").find("#"+id).show();
		return false;
	});
	$("table").find("#subtable tr").hover(function()
	{
		$(this).addClass('over');
	}, 
	function()
	{
		$(this).removeClass('over');
	});
});
</script>
JS;
						

$tpl->header($job->lang['settings'], true, $JScript);
	
$tpl->submenu(array(
				$job->lang['vacancy_setting'] => array('settings', 'vacancy_set.png', 'id="vacancy"'), 
				$job->lang['resume_setting'] => array('settings', 'resume_set.png', 'id="resume"'), 
				$job->lang['company_setting'] => array('settings', 'company_set.png', 'id="company"'), 
				$job->lang['general_title']=> array('settings', 'settings2.png', 'id="general"')
				), 
				$PHP_SELF, "/engine/job/images/admin/submenu");
				
if ($errors)
{
	$tpl->OpenTable();
	echo "  <font color=\"red\" >" . $job->lang['setting_error'] . "</font><ol>";
	foreach ($errors as $error)
	{
		echo "<li>" . $error . "</li>";
	}
	echo "</ol>";
	$tpl->CloseTable();
}			

$tpl->OpenTable();
$tpl->OpenForm('', $hidden_array + array('subaction' => 'save'));
echo "<table width=100% id='setting'>";
foreach ($settings_array as $table=>$settings)
{
	echo "<tr id=\"$table\" style='display:none'><td>";
	$tpl->OpenSubtable($job->lang[$table . '_title']);
	$tpl->OTable(array(), 'id="subtable"');
	foreach ($settings as $setting)
	{
		$tpl->SettingRow($setting['title'], $setting['descr'], $setting['setting']);
	}
	$tpl->CTable();
	$tpl->CloseSubtable();
	echo "</td></tr>";
}
echo "</table>";
$tpl->CloseTable($job->lang['btn_save']);
$tpl->CloseForm();

?>