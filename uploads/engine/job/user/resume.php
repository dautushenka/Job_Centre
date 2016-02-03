<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

if (!AJAX && (!$id || ($id && $job->config['resume_allow_show_filter'])))
{
	$template->load('resume_head');
	
	$JS = '';
	$job->ShowSearchResume(array(), $JS);
	
	$template->SetForm($hidden_array, $template->main_url, "GET", 'id="resume_filter"');
	
	if ($job->config['general_AJAX'])
	{
	    $JS .= <<<JS
$().ready(function()
{
    $('#resume_filter').live('submit', function()
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


$JScript = ShowEditMenu('resume');

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
	$job->SearchResume(array('id' => $id), array('get_count' => 0));
	
	if (empty($job->resumes[$id]))
	{
		$template->msg($job->lang['error'], $job->lang['resume_not_found']);
		return ;
	}
	
	$job->ResumeAddView($id);
	
	$template->load("resume_full");
	
	$array = $job->ShowResume($id);
	
	$template->Set($array);
	
	$template->temp_main_url = $config['http_home_url'] . "engine/job/print.php";
	$template->Set("<a target=\"_blank\" href=\"" . $template->GetUrl(array('action' => 'resume', 'id' => $id), array("print" => $id, 'id' => '')) . "\" >", "[print]")
	         ->Set("</a>", "[/print]");
	
	if (!AJAX)
        $template->WrapContent("<div id='job-content'>", "</div>");
        
    $template->TitleSpeedBar($job->lang['resumes'], $template->GetUrl(array('action' => 'resume'), array('action' => 'resumes')));
	$template->TitleSpeedBar("#" . $id . " " . $job->resumes[$id]->specialty_name . " (" . $job->resumes[$id]->sphere_name . ")");
	
	$template->Compile('content', $JScript);

}
else 
{
    if (RSS)
    {
        $job->SearchResume(array(), array("get_count" => false, 'count' => $job->config['RSS_resumes_count']));
    }
    else
    {
        $job->SearchResume(array(), array("count" => $job->config['resume_count_per_page']));
    }
	
	
	if ($job->resumes)
	{
	    if (!RSS)
	    {
	        $url_pages = $job->search_array + array('action' => 'resume',
    	                                            'sort' => $job->sort,
    	                                            'sub_sort' => $job->sub_sort);
    	                                            
    		$template->PageNavigation($url_pages, // Url array
    								  $job->resumes_count, // Count all resume in this query
    								  $job->config['resume_count_per_page'], // Count resume per page
    								  $_REQUEST['page'], // Current page
    								  count($job->resumes), // Now resume
    								  array("use_alt_url" => false, "link_script" => 'class="inside_ajax"', 'clear' => true) // Options links
    								  );
    
            if (Resume::$sort_array)
            {
                $job->ShowResumeSort();
            }
	    }

	    if (RSS)
	    {
            $template->load('resume_rss');
	    } 
	    else
	    {
	        $template->load('resume');
	    }
        $template->OpenRow('row_resume');
//			 ->Set($template->InputCheckbox('master', 1, 0, 'id="master"'), '{master_checkbox}')
//			 ->Set("<img src=\"{THEME}/car-market/images/compare_checked.gif\" id=\"compare_master\" title=\"{$job->lang['compare_master_title']}\" />", '{compare_master}');
				
		foreach ($job->resumes as $id=>$r)
		{
		    if (RSS)
		    {
		        $array = $job->ShowResume($id, array('show_edit' => false));
		        $array['{rss_add_date}'] = $r->rss_add_date;
		        $array['{rsslink}'] = $r->resume_url;
		    }
		    else
		    {
		        $array = $job->ShowResume($id);
		    }
		    
			$template->SetRow($array, 'row_resume');
		}
		
		$template->CloseRow('row_resume');
		
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
		        $template->temp_main_url = $config['http_home_url'] . "engine/job/resume_rss.php";
		        $template->Set("<a target=\"_blank\" href=\"" . $template->GetUrl($job->search_array, array(), array(), array(), array('use_alt_url' => false, "clear" => true)). "\" >", "[rss]")
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
	
	$template->TitleSpeedBar($job->lang['resumes']);
}


?>