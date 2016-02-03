<?php
/**
 * Caching data to file
 *
 * @package Core
 * @author Autushenko Denis <support@kaliostro.net>
 * @version 1.0.1
 * @copyright (c) kaliostro
 */

/**
 * Caching final static class
 *
 * @final
 * @package Core
 * @access public
 */
final class Cache extends Licencing
{
    /**
     * Patch for save array cache
     *
     * @var string
     */
    public static $array_cache_path = '';

    /**
     * Patch for save HTML data
     *
     * @var string
     */
    public static $HTML_cache_path = '';

    /**
     * Geting array cache
     *
     * @param string $name
     * @param int $safe_time - seconds of time for save cache
     * @return array
     */
    public static function GetArrayCache($name, $safe_time = 0)
    {
        if (!$name)
        return array();
        	
        if (!file_exists(self::$array_cache_path . $name . ".php") || !filesize(self::$array_cache_path . $name . ".php"))
        return array();
        	
        if ($safe_time && !self::SafeTime(self::$array_cache_path . $name . ".php", $safe_time))
        return array();
        	
        $array = @unserialize(file_get_contents(self::$array_cache_path . $name . ".php"));

        return ($array)?$array:array();
    }

    /**
     * Geting HTML cache
     *
     * @param string $name
     * @param int $safe_time - seconds of time for save cache
     * @return mixed - string or bool
     */
    public static static function GetHTMLCache($name, $safe_time = 0)
    {
        if (!$name)
        return false;
        	
        if (!file_exists(self::$HTML_cache_path . $name . ".tmp") && !filesize(self::$HTML_cache_path . $name . ".tmp"))
        return false;
        	
        if ($safe_time && !self::SafeTime(self::$HTML_cache_path . $name . ".tmp", $safe_time))
        return false;
        	
        return file_get_contents(self::$HTML_cache_path . $name . ".tmp");
    }

    /**
     * Saving array cache
     *
     * @param string $name
     * @param array $data
     * @return void
     */
    public static function SetArrayCache($name, $data)
    {
        if (!$name || !$data)
        return ;
        	
        $fp = fopen(self::$array_cache_path . $name . '.php', 'wb+');
        fwrite($fp, serialize($data) );
        fclose($fp);

        @chmod(self::$array_cache_path . $name . '.php', 0666);
    }

    /**
     * Saving HTML(string) cache
     *
     * @param string $name
     * @param string $data
     * @return void
     */
    public static function SetHTMLCache($name, $data)
    {
        if (!$name || !$data)
        return ;
        	
        $fp = fopen(self::$HTML_cache_path . $name . '.tmp', 'wb+');
        fwrite($fp, $data);
        fclose($fp);

        @chmod(self::$HTML_cache_path . $name . '.tmp', 0666);
    }

    /**
     * Clear all or for name array cache
     *
     * @param sting $name
     */
    public static function ClearArrayCache()
    {
        $args = func_get_args();
         
        if ($args)
        {
            foreach ($args as $cache_name)
            {
                @unlink(self::$array_cache_path . $cache_name . ".php");
            }
        }
        else
        {
            $dir = opendir(self::$array_cache_path);

            while ($file = readdir($dir))
            {
                if ($file != "." && $file != ".." && !is_dir(self::$array_cache_path . $file))
                {
                    @unlink(self::$array_cache_path . $file);
                }
            }
        }
    }

    /**
     * Clear all or for name HTML(scting) cache
     *
     * @param string $name
     */
    public static function ClearHTMLCache()
    {
        $args = func_get_args();
         
        if ($args)
        {
            foreach ($args as $cache_name)
            {
                @unlink(self::$HTML_cache_path . $cache_name . ".tmp");
            }
        }
        else
        {
            $dir = opendir(self::$HTML_cache_path);

            while ($file = readdir($dir))
            {
                if ($file != "." && $file != ".." && !is_dir(self::$HTML_cache_path . $file))
                {
                    @unlink(self::$HTML_cache_path . $file);
                }
            }
        }
    }

    /**
     * Clear all cache? array and HTML(string)
     *
     */
    public static function ClearAllCache()
    {
        self::ClearArrayCache();
        self::ClearHTMLCache();
    }

    /**
     * Check cache file for safe time
     *
     * @access private
     * @param string $file
     * @param int $time - seconds of time for save time
     * @return bool
     */
    private static function SafeTime($file, $time)
    {
        return (time() - filemtime($file) > $time);
    }
}
?>