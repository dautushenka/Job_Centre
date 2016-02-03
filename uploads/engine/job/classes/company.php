<?php
/**
 * @package ModuleJob
 * @author Autushenko Denis <support@kaliostro.net>
 * @copyright (c) kaliostro
 * @version 1.0.0
 */

/**
 * Class for using company and save all data for company
 *
 * @package ModuleJob
 * @access public
 * @final 
 */
final class Company
{
	/**
	 * Array of company fields
	 *
	 * @var array
	 */
	private $company = array();
	
	/**
	 * Search fields
	 *
	 * @var array
	 */
	public static $search_fields = array();
	
	/**
	 * Static property of OPFs
	 *
	 * @var array
	 */
	public static $OPF_array = array();
	
	/**
	 * Allowed type of images
	 *
	 * @var array
	 */
	public static $allowed_mime_types = array('jpeg', 'png', 'gif', 'jpg', 'jpe');
	
	/**
	 * DataBase fields array
	 *
	 * @var array
	 */
	public static $DB_fields = array();
	
	/**
	 * Delegate Job object
	 *
	 * @var Job
	 */
	public static $job = null;
	
	/**
	 * Costrusctor compan class
	 * 
	 * Create new object of company with company information
	 *
	 * @param array $company Array from DataBase
	 */
	public function __construct(array $company = array())
	{
		$this->company = array_intersect_key($company, array_flip(self::$DB_fields));
		
		if ($this->company['add_date'])
			$this->company['add_date'] = ConvertDate($this->company['add_date'], self::$job);
			
		if (!empty($this->company['OPF']))
			$this->company['OPF'] = self::$OPF_array[$this->company['OPF']];
			
		if (!empty($this->company['company_type']))
		{
			if ($this->company['company_type'] == "PA")
				$this->company['company_type'] = self::$job->lang['PA'];
			else 
				$this->company['company_type'] = self::$job->lang['EM'];
		}
		
		if ($this->company['logo'])
		{
		    $this->company['logo_url'] = LOGO_URL_PATCH . $this->company['logo'];
		}
		else
		{
		    $this->company['logo_url'] = LOGO_URL_PATCH . "no_logo.jpg";
		}
		
		$this->company['site'] = "<a href=\"" . $this->company['site'] . "\" >" . $this->company['site'] . "</a>";
		$this->company['description'] = nl2br($this->company['description']);
	}
	
	public static function GetCompany_by_user($user_id = 0)
	{
	    if (!$user_id)
	    {
	        $user_id = self::$job->member['id'];
	    }
	    
	    if ($user_id && $compnay = self::$job->dbase->SelectOne('job_companies', array("*"), array('user_id' => $user_id)))
	       return new Company($compnay);
	    else
	       return null;
	}
	
	public function __destruct() { }
	
	public function __get($varname)
	{
	    switch ($varname)
	    {
	        case "country_name":
	            if (self::$job->use_country && empty($this->company['country_name']) && !empty($this->company['country_id']))
        			$this->company['country_name'] = self::$job->GetCountryName($this->company['country_id']);
        		elseif (!self::$job->use_country)
                    $this->vacancy['country_name'] = '';
	            break;
	        
	        case "city_name":
        		if (self::$job->use_city && empty($this->company['city_name']) && !empty($this->company['city_id']))
        			$this->company['city_name'] = self::$job->GetCityName($this->company['counry_id'], $this->company['city_id']);
        		elseif (!self::$job->use_city)
                    $this->vacancy['city_name'] = '';
	            break;
	    }
	    
		if (array_key_exists($varname, $this->company))
		{
			return $this->company[$varname];
		}
		
		throw new ExceptionAllError('Поле ' . $varname . ' не существует в компании');
	}
	
	public function __set($varname, $value)
	{
		throw new ExceptionAllError('The fields only for read');
	}
}
?>