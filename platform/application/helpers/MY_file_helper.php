<?php defined('BASEPATH') OR exit('No direct script access allowed.');

// Load the url helper too!
if (!function_exists('url_title')) {
    ci()->load->helper('url');
}

if (!function_exists('clean_file_name')) {

    // For UTF-8 encoded sites only.
    function clean_file_name($file_name, $separator = '-',  $lowercase = false, $language = NULL) {

        $extension = UTF8::strtolower(extension($file_name));

        if ($extension != '') {
            $file_name = substr($file_name, 0, -(strlen($extension) + 1));
        }

        $file_name = url_title($file_name, $separator, $lowercase, true, $language);

        if ($file_name == '') {
            $file_name = 'file';
        }

        if ($extension != '') {
            $file_name .= '.'.$extension;
        }

        return $file_name;
    }

}

if (!function_exists('fixed_basename')) {

    // See http://api.drupal.org/api/drupal/core!includes!file.inc/function/drupal_basename/8

    function fixed_basename($uri, $suffix = NULL) {

        $separators = '/';

        if (DIRECTORY_SEPARATOR != '/') {
            // For Windows OS add special separator.
            $separators .= DIRECTORY_SEPARATOR;
        }

        // Remove right-most slashes when $uri points to directory.
        $uri = rtrim($uri, $separators);

        // Returns the trailing part of the $uri starting after one of the directory
        // separators.
        $filename = preg_match('@[^' . preg_quote($separators, '@') . ']+$@', $uri, $matches) ? $matches[0] : '';

        // Cuts off a suffix from the filename.
        if ($suffix) {
            $filename = preg_replace('@' . preg_quote($suffix, '@') . '$@', '', $filename);
        }

        return $filename;
    }

}

if (!function_exists('extension')) {

    function extension($path) {

        $qpos = strpos($path, '?');

        if ($qpos !== false) {

            // Eliminate query string.
            $path = substr($path, 0, $qpos);
        }

        return substr(strrchr($path, '.'), 1);
    }

}

if (!function_exists('recursive_chmod')) {

    /*
    Example:

    // Platform's core initialization.
    require dirname(__FILE__).'/config.php'; // This is www/config.php file.
    require $PLATFORMCREATE;

    $dir = FCPATH.'images/';
    ci()->load->helper('file');

    recursive_chmod($dir, FILE_WRITE_MODE, DIR_WRITE_MODE);

    echo 'OK';
    */

    // See http://snipplr.com/view.php?codeview&id=5350

    /**
      Chmods files and folders with different permissions.

      This is an all-PHP alternative to using: \n
      <tt>exec("find ".$path." -type f -exec chmod 644 {} \;");</tt> \n
      <tt>exec("find ".$path." -type d -exec chmod 755 {} \;");</tt>

      @author Jeppe Toustrup (tenzer at tenzer dot dk)
      @param $path An either relative or absolute path to a file or directory
      which should be processed.
      @param $file_permissions The permissions any found files should get.
      @param $directory_permissions The permissions any found folder should get.
      @return Returns TRUE if the path if found and FALSE if not.
      @warning The permission levels has to be entered in octal format, which
      normally means adding a zero ("0") in front of the permission level. \n
      More info at: http://php.net/chmod.
    */

    function recursive_chmod($path, $file_permissions = 0644, $directory_permissions = 0755) {

        // Check if the path exists
        if (!file_exists($path)) {
            return false;
        }

        // See whether this is a file
        if (is_file($path)) {

            // Chmod the file with our given filepermissions
            chmod($path, $file_permissions);

        // If this is a directory...
        } elseif (is_dir($path)) {

            // Then get an array of the contents
            $folders_and_files = scandir($path);

            // Remove "." and ".." from the list
            $entries = array_slice($folders_and_files, 2);

            // Parse every result...
            foreach ($entries as $entry) {

                // And call this function again recursively, with the same permissions
                recursive_chmod($path.'/'.$entry, $file_permissions, $directory_permissions);
            }

            // When we are done with the contents of the directory, we chmod the directory itself
            chmod($path, $directory_permissions);
        }

        // Everything seemed to work out well, return true
        return true;
    }

}
