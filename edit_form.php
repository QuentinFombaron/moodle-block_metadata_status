<?php
global $CFG;

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/metadata_status/lib.php');

/**
 * Class block_metadata_status_edit_form
 */
class block_metadata_status_edit_form extends block_edit_form {

    /**
     * @param object $mform
     *
     * @throws coding_exception
     */
    protected function specific_definition($mform) {

        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        // A sample string variable with a default value.
        $mform->addElement('text', 'config_text', get_string('blockstring', 'block_metadata_status'));
        $mform->setDefault('config_text', 'default value');
        $mform->setType('config_text', PARAM_RAW);
    }
}