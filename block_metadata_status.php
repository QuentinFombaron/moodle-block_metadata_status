<?php

use block_metadata_status\output\metadata_status;

class block_metadata_status extends block_base {

    /**
     * Allow the block to have a configuration page
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Enable to add the block only in a course
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'site-index' => true,
            'course-view' => true,
        );
    }

    /**
     * Allow more than one instance of the block on a page
     *
     * @return bool
     */
    function instance_allow_multiple() {
        return false;
    }

    /**
     * Instance specialisations (must have instance allow config true)
     *
     */
    public function specialization() {
    }

    /**
     * Post install configurations
     *
     */
    public function after_install() {
    }

    /**
     * Post delete configurations
     *
     */
    public function before_delete() {
    }

    /**
     * Block initializations
     *
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('metadata_status', 'block_metadata_status');
    }

    /**
     * Block contents
     *
     * @return string
     *
     * @throws coding_exception
     */
    public function get_content() {
        global $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        $renderable = new metadata_status($this->config);
        $renderer = $this->page->get_renderer('block_metadata_status');

        $this->content         =  new stdClass;
        $this->content->text   = $renderer->render($renderable);
        $this->content->footer = '';

        if ($this->page->user_is_editing()) {
            $params = [['courseId' => $COURSE->id]];

            $this->page->requires->js_call_amd('block_metadata_status/script_metadata_status', 'init', $params);
        }

        return $this->content;
    }

    /**
     * Serialize and store config data
     *
     * Save data from file manager when user is saving configuration.
     * Delete file storage if user disable custom emojis.
     *
     * @param mixed $data
     * @param mixed $nolongerused
     */
    public function instance_config_save($data, $nolongerused = false) {

        $config = clone($data);

        $config->text = file_save_draft_area_files(
            $data->text['itemid'],
            $this->context->id,
            'block_metadata_status',
            'content',
            0,
            array('subdirs' => true),
            $data->text['text']
        );

        $config->format = $data->text['format'];

        parent::instance_config_save($config, $nolongerused);

    }

    /**
     * Delete file storage.
     *
     * @return bool
     */
    public function instance_delete() {

        $fs = get_file_storage();

        $fs->delete_area_files($this->context->id, 'block_metadata_status');

        return true;

    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     *
     * @param int $fromid the id number of the block instance to copy from
     * @return boolean
     */
    public function instance_copy($fromid) {

        $fromcontext = context_block::instance($fromid);

        $fs = get_file_storage();

        if (!$fs->is_area_empty($fromcontext->id, 'block_metadata_status', 'content', 0, false)) {

            $draftitemid = 0;

            file_prepare_draft_area(
                $draftitemid,
                $fromcontext->id,
                'block_metadata_status',
                'content',
                0,
                array('subdirs' => true)
            );

            file_save_draft_area_files(
                $draftitemid,
                $this->context->id,
                'block_metadata_status',
                'content',
                0,
                array('subdirs' => true)
            );

        }

        return true;

    }
}
