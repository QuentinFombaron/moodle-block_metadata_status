<?php
class block_metadata_status extends block_base {
    public function init() {
        $this->title = get_string('metadata_status', 'block_metadata_status');
    }
    // The PHP tag and the curly bracket for the class definition
    // will only be closed after there is another function added in the next section.

    public function get_content() {
        global $USER, $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         =  new stdClass;
        $this->content->text   = 'The content of our Metadata_Status block!';
        $this->content->footer = 'Footer here...';

        $params = [['courseId' => $COURSE->id]];

        $this->page->requires->js_call_amd('block_metadata_status/script_metadata_status', 'init', $params);

        return $this->content;
    }
}
