<?php
/**
 * ExceptionCore file
 *
 * @author Autushenko Denis <support@kaliostro.net>
 * @version 1.0
 * @package Core
 * @copyright (c) kaliostro
 */

/**
 * Core Class for Exception
 *
 * @access public
 * @package Core
 * @link http://www.php.net/manual/ru/class.exception.php Exception class
 * @abstract
 */
abstract class ExceptionCore extends Exception
{
    /**
     * Debug All Error, Show all error on browser
     *
     * @var boolean
     */
    public static $debug = false;

    /**
     * Current Time in Unix format
     *
     * @var integer
     */
    public static $cur_time = 0;

    /**
     * Constructo
     *
     * @param string $message
     * @param integer $code
     */
    public function __construct($message = NULL, $code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * Write Error to file
     *
     * @param string $message
     * @param string $file full path
     */
    protected function WriteToFile($message, $file)
    {
        if (!$file)
        $file = dirname(__FILE__) . '/Log.log';
        	
        if (is_writable(dirname($file)))
        {
            $handler = fopen($file, 'a');
            fwrite($handler, '######################### ' . $this->GetTime() . " #############################\n" . $message . "\n\n");
            fclose($handler);
        }
    }

    /**
     * Send mail with Error Message
     *
     * @param string $message
     * @param string $email
     */
    protected function SendMail($message, $email)
    {
        if (!empty($email) && ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'. '@'.'[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.'[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email))
        {
            $subject = "=?UTF-8?b?" . base64_encode('Error Message from ' . $_SERVER['HTTP_HOST']). "?=";
            $headers .= "MIME-Version: 1.0\n";
            $headers .= "Content-type: text/plain; charset=\"UTF-8\"\n";
            //$headers  .= "From: \"".$from."\" <".$from.">\n";
            //$headers .= "Return-Path: <".$from.">\n";
            $headers .= "X-Priority: 1\n";
            $headers .= "X-Mailer: Error Log by kaliostro\n";
            	
            @mail($email, $subject, $this->GetTime() . '\n' . $message, $headers);
        }
    }

    /**
     * Show Error message in browser
     *
     * @param string $message
     */
    protected function Show($message)
    {
        echo '<div style="border:2px #333333 solid; background-color:#FFFFCC; padding:3px;"><pre>' . $message . '</pre></div><br />';
    }

    /**
     * conver time from Unix format
     *
     * @return string
     */
    private function GetTime()
    {
        if (self::$cur_time)
        return date('d-m-Y H:i', self::$cur_time);
        else
        return date('d-m-Y H:i');
    }

    /**
     * select  log type
     *
     * @param string $message
     * @param string $log_type
     * @param string $log_file
     * @param string $log_email
     */
    protected function Log($message, $log_type = 'none', $log_file = '', $log_email = '')
    {
        if (self::$debug)
        $this->Show($message);
        else
        switch ($log_type)
        {
            case 'file':
                $this->WriteToFile($message, $log_file);
                break;
                	
            case 'email':
                $this->SendMail($message, $log_email);
                break;
                	
            case 'show':
                $this->Show($message);
                break;
                	
            default:
                break;
        }
    }

    /**
     * Convert Trace in String format
     *
     * @param array $trace
     * @return string
     */
    protected function TraceFormat(array $trace = array())
    {
        if (!$trace)
        $trace = $this->getTrace();
        	
        $trace = array_reverse($trace);

        $i = 1; $traces = '';
        foreach ($trace as $call)
        {
            if (!eregi('(include)|(require)', $call['function']))
            {
                $traces .= "  [$i]\n\t";
                $traces .= "File      : $call[file]\n\t";
                $traces .= "Line      : $call[line]\n\t";
                $traces .= "Class     : $call[class]\n\t";
                $traces .= "Object    : $call[object]\n\t";
                $traces .= "Function  : $call[function]\n\t";
                $traces .= "Arguments : " . implode(', ', $call['args']) . "\n\n";
                $i++;
            }
        }
        return $traces;
    }

    /**
     * Show Critical Error in browser and exit();
     *
     */
    protected function ShowFatalMessage()
    {
        echo "Sorry, Critical Error.<br />
				Return on <a href=\"http://www.$_SERVER[HTTP_HOST]/\" >main page</a>";
        exit();
    }

    /**
     * Destructor
     *
     */
    public  function __destruct()
    {

    }

}

/**
 * Exception class for DataBase Error
 *
 * @access public
 * @package ExceptionCore
 * @final
 */
final class ExceptionDataBase extends ExceptionCore
{
    /**
     * Type of Log, allow value None, Show, email, file
     *
     * @var string
     */
    public static $log_type = 'None';

    /**
     * LogFile for error, full path
     *
     * @var string
     */
    public static $log_file = '';

    /**
     * Email for send error
     *
     * @var string
     */
    public static $log_email = '';

    /**
     * Constructor
     *
     * @param string $Error_str
     * @param string $query
     * @param integer $ErrorNo
     * @param string $Error_type value only Warning or Fatal
     */
    public function __construct($Error_str = '', $query = '', $ErrorNo = 0, $Error_type = 'Warning')
    {
        parent::__construct($Error_str, $ErrorNo);

        if (self::$log_type != 'none')
        {
            $message  = "Type/Code : $ErrorNo\n";
            $message .= "Message   : $Error_str\n";
            $message .= "Query     : $query\n";
            $message .= "File      : " . $this->getFile() . "\n";
            $message .= "Line      : " . $this->getLine() . "\n";
            $message .= "Trace     : \n" . $this->TraceFormat();
            	
            $this->Log($message, self::$log_type, self::$log_file, self::$log_email);
            	
            if ($Error_type == 'Fatal')
            $this->ShowFatalMessage();
        }
    }

    /**
     * Destructor
     *
     */
    public function __destruct()
    {

    }

}

/**
 * Exception class for All error
 *
 * @package ExceptionCore
 * @final
 * @access public
 */
final class ExceptionAllError extends ExceptionCore
{
    /**
     * Type of Log, allow value None, Show, email, file
     *
     * @var string
     */
    public static $log_type = 'none';

    /**
     * LogFile for error, full path
     *
     * @var string
     */
    public static $log_file = '';

    /**
     * Email for send error
     *
     * @var string
     */
    public static $log_email = '';

    /**
     * Constructor
     *
     * @param string $Error_str
     * @param integer $ErrorNo
     * @param string $Error_type value only Warning or Fatal
     */
    public function __construct($Error_str = '', $ErrorNo = 0, $Error_type = 'Warning')
    {
        parent::__construct($Error_str, $ErrorNo);

        if (self::$log_type != 'none')
        {
            $message  = "Type/Code : $ErrorNo\n";
            $message .= "Message   : $Error_str\n";
            $message .= "File      : " . $this->getFile() . "\n";
            $message .= "Line      : " . $this->getLine() . "\n";
            $message .= "Trace     : \n" . $this->TraceFormat();
            	
            $this->Log($message, self::$log_type, self::$log_file, self::$log_email);
            	
            if ($Error_type == 'Fatal')
            $this->ShowFatalMessage();
        }
    }

    /**
     * Destructor
     *
     */
    public function __destruct()
    {

    }
}

/**
 * Class for ErrorHandler and ExceptionHandler
 *
 * @package ExceptionCore
 * @access public
 * @final
 */
final class ExceptionErrorHandler extends ExceptionCore
{
    private $ErrorArray = array(
    E_ERROR           => 'E_ERROR',
    E_WARNING         => 'E_WARNING',
    E_PARSE           => 'E_PARSE',
    E_NOTICE          => 'E_NOTICE',
    E_CORE_ERROR      => 'E_CORE_ERROR',
    E_CORE_WARNING    => 'E_CORE_WARNING',
    E_COMPILE_ERROR   => 'E_COMPILE_ERROR',
    E_COMPILE_WARNING => 'E_COMPILE_WARNING',
    E_USER_ERROR      => 'E_USER_ERROR',
    E_USER_WARNING    => 'E_USER_WARNING',
    E_USER_NOTICE     => 'E_USER_NOTICE',
    E_STRICT          => 'E_STRICT'
    );

    /**
     * Type of Log, allow value none, show, email, file
     *
     * @var string
     */
    public static $log_type = 'none';

    /**
     * LogFile for error, full path
     *
     * @var string
     */
    public static $log_file = '';

    /**
     * Email for send error
     *
     * @var string
     */
    public static $log_email = '';

    /**
     * Log only, array of Error constant of Error or All or none
     *
     * @var mixed
     */
    private $log_only = array();

    /**
     * Constructor
     *
     * Set ErrorHandler and set ExceptionHandler
     *
     * @param mixed $log_only value -- 'none' or 'all' or array of Error constant
     */
    public function __construct($log_only = array())
    {
        set_error_handler(array ($this, 'ErrorHandler'));
        set_exception_handler(array ($this, 'DefautException'));

        $this->log_only = $log_only;
    }

    /**
     * ErrorHandler function
     *
     * @param integer $cErrno
     * @param string $sErrStr
     * @param string $sErrFile
     * @param string $iErrLine
     */
    public function ErrorHandler($cErrno, $sErrStr, $sErrFile, $iErrLine)
    {
        if (!preg_match('#sqli?_#si', $sErrStr))
        {
            if ($this->log_only == 'all' || (self::$log_type != 'none' && is_array($this->log_only) && in_array($cErrno, $this->log_only)))
            {
                $message  = "Type/Code : " . $this->ErrorArray[$cErrno] . "/$cErrno\n";
                $message .= "Message   : $sErrStr\n";
                $message .= "File      : $sErrFile\n";
                $message .= "Line      : $iErrLine";

                $this->Log($message, self::$log_type, self::$log_file, self::$log_email);
            }
        }
    }

    /**
     * ExceptionHandler function
     *
     * @param object $exception
     */
    public function DefautException($exception)
    {
        $message  = "Exception : " . get_class($exception) . "\n";
        $message .= "Code      : " . $exception->getCode() . "\n";
        $message .= "Message   : " . $exception->getMessage() . "\n";
        $message .= "File      : " . $exception->getFile() . "\n";
        $message .= "Line      : " . $exception->getLine() . "\n";
        $message .= "Trace     : \n" . $this->TraceFormat($exception->getTrace());

        $this->Log($message, self::$log_type, self::$log_file, self::$log_email);

        $this->ShowFatalMessage();
    }

    /**
     * Destructor
     *
     * Restore ErrorHandler and ExceptionHandler
     */
    public function __destruct()
    {
        restore_error_handler();
        restore_exception_handler();
    }
}

?>