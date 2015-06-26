<?php
namespace Payname;

class Autoloader
{
    /**
     * Register the autoloader
     *
     * @param boolean $prepend
     */
    public static function register($prepend = true)
    {
        spl_autoload_register(array(__CLASS__, 'autoload'), true, (bool) $prepend);
    }

    /**
     * Unregister this autoloader
     */
    public static function unregister()
    {
        spl_autoload_unregister(array(__CLASS__, 'autoload'));
    }

    /**
     * Give a class name and it will require the file.
     *
     * @param  string $class
     * @throws \Exception
     * @return bool
     */
    public static function autoload($class)
    {
        if (0 === strpos($class, 'Payname\\')) {
            $classname = substr($class, 8);
            $file = __DIR__.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $classname).'.php';
            if (is_file($file) && is_readable($file)) {
                require_once $file;
                return true;
            }
            throw new \Exception(sprintf('Class "%s" Not Found', $class));
        }
    }
}
