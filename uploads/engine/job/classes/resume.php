<?php
/**
 * @package ModuleJob
 * @author Autushenko Denis <support@kaliostro.net>
 * @copyright (c) kaliostro
 * @version 1.0.0
 */

/**
 * Resume Entity class
 *
 * @package ModuleJob
 * @access public
 * @final 
 */
final class Resume
{
	/**
	 * Array of fields 
	 *
	 * @var array
	 */
	private $resume = array();
	
	public static $fields = array();
	
	public static $DB_fields = array();
	
	public static $search_fields = array();
	
	public static $sort_array = array();
	
	public static $count_day_array = array();
	
	/**
	 * Allowed type of images
	 *
	 * @var array
	 */
	public static $allowed_mime_types = array('jpeg', 'png', 'gif', 'jpg', 'jpe');
	
	/**
	 * Count day for search, select user in search page
	 *
	 * @var array
	 */
	public static $search_count_day = array();
	
	/**
	 * Delegate Job object
	 *
	 * @var Job
	 */
	public static $job = null;
	
	/**
	 * Constructor for resume class
	 *
	 * @param array $resume_row
	 */
	public function __construct(array $resume_row)
	{
		$this->resume = array_intersect_key($resume_row, array_flip(self::$DB_fields));
		
		if (!empty($this->resume['add_date']))
			$this->resume['add_date'] = ConvertDate($this->resume['add_date'], self::$job);
			
		if (!empty(self::$job->tpl))
		{
		    $this->resume['resume_url'] = self::$job->tpl->GetUrl(array('action' => 'resume', 'id' => $this->resume['id']));
		}
		
		$this->resume['rss_add_date'] = date( "r", $this->resume['add_date']);
		
		$this->resume['description'] = nl2br($this->resume['description']);
			
		if (!empty($this->resume['date_exp']))
			$this->resume['date_exp'] = $this->resume['exp_date'] = ConvertDate($this->resume['date_exp'], self::$job);
		else
		{
		    $this->resume['date_exp'] = self::$job->lang['no_limit'];
		}
		
		if ($this->resume['salary_min'] && $this->resume['salary_max'] && $this->resume['salary_min'] != $this->resume['salary_max'])
			$this->resume['salary'] = $this->resume['salary_min'] . " - " . $this->resume['salary_max'] . " " . self::$job->lang[$this->resume['currency']];
		elseif ($this->resume['salary_min'] && $this->resume['salary_min'] == $this->resume['salary_max'])
			$this->resume['salary'] = $this->resume['salary_min'] . " " . self::$job->lang[$this->resume['currency']];
		elseif ($this->resume['salary_min'])
			$this->resume['salary'] = self::$job->lang['from'] . $this->resume['salary_min'] . " " . self::$job->lang[$this->resume['currency']];
		elseif ($this->resume['salary_max'])
			$this->resume['salary'] = self::$job->lang['to'] . $this->resume['salary_max'] . " " . self::$job->lang[$this->resume['currency']];
		else 
			$this->resume['salary'] = 0;
			
		foreach (self::$fields as $field_name=>$values_array)
		{
			if ($values_array[$this->resume[$field_name]])
				$this->resume[$field_name] = $values_array[$this->resume[$field_name]];
		}
        
		if ($this->resume['photo'])
		{
		    $this->resume['photo_url'] = PHOTO_URL_PATCH . $this->resume['photo'];
		}
		else
		{
		    $this->resume['photo_url'] = PHOTO_URL_PATCH . 'no_photo.jpg';
		}
		
	}
	
	public function __destruct() { }
	
	public function __get($varname)
	{
	    switch ($varname)
	    {
	        case "sphere_name":
	            if (empty($this->resume['sphere_name']) && !empty($this->resume['sphere_id']))
			         $this->resume['sphere_name'] = self::$job->GetSphereName($this->resume['sphere_id']);
	            break;
	            
	        case "specialty_name":
	            if (empty($this->resume['specialty_name']) && !empty($this->resume['specialty_id']))
			         $this->resume['specialty_name'] = self::$job->GetIncidenceName($this->resume['sphere_id'], $this->resume['specialty_id']);
	            break;
	            
	        case "country_name":
	            if (self::$job->use_country && empty($this->resume['country_name']) && !empty($this->resume['country_id']))
        			$this->resume['country_name'] = self::$job->GetCountryName($this->resume['country_id']);
        		elseif (!self::$job->use_country)
                    $this->resume['country_name'] = '';
	            break;
	            
	        case "city_name":
	            if (self::$job->use_city && empty($this->resume['city_name']) && !empty($this->resume['city_id']))
        			$this->resume['city_name'] = self::$job->GetCityName($this->resume['counry_id'], $this->resume['city_id']);
        		elseif (!self::$job->use_city)
                    $this->resume['city_name'] = '';
	            break;
	    }
	    
		if (array_key_exists($varname, $this->resume))
		{
			return $this->resume[$varname];
		}
		
		throw new ExceptionAllError('Поле ' . $varname . ' не существует в резюме');
	}
	
	public function __set($varname, $value)
	{
		throw new ExceptionAllError('The fields only for read');
	}
}

?>