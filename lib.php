<?php

use core\session\manager;

if (file_exists($CFG->dirroot . '/local/sharedspaceh/spacelib.php')) {
    require_once($CFG->dirroot . '/local/sharedspaceh/spacelib.php');
}


defined('MOODLE_INTERNAL') || die;

const DEFAULT_METADATA_STATUS_ENABLE_METADATA_TRACKING = 1;
const DEFAULT_METADATA_STATUS_ENABLE_PERCENTAGE_LABEL = 1;
const DEFAULT_METADATA_STATUS_SHARED_SHORT_NAME = 'shared';
const DEFAULT_METADATA_STATUS_PROGRESS_BAR_BACKGROUND_COLOR = '#D3D3D3';
const DEFAULT_METADATA_STATUS_PROGRESS_BAR_COLOR_BEFORE_THRESHOLD = '#FF0000';
const DEFAULT_METADATA_STATUS_PROGRESS_BAR_COLOR_AFTER_THRESHOLD = '#008000';
const DEFAULT_METADATA_STATUS_PROGRESS_BAR_THRESHOLD = 7;

/**
 * Get module metadata fields
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
 * Get module metadata field ids
 *
 * @return array
 *
 * @throws dml_exception
 */
function block_metadata_status_get_module_metadata_field_ids() {
    return array_map(function($metadata) {
        return $metadata->id;
    }, block_metadata_status_get_module_metadata_fields());
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
    global $DB, $COURSE;

    $modules = $DB->get_records('course_modules', array('course' => $COURSE->id), null, 'id');

    $moduleIds = array_map(function ($item) {
        return $item->id;
    }, $modules);

    $sharedMetadataId = block_metadata_status_get_shared_field_id();

    $sql = 'SELECT id
            FROM {local_metadata}
            WHERE (instanceid = :module' . join(' OR instanceid = :module', $moduleIds) . ') AND fieldid = :sharedid AND data = :data';

    $params = ['sharedid' => $sharedMetadataId, 'data' => '1'];

    foreach ($moduleIds as $moduleId) {
        $params['module' . $moduleId] = $moduleId;
    }

    return count($DB->get_records_sql($sql, $params));
}

/**
 * @param string $courseId
 *
 * @return int
 *
 * @throws dml_exception|ddl_exception
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

    $sql = 'SELECT name
            FROM {config_plugins}
            WHERE plugin = :plugin AND name LIKE :name AND value = :value';

    $params = ['plugin' => 'block_metadata_status', 'name' => 'enable_metadata%', 'value' => '1'];

    $metadataFieldsIds = block_metadata_status_get_module_metadata_field_ids();
    $trackedMetadataIds = array_map(function($metadata) {
        return intval(strtr($metadata->name, ['enable_metadata_' => '', '_tracking' => '']));
    }, $DB->get_records_sql($sql, $params));

    return count(array_intersect($metadataFieldsIds, $trackedMetadataIds));
}

/**
 * @param string $courseId
 *
 * @return stdClass
 *
 * @throws dml_exception|ddl_exception
 */
function block_metadata_status_get_metadata_status($courseId)
{
    global $DB;

    // $modules = $DB->get_records('course_modules', array('course' => $courseId), null, 'id');

    $modules = $DB->get_records_sql(
        'SELECT id FROM {course_modules} WHERE course = :courseid AND module != :moduleid',
        ['courseid' => $courseId, 'moduleid' => 12]
    );

    $sql = 'SELECT id, shortname, datatype, defaultdata
            FROM {local_metadata_field}
            WHERE contextlevel = :contextlevel AND datatype != :datatype';
    $params = ['contextlevel' => 70, 'datatype' => 'checkbox'];

    $moduleMetadataFields = $DB->get_records_sql($sql, $params);

    if ($DB->get_manager()->table_exists('local_sharedspaceh_teams')) {
        $teams = $DB->get_records_menu('local_sharedspaceh_teams', null, 'teamname ASC', 'id, capabilityid');
        foreach ($moduleMetadataFields as $index => $moduleMetadataField) {
            if (!h_has_capability_to_see_fieldid($moduleMetadataField->id, $teams, context_course::instance($courseId))) {
                unset($moduleMetadataFields[$index]);
            }
        }
        $moduleMetadataFields = array_values($moduleMetadataFields);
    }

    $moduleMetadataFieldIdsTracked = [];
    foreach ($moduleMetadataFields as $moduleMetadataField) {
        if (get_config('block_metadata_status', 'enable_metadata_' . $moduleMetadataField->id . '_tracking')) {
            array_push($moduleMetadataFieldIdsTracked, $moduleMetadataField->id);
        }
    }

    $moduleMetadataFieldIdsTrackedLength = count($moduleMetadataFieldIdsTracked);

    $sharedMetadataId = block_metadata_status_get_shared_field_id();

    $moduleIds = array_map(function ($item) {
        return $item->id;
    }, $modules);
    $sql = 'SELECT instanceid, fieldid, data
            FROM {local_metadata}
            WHERE instanceid = :module' . join(' OR instanceid = :module', $moduleIds);

    $params = [];

    foreach ($moduleIds as $moduleId) {
        $params['module' . $moduleId] = $moduleId;
    }

    $sets = $DB->get_recordset_sql($sql, $params);

    $moduleMetadata = [];
    foreach ($sets as $set) {
        $moduleMetadata[] = ['instanceid' => $set->instanceid, 'fieldid' => $set->fieldid, 'data' => $set->data];
    }

    $sets->close();

    $metadataStatus = new stdClass();

    $metadataStatus->modules = [];

    foreach ($modules as $module) {
        $moduleMetadataFieldsFilledLength = 0;
        foreach ($moduleMetadataFieldIdsTracked as $moduleMetadataFieldIdTracked) {
            $metadata = array_values(
                array_filter(
                    $moduleMetadata,
                    function ($item) use ($module, $moduleMetadataFieldIdTracked) {
                        return $item['instanceid'] == $module->id && $item['fieldid'] == $moduleMetadataFieldIdTracked;
                    }
                )
            );

            if (count($metadata) === 1) {
                $metadata = $metadata[0];

                $defaultValue = array_values(
                    array_filter(
                        $moduleMetadataFields,
                        function ($item) use ($moduleMetadataFieldIdTracked) {
                            return $item->id === $moduleMetadataFieldIdTracked;
                        }
                    )
                )[0]->defaultdata;

                if ($metadata && $metadata['data'] !== '' && $metadata['data'] !== $defaultValue) {
                    $moduleMetadataFieldsFilledLength++;
                }
            }
        }
        $percentage = intval((100 * $moduleMetadataFieldsFilledLength) / $moduleMetadataFieldIdsTrackedLength);
        $shared = array_values(
            array_filter(
                $moduleMetadata,
                function ($item) use ($module, $sharedMetadataId) {
                    return $item['instanceid'] == $module->id && $item['fieldid'] == $sharedMetadataId;
                }
            )
        );

        if (count($shared) === 1) {
            $shared = $shared[0]['data'] == '1';
        } else {
            $shared = false;
        }

        $metadataStatus->modules[] = ['id' => $module->id, 'status' => ['percentage' => $percentage, 'shared' => $shared]];
    }

    $metadataStatus->options = new stdClass();

    $metadataStatus->options->enablePercentageLabel = get_config('block_metadata_status', 'enable_percentage_label') === '1';
    $metadataStatus->options->progressBarBackgroundColor = get_config('block_metadata_status', 'progress_bar_background_color');
    $metadataStatus->options->progressBarThreshold = get_config('block_metadata_status', 'progress_bar_threshold');
    $metadataStatus->options->progressBarColorBeforeThreshold = get_config('block_metadata_status', 'progress_bar_color_before_threshold');
    $metadataStatus->options->progressBarColorAfterThreshold = get_config('block_metadata_status', 'progress_bar_color_after_threshold');

    return $metadataStatus;
}

/**
 * Form for editing HTML block instances.
 *
 * @param stdClass $course Course object
 * @param stdClass $bi Block instance record
 * @param stdClass $context Context object
 * @param string $filearea File area
 * @param array $args Extra arguments
 * @param bool $forcedownload Whether or not force download
 * @param array $options Additional options affecting the file serving
 *
 * @return bool
 *
 * @throws coding_exception
 * @throws moodle_exception
 * @throws require_login_exception
 */
function block_metadata_status_pluginfile($course, $bi, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG, $USER;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {

        $parentcontext = $context->get_parent_context();

        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {

            if (!core_course_category::get($parentcontext->instanceid, IGNORE_MISSING)) {
                send_file_not_found();
            }

        } else if ($parentcontext->contextlevel === CONTEXT_USER && $parentcontext->instanceid != $USER->id) {
            send_file_not_found();
        }
    }

    $fs = get_file_storage();

    $filename = array_pop($args);

    $filepath = '/';

    if ($filearea === 'content') {

        if (!$file = $fs->get_file($context->id, 'block_metadata_status', 'content', 0, $filepath, $filename) or $file->is_directory()) {
            send_file_not_found();
        }
    }

    manager::write_close();

    send_stored_file($file, null, 0, true, $options);

    return true;
}

/**
 * Perform global search replace such as when migrating site to new URL.
 *
 * @param string $search
 * @param string $replace
 *
 * @throws dml_exception
 */
function block_metadata_status_global_db_replace($search, $replace) {
    global $DB;

    $instances = $DB->get_recordset('block_instances', array('blockname' => 'metadata_status'));
    foreach ($instances as $instance) {
        $config = unserialize(base64_decode($instance->configdata));
        if (isset($config->text) and is_string($config->text)) {
            $config->text = str_replace($search, $replace, $config->text);
            $DB->update_record('block_instances', ['id' => $instance->id,
                'configdata' => base64_encode(serialize($config)), 'timemodified' => time()]);
        }
    }
    $instances->close();
}

/**
 * Given an array with a file path, it returns the itemid and the filepath for the defined filearea.
 *
 * @param string $filearea
 * @param array $args
 *
 * @return array
 */
function block_metadata_status_get_path_from_pluginfile($filearea, $args) {
    array_shift($args);

    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    return [
        'itemid' => 0,
        'filepath' => $filepath,
    ];
}
