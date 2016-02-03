<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

$template->load('sphere_list');

$job->dbase->DirectQuery('SELECT COUNT(v.id) AS count, s.* FROM ' . PREFIX . '_job_spheres AS s
                          LEFT JOIN ' . PREFIX . '_job_vacancies AS v
                          ON v.sphere_id=s.id AND (v.date_exp>' . $job->dbase->timer->cur_time . ' OR v.date_exp=0)
                          GROUP BY s.id');

$vsphere = array();

if ($job->dbase->NumRows())
{
    $template->OpenRow('vac_sphere');
    while ($sphere = $job->dbase->FetchArray())
    {
        $array = array(
                        '{name}' => $sphere['name'],
                        '{count}' => $sphere['count'],
                        '{url}' => $template->GetUrl(array('action' => 'vacancy', 'sphere_id' => $sphere['id']), array(), array(), array(), array('use_alt_url' => false)),
                       );
                       
       $template->SetRow($array, 'vac_sphere');
    }
    
    $template->CloseRow('vac_sphere');
}

$job->dbase->DirectQuery('SELECT COUNT(r.id) AS count, s.* FROM ' . PREFIX . '_job_spheres AS s
                          LEFT JOIN ' . PREFIX . '_job_resumes AS r
                          ON r.sphere_id=s.id AND (r.date_exp>' . $job->dbase->timer->cur_time . ' OR r.date_exp=0)
                          GROUP BY s.id');

$vsphere = array();

if ($job->dbase->NumRows())
{
    $template->OpenRow('res_sphere');
    while ($sphere = $job->dbase->FetchArray())
    {
        $array = array(
                        '{name}' => $sphere['name'],
                        '{count}' => $sphere['count'],
                        '{url}' => $template->GetUrl(array('action' => 'resume', 'sphere_id' => $sphere['id']), array(), array(), array(), array('use_alt_url' => false)),
                       );
                       
       $template->SetRow($array, 'res_sphere');
    }
    
    $template->CloseRow('res_sphere');
}

$template->Compile('content');





?>