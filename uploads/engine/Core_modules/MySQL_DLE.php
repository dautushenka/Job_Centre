<?php

require(dirname(__FILE__) . '/DataBaseCore.php');

class MySQL_DLE extends DataBaseCore
{

    protected $db;

    /**
     * Constructor
     *
     * @param db $db
     * @param int $timer Current time in UNIX format
     * @param array $tables
     * @param string $db_table_prefix
     */
    public function __construct(db &$db, &$timer, array $tables, $db_table_prefix = '')
    {
        if (is_object($db))
        $this->db =& $db;

        parent::__construct($tables, $db_table_prefix, $timer);
    }

    /**
     * Connect to DataBase Server? this protected method
     *
     * @access protected
     */
    protected function db_Connect()
    {
        if (!$this->db->db_id)
        $this->db->connect($this->db_user, $this->db_pass, $this->db_base, $this->db_host, 0);
        	
        $this->connect_id =& $this->db->db_id;
    }

    /**
     * Select DataBase
     *
     */
    protected function db_SelectDB()
    {
        return true;
    }

    /**
     * Return id for last insert data
     *
     * @return integer id
     */
    public function Insert_id()
    {
        return $this->db->insert_id();
    }

    /**
     * Fetch a result row as an associative array, a numeric array, or both
     *
     * @param string $type result type (MYSQL_ASSOC (default) or MYSQL_NUM or MYSQL_BOTH)
     */
    public function FetchArray($resource = null, $result_type = MYSQL_ASSOC)
    {
        if (!in_array($result_type, array(MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH)))
        $result_type = MYSQL_ASSOC;
        	
        if (!$resource)
        $resource = $this->resource;
        	
        if ($this->resource)
        {
            if ($result_type == MYSQL_ASSOC)
            return $this->db->get_row($resource);
            else
            return $this->db->get_array($resource);
        }
        else
        return false;
    }

    /**
     * Show affected Rows
     *
     * @return integer affected rows
     */
    public function Affected()
    {
        return @mysql_affected_rows($this->connect_id);
    }

    /**
     * Escape string value
     *
     * @return string
     */
    public function EscapeString($value)
    {
        return $this->db->safesql($value);
    }

    /**
     * Return count rows in last query
     *
     */
    public function NumRows()
    {
        return $this->db->num_rows($this->resource);
    }

    /**
     * Clear resourse
     *
     */
    public function FreeResult()
    {
        return $this->db->free($this->resource);
    }

    protected function ExecuteQuery()
    {//echo $this->query . "\n\n\n";
        $this->resource = $this->db->query($this->query, false);
    }

    /**
     * Close the connect
     *
     * @return void
     */
    public function db_Close()
    {
        $this->db->close();
    }

    /**
     * Get DataBase Error
     *
     * @return string
     */
    public function GetError()
    {
        return @mysql_error($this->connect_id);
    }

    /**
     * Get DataBase Error number
     *
     * @return integer
     */
    public function GetErrorNo()
    {
        return @mysql_errno($this->connect_id);
    }
}

?>