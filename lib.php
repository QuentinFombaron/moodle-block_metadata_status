<?php

defined('MOODLE_INTERNAL') || die;

/**
 * Get module metadata
 *
 * @return array
 *
 * @throws dml_exception
 */
function block_metadata_status_get_module_metadatas() {
    global $DB;

    $sql = 'SELECT id, name, datatype
            FROM {local_metadata_field}
            WHERE contextlevel = :contextlevel';

    $params = ['contextlevel' => 70];

    return $DB->get_records_sql($sql, $params);
}

