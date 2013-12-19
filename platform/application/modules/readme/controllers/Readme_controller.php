<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ivan Tcholakov <ivantcholakov@gmail.com>, 2013
 * @license The MIT License, http://opensource.org/licenses/MIT
 */

class Readme_controller extends Base_Controller {

    public function __construct() {

        parent::__construct();

        $this->load->parser('markdown');

        $this->template
            ->title('README')
        ;
    }

    public function index() {

        $content = '<h1>The file README.md has not been found.</h1>';

        $path = '';

        if (file_exists(PLATFORMPATH.'../README.md')) {
            $path = realpath(PLATFORMPATH.'../README.md');
        }
        elseif (file_exists(FCPATH.'../README.md')) {
            $path = realpath(FCPATH.'../README.md');
        }
        elseif (file_exists(FCPATH.'README.md')) {
            $path = FCPATH.'README.md';
        }

        if ($path != '') {
            $content = $this->markdown->parse_string(@ file_get_contents($path), null, true);
        }

        $this->template
            ->set('content', $content)
            ->build('readme');
    }

}