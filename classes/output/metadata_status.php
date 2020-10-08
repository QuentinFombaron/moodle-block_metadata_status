<?php

namespace block_metadata_status\output;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/blocks/metadata_status/lib.php');

use coding_exception;
use context;
use context_block;
use context_course;
use dml_exception;
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
     * @throws dml_exception
     */
    public function export_for_template(renderer_base $output) {
        global $COURSE, $DB;

        $data = new stdClass();

        $data->sharedModules = block_metadata_status_get_shared_modules_length();
        $data->sharedModulesText = mb_strtoupper(get_string('shared_modules', 'block_metadata_status'));

        $data->filledModules = block_metadata_status_get_filled_modules_length();
        $data->filledModulesText = mb_strtoupper(get_string('filled_modules', 'block_metadata_status'));

        $data->existingMetadata = block_metadata_status_get_metadata_length();
        $data->existingMetadataText = mb_strtoupper(get_string('existing_metadata', 'block_metadata_status'));

        $data->trackedMetadata = block_metadata_status_get_tracked_metadata_length();
        $data->trackedMetadataText = mb_strtoupper(get_string('tracked_metadata', 'block_metadata_status'));

        $filteropt = new stdClass;
        if ($this->content_is_trusted()) {
            $filteropt->noclean = true;
        }

        $coursecontext = context_course::instance($COURSE->id);
        $blockid = $DB->get_field('block_instances', 'id', ['blockname' => 'metadata_status', 'parentcontextid' => $coursecontext->id]);
        $context = context_block::instance($blockid);

        if (isset($this->config->text) && $this->config->text !== null && $this->config->text !== '' && strlen($this->config->text) > 0) {
            $this->config->text = file_rewrite_pluginfile_urls(
                $this->config->text,
                'pluginfile.php',
                $context->id,
                'block_metadata_status',
                'content',
                null
            );
        } else {
            $this->config = new stdClass();
            $this->config->text = get_config('block_metadata_status', 'config_text_admin');
        }

        $format = FORMAT_HTML;
        if (isset($this->config->format)) {
            $format = $this->config->format;
        }

        $data->hasText = $this->config->text !== null && $this->config->text !== '' && strlen($this->config->text) > 0;
        $data->text = format_text($this->config->text, $format, $filteropt);

        return $data;
    }

    /**
     * Is content trusted
     *
     * @return bool
     *
     * @throws coding_exception
     */
    public function content_is_trusted() {
        global $SCRIPT, $COURSE;

        if (!$context = context::instance_by_id(context_course::instance($COURSE->id)->get_parent_context()->id, IGNORE_MISSING)) {
            return false;
        }
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
}
