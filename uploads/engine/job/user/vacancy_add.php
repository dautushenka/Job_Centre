<?php


if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

$JS = '';

$company = Company::GetCompany_by_user();

///////////// Permission options ////////////

if ($job->config['vacacny_only_company'] && !$company)
{
    if (CheckGroupAllow('company_allow_manage'))
    {
        $template->msg($job->lang['error'], $job->lang['vacancy_need_company']);
    }
    else
    {
        $template->msg($job->lang['error'], $job->lang['vacancy_add_access_denied']);
    }
    return 0;
}

if (!$job->member['id'] && !$job->config['vacancy_allow_reg'])
{
    $template->msg($job->lang['error'], $job->lang['need_authorization']);
    return 0;
}

if (!MODER_EDIT && ($action == "vacancy_add" || $action == "vacancy_doadd") && !CheckGroupAllow('vacancy_allow_add'))
{
    $template->msg($job->lang['vacancy_add'], $job->lang['vacancy_add_access_denied']);
    return 0;
}

if (!MODER_EDIT && ($action == "vacancy_edit" || $action == "vacancy_save") && !CheckGroupAllow('vacancy_allow_edit', $company?-1:0))
{
    $template->msg($job->lang['vacancy_edit'], $job->lang['vacancy_edit_access_denied']);
    return 0;
}

if (!MODER_DEL && $action == "vacancy_del" && !CheckGroupAllow('vacancy_allow_del', $company?-1:0))
{
    $template->msg($job->lang['vacancy_del'], $job->lang['vacancy_del_access_denied']);
    return 0;
}


include_once(ENGINE_DIR . "/job/classes/parse.class.php");

$job->xfields = new Fields(XFIELDS_VACANCY, $job);

/////////////////////////////////////////////


$validete_options = array();
$validete_array = array(
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

if (empty($company))
    $validete_array['contact_person,email,phone'] = array( 
                                                                "valid" => array("required" => "true"
																            	),
                                                          );
switch ($action)
{
    case 'vacancy_doadd':
        if (empty($_SESSION['vacancy_form_code']) || $_SESSION['vacancy_form_code'] != $_POST['vacancy_form_code'])
        {
            $template->msg($job->lang['error'], $job->lang['wrong_form_code']);
            return 0;
        }
        
        if ($id = $job->NewVacancy($_POST, $company))
        {
            if (CheckGroupAllow('vacancy_add_no_moder'))
            {
                $template->msg($job->lang['vacancy_add'], $job->lang['vacancy_add_ok']);
                
                if ($company)
                {
                    Cache::ClearArrayCache('vacancies_company_' . $company->company_id);
                }
                
                Cache::ClearHTMLCache('main');
                Cache::ClearArrayCache('stats');
            }
            else
            {
                $template->msg($job->lang['vacancy_add'], $job->lang['vacancy_add_on_moder']);
            }
            
            SendMail('vacancy', $id);
            unset($_SESSION['vacancy_form_code']);
            return 0;
        }
        $edit = $_POST;
        break;
        
    case "vacancy_edit":
        if (!$id || !($edit = $job->GetVacancyForEdit($id)))
        {
            $template->msg($job->lang['vacancy_edit'], $job->lang['vacancy_not_found']);
            return 0;
        }
        
        if (!MODER_EDIT && $job->member['id'] != $edit['user_id'] && !empty($company) && $company->company_id != $edit['company_id'])
        {
            $template->msg($job->lang['vacancy_edit'], $job->lang['vacancy_wrong_author']);
            return 0;
        }
        
        foreach (array('age_min', 'age_max', 'salary_min', 'salary_max') as $field)
		{
			if (!$edit[$field])
				$edit[$field] = '';
		}
		
		$hidden_array['id'] = $id;
        $hidden_array['action'] = "vacancy_save";
        break;
        
    case "vacancy_save":
        if (!$id)
        {
            $template->msg($job->lang['vacancy_edit'], $job->lang['vacancy_not_found']);
            return 0;
        }
        
        if ($job->UpdateVacancy($_POST, $id))
        {
            $template->msg($job->lang['vacancy_edit'], $job->lang['vacancy_edit_ok']);
            
            if ($company)
            {
                Cache::ClearArrayCache('vacancies_company_' . $company->company_id);
            }
                
            return 1;
        }
            
        $edit = $_POST;
		$hidden_array['id'] = $id;
		$hidden_array['action'] = "vacancy_save";
        break;
        
    case "vacancy_del":
        if (!$selected)
        {
            $template->msg($job->lang['vacancy_del'], $job->lang['vacancy_not_selected']);
            
            if ($company)
            {
                Cache::ClearArrayCache('vacancies_company_' . $company->company_id);
            }
            
            return 0;
        }
        
        $job->DeleteVacancy($selected, $company);
        Cache::ClearArrayCache('stats');
        $template->msg($job->lang['vacancy_del'], $job->lang['vacancy_del_ok']);
        return 1;
        break;
        
    default:
        $hidden_array['action'] = "vacancy_doadd";
        $edit = ConvertTypeToValue($TABLES['job_vacancies']);
        foreach (array('age_min', 'age_max', 'salary_min', 'salary_max') as $field)
		{
			$edit[$field] = '';
		}
        break;
}

$hidden_array['vacancy_form_code'] = $_SESSION['vacancy_form_code'] = empty($edit['vacancy_form_code'])?uniqid(microtime(1)):$edit['vacancy_form_code'];

foreach ($job->config['vacancy_need_field'] as $field)
{
	$validete_array[$field]['valid']['required'] = "true";
	if (!empty($job->lang['vacancy_error_' . $field]))
		$validete_array[$field]['messages']['required'] = $job->lang['vacancy_error_' . $field];
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

$template->load('vacancy_add');

$set_param = array(
                    '{sphere}'      => $template->Selection($job->GetSpheres('add'), 'sphere_id', $edit['sphere_id'], "id='sphere_id'"),
//                    '{specialty}'   => $template->Selection($job->GetSpecialties($edit['sphere_id']), 'specialty_id', $edit['specialty_id'], "id='specialty_id'"),
                    '{specialty}'   => $template->InputText('specialty', empty($edit['sphere_id'])?$job->lang['sel_sphere']:$edit['specialty'], 'id="specialty"'),
                    '{currency}'    => $template->Selection(Job::$currecy_array, 'cuurency', $edit['cuurency']),
                    '{age_min}'     => $edit['age_min'],
                    '{age_max}'     => $edit['age_max'],
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

foreach (Vacancy::$fields as $field=>$values_array)
{
    $set_param["{" . $field . "}"] = $template->Selection(array("" => $job->lang['no_show']) + $values_array, $field, $edit[$field]);
}
                
if (empty($company))
{
    $template->SetBlock('no_company');
    $set_param['{contact_person}'] = $template->InputText('contact_person', $edit['contact_person']);
    $set_param['{email}'] = $template->InputText('email', $edit['email'], "validate=\"email:true\"");
    $set_param['{phone}'] = $template->InputText('phone', $edit['phone']);
}

if ($hidden_array['action'] == "vacancy_doadd" && CheckGroupAllow('vacancy_allow_change_exp', $company?-1:0))
{
    $template->SetBlock('count_day');
    $set_param['{count_day}'] = $template->Selection(Vacancy::$count_day_array, 'count_day', $job->config['vacancy_default_day']);
}

if ($hidden_array['action'] == "vacancy_doadd" && CheckGroupAllow('vacancy_add_capcha', $company?-1:0))
{
    SetCapchaBlock($set_param);
}

if (!$job->member['id'] && $hidden_array['action'] == 'vacancy_doadd' && $job->config['vacancy_allow_reg'])
{
    SetRegisterBlock($set_param);
}

if ($hidden_array['action'] == "vacancy_save" && CheckGroupAllow('vacancy_allow_extend', $company?-1:0))
{
    $template->SetBlock('extend');
    $set_param['{count_extend}'] = $template->Selection(array('-1' => $auto->lang['no_extend']) + Vacancy::$count_day_array, 'count_extend', '-1');
}

$template->SetStyleScript(array($config['http_home_url'] . 'engine/job/images/admin/jquery.autocomplete.css'),                                                 array('engine/job/javascript/jquery.metadata.js',
												'engine/job/javascript/jquery.validate.js',
												'engine/job/javascript/messages_ru.js',
//												'engine/job/javascript/jquery.autocomplete.pack.js'
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
	       //extraParams:{sphere:function(){return $('#sphere_id').val();}},
	       //selectOnly: true
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

$template->SetForm($hidden_array, $template->main_url, 'POST', 'id="form_add"');
$template->Set($set_param);

if ($hidden_array['action'] == "vacancy_doadd")
{
    $template->SetBlock('add');
}
else 
{
    $template->SetBlock('edit');
}

$template->Compile('content', $JS);

if ($hidden_array['action'] == "vacancy_doadd")
	$template->TitleSpeedBar($job->lang['add_vacancy']);
else 
	$template->TitleSpeedBar($job->lang['edit_vacancy']);
?>