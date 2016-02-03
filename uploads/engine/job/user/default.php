<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}



$metatags['description'] = $job->lang['meta_descr_main'];
$metatags['keywords'] = $job->lang['meta_keys_main'];

$template->TitleSpeedBar($job->lang['job_centre']);

if (!MODER_DEL && !MODER_EDIT && $job->config['general_cache'] && $cache = Cache::GetHTMLCache('main'))
{
    $template->AddToContent($cache);
}
else
{
    //require(ENGINE_DIR . "/job/user/sphere_list.php");
    
    $template->load('default');

    $job->sort = 'add_date';
    $job->sub_sort = "DESC";

    $job->SearchVacancy(array(), array('count' => $job->config['general_count_last_vacancy'],
                                       'get_count' => false));

    if ($job->vacancies)
    {
        $template->OpenRow('row_vacancy');
        
        foreach ($job->vacancies as $id=>$vac)
        {
            $template->SetRow($job->ShowVacancy($id), 'row_vacancy');
        }
        
        $template->CloseRow('row_company');
    }
    

    $job->SearchResume(array(), array('count' => $job->config['general_count_last_resume'],
                                  'get_count' => false));
    
    if ($job->resumes)
    {
        $template->OpenRow('row_resume');
        
        foreach ($job->resumes as $id=>$res)
        {
            $template->SetRow($job->ShowResume($id), 'row_resume');
        }  
        
        $template->CloseRow('row_resume');
    }

    $cache = $template->WrapContent("<div id='job-content'>", "</div>")->Compile('content');
    
    if (!MODER_DEL && !MODER_EDIT && $job->config['general_cache'])
    {
        Cache::SetHTMLCache('main', $cache);
    }
}

if ($job->config['general_allow_statistic'])
{
    $template->load('stats');
    
    foreach ($job->Stats() as $tag=>$data)
    {
        $template->Set($data, "{" . $tag . "}");
    }
    
    $template->Compile('content');
}

?>