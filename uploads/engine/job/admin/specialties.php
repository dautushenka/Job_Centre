<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

$PHP_SELF .= "specialties";
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
			
			if ($type == "sphere")
			{
				$dbase->Insert('job_spheres', array("name" => $name, "alt_name" => $alt_name));
				Cache::ClearArrayCache('spheres');
			}
			elseif (intval($_REQUEST['sphere_id']))
			{
				$dbase->Insert('job_specialties', array("name" => $name, "alt_name" => $alt_name, "sphere_id" => $_REQUEST['sphere_id']));
				Cache::ClearArrayCache('specialties_s' . $_REQUEST['sphere_id']);
			}
			else 
				$tpl->msg($job->lang['error'], $job->lang['no_spheres'], $PHP_SELF);
				
			$tpl->msg($job->lang['add'], $job->lang['add_desc'], $PHP_SELF);
		}
		else 
			$tpl->msg($job->lang['error'], $job->lang['specialties_name_empty'], $PHP_SELF);
		break;
		
	case "edit":
		if ($id)
		{
			if ($type == "sphere")
				$edit = $dbase->SelectOne('job_spheres', array("*"), array("id" => $id));
			else 
				$edit = $dbase->SelectOne('job_specialties', array("*"), array("id" => $id));
				
			$hidden_array['subaction'] = 'save';
			$hidden_array['id'] = $id;
			$job->lang['btn_add'] = $job->lang['btn_save'];
		}
		else 
			$tpl->msg($job->lang['error'], $job->lang['specialties_no_select'], $PHP_SELF);
		break;

	case "save":
		if ($name && $id)
		{
			if (!$alt_name)
				$alt_name = job_totranslit($name);
				
			if ($type == "sphere")
			{
				$dbase->Update('job_spheres', array("name" => $name, "alt_name" => $alt_name), array('id' => $id));
				Cache::ClearArrayCache('spheres');
			}
			else 
			{
				if (empty($_REQUEST['sphere_id']))
					$tpl->msg($job->lang['error'], $job->lang['specialties_no_sphere'], $PHP_SELF);
					
				$dbase->Update('job_specialties', array("name" => $name, "alt_name" => $alt_name, "sphere_id" => $_REQUEST['sphere_id']), array("id" => $id));
				Cache::ClearArrayCache('specialties_s' . $_REQUEST['sphere_id']);
			}
			$tpl->msg($job->lang['save'], $job->lang['save_desc'], $PHP_SELF);
		}
		else 
			$tpl->msg($job->lang['error'], $job->lang['specialties_name_empty'], $PHP_SELF);
		break;
		
	case "del":
		if ($id)
		{
			if ($type == "sphere")
			{
				$dbase->Delete('job_spheres', array("id" => $id));
				$dbase->Delete('job_specialties', array("sphere_id" => $id));
				$dbase->Delete('job_resumes', array("sphere_id" => $id));
				$dbase->Delete('job_vacancies', array("sphere_id" => $id));
			}
			else 
			{
				$dbase->Delete('job_specialties', array("id" => $id));
				$dbase->Delete('job_vacancies', array("specialty_id" => $id));
				$dbase->Delete('job_resumes', array("specialty_id" => $id));
			}
			Cache::ClearAllCache();
			$tpl->msg($job->lang['del'],  $job->lang['del_desc_ok'], $PHP_SELF);
		}
		else 
			$tpl->msg($job->lang['error'], $job->lang['specialties_no_select'], $PHP_SELF);
		break;
		
	default:
		break;
}

$JScript = <<<JS
<script type="text/javascript">
$.metadata.setType("attr", "validate");
$(document).ready(function()
{
	$("#spheres tr:nth-child(even)").addClass("even");
	$("#spheres tbody tr").hover(function()
	{
		$(this).addClass("over");
	}, function()
	{
		$(this).removeClass("over");
	});
	$("#spheres").find("td:not(:has(a))").click(function()
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
			$(this).parent("tr").next().find("td").attr("colSpan", "4").load(dle_root + "engine/job/ajax.php", {'id':id, 'action':"GetSpecialtiesEdit"}, function()
			{
				$(this).parent("tr").show();
				$("div.loader").toggleClass("loader");
				$("#specialty_" + id + " tr:nth-child(even)").addClass("even");
				$("#specialty_" + id + " tr").hover(function()
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
	$("#spheres tbody").find("td:not(:has(a))").css("cursor", "pointer");
	$('#edit_sphere').validate();
	$('#edit_specialty').validate();
});
</script>
JS;

$tpl->header($job->lang['set_specialty'], true, $JScript, array(), array(
																		$config['http_home_url'] . 'engine/job/javascript/jquery.metadata.js',
																		$config['http_home_url'] . 'engine/job/javascript/jquery.validate.js',
																		$config['http_home_url'] . 'engine/job/javascript/messages_ru.js',
																		));

if (($hidden_array['subaction'] == "save" && $type == "sphere") || $hidden_array['subaction'] == "add")
{
	$tpl->OpenTable();
	$tpl->OpenSubtable($job->lang['set_sphere']);
	$tpl->OpenForm('', $hidden_array + array('type'=> 'sphere'), 'id="edit_sphere"');
	$tpl->OTable();
	$tpl->echo = FALSE;
	
	echo $tpl->row(array('align="right"' => $job->lang['sphere_name'] . $tpl->InputText('name', $edit['name'], 'size="35" validate="required:true"') . " " . 
											$job->lang['sphere_alt_name'] . $tpl->InputText('alt_name', $edit['alt_name'], 'size="35"') . " " . 
											$tpl->InputSubmit($job->lang['btn_add']))
						, false);
	
	$tpl->echo = TRUE;
	$tpl->CTable();
	$tpl->CloseForm();
	$tpl->CloseSubtable();
	$tpl->CloseTable();
}

if ((($hidden_array['subaction'] == "save" && $type == "specialty") || $hidden_array['subaction'] == "add") && $job->GetSpheres())
{
	$tpl->OpenTable();
	$tpl->OpenSubtable($job->lang['set_specialty']);
	$tpl->OpenForm('', $hidden_array + array('type'=> 'specialty'), 'id="edit_specialty"');
	$tpl->OTable();
	$tpl->echo = FALSE;
	
	echo $tpl->row(array('align="right"' => $tpl->selection($job->GetSpheres(), "sphere_id", $edit['sphere_id']) . " " . 
											$job->lang['specialty_name'] . $tpl->InputText('name', $edit['name'], 'size="35" validate="required:true"') . " " . 
											$job->lang['specialty_alt_name'] . $tpl->InputText('alt_name', $edit['alt_name'], 'size="35"') . " " . 
											$tpl->InputSubmit($job->lang['btn_add']))
						, false);
	
	$tpl->echo = TRUE;
	$tpl->CTable();
	$tpl->CloseForm();
	$tpl->CloseSubtable();
	$tpl->CloseTable();
}

if ($job->GetSpheres())
{
	$tpl->OpenTable();
	$tpl->OpenSubtable('');
	$tpl->OTable(array( "",
						"ID",
						$job->lang['specialties_name'],
						'width="20px;"' => $job->lang['action']), "id=\"spheres\"");
	$tpl->echo = FALSE;
	
	foreach ($job->GetSpheres() as $id=>$name)
	{
		echo $tpl->row(array('style="width:30px;" align="center"' => "<div class=\"plus\" > </div>", 
																	 $id, 
																	 $name, 
																	 "[<a href=\"{$PHP_SELF}&subaction=edit&type=sphere&id=$id\">{$job->lang['action_edit']}</a>]
																	 [<a OnClick=\"if (confirm('{$job->lang['del_confirm_sphere']}'))return true; else return false;\" href=\"{$PHP_SELF}&subaction=del&type=sphere&id=$id\">{$job->lang['action_del']}</a>]"),
						 false, false, "id=\"$id\"");
	}
	
	$tpl->echo = TRUE;
	$tpl->CTable();
	$tpl->CloseSubtable();
	$tpl->CloseTable();
}
?>