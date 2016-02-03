<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}


if (!AJAX && (!$id || ($id && $job->config['vacancy_allow_show_filter'])))
{
	$template->load('vacancy_head');
	
	$JS = '';
	$job->ShowSearchVacancy(array(), $JS);
	
	$template->SetForm($hidden_array, $template->main_url, "GET", 'id="vacancy_filter"');
	
	if ($job->config['general_AJAX'])
	{
	    $JS .= <<<JS
$().ready(function()
{
    $('#vacancy_filter').live('submit', function()
    {
        var str = $(this).serialize();
        BlockContent($('#job-content'));
        $('#job-content').load(dle_root + "?" + str);
        
        return false;
    });
});
JS;
	}
	
	if ($JS)
	{
	    $JS = "<script type='text/javascript'>\n$JS</script>";
	}
	
	$template->Compile('content', $JS);
}

$JScript = ShowEditMenu('vacancy');

if ($JScript)
{
        $JScript = <<<JS
<script type="text/javascript" >
$JScript
</script>     
JS;
}



if ($id)
{
	$job->SearchVacancy(array('id' => $id), array('get_count' => 0));
	
	if (empty($job->vacancies[$id]))
	{
		$template->msg($job->lang['error'], $job->lang['vacancy_not_found']);
		return ;
	}
	
	$job->VacancyAddView($id);
	
	$template->load("vacancy_full");
	
	$array = $job->ShowVacancy($id);
	
	$template->Set($array);
	
	$template->temp_main_url = $config['http_home_url'] . "engine/job/print.php";
	$template->Set("<a target=\"_blank\" href=\"" . $template->GetUrl(array('action' => 'vacancy', 'id' => $id), array("print" => $id, 'id' => '')) . "\" >", "[print]")
	         ->Set("</a>", "[/print]");
	
	if (!AJAX)
        $template->WrapContent("<div id='job-content'>", "</div>");
	
	$template->Compile('content', $JScript);
	
	$template->TitleSpeedBar($job->lang['vacancies'], $template->GetUrl(array('action' => 'vacancy'), array('action' => 'vacancies')));
	$template->TitleSpeedBar("#" . $id . " " . $job->vacancies[$id]->specialty_name . " (" . $job->vacancies[$id]->sphere_name . ")");
	

}
else 
{
    if (RSS)
    {
        $job->SearchVacancy(array(), array("get_count" => 0, 'count' => $job->config['RSS_vacancies_count']));
    }
    else
    {
        $job->SearchVacancy(array(), array("count" => $job->config['vacancy_count_per_page']));
    }
	
	
	if ($job->vacancies)
	{
	    $url_pages = $job->search_array + array('action' => 'vacancy',
	                                            'sort' => $job->sort,
	                                            'sub_sort' => $job->sub_sort);
	                                            
        if (!RSS)
        {
            $template->PageNavigation($url_pages, // Url array
    								  $job->vacancies_count, // Count all vacancy in this query
    								  $job->config['vacancy_count_per_page'], // Count vacancy per page
    								  $_REQUEST['page'], // Current page
    								  count($job->vacancies), // Now vacancy
    								  array("use_alt_url" => false, "link_script" => 'class="inside_ajax"', 'clear' => true) // Options links
    								  );
            if (Vacancy::$sort_array)
            {
                $job->ShowVacancySort();
            }
        }
								  
        if (RSS)
        {
            $template->load('vacancy_rss');
        }
        else
        {
            $template->load('vacancy');
        }
        
		$template->OpenRow('row_vacancy');
//			 ->Set($template->InputCheckbox('master', 1, 0, 'id="master"'), '{master_checkbox}')
//			 ->Set("<img src=\"{THEME}/car-market/images/compare_checked.gif\" id=\"compare_master\" title=\"{$job->lang['compare_master_title']}\" />", '{compare_master}');
								  
		foreach ($job->vacancies as $id=>$v)
		{
		    if (RSS)
		    {
		        $array = $job->ShowVacancy($id, array('show_edit' => 0));
		        $array['{rss_add_date}'] = $v->rss_add_date;
		        $array['{rsslink}'] = $v->vacancy_url;
		    }
		    else
		    {
		        $array = $job->ShowVacancy($id);
		    }
		    
			$template->SetRow($array, 'row_vacancy');
		}
		
		$template->CloseRow('row_vacancy');
		
		if (!AJAX && !RSS)
			$template->WrapContent("<div id='job-content'>", "</div>");
		
        if (RSS)
        {
            $template->Compile('rss');
        }
        else
        {
            if ($job->config['general_RSS'])
            {
                $rss_url = $job->search_array + array('sort' => $job->sort, 'sub_sort' => $job->sub_sort);
                $template->temp_main_url = $config['http_home_url'] . "engine/job/vacancy_rss.php";
                $template->Set("<a target=\"_blank\" href=\"" . $template->GetUrl($rss_url, array(), array(), array(), array('use_alt_url' => false, "clear" => 1)) . "\" >", "[rss]")
                         ->Set("</a>", "[/rss]");
            }
            
            $template->SetResult('PageNavigation', '{pages}')
		             ->SetResult('sort', '{sort}')
                     ->Compile('content', $JScript);
        }
		         
	}
	else if (!AJAX)
	{
		$template->AddToContent("<div id='job-content'>" . $job->lang['not_found'] . "</div>");
	}
	else
	{
	    $template->AddToContent($job->lang['not_found']);
	}
	
	$template->TitleSpeedBar($job->lang['vacancies']);
}


?>