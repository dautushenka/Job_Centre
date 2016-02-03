<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}


/////////////////// Permission options /////////
if (!$job->member['id'] && !$job->config['resume_allow_reg'])
{
    $template->msg($job->lang['error'], $job->lang['need_authorization']);
    return 0;
}

if (!MODER_EDIT && !CheckGroupAllow('company_allow_manage'))
{
    $template->msg($job->lang['company_add'], $job->lang['company_add_access_denied']);
    return 0;
}

$company = Company::GetCompany_by_user();

if (($action == "company_add" || $action == "company_doadd") && !empty($company))
{
    $template->msg($job->lang['error'], $job->lang['company_user_exits']);
    return 0;
}

if (!MODER_DEL && $action == "company_del" && !$job->config['company_allow_del'])
{
    $template->msg($job->lang['error'], $job->lang['company_del_access_denied']);
    return 0;
}
////////////////////////////////////////////////


require(ENGINE_DIR . "/job/classes/thumb.class.php");
include_once(ENGINE_DIR . "/job/classes/parse.class.php");

$job->xfields = new Fields(XFIELDS_COMPANY, $job);

$validete_array = array(
                        "logo"                 => array( "valid" => array("accept" => "'jpeg?|png|gif|jpg'"
                                                                    ),
                                                      "messages" => array("accept" => $job->lang['company_error_type_image'],
                                                                          "required" => $job->lang['company_error_logo']
                                                                          )
                                                    ),
                        "name"                 => array( "valid" => array("required" => "true"
                                                                    ),
                                                     "messages" => array("required" => $job->lang['company_error_name']
                                                                          )
                                                    ),
                        "site"                 => array("valid" => array("url" => "true"
                                                                    ),
                                                     "messages" => array("url" => $job->lang['company_wrong_site'],
                                                                          "required" => $job->lang['company_error_site']
                                                                          )
                                                     ),
                        "email"             => array("valid" => array("email" => "true"
                                                                    ),
                                                     "messages" => array("email" => $job->lang['company_wrong_email'],
                                                                          "required" => $job->lang['company_error_email']
                                                                          )
                                                     ),
                        "date_register"     => array("valid" => array("dateISO" => "true"
                                                                    ),
                                                     "messages" => array("dateISO" => $job->lang['company_wrong_date_register'],
                                                                          "required" => $job->lang['company_error_date_register']
                                                                          )
                                                     ),
                        );


switch ($action)
{
    case "company_doadd":
        if (empty($_SESSION['company_form_code']) || $_SESSION['company_form_code'] != $_POST['company_form_code'])
        {
            $template->msg($job->lang['error'], $job->lang['wrong_form_code']);
            return 0;
        }
        if ($id = $job->NewCompany($_POST))
        {
            $template->msg($job->lang['company_add'], $job->lang['company_add_ok']);
            SendMail('company', $id);
            Cache::ClearHTMLCache('companies');
            Cache::ClearArrayCache('stats');
            unset($_SESSION['company_form_code']);
            return 1;
        }
        
        $edit = $_POST;
        break;
        
    case "company_edit":
        if (!$id || !($edit = $job->GetCompanyForEdit($id)))
        {
            $template->msg($job->lang['company_edit'], $job->lang['company_not_found']);
            return 0;
        }
        
        if (!MODER_EDIT && $edit['user_id'] != $job->member['id'])
        {
            $template->msg($job->lang['company_edit'], $job->lang['company_wrong_author']);
            return 0;
        }
        
        $hidden_array['id'] = $id;
        $hidden_array['action'] = "company_save";
        break;
        
    case "company_save":
        if (!$id)
        {
            $template->msg($job->lang['company_edit'], $job->lang['company_not_found']);
            return 0;
        }
        
        if ($job->UpdateCompany($_POST, $id))
        {
            $template->msg($job->lang['company_edit'], $job->lang['company_edit_ok']);
            Cache::ClearHTMLCache('companies');
            Cache::ClearArrayCache('company_obj_' . $id);
            return 1;
        }
        
        $edit = $_POST;
        $hidden_array['id'] = $id;
        $hidden_array['action'] = "company_save";
        break;
        
    case "company_del":
        if (!$id)
        {
            $template->msg($job->lang['company_del'], $job->lang['company_not_found']);
            return 0;
        }
        
        $job->DeleteCompany($id);
        $template->msg($job->lang['company_del'], $job->lang['company_del_ok']);
        Cache::ClearHTMLCache('companies', 'main');
        Cache::ClearArrayCache('company_obj_' . $id, 'stats');
        return 1;
        break;
        
    default:
        $hidden_array['action'] = "company_doadd";
        $edit = ConvertTypeToValue($TABLES['job_companies']);
        break;
}

$hidden_array['company_form_code'] = $_SESSION['company_form_code'] = empty($edit['company_form_code'])?uniqid(microtime(1)):$edit['company_form_code'];

foreach ($job->config['company_need_field'] as $field)
{
	$validete_array[$field]['valid']['required'] = "true";
	if (!empty($job->lang['company_error_' . $field]))
		$validete_array[$field]['messages']['required'] = $job->lang['company_error_' . $field];
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

$template->load('company_add');

$set_param = array(
                    "{name}"            => $template->InputText("name", $edit['name']),
                    "{alt_name}"        => $template->InputText("alt_name", $edit['alt_name']),
                    "{company_type}"    => $template->Selection(array('PA' => $job->lang['PA'], 'EM' => $job->lang['EM']), 'company_type', $edit['company_type']),
                    "{OPF}"             => $template->Selection(Company::$OPF_array, 'OPF', $edit['OPF']),
                    "{date_register}"   => $template->InputText('date_register', $edit['date_register'], "id=\"date_register\""),
                    "{description}"     => $edit['description'],
                    "{contact_person}"  => $template->InputText('contact_person', $edit['contact_person']),
                    "{phone}"           => $template->InputText('phone', $edit['phone']),
                    "{site}"            => $template->InputText('site', $edit['site']),
                    "{email}"           => $template->InputText('email', $edit['email']),
                    "{address}"         => $template->InputText('address', $edit['address']),
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

if ($hidden_array['action'] == "company_doadd" && CheckGroupAllow('company_add_capcha'))
{
    SetCapchaBlock($set_param);
}

if (!$job->member['id'] && $hidden_array['action'] =='company_doadd' && $job->config['company_allow_reg'])
{
    SetRegisterBlock($set_param);
}

$template->SetStyleScript(array('{THEME}/job/css/humanity.datepick.css'), array(   '/engine/job/javascript/jquery.metadata.js',
                                                                                    '/engine/job/javascript/jquery.validate.js',
                                                                                    '/engine/job/javascript/messages_ru.js',
                                                                                    '/engine/job/javascript/jquery.datepick.pack.js',
                                                                                    '/engine/job/javascript/jquery.datepick-ru.js',
                                                                                                                    ));

$valid_fields = GetValidateRules($validete_array);

$JS = <<<JS
<script type="text/javascript">
$.metadata.setType("attr", "validate");
$(document).ready(function()
{
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
    
    $('#date_register').datepick(
    {
       showOn: 'both',
       maxDate: new Date(),
       dateFormat: $.datepick.ISO_8601,
       buttonImageOnly: true, 
       buttonImage: '{THEME}/job/images/calendar.gif'
    }
    );
});
$JS
</script>
JS;

$template->SetForm($hidden_array, $template->main_url, 'POST', 'enctype="multipart/form-data" id="form_add"');
$template->Set($set_param);

if ($hidden_array['action'] == "company_doadd")
{
    $template->SetBlock('add');
}
else 
{
    $template->SetBlock('edit');
}


$template->Compile('content', $JS);

if ($hidden_array['action'] == "company_doadd")
    $template->TitleSpeedBar($job->lang['add_company']);
else 
    $template->TitleSpeedBar($job->lang['edit_company']);

?>