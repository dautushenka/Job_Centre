<?php

$group = $group_no_guest = get_usergroup();
unset($group_no_guest[5]);

$show_moder = array($job->lang['general_show_moder_now_new'],
					$job->lang['general_show_moder_all'],
					$job->lang['general_show_moder_new'],
					$job->lang['general_show_moder_new_old'],
					$job->lang['general_show_moder_old'] );

$company_need_fields  = $vacancy_need_fields = $resume_need_fields = "<div align=\"left\" style=\"margin-left:150px;\">";
$company_need_fields_array  = array('contact_person', 'description', 'logo', 'phone', 'site', 'email', 'address', 'date_register');
foreach ($company_need_fields_array as $field)
{
	$company_need_fields .= $tpl->InputCheckbox("save_con[company_need_field][]", $field, !@in_array($field, $job->config['company_need_field'])?0:1) . " " . $job->lang[$field] . "<br />";
}
$company_need_fields .= "</div>";

$vacancy_need_fields_array  = array('experience', 'student', 'education', 'sex', 'language', 'work_place', 'work_type', 'work_schedule', 'age_min', 'age_max', 'salary_min', 'salary_max', 'description', 'email', 'phone');
foreach ($vacancy_need_fields_array as $field)
{
	$vacancy_need_fields .= $tpl->InputCheckbox("save_con[vacancy_need_field][$field]", $field, !@in_array($field, $job->config['vacancy_need_field'])?0:1) . " " . $job->lang[$field] . "<br />";
}
$vacancy_need_fields .= "</div>";

$resume_need_fields_array = array('experience', 'student', 'education', 'sex', 'language', 'work_place', 'work_type', 'work_schedule', 'age', 'salary_min', 'salary_max', 'description', 'photo', 'email', 'phone');
foreach ($resume_need_fields_array as $field)
{
    $resume_need_fields .= $tpl->InputCheckbox("save_con[resume_need_field][$field]", $field, !@in_array($field, $job->config['resume_need_field'])?0:1) . " " . $job->lang[$field] . "<br />";
}
$resume_need_fields .= "</div>";

$general_currency = "<div align=\"left\" style=\"margin-left:150px;\"><table>";
foreach (job::$currecy_array as $currency=>$currency_name)
{
	$general_currency .= "<tr><td align='right'>" . $currency_name . "</td><td> " . $tpl->InputText("save_con[currency][$currency]", $job->config['currency'][$currency], 'size="8" style="margin:2px;"')  . "</td></tr>";
}
$general_currency .= "</table></div>";

$sub_sort = array(
                   'ASC' => $job->lang['sub_sort_ASC'],
                   'DESC' => $job->lang['sub_sort_DESC'],
                    );


$settings_array = array(
						"vacancy" => array(
						                array(
											"title"   => $job->lang['vacacny_only_company'],
											"descr"   => $job->lang['vacacny_only_company_desc'],
											"setting" => YesNo('vacacny_only_company'),
											"regexp"  => false,
											),
									    array(
											"title"   => $job->lang['vacancy_allow_add'],
											"descr"   => $job->lang['vacancy_allow_add_desc'],
											"setting" => $tpl->SelectionMulti($group, 'save_con[vacancy_allow_add][]', $job->config['vacancy_allow_add']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_add_capcha'],
											"descr"   => $job->lang['vacancy_add_capcha_desc'],
											"setting" => $tpl->SelectionMulti(array(-1 => $job->lang['company_admins']) + $group, 'save_con[vacancy_add_capcha][]', $job->config['vacancy_add_capcha']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_add_no_moder'],
											"descr"   => $job->lang['vacancy_add_no_moder_desc'],
											"setting" => $tpl->SelectionMulti(array(-1 => $job->lang['company_admins']) + $group, 'save_con[vacancy_add_no_moder][]', $job->config['vacancy_add_no_moder']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_allow_edit'],
											"descr"   => $job->lang['vacancy_allow_edit_desc'],
											"setting" => $tpl->SelectionMulti(array(-1 => $job->lang['company_admins']) + $group_no_guest, 'save_con[vacancy_allow_edit][]', $job->config['vacancy_allow_edit']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_allow_del'],
											"descr"   => $job->lang['vacancy_allow_del_desc'],
											"setting" => $tpl->SelectionMulti(array(-1 => $job->lang['company_admins']) + $group_no_guest, 'save_con[vacancy_allow_del][]', $job->config['vacancy_allow_del']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_allow_change_exp'],
											"descr"   => $job->lang['vacancy_allow_change_exp_desc'],
											"setting" => $tpl->SelectionMulti(array(-1 => $job->lang['company_admins']) + $group_no_guest, 'save_con[vacancy_allow_change_exp][]', $job->config['vacancy_allow_change_exp']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_allow_extend'],
											"descr"   => $job->lang['vacancy_allow_extend_desc'],
											"setting" => $tpl->SelectionMulti(array(-1 => $job->lang['company_admins']) + $group_no_guest, 'save_con[vacancy_allow_extend][]', $job->config['vacancy_allow_extend']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_need_field'],
											"descr"   => $job->lang['vacancy_need_field_desc'],
											"setting" => $vacancy_need_fields,
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_allow_reg'],
											"descr"   => $job->lang['vacancy_allow_reg_desc'],
											"setting" => YesNo('vacancy_allow_reg'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_allow_show_filter'],
											"descr"   => $job->lang['vacancy_allow_show_filter_desc'],
											"setting" => YesNo('vacancy_allow_show_filter'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_default_sort'],
											"descr"   => $job->lang['vacancy_default_sort_desc'],
											"setting" => $tpl->selection(Vacancy::$sort_array, 'save_con[vacancy_default_sort]', $job->config['vacancy_default_sort']) . "&nbsp;" . 
											             $tpl->selection($sub_sort, 'save_con[vacancy_default_sub_sort]', $job->config['vacancy_default_sub_sort']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_default_day'],
											"descr"   => $job->lang['vacancy_default_day_desc'],
											"setting" => $tpl->selection(Vacancy::$count_day_array, 'save_con[vacancy_default_day]', $job->config['vacancy_default_day']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['vacancy_count_per_page'],
											"descr"   => $job->lang['vacancy_count_per_page_desc'],
											"setting" => $tpl->InputText('save_con[vacancy_count_per_page]', $job->config['vacancy_count_per_page'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'vacancy_count_per_page',
											),
										array(
											"title"   => $job->lang['block_last_vacancies'],
											"descr"   => $job->lang['block_last_vacancies_desc'],
											"setting" => YesNo('block_last_vacancies'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['block_last_vacancies_count'],
											"descr"   => $job->lang['block_last_vacancies_count_desc'],
											"setting" => $tpl->InputText('save_con[block_last_vacancies_count]', $job->config['block_last_vacancies_count'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'block_last_vacancies_count',
											),
										array(
											"title"   => $job->lang['RSS_vacancies_count'],
											"descr"   => $job->lang['RSS_vacancies_count_desc'],
											"setting" => $tpl->InputText('save_con[RSS_vacancies_count]', $job->config['RSS_vacancies_count'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'RSS_vacancies_count',
											),
										),
						"resume" => array(
										array(
											"title"   => $job->lang['resume_allow_add'],
											"descr"   => $job->lang['resume_allow_add_desc'],
											"setting" => $tpl->SelectionMulti($group, 'save_con[resume_allow_add][]', $job->config['resume_allow_add']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_add_capcha'],
											"descr"   => $job->lang['resume_add_capcha_desc'],
											"setting" => $tpl->SelectionMulti($group, 'save_con[resume_add_capcha][]', $job->config['resume_add_capcha']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_add_no_moder'],
											"descr"   => $job->lang['resume_add_no_moder_desc'],
											"setting" => $tpl->SelectionMulti($group, 'save_con[resume_add_no_moder][]', $job->config['resume_add_no_moder']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_allow_edit'],
											"descr"   => $job->lang['resume_allow_edit_desc'],
											"setting" => $tpl->SelectionMulti($group_no_guest, 'save_con[resume_allow_edit][]', $job->config['resume_allow_edit']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_allow_del'],
											"descr"   => $job->lang['resume_allow_del_desc'],
											"setting" => $tpl->SelectionMulti($group_no_guest, 'save_con[resume_allow_del][]', $job->config['resume_allow_del']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_allow_change_exp'],
											"descr"   => $job->lang['resume_allow_change_exp_desc'],
											"setting" => $tpl->SelectionMulti($group_no_guest, 'save_con[resume_allow_change_exp][]', $job->config['resume_allow_change_exp']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_allow_extend'],
											"descr"   => $job->lang['resume_allow_extend_desc'],
											"setting" => $tpl->SelectionMulti($group_no_guest, 'save_con[resume_allow_extend][]', $job->config['resume_allow_extend']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_need_field'],
											"descr"   => $job->lang['resume_need_field_desc'],
											"setting" => $resume_need_fields,
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_allow_one_resume'],
											"descr"   => $job->lang['resume_allow_one_resume_desc'],
											"setting" => YesNo('resume_allow_one_resume'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_allow_reg'],
											"descr"   => $job->lang['resume_allow_reg_desc'],
											"setting" => YesNo('resume_allow_reg'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_allow_show_filter'],
											"descr"   => $job->lang['resume_allow_show_filter_desc'],
											"setting" => YesNo('resume_allow_show_filter'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_default_sort'],
											"descr"   => $job->lang['resume_default_sort_desc'],
											"setting" => $tpl->selection(Resume::$sort_array, 'save_con[resume_default_sort]', $job->config['resume_default_sort']) . "&nbsp;" . 
											             $tpl->selection($sub_sort, 'save_con[resume_default_sub_sort]', $job->config['resume_default_sub_sort']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_default_day'],
											"descr"   => $job->lang['resume_default_day_desc'],
											"setting" => $tpl->selection(Vacancy::$count_day_array, 'save_con[resume_default_day]', $job->config['resume_default_day']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_count_per_page'],
											"descr"   => $job->lang['resume_count_per_page_desc'],
											"setting" => $tpl->InputText('save_con[resume_count_per_page]', $job->config['resume_count_per_page'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'resume_count_per_page',
											),
										array(
											"title"   => $job->lang['resume_watermark'],
											"descr"   => $job->lang['resume_watermark_desc'],
											"setting" => YesNo('resume_watermark'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['resume_photo_size_byte'],
											"descr"   => $job->lang['resume_photo_size_byte_desc'],
											"setting" => $tpl->InputText('save_con[resume_photo_size_byte]', $job->config['resume_photo_size_byte'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'resume_photo_size_byte',
											),
										array(
											"title"   => $job->lang['resume_photo_size'],
											"descr"   => $job->lang['resume_photo_size_desc'],
											"setting" => $tpl->InputText('save_con[resume_photo_size]', $job->config['resume_photo_size'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'resume_photo_size',
											),
										array(
											"title"   => $job->lang['resume_size_for_watermark'],
											"descr"   => $job->lang['resume_size_for_watermark_desc'],
											"setting" => $tpl->InputText('save_con[resume_size_for_watermark]', $job->config['resume_size_for_watermark'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'resume_size_for_watermark',
											),
										array(
											"title"   => $job->lang['block_last_resumes'],
											"descr"   => $job->lang['block_last_resumes_desc'],
											"setting" => YesNo('block_last_resumes'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['block_last_resumes_count'],
											"descr"   => $job->lang['block_last_resumes_count_desc'],
											"setting" => $tpl->InputText('save_con[block_last_resumes_count]', $job->config['block_last_resumes_count'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'block_last_resumes_count',
											),
										array(
											"title"   => $job->lang['RSS_resumes_count'],
											"descr"   => $job->lang['RSS_resumes_count_desc'],
											"setting" => $tpl->InputText('save_con[RSS_resumes_count]', $job->config['RSS_resumes_count'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'RSS_resumes_count',
											),
										),
						"company" => array(
										array(
											"title"   => $job->lang['company_allow_manage'],
											"descr"   => $job->lang['company_allow_manage_desc'],
											"setting" => $tpl->SelectionMulti($group, 'save_con[company_allow_manage][]', $job->config['company_allow_manage']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['company_add_capcha'],
											"descr"   => $job->lang['company_add_capcha_desc'],
											"setting" => $tpl->SelectionMulti($group, 'save_con[company_add_capcha][]', $job->config['company_add_capcha']),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['company_allow_reg'],
											"descr"   => $job->lang['company_allow_reg_desc'],
											"setting" => YesNo('company_allow_reg'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['company_allow_del'],
											"descr"   => $job->lang['company_allow_del_desc'],
											"setting" => YesNo('company_allow_del'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['company_show_vacancies'],
											"descr"   => $job->lang['company_show_vacancies_desc'],
											"setting" => YesNo('company_show_vacancies'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['company_need_field'],
											"descr"   => $job->lang['company_need_field_desc'],
											"setting" => $company_need_fields,
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['company_per_page'],
											"descr"   => $job->lang['company_per_page_desc'],
											"setting" => $tpl->InputText('save_con[company_per_page]', $job->config['company_per_page'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'company_per_page',
											),
										array(
											"title"   => $job->lang['company_watermark'],
											"descr"   => $job->lang['company_watermark_desc'],
											"setting" => YesNo('company_watermark'),
											"regexp"  => false,
											),
										array(
											"title"   => $job->lang['company_logo_size_byte'],
											"descr"   => $job->lang['company_logo_size_byte_desc'],
											"setting" => $tpl->InputText('save_con[company_logo_size_byte]', $job->config['company_logo_size_byte'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'company_logo_size_byte',
											),
										array(
											"title"   => $job->lang['company_logo_size'],
											"descr"   => $job->lang['company_logo_size_desc'],
											"setting" => $tpl->InputText('save_con[company_logo_size]', $job->config['company_logo_size'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'company_logo_size',
											),
										array(
											"title"   => $job->lang['company_size_for_watermark'],
											"descr"   => $job->lang['company_size_for_watermark_desc'],
											"setting" => $tpl->InputText('save_con[company_size_for_watermark]', $job->config['company_size_for_watermark'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'company_size_for_watermark',
											),
										),
						"general" => array(
										array(
											"title" => $job->lang['general_allow_module'],
											"descr" => $job->lang['general_allow_module_desc'],
											"setting" => YesNo('general_allow_module'),
											"regexp" => false,
											),
										array(
											"title" => $job->lang['general_name_module'],
											"descr" => $job->lang['general_name_module_desc'],
											"setting" => $tpl->InputText("save_con[general_name_module]", $job->config['general_name_module']),
											"regexp" => '#[a-z_\-]+#',
											"name" => 'general_name_module',
											),
										array(
											"title" => $job->lang['general_mod_rewrite'],
											"descr" => $job->lang['general_mod_rewrite_desc'],
											"setting" => YesNo('general_mod_rewrite'),
											"regexp" => false,
											),
										array(
											"title" => $job->lang['general_AJAX'],
											"descr" => $job->lang['general_AJAX_desc'],
											"setting" => YesNo('general_AJAX'),
											"regexp" => false,
											),
										array(
											"title" => $job->lang['general_cache'],
											"descr" => $job->lang['general_cache_desc'],
											"setting" => YesNo('general_cache'),
											"regexp" => false,
											),
										array(
											"title" => $job->lang['general_main_page'],
											"descr" => $job->lang['general_main_page_desc'],
											"setting" => YesNo('general_main_page'),
											"regexp" => false,
											),
										array(
											"title" => $job->lang['general_RSS'],
											"descr" => $job->lang['general_RSS_desc'],
											"setting" => YesNo('general_RSS'),
											"regexp" => false,
											),
										array(
											"title" => $job->lang['general_inform'],
											"descr" => $job->lang['general_inform_desc'],
											"setting" => YesNo('general_inform'),
											"regexp" => false,
											),
										array(
											"title" => $job->lang['general_email'],
											"descr" => $job->lang['general_email_desc'],
											"setting" => $tpl->InputText('save_con[general_email]', $job->config['general_email']),
											"regexp" => '#(^[\w-]+(\.[\w-]+)*@([\w-]+)\.+[a-zA-Z]{2,3}$|^$)#',
											"name" => 'general_email',
											),
/*										array(
											"title" => $job->lang['general_view_mode'],
											"descr" => $job->lang['general_view_mode_desc'],
											"setting" => $tpl->selection(array("table" => $job->lang['general_view_mode_table'], "modern" => $job->lang['general_view_mode_modern']), 'save_con[general_view_mode]', $job->config['general_view_mode']),
											"regexp" => false,
											),*/
										array(
											"title" => $job->lang['general_currency'],
											"descr" => $job->lang['general_currency_desc'],
											"setting" => $general_currency,
											"regexp" => '#^[0-9\.]+$#',
											"name" => 'currency',
											),
										array(
											"title" => $job->lang['general_moderator_edit'],
											"descr" => $job->lang['general_moderator_edit_desc'],
											"setting" => $tpl->SelectionMulti($group_no_guest, 'save_con[general_moderator_edit][]', $job->config['general_moderator_edit']),
											"regexp" => false,
											),
										array(
											"title" => $job->lang['general_moderator_del'],
											"descr" => $job->lang['general_moderator_del_desc'],
											"setting" => $tpl->SelectionMulti($group_no_guest, 'save_con[general_moderator_del][]', $job->config['general_moderator_del']),
											"regexp" => false,
											),
										array(
											"title" => $job->lang['general_show_moder'],
											"descr" => $job->lang['general_show_moder_desc'],
											"setting" => $tpl->selection($show_moder, 'save_con[general_show_moder]', $job->config['general_show_moder']),
											"regexp" => false,
											),
										array(
											"title"   => $job->lang['general_image_quality'],
											"descr"   => $job->lang['general_image_quality_desc'],
											"setting" => $tpl->InputText('save_con[general_image_quality]', $job->config['general_image_quality'], 'size="8"'),
											"regexp"  => '#^[0-9]+$#',
											"name"    => 'general_image_quality',
											),
										array(
											"title" => $job->lang['general_count_last_vacancy'],
											"descr" => $job->lang['general_count_last_vacancy_desc'],
											"setting" => $tpl->InputText('save_con[general_count_last_vacancy]', $job->config['general_count_last_vacancy'], 'size="8"'),
											"regexp" => '#[0-9]+#',
											"name" => 'general_count_last_vacancy',
											),
										array(
											"title" => $job->lang['general_count_last_resume'],
											"descr" => $job->lang['general_count_last_resume_desc'],
											"setting" => $tpl->InputText('save_con[general_count_last_resume]', $job->config['general_count_last_resume'], 'size="8"'),
											"regexp" => '#[0-9]+#',
											"name" => 'general_count_last_resume',
											),
										array(
											"title" => $job->lang['general_allow_statistic'],
											"descr" => $job->lang['general_allow_statistic_desc'],
											"setting" => YesNo('general_allow_statistic'),
											"regexp" => false,
											),
										array(
											"title" => $job->lang['general_debug'],
											"descr" => $job->lang['general_debug_desc'],
											"setting" => YesNo('general_debug'),
											"regexp" => false,
											),
									),

						);


?>