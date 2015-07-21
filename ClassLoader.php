<?php namespace Sframe;

class ClassLoader
{
    protected static $_directories = array();
    protected static $_registered = false;

    /**
     * Load the given class file.
     *
     * @param  string  $class
     * @return bool
     */
    public static function load($class)
    {
        $class = ltrim($class, '\\');
        foreach (static::$_directories as $k => $directory) {
            if (strpos($class, $k) === 0) {
                $class = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, strstr($class, '\\')) . '.php';
                if (file_exists($path = $directory . $class)) {
                    require_once $path;
                }
            }
        }
    }

    /**
     * Register the given class loader on the auto-loader stack.
     *
     * @return void
     */
    public static function register()
    {
        if (!static::$_registered) {
            static::$_registered = spl_autoload_register(array('ClassLoader', 'load'));
        }
    }

    /**
     * Add directories to the class loader.
     *
     * @param  string|array  $_directories
     * @return void
     */
    public static function addDirectories($_directories)
    {
        static::$_directories = array_merge(static::$_directories, (array)$_directories);
        static::$_directories = array_unique(static::$_directories);
    }
    
    /**
     * Remove directories from the class loader.
     *
     * @param  string|array  $_directories
     * @return void
     */
    public static function removeDirectories($_directories = null)
    {
        if (is_null($_directories)) {
            static::$_directories = array();
        } else {
            $_directories = (array)$_directories;
            static::$_directories = array_filter(static::$_directories, function($directory) use ($_directories) {
                return (!in_array($directory, $_directories));
            });
        }
    }

    /**
     * Gets all the directories registered with the loader.
     *
     * @return array
     */
    public static function getDirectories()
    {
        return static::$_directories;
    }

}
