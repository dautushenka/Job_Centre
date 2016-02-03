<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}


$template->load('resume_search');

$job->ShowSearchResume();


$hidden_array['action'] = 'resume';
$template->SetForm($hidden_array, $template->main_url, 'GET');
$template->Compile('content');

$metatags['description'] = $job->lang['meta_descr_resume_search'];
$metatags['keywords'] = $job->lang['meta_keys_resume_search'];

$template->TitleSpeedBar($job->lang['resume_search']);



?>