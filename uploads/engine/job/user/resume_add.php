<?php


if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}


$JS = '';

///////////// Permission options ////////////

if (!$job->member['id'] && !$job->config['resume_allow_reg'])
{
    $template->msg($job->lang['error'], $job->lang['need_authorization']);
    return 0;
}

if (!MODER_EDIT && ($action == "resume_add" || $action == "resume_doadd") && !CheckGroupAllow('resume_allow_add'))
{
    $template->msg($job->lang['resume_add'], $job->lang['resume_add_access_denied']);
    return 0;
}

if (!MODER_EDIT && ($action == "resume_edit" || $action == "resume_save") && !CheckGroupAllow('resume_allow_edit'))
{
    $template->msg($job->lang['resume_edit'], $job->lang['resume_edit_access_denied']);
    return 0;
}

if (!MODER_DEL && $action == "resume_del" && !CheckGroupAllow('resume_allow_del'))
{
    $template->msg($job->lang['resume_del'], $job->lang['resume_del_access_denied']);
    return 0;
}

if ($job->config['resume_allow_one_resume'] && ($action == "resume_add" || $action == "resume_doadd") && $dbase->SelectOne("job_resumes", array("*"), array('user_id' => $job->member['id'])))
{
    $template->msg($job->lang['resume_add'], $job->lang['resume_only_one']);
    return 0;
}


include_once(ENGINE_DIR . "/job/classes/parse.class.php");
require(ENGINE_DIR . "/job/classes/thumb.class.php");
/////////////////////////////////////////////

$job->xfields = new Fields(XFIELDS_RESUME, $job);

$validete_options = array();
$validete_array = array(
                        "photo"              => array( "valid" => array("accept" => "'jpeg?|png|gif|jpg'"
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
						);

switch ($action)
{
    case 'resume_doadd':
        if (empty($_SESSION['resume_form_code']) || $_SESSION['resume_form_code'] != $_POST['resume_form_code'])
        {
            $template->msg($job->lang['error'], $job->lang['wrong_form_code']);
            return 0;
        }
        if ($id = $job->NewResume($_POST))
        {
            if (CheckGroupAllow('resume_add_no_moder'))
            {
                $template->msg($job->lang['resume_add'], $job->lang['resume_add_ok']);
                Cache::ClearHTMLCache('main');
                Cache::ClearArrayCache('stats');
            }
            else
            {
                $template->msg($job->lang['resume_add'], $job->lang['resume_add_on_moder']);
            }
            SendMail('resume', $id);
            unset($_SESSION['resume_form_code']);
            return 0;
        }
        $edit = $_POST;
        break;
        
    case "resume_edit":
        if (!$id || !($edit = $job->GetResumeForEdit($id)))
        {
            $template->msg($job->lang['resume_edit'], $job->lang['resume_not_found']);
            return 0;
        }
        
        if (!MODER_EDIT && $job->member['id'] != $edit['user_id'])
        {
            $template->msg($job->lang['resume_edit'], $job->lang['resume_wrong_author']);
            return 0;
        }
        
        foreach (array('age_min', 'salary_min', 'salary_max') as $field)
		{
			if (!$edit[$field])
				$edit[$field] = '';
		}
		
		$hidden_array['id'] = $id;
        $hidden_array['action'] = "resume_save";
        break;
        
    case "resume_save":
        if (!$id)
        {
            $template->msg($job->lang['resume_edit'], $job->lang['resume_not_found']);
            return 0;
        }
        
        if ($job->UpdateResume($_POST, $id))
        {
            $template->msg($job->lang['resume_edit'], $job->lang['resume_edit_ok']);
            return 1;
        }
            
        $edit = $_POST;
		$hidden_array['id'] = $id;
		$hidden_array['action'] = "resume_save";
        break;
        
    case "resume_del":
        if (!$selected)
        {
            $template->msg($job->lang['resume_del'], $job->lang['resume_not_selected']);
            return 0;
        }
        
        $job->DeleteResume($selected);
        Cache::ClearArrayCache('stats');
        $template->msg($job->lang['resume_del'], $job->lang['resume_del_ok']);
        return 1;
        break;
        
    default:
        $hidden_array['action'] = "resume_doadd";
        $edit = ConvertTypeToValue($TABLES['job_resumes']);
        foreach (array('age', 'salary_min', 'salary_max') as $field)
		{
			$edit[$field] = '';
		}
        break;
}

$hidden_array['resume_form_code'] = $_SESSION['resume_form_code'] = empty($edit['resume_form_code'])?uniqid(microtime(1)):$edit['resume_form_code'];

foreach ($job->config['resume_need_field'] as $field)
{
	$validete_array[$field]['valid']['required'] = "true";
	if (!empty($job->lang['resume_error_' . $field]))
		$validete_array[$field]['messages']['required'] = $job->lang['resume_error_' . $field];
}


if ($job->Errors)
{
	$errors =  "  <font color=\"red\" >" . $job->lang['isset_error'] . "</font><ol>";
	foreach ($job->Errors as $error)
	{
		$errors .= "<li>" . $error . "</li>";
	}
	$errors .= "</ol>";
	$template->msg($job->lang['error'], $errors);
}

$template->load('resume_add');

$set_param = array(
                    '{sphere}'      => $template->Selection($job->GetSpheres('add'), 'sphere_id', $edit['sphere_id'], "id='sphere_id'"),
                    '{specialty}'   => $template->InputText('specialty', $edit['specialty'], "id='specialty'"),
//                    '{specialty}'   => $template->Selection($job->GetSpecialties($edit['sphere_id'], 'add'), 'specialty_id', $edit['specialty_id'], "id='specialty_id'"),
                    '{currency}'    => $template->Selection(Job::$currecy_array, 'currency', $edit['currency']),
                    '{age}'         => $edit['age'],
                    '{salary_min}'  => $edit['salary_min'],
                    '{salary_max}'  => $edit['salary_max'],
                    '{sex}'         => $template->Selection(array( 0  => $job->lang['no_show'],
                                                                  "M" => $job->lang['sex_man'],
                                                                  "W" => $job->lang['sex_woman']), 'sex', $edit['sex']),
                    '{description}' => $edit['description'],
                );
                
$set_param["{xfields}"] = '';
foreach ($job->xfields->DecodeFields($edit['xfields']) as $fid => $field)
{
    if (!empty($job->lang[$field['title']]))
    {
        $field['title'] = $job->lang[$field['title']];
    }
    
    $set_param["{xfield_{$fid}_title}"] = $field['title'];
    $set_param["{xfield_{$fid}_descr}"] = $field['description'];
    $set_param["{xfield_{$fid}_html}"] = $set_param["{xfield_$fid}"] = $field['html'];
    
    $set_param["{xfields}"] .= $field['title'] . ": " . $field['html'] . "<br />";
} 

SetCountryCityBlock($set_param);
                
foreach (Resume::$fields as $field=>$values_array)
{
    $set_param["{" . $field . "}"] = $template->Selection(array("" => $job->lang['no_show']) + $values_array, $field, $edit[$field]);
}
                
$set_param['{contact_person}'] = $template->InputText('contact_person', $edit['contact_person']);
$set_param['{email}'] = $template->InputText('email', $edit['email'], "validate=\"email:true\"");
$set_param['{phone}'] = $template->InputText('phone', $edit['phone']);

if ($hidden_array['action'] == "resume_doadd" && CheckGroupAllow('resume_allow_change_exp'))
{
    $template->SetBlock('count_day');
    $set_param['{count_day}'] = $template->Selection(Resume::$count_day_array, 'count_day', $job->config['resume_default_day']);
}

if ($hidden_array['action'] == "resume_doadd" && CheckGroupAllow('resume_add_capcha'))
{
   SetCapchaBlock($set_param);
}

if (!$job->member['id'] && $hidden_array['action'] =='resume_doadd' && $job->config['resume_allow_reg'])
{
    SetRegisterBlock($set_param);
}

if ($hidden_array['action'] == "resume_save" && CheckGroupAllow('resume_allow_extend'))
{
    $template->SetBlock('extend');
    $set_param['{count_extend}'] = $template->Selection(array('-1' => $auto->lang['no_extend']) + Resume::$count_day_array, 'count_extend', '-1');
}

$template->SetStyleScript(array('engine/job/images/admin/jquery.autocomplete.css'), 
                          array('engine/job/javascript/jquery.metadata.js',
								'engine/job/javascript/jquery.validate.js',
								'engine/job/javascript/messages_ru.js',
//                                $config['http_home_url'] . 'engine/job/javascript/jquery.autocomplete.pack.js'
                          ));

$valid_fields = GetValidateRules($validete_array, $validete_options);

$JS = <<<JS
<script type="text/javascript">
$.metadata.setType("attr", "validate");
$(document).ready(function()
{
	$('#specialty').autocomplete(
	       {
	       source: ajax_url + '?action=specialty',
	       autoFocus: true,
	       //multiple: true,
	       minLength: 3,
	       search: function( event, ui ) {
	        $('#specialty').autocomplete("option", "source", ajax_url + '?action=specialty&sphere=' + $('#sphere_id').val());
	        }
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
});
$JS
</script>
JS;

$template->SetForm($hidden_array, $template->main_url, 'POST', 'id="form_add" enctype="multipart/form-data"');
$template->Set($set_param);

if ($hidden_array['action'] == "resume_doadd")
{
    $template->SetBlock('add');
}
else 
{
    $template->SetBlock('edit');
}


$template->Compile('content', $JS);

if ($hidden_array['action'] == "resume_doadd")
	$template->TitleSpeedBar($job->lang['add_resume']);
else 
	$template->TitleSpeedBar($job->lang['edit_resume']);
?>