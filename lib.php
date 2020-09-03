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
            WHERE contextlevel = :contextlevel AND datatype != :datatype';

    $params = ['contextlevel' => 70, 'datatype' => 'checkbox'];

    return $DB->get_records_sql($sql, $params);
}

/**
 * Get Shared field ID
 *
 * @return mixed
 *
 * @throws dml_exception
 */
function block_metadata_status_get_shared_field_id() {
    global $DB;

    $sharedMetadataShortName = get_config('block_metadata_status', 'shared_metadata_short_name');

    $sql = 'SELECT id
            FROM {local_metadata_field}
            WHERE contextlevel = :contextlevel AND shortname = :sharedshortname';

    $params = ['contextlevel' => 70, 'sharedshortname' => $sharedMetadataShortName];

    return intval(array_values($DB->get_records_sql($sql, $params))[0]->id);
}

/**
 * Get shared modules length
 *
 * @return int
 *
 * @throws dml_exception
 */
function block_metadata_status_get_shared_modules_length() {
    global $DB;

    $sharedMetadataId = block_metadata_status_get_shared_field_id();

    $sql = 'SELECT id
            FROM {local_metadata}
            WHERE fieldid = :sharedid AND data = :data';

    $params = ['sharedid' => $sharedMetadataId, 'data' => '1'];

    return count($DB->get_records_sql($sql, $params));
}

/**
 * @param int $courseId
 *
 * @return int
 *
 * @throws dml_exception
 */
function block_metadata_status_get_filled_modules_length($courseId) {
    $metadataStatus = block_metadata_status_get_metadata_status($courseId)->modules;

    $modulesFilled = array_values(
        array_filter(
            $metadataStatus,
            function($item) {
                return $item['status']['percentage'] == 100;
            }
        )
    );

    return count($modulesFilled);
}

/**
 * @return int
 *
 * @throws dml_exception
 */
function block_metadata_status_get_metadata_length() {
    global $DB;

    $sql = 'SELECT id
            FROM {local_metadata_field}
            WHERE contextlevel = :contextlevel';

    $params = ['contextlevel' => 70];

    return count($DB->get_records_sql($sql, $params));
}

/**
 * @return int
 *
 * @throws dml_exception
 */
function block_metadata_status_get_tracked_metadata_length() {
    global $DB;

    $sql = 'SELECT id
            FROM {config_plugins}
            WHERE plugin = :plugin AND name LIKE :name AND value = :value';

    $params = ['plugin' => 'block_metadata_status', 'name' => 'enable_metadata%', 'value' => '1'];

    return count($DB->get_records_sql($sql, $params));
}

/**
 * @param int $courseId
 *
 * @return stdClass
 *
 * @throws dml_exception
 */
function block_metadata_status_get_metadata_status($courseId) {
    global $DB;

    $modules = $DB->get_records('course_modules', array('course' => $courseId), null, 'id');

    /** TODO Improve checkbox filter */
    $sql = 'SELECT id, shortname, datatype, defaultdata
            FROM {local_metadata_field}
            WHERE contextlevel = :contextlevel';

    $params = ['contextlevel' => 70];

    $moduleMetadataFields = $DB->get_records_sql($sql, $params);

    $moduleMetadataFieldIdsTracked = [];
    foreach ($moduleMetadataFields as $moduleMetadataField) {
        if ($moduleMetadataField->datatype !== 'checkbox'
            || get_config('block_metadata_status', 'enable_metadata_' . $moduleMetadataField->id . '_tracking')) {
            array_push($moduleMetadataFieldIdsTracked, $moduleMetadataField->id);
        }
    }

    $moduleMetadataFieldIdsTrackedLength = count($moduleMetadataFieldIdsTracked);

    $sharedMetadataId = block_metadata_status_get_shared_field_id();

    $moduleIds = array_map(function($item) {return $item->id;}, $modules);
    $sql = 'SELECT instanceid, fieldid, data
            FROM {local_metadata}
            WHERE instanceid = :module'. join(' OR instanceid = :module', $moduleIds);

    $params = [];

    foreach ($moduleIds as $moduleId) {
        $params['module'. $moduleId]= $moduleId;
    }

    $sets = $DB->get_recordset_sql($sql, $params);

    $moduleMetadata = [];
    foreach ($sets as $set) {
        $moduleMetadata[] = ['instanceid' => $set->instanceid, 'fieldid' => $set->fieldid, 'data' => $set->data];
    }

    $sets->close();

    $metadataStatus = new stdClass();

    foreach ($modules as $module) {
        $moduleMetadataFieldsFilledLength = 0;
        foreach ($moduleMetadataFieldIdsTracked as $moduleMetadataFieldIdTracked) {
            $metadata = array_values(
                array_filter(
                    $moduleMetadata,
                    function($item) use ($module, $moduleMetadataFieldIdTracked) {
                        return $item['instanceid'] == $module->id && $item['fieldid'] == $moduleMetadataFieldIdTracked;
                    }
                )
            )[0];

            $defaultValue = array_values(
                array_filter(
                    $moduleMetadataFields,
                    function($item) use ($moduleMetadataFieldIdTracked) {
                        return $item->id === $moduleMetadataFieldIdTracked;
                    }
                )
            )[0]->defaultdata;

            if ($metadata && $metadata['data'] !== '' && $metadata['data'] !== $defaultValue) {
                $moduleMetadataFieldsFilledLength++;
            }
        }
        $percentage = intval((100 * $moduleMetadataFieldsFilledLength ) / $moduleMetadataFieldIdsTrackedLength);
        $shared = array_values(
                array_filter(
                    $moduleMetadata,
                    function($item) use ($module, $sharedMetadataId) {
                        return $item['instanceid'] == $module->id && $item['fieldid'] == $sharedMetadataId;
                    }
                )
            )[0]['data'] == '1';
        $metadataStatus->modules[] = ['moduleId' => $module->id, 'status' => ['percentage' => $percentage, 'shared' => $shared]];
    }

    $metadataStatus->options->enablePercentageLabel = get_config('block_metadata_status', 'enable_percentage_label') === '1';
    $metadataStatus->options->progressBarBackgroundColor = get_config('block_metadata_status', 'progress_bar_background_color');
    $metadataStatus->options->progressBarColor = get_config('block_metadata_status', 'progress_bar_color');

    return $metadataStatus;
}

