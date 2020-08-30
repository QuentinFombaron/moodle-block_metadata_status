<?php

namespace block_metadata_status\output;

defined('MOODLE_INTERNAL') || die;

use moodle_exception;
use plugin_renderer_base;

class renderer extends plugin_renderer_base {

    /**
     * Return the main content for the block metadata_status.
     *
     * @param metadata_status $metadata_status The metadata_status renderable
     *
     * @return string HTML string
     *
     * @throws moodle_exception
     */
    public function render_metadata_status(metadata_status $metadata_status) {
        return $this->render_from_template('block_metadata_status/metadata_status', $metadata_status->export_for_template($this));
    }
}
