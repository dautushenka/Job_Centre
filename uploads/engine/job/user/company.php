<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}


if ($id)
{
    $template->load('company');
    
    if ($job->config['general_cache'] && $com_obj = Cache::GetArrayCache('company_obj_' . $id))
    {
        $job->companies[$id] = $com_obj;
    }
    else
    {
        $job->SearchCompany(array('company_id' => $id), array('get_count' => false, 'page' => 0));
        
        if ($job->config['general_cache'])
        {
            Cache::SetArrayCache('company_obj_' . $id, $job->companies[$id]);
        }
    }
    
    if (!AJAX_INSIDE)
    {
    	if (empty($job->companies[$id]))
    	{
    		$template->msg($job->lang['error'], $job->lang['company_not_found']);
    		return ;
    	}
    	
    	$array = $job->ShowCompany($id);
	
    	$template->Set($array);
    	
    	if (MODER_EDIT || (CheckGroupAllow('company_allow_del') && $job->member['id'] == $job->companies[$id]->user_id))
    	{
    	    $template->Set("<a target=\"_blank\" href=\"" . $template->GetUrl(array('action'=> 'company_edit', 'id' => $id), array('id' => '/', $id . "-" . $job->companies[$id]->alt_name)) . "\" >", "[company_edit]")
    	             ->Set("</a>", "[/company_edit]");
    	}
    	
    	$template->temp_main_url = $config['http_home_url'] . "engine/job/print.php";
    	$template->Set("<a target=\"_blank\" href=\"" . $template->GetUrl(array('action' => 'company', 'id' => $id), array('id' =>'/', $id . "-" . $job->companies[$id]->alt_name, "print" => '')) . "\" >", "[print]")
    	         ->Set("</a>", "[/print]");
    	
    	$template->SetBlock('company');
    }
    else
    {
        $template->SetBlock('company', 0);
    }
	
	if ($job->config['company_show_vacancies'])
	{
	    if ($job->config['general_RSS'])
	    {
	         $template->temp_main_url = $config['http_home_url'] . "engine/job/vacancy_rss.php";
             $template->Set("<a target=\"_blank\" href=\"" . $template->GetUrl(array('company_id' => $id), array('company_id' =>'company/', $id . "-" . $job->companies[$id]->alt_name, "rss" => ''), array(), array(), array('alt_ext' => ".xml")) . "\" >", "[rss]")
                      ->Set("</a>", "[/rss]");
	    }
	    
	    if (!MODER_DEL && !MODER_EDIT && empty($_REQUEST['page']) && $job->config['general_cache'] && $vac_com = Cache::GetArrayCache('vacancies_company_' . $id))
	    {
	        $job->vacancies = $vac_com;
	        $job->vacancies_count = (int)Cache::GetHTMLCache('vacancies_count_comapny_' . $id);
	    }
	    else
	    {
	        $job->SearchVacancy(array('company_id' => $id), array('count' => $job->config['vacancy_count_per_page']));
	        
	        if (!MODER_DEL && !MODER_EDIT && empty($_REQUEST['page']) && $job->config['general_cache'])
	        {
	            Cache::SetArrayCache('vacancies_company_' . $id, $job->vacancies);
	            Cache::SetHTMLCache('vacancies_count_comapny_' . $id, $job->vacancies_count);
	        }
	    }
	    
	    if ($job->vacancies)
	    {
	        $template->PageNavigation(array('action' => 'company', 'id' => $id), // Url array
								  $job->vacancies_count, // Count all resume in this query
								  $job->config['vacancy_count_per_page'], // Count resume per page
								  $_REQUEST['page'], // Current page
								  count($job->vacancies), // Now resume
								  array("link_script" => 'class="inside_ajax"', 'alt_url_array' =>array('id' =>'/', $id . "-" . $job->companies[$id]->alt_name)) // Options links
								  );
	        
	        $template->WrapBlock('vacancy', "<div id='job-content'>", "</div>")
	                 ->SetBlock('vacancy')
	                 ->OpenRow('row_vacancy');
	        
	        foreach ($job->vacancies as $vac_id=>$vac)
	        {
	            $array = $job->ShowVacancy($vac_id, array('show_edit' => 0));
                $template->SetRow($array, 'row_vacancy');
	        }
	        
	        $template->CloseRow('row_vacancy')->SetResult('PageNavigation', '{pages}');
	    }
	}
	
	$metatags['description'] = $template->StringFormat($job->lang['meta_descr_company'], $job->companies[$id]->name);
	$metatags['keywords'] = $job->lang['meta_keys_company'];
	
	$template->TitleSpeedBar($job->lang['companies'], $template->GetUrl(array('action' => 'company'), array('action' => 'companies')));
	$template->TitleSpeedBar($job->companies[$id]->name);
	
	$template->Compile('content');
}
else
{
    if (empty($_REQUEST['page']) && $job->config['general_cache'] && $companies = Cache::GetHTMLCache('companies'))
    {
        $template->AddToContent($companies);
    }
    else
    {
    	$job->SearchCompany(array(), array('count' => $job->config['company_per_page']));
    	
    	if ($job->companies)
    	{
    	    $template->PageNavigation($job->search_array, // Url array
    								  $job->companies_count, // Count all resume in this query
    								  $job->config['company_per_page'], // Count resume per page
    								  $_REQUEST['page'], // Current page
    								  count($job->companies), // Now resume
    								  array("use_alt_url" => false, "link_script" => 'class="inside_ajax"') // Options links
    								  );
    								  
    		$template->load('companies')
    				 ->OpenRow('row_company');
    //			 ->Set($template->InputCheckbox('master', 1, 0, 'id="master"'), '{master_checkbox}')
    //			 ->Set("<img src=\"{THEME}/car-market/images/compare_checked.gif\" id=\"compare_master\" title=\"{$job->lang['compare_master_title']}\" />", '{compare_master}');
    				
    		foreach ($job->companies as $id=>$r)
    		{
    			$array = $job->ShowCompany($id);
    			$template->SetRow($array, 'row_company');
    		}
    		$template->CloseRow('row_company');
    		$cache = $template->SetResult('PageNavigation', '{pages}')->Compile('content', $JScript);
    		
    		if (empty($_REQUEST['page']) && $job->config['general_cache'])
    		{
    		    Cache::SetHTMLCache('companies', $cache);
    		}
    	}
    	else 
    	   $template->AddToContent($job->lang['not_found']);
    }
	   
	$metatags['description'] = $job->lang['meta_descr_companies'];
	$metatags['keywords'] = $job->lang['meta_keys_companies'];
	   
	$template->TitleSpeedBar($job->lang['companies']);
}



?>