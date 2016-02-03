<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

if (!$job->use_country && !$job->use_city)
{
    $tpl->header("", true);
	$tpl->msg($job->lang['no_use_cities'], $job->lang['no_use_cities_desc']);
}

$PHP_SELF .= "cities";
$type = (empty($_REQUEST['type']))?'':$_REQUEST['type'];
$name = (empty($_REQUEST['name']))?'':$_REQUEST['name'];
$alt_name = (empty($_REQUEST['alt_name']))?'':job_totranslit($_REQUEST['alt_name']);
$hidden_array['subaction'] = 'add';

switch ($subaction) 
{
	case "add":
		if ($name)
		{
			if (!$alt_name)
				$alt_name = job_totranslit($name);
						
			switch ($type) 
			{
				case "country":
					$dbase->Insert('job_countries', array('name' => $name, 'alt_name' => $alt_name));
					Cache::ClearArrayCache('countries');
					break;
					
				case "city":
					if ($job->use_country && empty($_REQUEST['country_id']))
						$tpl->msg($job->lang['cities_add_city'], $job->lang['cities_no_country'], $PHP_SELF);
						
					$dbase->Insert('job_cities', array('name' => $name, "alt_name" => $alt_name, "country_id" => $_REQUEST['country_id']));
					Cache::ClearArrayCache('cities_c' . $_REQUEST['country_id']);
					break;
			
				default:
					break;
			}
			$tpl->msg($job->lang['add'], $job->lang['add_desc_ok'], $PHP_SELF);
		}
		else 
			$tpl->msg($job->lang['error'], $job->lang['cities_name_empty'], $PHP_SELF);
		break;
		
	case "edit":
		if ($id)
		{
			switch ($type) 
			{
				case "country":
					$edit = $dbase->SelectOne('job_countries', array("*"), array("id" => $id));
					break;
					
				case "city":
					$edit = $dbase->SelectOne('job_cities', array("*"), array("id" => $id));
					break;
			
				default:
					break;
			}
			$hidden_array['subaction'] = 'save';
			$hidden_array['id'] = $id;
			$job->lang['btn_add'] = $job->lang['btn_save'];
		}
		else 
			$tpl->msg($job->lang['error'], $job->lang['cities_no_select'], $PHP_SELF);
		break;
		
	case "save":
		if ($name && $id)
		{
			if (!$alt_name)
				$alt_name = job_totranslit($name);
				
			switch ($type) 
			{
				case "country":
					$dbase->Update('job_countries', array('name' => $name, 'alt_name' => $alt_name), array("id" => $id));
					Cache::ClearArrayCache('countries');
					break;
					
				case "city":
					if ($job->use_country && empty($_REQUEST['country_id']))
						$tpl->msg($job->lang['error'], $job->lang['cities_no_country'], $PHP_SELF);
						
					$dbase->Update('job_cities', array('name' => $name, "country_id" => $_REQUEST['country_id'], 'alt_name' => $alt_name), array("id" => $id));
					Cache::ClearArrayCache('cities_c' . $_REQUEST['country_id']);
					break;
			
				default:
					break;
			}
			$tpl->msg($job->lang['save'], $job->lang['save_desc_ok'], $PHP_SELF);
		}
		else 
			$tpl->msg($job->lang['error'], $job->lang['cities_name_empty'], $PHP_SELF);
		break;
		
	case "del":
		if ($id)
		{
			switch ($type) 
			{
				case "country":
					$dbase->Delete('job_countries', array("id" => $id));
					$dbase->Delete('job_cities', array("country_id" => $id));
					$dbase->Delete('job_resumes', array("country_id" => $id));
					$dbase->Delete('job_vacancies', array("country_id" => $id));
					$dbase->Select('job_companies', array("company_id"), array("country_id" => $id));
					break;
					
				case "city":
					$dbase->Delete('job_cities', array("id" => $id));
					$dbase->Delete('job_resumes', array("city_id" => $id));
					$dbase->Delete('job_vacancies', array("city_id" => $id));
					$dbase->Select('job_companies', array("company_id"), array("city_id" => $id));
					break;
			
				default:
					break;
			}
			$comp_id = array();
			while ($row = $dbase->FetchArray())
			{
				$comp_id[] = $row['company_id'];
			}
			
			if ($comp_id)
			{
			    $dbase->SetWhere('company_id', $comp_id, "IN", 'job_vacancies');
			    $dbase->Delete('job_vacancies');
			    $dbase->SetWhere('company_id', $comp_id, "IN", 'job_companies');
			    $dbase->Delete('job_companies');
			}
			
			Cache::ClearAllCache();
			$tpl->msg($job->lang['del'],  $job->lang['del_desc_ok'], $PHP_SELF);
		}
		else 
			$tpl->msg($job->lang['error'], $job->lang['cities_no_select'], $PHP_SELF);
		break;

	default:
		break;
}

$JScript = <<<JS
<script type="text/javascript">
$.metadata.setType("attr", "validate");
$(document).ready(function()
{
	$("#country tr:nth-child(even), #city tr:nth-child(even)").addClass("even");
	$("#country tbody tr, #city tbody tr").hover(function()
	{
		$(this).addClass("over");
	}, function()
	{
		$(this).removeClass("over");
	});
	
	$('#edit_country').validate();
	$('#edit_city').validate();
	
	$("#country").find("td:not(:has(a))").click(function()
	{
		if ($(this).parent("tr").next().children("td").is("[colspan='4']"))
		{
			$(this).parent("tr").find("div").toggleClass("minus");
			$(this).parent("tr").next().toggle();
		}
		else
		{
			var id = $(this).parent("tr").attr("id");
			$(this).parent("tr").find("div").toggleClass("loader");
			$(this).parent("tr").after("<tr><td></td></tr>").next().hide();
			$(this).parent("tr").next().find("td").attr("colSpan", "4").load(dle_root + "engine/job/ajax.php", {'id':id, 'action':"GetCityEdit"}, function()
			{
			  $(this).parent("tr").show();
				$("div.loader").toggleClass("loader");
				$("#city_" + id + " tr:nth-child(even)").addClass("even");
				$("#city_" + id + " tr").hover(function()
				{
					$(this).addClass("over");
				}, function()
				{
					$(this).removeClass("over");
				});
			});
			$(this).parent("tr").find("div").toggleClass("minus");
		}
	});
	$("#country tbody").find("td:not(:has(a))").css("cursor", "pointer");
});
</script>
JS;

$tpl->header($job->lang['set_country'], true, $JScript, array(), array(
																$config['http_home_url'] . 'engine/job/javascript/jquery.metadata.js',
																$config['http_home_url'] . 'engine/job/javascript/jquery.validate.js',
																$config['http_home_url'] . 'engine/job/javascript/messages_ru.js',
																));

if ($job->use_country && (($hidden_array['subaction'] == "save" && $type == "country") || $hidden_array['subaction'] == "add"))
{
	$tpl->OpenTable();
	$tpl->OpenSubtable($job->lang['set_country']);
	$tpl->OTable();
	$tpl->OpenForm('', $hidden_array + array("type" => 'country'), 'id="edit_country"');
	$tpl->echo = FALSE;
	
	echo $tpl->row(array('align="right"' => $job->lang['country_name'] . $tpl->InputText('name', $edit['name'], 'size="35" validate="required:true"') . " " . 
											$job->lang['country_alt_name'] . $tpl->InputText('alt_name', $edit['alt_name'], 'size="35"') . " " . 
											$tpl->InputSubmit($job->lang['btn_add']))
					, false);
	
	$tpl->echo = TRUE;
	$tpl->CTable();
	$tpl->CloseSubtable();
	$tpl->CloseForm();
	$tpl->CloseTable();
}

if (!($hidden_array['subaction'] == "save" && $type == "country"))
{
	
	$tpl->OpenTable();
	$tpl->OpenSubtable($job->lang['set_city']);
	$tpl->OTable();
	$tpl->OpenForm('', $hidden_array + array("type" => 'city'), 'id="edit_city"');
	$tpl->echo = FALSE;

	if ($job->use_country)
	{
		if (!$job->countries)
			$job->GetCountries();
		$select_country = $tpl->selection($job->countries, 'country_id', $edit['country_id']) . " ";
	}
	else 
		$select_country = '';
		
			
	echo $tpl->row(array('align="right"' => $select_country . 
											$job->lang['city_name'] . $tpl->InputText('name', $edit['name'], 'size="35" validate="required:true"') . " " . 
											$job->lang['city_alt_name'] . $tpl->InputText('alt_name', $edit['alt_name'], 'size="35"') . " " . 
											$tpl->InputSubmit($job->lang['btn_add']))
						, false);
		
	$tpl->echo = TRUE;
	$tpl->CTable();
	$tpl->CloseSubtable();
	$tpl->CloseForm();
	$tpl->CloseTable();
}

$type = 'city';
if ($job->use_country)
{
	$values = $job->GetCountries();
	$job->lang['del_confirm_city'] = $job->lang['del_confirm_country'];
	$type = 'country';
}
else 
	$values = $job->GetCities();
	
	
if ($values)
{
	$tpl->OpenTable();
	$tpl->OpenSubtable('');
	$tpl->OTable(array( "",
						"ID",
						$job->lang['cities_name'],
						'width="20px;"' => $job->lang['action']), "id=\"$type\"");
						
	if ($job->use_country)
		$img = "<div class=\"plus\" > </div>";
	else 
		$img = '';
			
	foreach ($values as $id=>$name)
	{
		$tpl->row(array('style="width:30px;" align="center"' => $img, 
						$id, 
						$name, 
						"[<a href=\"{$PHP_SELF}&subaction=edit&type={$type}&id=$id\">{$job->lang['action_edit']}</a>]
						[<a OnClick=\"if (confirm('{$job->lang['del_confirm_city']}'))return true; else return false;\" href=\"{$PHP_SELF}&subaction=del&type={$type}&id=$id\">{$job->lang['action_del']}</a>]"), 
						false, 
						false, 
						"id=\"$id\"");
	}
	
	$tpl->CTable();
	$tpl->CloseSubtable();
	$tpl->CloseTable();
}

?>