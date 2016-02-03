<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

require(ENGINE_DIR . "/job/classes/thumb.class.php");
require_once ENGINE_DIR . '/job/classes/Fields.php';

$validete_array = array(
						"logo" 				=> array( "valid" => array("accept" => "'jpeg?|png|gif|jpg'"
																	),
													  "messages" => array("accept" => $job->lang['company_error_type_image'],
													 					 "required" => $job->lang['company_error_logo']
													 					 )
													),
						"name" 				=> array( "valid" => array("required" => "true"
																	),
													 "messages" => array("required" => $job->lang['company_error_name']
													 					 )
													),
						"site" 				=> array("valid" => array("url" => "true"
																	),
													 "messages" => array("url" => $job->lang['company_wrong_site'],
													 					 "required" => $job->lang['company_error_site']
													 					 )
													 ),
						"email" 			=> array("valid" => array("email" => "true"
																	),
													 "messages" => array("email" => $job->lang['company_wrong_email'],
													 					 "required" => $job->lang['company_error_email']
													 					 )
													 ),
						"date_register" 	=> array("valid" => array("dateISO" => "true"
																	),
													 "messages" => array("dateISO" => $job->lang['company_wrong_date_register'],
													 					 "required" => $job->lang['company_error_date_register']
													 					 )
													 ),
						);
						
$PHP_SELF .= "companies";
$edit = ConvertTypeToValue($TABLES['job_companies']);
$where = (empty($_REQUEST['where']))?array():$_REQUEST['where'];
$per_page = (intval(!empty($_REQUEST['per_page'])))?intval($_REQUEST['per_page']):50;
$page = (intval(!empty($_REQUEST['page'])))?intval($_REQUEST['page']):0;

$xfields = new Fields(XFIELDS_COMPANY, $job);

switch ($subaction)
{
	case "add":
		$job->NewCompany($_POST);
		if ($job->Errors)
		{
			$edit = $_POST;
			$hidden_array['subaction'] = "add";
		}
		else 
		{
		    Cache::ClearHTMLCache('companies');
		    Cache::ClearArrayCache('stats');
			$tpl->msg($job->lang['compaany_add'], $job->lang['compaany_add_ok'], $PHP_SELF);
		}
		break;
		
	case "edit":
		if (!$id || !($edit = $job->GetCompanyForEdit($id)))
			$tpl->msg($job->lang['error'], $job->lang['company_not_found'], $PHP_SELF);
			
		$hidden_array['id'] = $id;
		$hidden_array['logo_cur'] = $edit['logo'];
		$hidden_array['subaction'] = 'save';
		$hidden_array['referal'] = $_SERVER['HTTP_REFERER'];
		$job->lang['btn_add'] = $job->lang['btn_save'];
		break;
		
	case "save":
		if (!$id)
			$tpl->msg($job->lang['error'], $job->lang['company_not_found'], $PHP_SELF);
			
		$job->UpdateCompany($_POST, $id);
		
		if (!$job->Errors)
		{
		    Cache::ClearArrayCache('company_obj_' . $id);
		    Cache::ClearHTMLCache('companies');
			$tpl->msg($job->lang['compaany_update'], $job->lang['compaany_update_ok'], (empty($_POST['referal']))?$PHP_SELF:$_POST['referal']);
		}
			
		$edit = $_POST;
		$hidden_array['subaction'] = 'save';
		$hidden_array['logo_cur'] = $edit['logo'] = $_POST['logo_cur'];
		$hidden_array['referal'] = $_POST['referal'];
		$hidden_array['id'] = $id;
		$job->lang['btn_add'] = $job->lang['btn_save'];
		break;
		
	case "del":
		if (!$id)
			$tpl->msg($job->lang['error'], $job->lang['company_not_found'], $PHP_SELF);
			
		$job->DeleteCompany($id);
		Cache::ClearHTMLCache('companies');
		Cache::ClearArrayCache('company_obj_' . $id, 'stats');
		$tpl->msg($job->lang['compaany_delete'], $job->lang['compaany_delete_ok'], $_SERVER['HTTP_REFERER']);
		break;
		
	default:
		$hidden_array['subaction'] = "add";
		break;
}
	
$tpl->echo = FALSE;

if ($hidden_array['subaction'] == "add")
{
    $data[] = array($job->lang['company_admin'], $tpl->InputText('company_admin', $edit['company_admin'], "id='company_admin'"));
}
	
if ($hidden_array['subaction'] == "save")
{
	$data[] = array('', '<b>ID: </b>' . $edit['company_id']);
	$data[] = array($job->lang['company_admin'], $edit['user_name']);
}
	
$data[] = array($job->lang['company_name'], $tpl->InputText('name', $edit['name'], "size=\"35\""));
$data[] = array($job->lang['company_alt_name'], $tpl->InputText('alt_name', $edit['alt_name'], "size=\"35\""));
$data[] = array($job->lang['company_type'], $tpl->selection(array('PA' => $job->lang['PA'], 'EM' => $job->lang['EM']), 'company_type', $edit['company_type']));
$data[] = array($job->lang['company_OPF'], $tpl->selection(Company::$OPF_array, 'OPF', $edit['OPF']));
$data[] = array($job->lang['company_register'], $tpl->InputText('date_register', $edit['date_register'], "id=\"date_register\"") . " " . $tpl->calendar('date_register', false));
$data[] = array($job->lang['company_description'], $tpl->TextArea('description', $edit['description'], "style=\"width:70%;height:80px;\""));

if ($edit['logo'])
	$data[] = array($job->lang['company_logo'], "<img src=\"" . LOGO_URL_PATCH . $edit['logo'] . "\" /><br />" . $tpl->InputCheckbox('del_logo', 1, $edit['del_logo']) . " " . $job->lang['delete_logo']);
 
$data[] = array($job->lang['company_new_logo'], $tpl->InputFile('logo', "size=\"45\""));

if ($job->use_country)
{
	$data[] = array($job->lang['country'], $tpl->selection($job->GetCountries('add'), 'country_id', $edit['country_id'], "id=\"country_id\""));
	$validete_array['country_id']['valid']['required'] = "true";
	$validete_array['country_id']['messages']['required'] = $job->lang['sel_country'];
}
	
if ($job->use_city)
{
	$data[] = array($job->lang['city'], $tpl->selection($job->GetCities($edit['country_id'], 'add'), 'city_id', $edit['city_id'], "id=\"city_id\""));
}

foreach ($xfields->DecodeFields($edit['xfields']) as $fid => $field)
{
    if (!empty($auto->lang[$field['title']]))
    {
        $field['title'] = $auto->lang[$field['title']];
    }
    
    $data[] = array($field['title'], $field['html'], $field['description']);
}

$data[] = array($job->lang['company_con_per'], $tpl->InputText('contact_person', $edit['contact_person'], "size=\"45\""));
$data[] = array($job->lang['company_phone'], $tpl->InputText('phone', $edit['phone'], "size=\"45\""));
$data[] = array($job->lang['company_site'], $tpl->InputText('site', $edit['site'], "size=\"45\""));
$data[] = array($job->lang['company_email'], $tpl->InputText('email', $edit['email'], "size=\"45\""));
$data[] = array($job->lang['company_address'], $tpl->InputText('address', $edit['address'], "size=\"45\""));

$tpl->echo = TRUE;

if (!empty($job->config['company_need_field']))
{
    foreach ($job->config['company_need_field'] as $field)
    {
    	if ($field != 'logo' || !$edit['logo'])
    	{
    		$validete_array[$field]['valid']['required'] = "true";
    		if (!empty($job->lang['company_error_' . $field]))
    			$validete_array[$field]['messages']['required'] = $job->lang['company_error_' . $field];
    	}
    }
}

$valid_fields = GetValidateRules($validete_array);

$JS = <<<JS
<script type="text/javascript" >
$(document).ready(function()
{
	$("#companies tbody tr").hover(function()
	{
		$(this).addClass("over");
	}, function()
	{
		$(this).removeClass("over");
	});
	
	$('#company_admin').autocomplete(ajax_url + '?action=company_admin',
	{
	   cacheLength: 10,
	   selectFirst: true,
	   selectOnly: true
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
</script>
JS;

$tpl->header($job->lang['companies'], true, $JS, array($config['http_home_url'] . 'engine/job/images/admin/jquery.autocomplete.css',), array(
                                                                        																$config['http_home_url'] . 'engine/job/javascript/jquery.metadata.js',
                                                                        																$config['http_home_url'] . 'engine/job/javascript/jquery.validate.js',
                                                                        																$config['http_home_url'] . 'engine/job/javascript/messages_ru.js',
                                                                        																$config['http_home_url'] . 'engine/job/javascript/jquery.autocomplete.pack.js',
                                                                        																));

if (empty($where))
{
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
	$tpl->OpenSubtable($job->lang['add_company']);
	$tpl->OpenForm('', $hidden_array, 'enctype="multipart/form-data" id="form_add"');
	$tpl->OTable();
	
	foreach ($data as $row)
	{
		echo <<<HTML
	   <tr>
	       <td height="29" style="padding-left:5px;">{$row[0]}</td>
	       <td>{$row[1]}</td>
	   </tr>
HTML;
	}
	
	$tpl->CTable();
	$tpl->CloseSubtable($job->lang['btn_add']);
	$tpl->CloseForm();
	$tpl->CloseTable();
}

if ($hidden_array['subaction'] != "save" && !$job->Errors)
{
	if (!empty($_REQUEST['city_id']))
	{
		$city_str = '';
		foreach ($job->GetCities($where['country_id']) as $id=>$name)
		{
			if (@in_array($id, $_REQUEST['city_id']))
			{
                if ($city_str)
                    $city_str .= ", ";
                    
				$city_str .= "{id:$id, name:'$name'}";
			}
		}
		
		if ($city_str)
echo <<<JS
<script type="text/javascript">
$(document).ready(function()
{
	SetCities(new Array($city_str));
});
</script>
JS;
		
		$where += array('city_id' => $_REQUEST['city_id']);
	}
	
	$job->SearchCompany($where, array('count' => $per_page));
}

if ($hidden_array['subaction'] != "save" && $job->companies)
{
	
	$tpl->OpenTable();
	$tpl->OpenSubtable($job->lang['company_filter']);
	$tpl->OpenGetForm($PHP_SELF, array_merge($hidden_array, array('subaction'=>'')));
	$tpl->echo = FALSE;
	
	echo "<table width=\"100%\" id=\"filter\"><tr>
					<td class=\"title\"><b>ID</b></td><td class=\"value\"> ";
	echo $tpl->InputText('where[id]', $where['id']);
	echo "</td>
					<td class=\"title\"><b>{$job->lang['company_name']}</b></td><td> ";
	echo $tpl->InputText('where[name]', $where['name']);
	echo "</td>
				</tr>
				<tr>
					<td class=\"title\"><b>{$job->lang['company_type']}</b></td><td class=\"value\"> ";
	echo $tpl->selection(array('' => $job->lang['any'], 'PA' => $job->lang['PA'], 'EM' => $job->lang['EM']), 'where[company_type]', $where['company_type']);
	echo "</td>
					<td class=\"title\"><b>{$job->lang['company_OPF']}</b></td><td class=\"value\"> ";
	echo $tpl->selection(array('' => $job->lang['any']) + Company::$OPF_array, 'where[OPF]', $where['OPF']);
	echo "</td>
				</tr>
				<tr>
					<td class=\"title\"><b>{$job->lang['sel_country']}</b></td><td class=\"value\"> ";
	if ($job->use_country)
	{
	    echo $tpl->selection($job->GetCountries('search'), 'where[country_id]', $where['country_id'], 'id="country_id_search"');
	}
	echo "</td>
					<td class=\"title\"><b>{$job->lang['sel_city']}</b> </td><td class=\"value\"> ";
	if ($job->use_city)
	{
	    echo $tpl->selection($job->GetCities($where['country_id'], 'search'), 'cities', '', 'id="city_id_search"');
	}
	echo "</td>
				</tr>
				</table>
				";
	
	$tpl->echo = TRUE;
	$tpl->CloseSubtable($job->lang['btn_search']);
	$tpl->CloseForm();
	$tpl->CloseTable();
	
	$tpl->echo = FALSE;
	$nav = $tpl->navigation($page, $per_page, $job->companies_count, $PHP_SELF . PageURL($where));
	$tpl->echo = TRUE;
	
	$tpl->OpenTable();
	$tpl->OpenSubtable($job->lang['companies']);
	
	$tpl->OTable(array( "ID",
						$job->lang['company_type'],
						$job->lang['company_name'],
						$job->lang['company_admin'],
						$job->lang['company_register'],
						$job->lang['company_create'],
						$job->lang['action']
						), "id=\"companies\"");
	
	if ($nav)
		$tpl->row($nav, true, true);					
						
	foreach ($job->companies as $company)
	{
		$tpl->row(array('style="padding: 2px;"' => $company->company_id, 
						$company->company_type, 
						$company->name, 
						$company->user_name, 
						$company->date_register, 
						$company->add_date, 
						"[<a href=\"$PHP_SELF&subaction=edit&id={$company->company_id}\" >{$job->lang['action_edit']}</a>]
						[<a OnClick=\"if (confirm('{$job->lang['del_confirm']}'))return true; else return false;\" href=\"$PHP_SELF&subaction=del&id={$company->company_id}\" >{$job->lang['action_del']}</a>]"));
	}
	
	if ($nav)
		$tpl->row($nav, false, true);
	
	$tpl->CTable();
	$tpl->CloseSubtable();
	$tpl->CloseTable();
}
elseif (!empty($where))
	$tpl->msg($job->lang['companies'], $job->lang['companies_not_found'], $PHP_SELF);

?>