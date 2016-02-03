<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

$apage = (empty($_REQUEST['apage']))?'vacancy':$_REQUEST['apage'];

if (!AJAX)
{
    $template->load('account_menu');
    
    if ($company = Company::GetCompany_by_user())
    {
        $template->Set("<a href=\"" . $template->GetUrl(array('action'=> 'company_edit', 'id' => $company->company_id), array('id' => '/', $company->company_id . "-" . $company->alt_name)) . "\" >", "[company]")
                 ->Set("</a>", "[/company]");
                 
        if ($job->config['company_allow_del'])
        {
            $template->Set("<a OnClick=\"if (confirm('{$job->lang['company_pre_del_msg']}')) return true; else return false;\" href=\"" . $template->GetUrl(array('action'=> 'company_del', 'id' => $company->company_id), array('id' => '/', $company->company_id . "-" . $company->alt_name)) . "\" >", "[company_del]")
                     ->Set("</a>", "[/company_del]");
        }
    }
    
    if ($job->member['id'])
    {
        $template->Set("<a class=\"inside_ajax\" href=\"" . $template->GetUrl(array('action'=> 'account', 'apage' => 'vacancy')) . "\" >", "[vacancy]")
                 ->Set("</a>", "[/vacancy]")
                 ->Set("<a class=\"inside_ajax\" href=\"" . $template->GetUrl(array('action'=> 'account', 'apage' => 'resume')) . "\" >", "[resume]")
                 ->Set("</a>", "[/resume]");
                 
        if ($apage == "vacancy")
        {
            $JScript = ShowEditMenu('vacancy');
        }
        else
        {
            $JScript = ShowEditMenu('resume');
        }
        
        if ($JScript)
        {
            $JScript = <<<JS
<script type="text/javascript" >
$JScript
</script>     
JS;
        }
    }
    
    if ($job->favorites['resume'])
    {
        $template->Set("<a class=\"inside_ajax\" href=\"" . $template->GetUrl(array('action'=> 'account', 'apage' => 'favorites_resume')) . "\" >", "[favorites_resume]")
                 ->Set("</a>", "[/favorites_resume]");
    }
    
    if ($job->favorites['vacancy'])
    {
        $template->Set("<a class=\"inside_ajax\" href=\"" . $template->GetUrl(array('action'=> 'account', 'apage' => 'favorites_vacancy')) . "\" >", "[favorites_vacancy]")
                 ->Set("</a>", "[/favorites_vacancy]");
    }
    
             
    $template->Compile('content', $JScript);
}


$template->TitleSpeedBar($job->lang['my_' . $apage]);

if (empty($_REQUEST['page']) && $job->config['general_cache'] && $cache = Cache::GetHTMLCache("account_{$apage}_user_" . $job->member['id']))
{
    $template->AddToContent($cache);
    return 1;
}


$template->load('account');

switch ($apage)
{
    case "resume":
        $template->SetBlock('resume');
        $job->SearchResume(array('user_id' => $job->member['id']), array('count' => $job->config['resume_count_per_page']));
        
        if ($job->resumes)
        {
            $all_count = $job->resumes_count;
            $per_page = $job->config['resume_count_per_page'];
            $count = count($job->resumes);
            
            $template->OpenRow('row_resume');
            
            foreach ($job->resumes as $id=>$res)
            {
                $template->SetRow($job->ShowResume($id), 'row_resume');
            }
            
            $template->CloseRow('row_resume');
        }
        else
        {
            $template->SetBlockContent('row_resume', $job->lang['account_resume_not_found']);
        }
        break;
        
    case 'favorites_resume':
        $template->SetBlock('favorites_resume');
        $template->SetBlock('resume', 0);
        
        if (!$job->favorites['resume'])
        {
            $template->SetBlockContent('favorites_resume', $job->lang['account_favorites_resume_not_found']);
            setcookie("favorites_resume", '', time()-365*24*3600, "/");
        }
        else 
        {
            $job->SearchResume(array('id' => $job->favorites['resume']), array('count' => $job->config['resume_count_per_page']));
            
            if ($job->resumes)
            {
                $all_count = $job->resumes_count;
                $per_page = $job->config['resume_count_per_page'];
                $count = count($job->resumes);
                
                $template->OpenRow('row_resume');
                
                foreach ($job->resumes as $id=>$res)
                {
                    $template->SetRow($job->ShowResume($id), 'row_resume');
                }
                
                $tempalte->CloseRow('row_resume');
            }
            else
            {
                $template->SetBlockContent('row_resume', $job->lang['account_favorites_resume_not_found']);
                setcookie("favorites_resume", '', time()-365*24*3600, "/");
            }
        }
        break;
        
    case 'favorites_vacancy':
        $template->SetBlock('favorites_vacancy');
        $template->SetBlock('vacancy', 0);
        
        if (!$job->favorites['vacancy'])
        {
            $template->SetBlockContent('favorites_vacancy', $job->lang['account_favorites_vacancy_not_found']);
            setcookie("favorites_vacancy", '', time()-365*24*3600, "/");
        }
        else 
        {
            $job->SearchVacancy(array('id' => $job->favorites['vacancy']), array('count' => $job->config['vacancy_count_per_page']));
            
            if ($job->vacancies)
            {
                $all_count = $job->vacancies_count;
                $per_page = $job->config['vacancy_count_per_page'];
                $count = count($job->vacancies);
        								  
                $template->OpenRow('row_vacancy');
                
                foreach ($job->vacancies as $id=>$vac)
                {
                    $template->SetRow($job->ShowVacancy($id), 'row_vacancy');
                }
                
                $template->CloseRow('row_vacancy');
            }
            else
            {
                $template->SetBlockContent('row_vacancy', $job->lang['account_favorites_vacancy_not_found']);
                setcookie("favorites_vacancy", '', time()-365*24*3600, "/");
            }
        }
        break;
        
    case "vacancy":
    default:
        $apage = 'vacancy';
        $template->SetBlock('vacancy');
        $job->SearchVacancy(array('user_id' => $job->member['id']), array('count' => $job->config['vacancy_count_per_page']));
        
        if ($job->vacancies)
        {
            $all_count = $job->vacancies_count;
            $per_page = $job->config['vacancy_count_per_page'];
            $count = count($job->vacancies);
    								  
            $template->OpenRow('row_vacancy');
            
            foreach ($job->vacancies as $id=>$vac)
            {
                $template->SetRow($job->ShowVacancy($id, array('show_edit' => 1)), 'row_vacancy');
            }
            
            $template->CloseRow('row_vacancy');
        }
        else
        {
            $template->SetBlockContent('row_vacancy', $job->lang['account_vacancy_not_found']);
        }
        break;
}

$url = array(
            'action' => 'account',
            'apage' => $apage
            );

$template->PageNavigation($url, // Url array
    					  $all_count, // Count all resume in this query
    				      $per_page, // Count resume per page
    					  $_REQUEST['page'], // Current page
    					  $count, // Now resume
    					  array("link_script" => 'class="inside_ajax"') // Options links
    					);
    								  
if (!AJAX)
    $template->WrapContent("<div id='job-content'>", "</div>");

$cache = $template->SetResult('PageNavigation', '{pages}')->Compile('content');

if (empty($_REQUEST['page']) && $job->config['general_cache'])
{
    Cache::SetHTMLCache("account_{$apage}_user_" . $job->member['id'], $cache);
}


?>