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
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all'=>true);
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
}
