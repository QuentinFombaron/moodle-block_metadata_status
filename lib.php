<?php

defined('MOODLE_INTERNAL') || die;

const DEFAULT_METADATA_STATUS_ENABLE_METADATA_TRACKING = 1;
const DEFAULT_METADATA_STATUS_ENABLE_PERCENTAGE_LABEL = 1;
const DEFAULT_METADATA_STATUS_SHARED_SHORT_NAME = 'shared';
const DEFAULT_METADATA_STATUS_PROGRESS_BAR_BACKGROUND_COLOR = '#D3D3D3';
const DEFAULT_METADATA_STATUS_PROGRESS_BAR_COLOR = '#008000';

/**
 * Get module metadata
 *
 * @return array
 *
 * @throws dml_exception
 */
function block_metadata_status_get_module_metadata_fields() {
    global $DB;

    $sql = 'SELECT id, name, shortname, datatype
            FROM {local_metadata_field}
            WHERE contextlevel = :contextlevel AND datatype != "checkbox"';

    $params = ['contextlevel' => 70];

    return $DB->get_records_sql($sql, $params);
}

