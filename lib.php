<?php

defined('MOODLE_INTERNAL') || die;

/** Administration configuration to enable metadata tracking */
const DEFAULT_METADATA_STATUS_ENABLE_METADATA_TRACKING = 1;
/** Administration configuration to specify the metadata shortname of sharing feature */
const DEFAULT_METADATA_STATUS_SHARED_SHORT_NAME = 'shared';

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

