<?php


final class Timer
{
    public $cur_time = '';

    private $start_db_query = 0;

    public $db_query_time = 0;

    private $start_array = array();

    private $last_start = 0;

    public function __construct($time)
    {
        if (intval($time))
        $this->cur_time = $time;
        else
        $this->cur_time = time();
    }

    public function DBTimer()
    {
        if (!$this->start_db_query)
        $this->start_db_query = microtime(true);
        else
        {
            $cur_time_query = microtime(true) - $this->start_db_query;
            $this->db_query_time += $cur_time_query;
            $this->start_db_query = 0;
            	
            return $cur_time_query;
        }
    }

    public function StartTimer($name='')
    {
        if ($name)
        $last_start = $start_array[$name] = microtime(true);
        else
        $last_start = microtime(true);
    }

    public function EndTimer($name='')
    {
        if ($name)
        return microtime(true) - $start_array[$name];
        else
        return microtime(true) - $last_start;
    }
}


?>