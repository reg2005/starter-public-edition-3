<?php defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('get_config'))
{
    /**
     * Loads the main config.php file
     *
     * This function lets us grab the config file even if the Config class
     * hasn't been instantiated yet
     *
     * @param     array
     * @return    array
     */
    function &get_config(Array $replace = array())
    {
        static $_config;
        // Added by Ivan Tcholakov, 13-OCT-2013.
        global $DETECT_URL;
        //

        if (empty($_config))
        {
            $file_path = APPPATH.'config/config.php';
            $found = FALSE;
            if (file_exists($file_path))
            {
                $found = TRUE;
                require($file_path);
            }

            // Is the config file in the environment folder?
            if (file_exists($file_path = APPPATH.'config/'.ENVIRONMENT.'/config.php'))
            {
                require($file_path);
            }
            elseif ( ! $found)
            {
                set_status_header(503);
                echo 'The configuration file does not exist.';
                exit(EXIT_CONFIG);
            }

            // Does the $config array exist in the file?
            if ( ! isset($config) OR ! is_array($config))
            {
                set_status_header(503);
                echo 'Your config file does not appear to be formatted correctly.';
                exit(EXIT_CONFIG);
            }

            // references cannot be directly assigned to static variables, so we use an array
            $_config[0] =& $config;
        }

        // Are any values being dynamically added or replaced?
        foreach ($replace as $key => $val)
        {
            $_config[0][$key] = $val;
        }

        return $_config[0];
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_exception_handler'))
{
    /**
     * Exception Handler
     *
     * This is the custom exception handler that is declared at the top
     * of CodeIgniter.php. The main reason we use this is to permit
     * PHP errors to be logged in our own log files since the user may
     * not have access to server logs. Since this function
     * effectively intercepts PHP errors, however, we also need
     * to display errors based on the current error_reporting level.
     * We do that with the use of a PHP error template.
     *
     * @param       int         $severity
     * @param       string      $message
     * @param       string      $filepath
     * @param       int         $line
     * @return      void
     */
    function _exception_handler($severity, $message, $filepath, $line)
    {
        // We don't bother with "strict" notices since they tend to fill up
        // the log file with excess information that isn't normally very helpful.
        // For example, if you are running PHP 5 and you use version 4 style
        // class functions (without prefixes like "public", "private", etc.)
        // you'll get notices telling you that these have been deprecated.
        if ($severity == E_STRICT)
        {
            return;
        }

        $is_error = (((E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);

        // When an error occurred, set the status header to '500 Internal Server Error'
        // to indicate to the client something went wrong.
        // This can't be done within the $_error->show_php_error method because
        // it is only called when the display_errors flag is set (which isn't usually
        // the case in a production environment) or when errors are ignored because
        // they are above the error_reporting threshold.
        if ($is_error)
        {
            set_status_header(500);
        }

        $_error =& load_class('Exceptions', 'core');

        // Should we ignore the error? We'll get the current error_reporting
        // level and add its bits with the severity bits to find out.
        if (($severity & error_reporting()) !== $severity)
        {
            return;
        }

        // Should we display the error?
        if ((bool) ini_get('display_errors') === TRUE)
        {
            $_error->show_php_error($severity, $message, $filepath, $line);
        }

        $_error->log_exception($severity, $message, $filepath, $line);

        // If the error is fatal, the execution of the script should be stopped because
        // errors can't be recovered from. Halting the script conforms with PHP's
        // default error handling. See http://www.php.net/manual/en/errorfunc.constants.php
        if ($is_error)
        {
            exit(EXIT_ERROR);
        }
    }
}
