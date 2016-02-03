<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

require(ENGINE_DIR . "/job/classes/thumb.class.php");
require_once ENGINE_DIR . '/job/classes/Fields.php';


$validete_array = array(

                        "photo"             => array( "valid" => array("accept" => "'jpeg?|png|gif|jpg'"
                                                                    ),
                                                      "messages" => array("accept" => $job->lang['resume_error_type_image'],
                                                                          "required" => $job->lang['resume_error_photo']
                                                                          )
                                                    ),
						"sphere_id" 		=> array( "valid" => array("required" => "true"
																	),
													  "messages" => array("required" => $job->lang['sel_sphere']
													 					 )
													),
						"specialty_id" 		=> array( "valid" => array("required" => "true"
																	),
													 "messages" => array("required" => $job->lang['sel_specialty']
													 					 )
													),
						"country_id" 		=> array("valid" => array("required" => "true"
																	),
													 "messages" => array(
													 					 "required" => $job->lang['sel_country']
													 					 )
													 ),
						"city_id" 			=> array("valid" => array("required" => "true"
																	),
													 "messages" => array(
													 					 "required" => $job->lang['sel_city']
													 					 )
													 ),
						"contact_person" => array("valid" => array("required" => "true"
																	),
													 ),
						);


$PHP_SELF .= "resumes";
$where = (empty($_REQUEST['where']))?array():$_REQUEST['where'];
$edit = ConvertTypeToValue($TABLES['job_resumes']);
$per_page = (intval(!empty($_REQUEST['per_page'])))?intval($_REQUEST['per_page']):50;;
$page = (intval(!empty($_REQUEST['page'])))?intval($_REQUEST['page']):0;
$hidden_array['subaction'] = 'add';

$xfields = new Fields(XFIELDS_RESUME, $job);

switch ($subaction)
{
	case "add":
		$job->NewResume($_POST);
		
		if (!$job->Errors)
		{
		    Cache::ClearHTMLCache('main');
		    Cache::ClearArrayCache('stats');
			$tpl->msg($job->lang['add_resume'], $job->lang['add_resume_ok'], $PHP_SELF);
		}
			
		$edit = $_POST;
		break;
		
	case "edit":
		if (!$id)
			$tpl->msg($job->lang['error'], $job->lang['resume_not_found'], $PHP_SELF);
		
		$edit = $job->GetResumeForEdit($id);
		ConvertDBResultToType($TABLES['job_resumes'], $edit);
		
		foreach (array('age', 'salary_min', 'salary_max', 'date_exp') as $field)
		{
			if (!$edit[$field])
				$edit[$field] = '';
		}
		
		$hidden_array['id'] = $id;
		$hidden_array['subaction'] = "save";
		
		if ($edit['date_exp'])
			$edit['date_exp'] = date('Y-m-d H:i', $edit['date_exp']);
			
		$job->lang['btn_add'] = $job->lang['btn_save'];
		$hidden_array['referal'] = $_SERVER['HTTP_REFERER'];
		break;
		
	case "save":
		if (!$id)
			$tpl->msg($job->lang['error'], $job->lang['resume_not_found'], $PHP_SELF);
			
		$job->UpdateResume($_POST, $id);
		
		if (!$job->Errors)
			$tpl->msg($job->lang['edit_resume'], $job->lang['edit_resume_ok'], (empty($_POST['referal']))?$PHP_SELF:$_POST['referal']);
			
		$edit = $_POST;
		$hidden_array['id'] = $id;
		$hidden_array['subaction'] = "save";
		$hidden_array['referal'] = $_POST['referal'];
		$job->lang['btn_add'] = $job->lang['btn_save'];
		break;
		
	case "del":
		if (!$id)
			$tpl->msg($job->lang['error'], $job->lang['resume_not_found'], $PHP_SELF);
			
		$job->DeleteResume($id);
		Cache::ClearArrayCache('stats');
		$tpl->msg($job->lang['del_resume'], $job->lang['del_resume_ok'], $_SERVER['HTTP_REFERER']);
		break;
		
	default:
		$edit['date_exp'] = $edit['age'] = $edit['salary_min'] = $edit['salary_max'] = '';
		break;
}

if (!empty($job->config['resume_need_field']))
{
    foreach ($job->config['resume_need_field'] as $field)
    {
    	$validete_array[$field]['valid']['required'] = "true";
    	if (!empty($job->lang['resume_error_' . $field]))
    		$validete_array[$field]['messages']['required'] = $job->lang['resume_error_' . $field];
    }
}

$valid_fields = GetValidateRules($validete_array);

$JS = <<<JS
<script type="text/javascript">
$.metadata.setType("attr", "validate");
$(document).ready(function()
{
	$("#resumes tbody tr").hover(function()
	{
		$(this).addClass("over");
	}, function()
	{
		$(this).removeClass("over");
	});

	$('#form_add').validate(
	{
		rules:
			{
			{$valid_fields['rules']}
			},
		messages:
		{
			{$valid_fields['messages']}
		}
	});
	
	$('#specialty').autocomplete({
	       source: ajax_url + '?action=specialty',
	       autoFocus: true,
	       //multiple: true,
	       minLength: 3,
	       search: function( event, ui ) {
	        $('#specialty').autocomplete("option", "source", ajax_url + '?action=specialty&sphere=' + $('#sphere_id').val());
	        }
	       });
});
</script>
JS;

$tpl->header($job->lang['resumes'], true, $JS, array($config['http_home_url'] . 'engine/job/images/admin/jquery.autocomplete.css'), array(
																$config['http_home_url'] . 'engine/job/javascript/jquery.metadata.js',
																$config['http_home_url'] . 'engine/job/javascript/jquery.validate.js',
																$config['http_home_url'] . 'engine/job/javascript/messages_ru.js',
//																$config['http_home_url'] . 'engine/job/javascript/jquery.autocomplete.pack.js'
																));

if (empty($where))
{
	$tpl->echo = FALSE;
	
	
	if ($edit['id'])
		$data[] = array('', "<b>ID</b>" . $edit['id']);
		
	if ($edit['add_date'])
		$data[] = array($job->lang['resume_added'], langdate('j F Yã. â H:i', $edit['add_date']));
		
	$data[] = array($job->lang['sphere'], $tpl->selection($job->GetSpheres('add'), 'sphere_id', $edit['sphere_id'], 'id="sphere_id"'));
	$data[] = array($job->lang['specialty'], $tpl->InputText('specialty', $edit['specialty'], "id='specialty'"));
	//$data[] = array($job->lang['specialty'], $tpl->selection($job->GetSpecialties($edit['sphere_id']), 'specialty_id', $edit['specialty_id'], 'id="specialty_id"'));
	
	if ($job->use_country)
		$data[] = array($job->lang['country'], $tpl->selection($job->GetCountries('add'), 'country_id', $edit['country_id'], "id='country_id'"));
	
	if ($job->use_city)
		$data[] = array($job->lang['city'], $tpl->selection($job->GetCities($edit['country_id']), 'city_id', $edit['city_id'], "id='city_id'"));
		
	foreach (Resume::$fields as $field_name=>$values_array)
	{
		$data[] = array($job->lang[$field_name], $tpl->selection(array("" => $job->lang['no_show']) + $values_array, $field_name, $edit[$field_name]));
	}	
    
	$data[] = array($job->lang['sex'], $tpl->selection(array( 0  => $job->lang['no_show'],
                                                           "M" => $job->lang['sex_man'],
                                                           "W" => $job->lang['sex_woman']), 'sex', $edit['sex']));
	$data[] = array($job->lang['age'], $tpl->InputText('age', $edit['age'], 'size=10'));
	$data[] = array($job->lang['salary'], $job->lang['from'] . $tpl->InputText('salary_min', $edit['salary_min'], 'size=10') . $job->lang['to'] . $tpl->InputText('salary_max', $edit['salary_max'], 'size=10') . " " . $tpl->selection(Job::$currecy_array, 'currency', $edit['currency']), $job->lang['salary_desc']);
	$data[] = array($job->lang['description'], $tpl->TextArea('description', $edit['description'], 'style="width:70%;height:80px;"'));

    foreach ($xfields->DecodeFields($edit['xfields']) as $fid => $field)
    {
        if (!empty($auto->lang[$field['title']]))
        {
            $field['title'] = $auto->lang[$field['title']];
        }
        
        $data[] = array($field['title'], $field['html'], $field['description']);
    }
	
	
	if ($edit['photo'])
	   $data[] = array($job->lang['resume_photo'], "<img src=\"" . PHOTO_URL_PATCH . $edit['photo'] . "\" /><br />" . $tpl->InputCheckbox('del_photo', 1, $edit['del_photo']) . " " . $job->lang['delete_photo']);
 
    $data[] = array($job->lang['photo'], $tpl->InputFile('photo', "size=\"45\""));
	
	$data[] = array($job->lang['contact_person'], $tpl->InputText('contact_person', $edit['contact_person']));
	$data[] = array($job->lang['email'], $tpl->InputText('email', $edit['email']));
	$data[] = array($job->lang['phone'], $tpl->InputText('phone', $edit['phone']));
	$data[] = array($job->lang['res_exp_date'], $tpl->InputCheckbox('allow_site', 1, $edit['allow_site']) . " " . $tpl->InputText('date_exp', $edit['date_exp'], "id='date_exp' data-rel=\"calendar\""));
	
	$tpl->echo = TRUE;
	
	if ($job->Errors)
	{
		$tpl->OpenTable();
		echo $job->lang['current_errors'] . "<ol>";
		foreach ($job->Errors as $error)
		{
			echo "<li>" . $error . "</li>";
		}
		echo "</ol>";
		$tpl->CloseTable();
	}
	
	$tpl->OpenTable();
	$tpl->OpenSubtable($job->lang['add_resume']);
	$tpl->OpenForm('', $hidden_array, 'id="form_add" enctype="multipart/form-data"');
	$tpl->OTable();
	
	foreach ($data as $row)
	{
		if (!empty($row[2]))
		$hit = "&nbsp;<a href=\"#\" class=\"hintanchor\" onMouseover=\"showhint('{$row[2]}', this, event, '320px')\">[?]</a>";
	else 
		$hit = "";
		
		echo <<<HTML
	   <tr>
	       <td height="29" style="padding-left:5px;">{$row[0]}</td>
	       <td>{$row[1]} $hit</td>
	   </tr>
HTML;
	}
	
	$tpl->CTable();
	$tpl->CloseSubtable($job->lang['btn_add']);
	$tpl->CloseForm();
	$tpl->CloseTable();
}

if ($hidden_array['subaction'] != "save")
{
	if (!empty($_REQUEST['specialty_id']))
	{
		$specialty_str = '';
		foreach ($job->GetSpecialties($where['sphere_id']) as $id=>$name)
		{
			if (@in_array($id, $_REQUEST['specialty_id']))
			{
			    if ($specialty_str)
				    $specialty_str .= ", ";
				    
				$specialty_str .= "{id:$id, name:'$name'}";
			}
		}
		
		if ($specialty_str)
echo <<<JS
<script type="text/javascript">
$(document).ready(function()
{
	SetSpecialties(new Array($specialty_str));
});
</script>
JS;
		
		$where += array('specialty_id' => $_REQUEST['specialty_id']);
	}

	$job->SearchResume($where, array("count" => $per_page));
}

if ($hidden_array['subaction'] != "edit" && $job->resumes)
{
	unset($hidden_array['subaction']);
	$tpl->OpenTable();
	$tpl->OpenSubtable($job->lang['res_filter']);
	$tpl->OpenGetForm($PHP_SELF, $hidden_array);
	$tpl->echo = FALSE;
	
	echo "<table width=\"100%\" id=\"filter\"><tr>
					<td class=\"title\"><b>ID</b></td><td class=\"value\"> " . $tpl->InputText('where[id]', $where['id']) . "</td>
					<td class=\"title\"><b>{$job->lang['salary']}</b></td><td> " . $job->lang['from'] . $tpl->InputText('where[salary]', $where['salary'], "size='10'") . " " . $tpl->selection(Job::$currecy_array, 'where[currency]', $where['currency']) ."</td>
				</tr>
				<tr>
					<td class=\"title\"><b>{$job->lang['age']}</b></td><td class=\"value\"> " . $job->lang['from'] . $tpl->InputText('where[age_min]', $where['age_min'], "size='10'") . $job->lang['to'] . $tpl->InputText('where[age_max]', $where['age_max'], "size='10'") . "</td>
					<td class=\"title\"><b>{$job->lang['res_descr']}</b></td><td class=\"value\"> " . $tpl->InputText('where[description]', urldecode($where['description']), 'size="40"') . "</td>
				</tr>
				<tr>
					<td class=\"title\"><b>{$job->lang['sel_sphere']}</b></td><td class=\"value\"> " . $tpl->selection($job->GetSpheres('search'), 'where[sphere_id]', $where['sphere_id'], 'id="sphere_id_search"') . "</td>
					<td class=\"title\"><b>{$job->lang['sel_specialty']}</b> </td><td class=\"value\">" . $tpl->selection($job->GetSpecialties($where['sphere_id'], 'search'), 'specialties', '',  'id="specialty_id_search"') . "</td>
				</tr>
				</table>
				";
	
	$tpl->echo = TRUE;
	$tpl->CloseSubtable($job->lang['btn_search']);
	$tpl->CloseForm();
	$tpl->CloseTable();
	
	$tpl->echo = FALSE;
	$nav = $tpl->navigation($page, $per_page, $job->resumes_count, $PHP_SELF . PageURL($where));
	$tpl->echo = TRUE;
	
	$tpl->OpenTable();
	$tpl->OpenSubtable($job->lang['resumes']);
	$tpl->OTable(array( "ID",
						(!$job->use_country && !$job->use_city)?"":$job->lang['vac_country_city'],
						$job->lang['specialty'],
						$job->lang['age'],
						$job->lang['salary'],
						$job->lang['res_added'],
						$job->lang['res_exp_date'],
						$job->lang['action']
						), "id='resumes'");
						
	if ($nav)
		$tpl->row($nav, true, true);
	
	foreach ($job->resumes as $res)
	{
		$tpl->row(array('style="padding: 2px;"' => $res->allow_site?$res->id:"<font color='red'>" . $res->id . "</font>", 
						($res->country_name?$res->country_name . "/":'') . $res->city_name,
						$res->specialty_name, 
						$res->age?$res->age:" - ", 
						$res->salary?$res->salary:" - ", 
						$res->add_date, 
						$res->date_exp, 
						"[<a href=\"$PHP_SELF&subaction=edit&id={$res->id}\" >{$job->lang['action_edit']}</a>]
						[<a OnClick=\"if (confirm('{$job->lang['vac_del_confirm']}'))return true; else return false;\" href=\"$PHP_SELF&subaction=del&id={$res->id}\" >{$job->lang['action_del']}</a>]"));
	}
	
	if ($nav)
		$tpl->row($nav, false, true);
	
	$tpl->CTable();
	$tpl->CloseSubtable();
	$tpl->CloseTable();
}
elseif (!empty($where))
	$tpl->msg($job->lang['vacancies'], $job->lang['vacancies_not_found'], $PHP_SELF);

?>