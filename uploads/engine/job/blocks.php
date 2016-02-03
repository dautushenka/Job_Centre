<?php

require_once(ENGINE_DIR . "/job/includes.php");


$job->search_array = array();

if ($job->config['block_last_vacancies'])
{
    if ($job->config['general_cache'] && $cache = Cache::GetHTMLCache('block_last_vacancies'))
    {
        $template->block_last_vacancies = $cache;
    }
    else
    {
        $job->sort = 'add_date';
        $job->sub_sort = 'DESC';
        $job->SearchVacancy(array(), array('no_moder' => 1, 'count' => $job->config['block_last_vacancies_count']));
    
        if ($job->vacancies)
        {
            $template->load('block_last_vacancies')
                     ->OpenRow('row_vacancy');
                     
            foreach ($job->vacancies as $id=>$vac)
            {
                $array = $job->ShowVacancy($id, array('show_edit' => 0));
                $template->SetRow($array, 'row_vacancy');
            }
            $template->CloseRow('row_vacancy');
            $cache = $template->Compile('block_last_vacancies');
            
            if ($job->config['general_cache'])
            {
                Cache::SetHTMLCache('block_last_vacancies', $cache);
            }
        }
    }
}

if ($job->config['block_last_resumes'])
{
    if ($job->config['general_cache'] && $cache = Cache::GetHTMLCache('block_last_resumes'))
    {
        $template->block_last_resumes = $cache;
    }
    else
    {
        $job->sort = "add_date";
        $job->sub_sort = "DESC";
        
        $job->SearchResume(array(), array('count' => $job->config['block_last_resumes_count'], 'no_moder' => 1));
        
        if ($job->resumes)
        {
            $template->load('block_last_resumes')
                     ->OpenRow('row_resume');
                     
            foreach ($job->resumes as $id=>$res)
            {
                $array = $job->ShowResume($id, array('show_edit' => 0));
                $template->SetRow($array, 'row_resume');
            }
            $template->CloseRow('row_resume');
        }
        
        $cache = $template->Compile('block_last_resumes');
        
        if ($job->config['general_cache'])
        {
            Cache::SetHTMLCache('block_last_resumes', $cache);
        }
    }
}


unset($exc, $dbase, $job, $template, $timer, $licence);

?>