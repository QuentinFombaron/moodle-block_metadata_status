<?php

namespace block_metadata_status\output;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use renderable;
use renderer_base;
use stdClass;
use templatable;

class metadata_status implements renderable, templatable {

    /**
     * @var object An object containing the configuration information for the current instance of this block.
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param object $config An object containing the configuration information for the current instance of this block.
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     *
     * @return stdClass
     *
     * @throws coding_exception
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $data->sharedModules = 0;
        $data->sharedModulesText = strtoupper(get_string('shared_modules', 'block_metadata_status'));

        $data->filledModules = 0;
        $data->filledModulesText = strtoupper(get_string('filled_modules', 'block_metadata_status'));

        $data->existingMetadata = 0;
        $data->existingMetadataText = strtoupper(get_string('existing_metadata', 'block_metadata_status'));

        $data->trackedMetadata = 0;
        $data->trackedMetadataText = strtoupper(get_string('tracked_metadata', 'block_metadata_status'));

        $data->text = $this->config->text;

        return $data;
    }
}
