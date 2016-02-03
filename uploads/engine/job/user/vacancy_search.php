<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}


$template->load('vacancy_search');

$job->ShowSearchVacancy();


$hidden_array['action'] = 'vacancy';
$template->SetForm($hidden_array, $template->main_url, 'GET');
$template->Compile('content');

$metatags['description'] = $job->lang['meta_descr_vacancy_search'];
$metatags['keywords'] = $job->lang['meta_keys_vacancy_search'];

$template->TitleSpeedBar($job->lang['vacancy_search']);



?>