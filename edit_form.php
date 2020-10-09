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

        $mform->addElement(
            'advcheckbox',
            'config_disable_text',
            get_string('config_disable_block_content', 'block_metadata_status'),
            get_string('config_disable_block_content_desc', 'block_metadata_status'),
            array(),
            array(0, 1)
        );

        $mform->addHelpButton('config_disable_text', 'howto_disable_text', 'block_metadata_status');

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context);
        $mform->addElement(
            'editor',
            'config_text',
            get_string('config_block_content', 'block_metadata_status'),
            get_string('config_block_content_desc', 'block_metadata_status'),
            $editoroptions
        );
        $mform->setType('config_text', PARAM_RAW);

        $mform->addHelpButton('config_text', 'howto_text', 'block_metadata_status');

        $mform->disabledIf(
            'config_text',
            'config_disable_text',
            'checked'
        );
    }

    /**
     * Editor data
     *
     * @param {array} $defaults
     */
    public function set_data($defaults) {
        $text = '';
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $text = $this->block->config->text;
            $draftideditor = file_get_submitted_draft_itemid('config_text');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_text['text'] = file_prepare_draft_area(
                $draftideditor,
                $this->block->context->id,
                'block_metadata_status',
                'content',
                0,
                array('subdirs' => true),
                $currenttext
            );
            $defaults->config_text['itemid'] = $draftideditor;
            $defaults->config_text['format'] = $this->block->config->format;
        }

        unset($this->block->config->text);

        parent::set_data($defaults);

        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }

        $this->block->config->text = $text;
    }
}