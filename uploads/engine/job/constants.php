<?php

define('LOGO_PATCH', ROOT_DIR . "/uploads/company_logo/");
define('PHOTO_PATCH', ROOT_DIR . "/uploads/resume_photo/");
define('LOGO_URL_PATCH', $config['http_home_url'] . "uploads/company_logo/");
define('PHOTO_URL_PATCH', $config['http_home_url'] . "uploads/resume_photo/");
define('AJAX' , ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') || !empty($_REQUEST['rndval']))?TRUE:FALSE);
define('AJAX_INSIDE', empty($_REQUEST['inside'])?false:true);

Company::$OPF_array = array( 'AF'  => $lang_job['AF'],
							 'ZAO' => $lang_job['ZAO'],
							 'IP'  => $lang_job['IP'],
							 'COO' => $lang_job['COO'],
							 'NKO' => $lang_job['NKO'],
							 'OAO' => $lang_job['OAO'],
							 'OOO' => $lang_job['OOO'],
							 'POO' => $lang_job['POO'],
							 'PAR' => $lang_job['PAR'],
							 'UE'  => $lang_job['UE']
							 );
							 
Job::$currecy_array = array(   'USD' => $lang_job['USD'],
							   'RUR' => $lang_job['RUR'],
							   'EUR' => $lang_job['EUR'],
							);
															
Vacancy::$fields = Resume::$fields = array(
											"experience" => array(
											                     1  => $lang_job['experience_1'],
											                           $lang_job['experience_2'],
											                           $lang_job['experience_3'],
											                           $lang_job['experience_4'],
											                           $lang_job['experience_5'],
											                           $lang_job['experience_6'],
											                     ),
											"student" => array(
											                     1 => $lang_job['student_1'],
											                          $lang_job['student_2'],
											                          $lang_job['student_3'],
											                          $lang_job['student_4'],
											                   ),
											"education" => array(
											                     1 => $lang_job['education_1'],
											                          $lang_job['education_2'],
											                          $lang_job['education_3'],
											                          $lang_job['education_4'],
											                          $lang_job['education_5'],
											                     ),
											"language" => array(
											                     1 => $lang_job['language_1'],
											                          $lang_job['language_2'],
											                          $lang_job['language_3'],
											                          $lang_job['language_4'],
											                          $lang_job['language_5'],
											                          $lang_job['language_6'],
											                          $lang_job['language_7'],
											                          $lang_job['language_8'],
											                     ),
											"work_place" => array(
											                     1 => $lang_job['work_place_1'],
											                          $lang_job['work_place_2'],
											                     ),
											"work_type" => array(
											                     1 => $lang_job['work_type_1'],
											                          $lang_job['work_type_2']
											                     ),
											"work_schedule" => array(
											                     1 => $lang_job['work_schedule_1'],
											                          $lang_job['work_schedule_2'],
											                          $lang_job['work_schedule_3'],
											                     ),
											"sex" => array(
											                     0 => $lang_job['no_show'],
                                                                "M" =>$lang_job['sex_man'],
											                     "W" =>     $lang_job['sex_woman'],
											                     ),
											);
											
Vacancy::$count_day_array = Resume::$count_day_array = array(
                                                              -1 => $lang_job['count_day_never'],
                                                              10 => $lang_job['count_day_10'],
                                                              14 => $lang_job['count_day_14'],
                                                              21 => $lang_job['count_day_21'],
                                                              31 => $lang_job['count_day_31'],
                                                              62 => $lang_job['count_day_62'],
                                                             );
                                                             
Vacancy::$search_count_day = Resume::$search_count_day = array(
                                                              -1 => $lang_job['search_day_all'],
                                                              10 => $lang_job['search_day_10'],
                                                              14 => $lang_job['search_day_14'],
                                                              21 => $lang_job['search_day_21'],
                                                              31 => $lang_job['search_day_31'],
                                                              62 => $lang_job['search_day_62'],
                                                             );
$TABLES = array(
				 'job_companies'  => array( 'company_id'     => 'int',
				                            'user_id'        => 'int',
				                            'user_name'      => 'string',
				                            'name'           => 'string',
				                            'alt_name'       => 'string',
				                            'company_type'   => array('PA','EM'),
				                            'OPF'            => 'string',
				                            'contact_person' => 'string',
				                            'description'    => 'string',
				                            'logo'           => 'string',
				                            'country_id'     => 'int',
				                            'city_id'        => 'int',
				                            'phone'          => 'string',
				                            'site'           => 'string',
				                            'email'          => 'string',
				                            'address'        => 'string',
				                            'date_register'  => 'string',
				                            'xfields'        => 'string',
				                            'add_date'       => 'int'
				                            ),
				 'job_vacancies'  => array( 'id'			 => 'int',
				 							'user_id'		 => 'int',
				 							'user_name'		 => 'string',
				 							'country_id'	 => 'int',
				 							'city_id'		 => 'int',
				 							'sphere_id'		 => 'int',
				 							'specialty_id'	 => 'int',
				 							'company_id'	 => 'int',
				 							'view_count'	 => 'int',
				 							'experience'	 => 'int',
				 							'student'		 => 'int',
				 							'education'		 => 'int',
				 							'age_min'		 => 'int',
				 							'age_max'		 => 'int',
				 							'salary_min'	 => 'int',
				 							'salary_min_search'	 => 'int',
				 							'salary_max'	 => 'int',
				 							'salary_max_search'=> 'int',
				 							'currency'       => 'string',
				 							'sex'			 => array('M','W', '0'),
				 							'language'		 => 'int',
				 							'work_place'	 => 'int',
				 							'work_type'		 => 'int',
				 							'work_schedule'	 => 'int',
				 							'description'	 => 'string',
				 							'contact_person' => 'string',
				 							'email'			 => 'string',
				 							'phone'			 => 'string',
				 							'xfields'		 => 'string',
				 							'add_date'		 => 'int',
				 							'date_exp'		 => 'int',
				 							'allow_site'	 => 'int'
				 							),
				 'job_resumes'	  => array( 'id'			 => 'int',
				 							'user_id'		 => 'int',
				 							'user_name'		 => 'string',
				 							'country_id'	 => 'int',
				 							'photo'          => 'string',
				 							'city_id'		 => 'int',
				 							'sphere_id'		 => 'int',
				 							'specialty_id'	 => 'int',
				 							'view_count'	 => 'int',
				 							'experience'	 => 'int',
				 							'student'		 => 'int',
				 							'education'		 => 'int',
				 							'age'			 => 'int',
				 							'salary_min'	 => 'int',
				 							'salary_min_search'	 => 'int',
				 							'salary_max'	 => 'int',
				 							'salary_max_search'=> 'int',
				 							'currency'       => 'string',
				 							'sex'			 => array('M','W', '0'),
				 							'language'		 => 'int',
				 							'work_place'	 => 'int',
				 							'work_type'		 => 'int',
				 							'work_schedule'	 => 'int',
				 							'description'	 => 'string',
				 							'contact_person' => 'string',
				 							'email'			 => 'string',
				 							'phone'			 => 'string',
				 							'xfields'		 => 'string',
				 							'add_date'		 => 'int',
				 							'date_exp'		 => 'int',
				 							'allow_site'	 => 'int'
				 							),
				 'job_spheres'    => array( 'id'       => 'int',
				                            'name'     => 'string',
				                            'alt_name' => 'string'
				                            ),
				 'job_specialties' => array( 'id'       => 'int',
				                            'sphere_id'=> 'int',
				                            'name'     => 'string',
				                            'alt_name' => 'string'
				                            ),
				 'job_countries'  => array( 'id'       => 'int',
				                            'name'     => 'string',
				                            'alt_name' => 'string'
				                            ),
				 'job_cities'     => array( 'id'         => 'int',
				                            'country_id' => 'int',
				                            'name'       => 'string',
				                            'alt_name'   => 'string'
				                            ),
                 'job_fields'     => array('id' 		 => 'int',
				 							'ctype' 	 => 'int',
				 							'title' 	 => 'string',
				 							'description'=> 'string',
				 							'type'  	 => array('text', 'select', 'textarea', 'checkbox'),
				 							'data'       => 'string',
				 							'regex'      => 'string',
				 							'required'   => 'int',
				 							'active'     => 'int',
				 							'default'    => 'string'
				                            ),
				);

Vacancy::$DB_fields = array_merge(array_keys($TABLES['job_vacancies']), array('sphere_name', 'specialty_name', 'country_name', 'city_name', 'company_name'));
Resume::$DB_fields = array_merge(array_keys($TABLES['job_resumes']), array('sphere_name', 'specialty_name', 'country_name', 'city_name'));
Company::$DB_fields = array_merge(array_keys($TABLES['job_companies']), array('country_name', 'city_name'));

Vacancy::$search_fields = array_merge(array(
                                            'id',
            				 				'user_id',
            				 				'country_id',
            				 				'city_id',
            				 				'sphere_id',
            				 				'specialty_id',
            				 				'company_id',
            	 							'age',
            				 				'salary_min',
            	 							'salary_max',
            	 							'description',
            	 							'add_date',
                                            ), array_keys(Vacancy::$fields));

Resume::$search_fields = array_merge(array(
                                            'id',
				 							'user_id',
				 							'country_id',
				 							'city_id',
				 							'sphere_id',
				 							'specialty_id',
				 							'age_min',
				 							'age_max',
				 							'salary_min',
				 							'salary_max',
				 							'sex',
				 							'description',
				 							'add_date',
				 							'allow_site'
                                           ), array_keys(Resume::$fields));

Company::$search_fields = array(
                                            'company_id',
				                            'user_id',
				                            'name',
				                            'company_type',
				                            'OPF',
				                            'country_id',
				                            'city_id'
                                            );

Vacancy::$sort_array = array(
                             'salary'   => $lang_job['sort_salary'],
                             'age'      => $lang_job['sort_age'],
                             'add_date' => $lang_job['sort_add_date'],
                             'sex'      => $lang_job['sort_sex'],
                            );
                            
Resume::$sort_array = array(
                             'salary'   => $lang_job['sort_salary'],
                             'age'      => $lang_job['sort_age'],
                             'add_date' => $lang_job['sort_add_date'],
                             'sex'      => $lang_job['sort_sex'],
                            );
                            
define('XFIELDS_VACANCY', 2);
define('XFIELDS_RESUME',  4);
define('XFIELDS_COMPANY', 8);

?>