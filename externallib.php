<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/externallib.php');

class block_metadata_status_external extends external_api {
    /**
     * All necessary parameters
     *
     * @return external_function_parameters
     */
    public static function get_modules_status_parameters() {
        return new external_function_parameters(
            array(
                'courseId' => new external_value(PARAM_INT, 'ID of course', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Get course module IDs
     *
     * @param int $courseId Course ID
     *
     * @return array Modules IDs
     *
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_modules_status($courseId) {
        global $DB;

        self::validate_parameters(self::get_modules_status_parameters(), array(
                'courseId' => $courseId
            )
        );

        $modules = $DB->get_records('course_modules', array('course' => $courseId), null, 'id');

        $sql = 'SELECT id
            FROM {local_metadata_field}
            WHERE contextlevel = :contextlevel';

        $params = ['contextlevel' => 70];

        $moduleMetadataFieldIds = array_map(function ($item) { return $item->id; }, $DB->get_records_sql($sql, $params));
        $moduleMetadataFieldLength = count($moduleMetadataFieldIds);

        $moduleIds = array_map(function($item) {return $item->id;}, $modules);
        $sql = 'SELECT instanceid, fieldid, data
            FROM {local_metadata}
            WHERE instanceid = :module'. join(' OR instanceid = :module', $moduleIds);

        $params = [];

        foreach ($moduleIds as $moduleId) {
            $params['module'. $moduleId]= $moduleId;
        }

        $moduleMetadata = $DB->get_records_sql($sql, $params);

        // isset($blockinstance->config->{'moduleselectm' . $row->id})

        $metadataStatus = [];

        foreach ($modules as $module) {
            $temp = array_filter($moduleMetadata, function ($item) use ($module) {return $item->instanceid === $module->id && $item->fieldid === 1;});
            $shared = $temp[0]->data === 1;
            foreach ($moduleMetadataFieldIds as $moduleMetadataFieldId) {
            }
            $metadataStatus[] = ['moduleId' => $module->id, 'status' => ['percentage' => 25, 'shared' => $shared]];
        }

        return $metadataStatus;
    }

    /**
     * Return course module IDs array
     *
     * @return external_description
     */
    public static function get_modules_status_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'moduleId' => new external_value(PARAM_INT, 'Module ID'),
                    'status' => new external_single_structure(
                        array(
                            'percentage' => new external_value(PARAM_INT, 'Percentage of metadata filling'),
                            'shared' => new external_value(PARAM_BOOL, 'Is module shared')
                        )
                    )
                )
            )
        );
    }














    /**
     * All necessary parameters
     *
     * @return external_function_parameters
     */
    public static function get_debug_parameters() {
        return new external_function_parameters(
            array(
                'courseId' => new external_value(PARAM_INT, 'ID of course', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Get course module IDs
     *
     * @param int $courseId Course ID
     *
     * @return array Modules IDs
     *
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_debug($courseId) {
        global $DB;

        self::validate_parameters(self::get_modules_status_parameters(), array(
                'courseId' => $courseId
            )
        );

        $modules = $DB->get_records('course_modules', array('course' => $courseId), null, 'id');

        $moduleIds = array_map(function($item) {return $item->id;}, $modules);
        $sql = 'SELECT instanceid, fieldid, data
            FROM {local_metadata};';

        $result = $DB->get_records_sql($sql);

        return $result;
    }

    /**
     * Return course module IDs array
     *
     * @return external_description
     */
    public static function get_debug_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'instanceid' => new external_value(PARAM_INT, 'Module ID', VALUE_DEFAULT, 0),
                    'fieldid' => new external_value(PARAM_INT, 'Module ID', VALUE_DEFAULT, 0),
                    'data' => new external_value(PARAM_TEXT, 'Module ID', VALUE_DEFAULT, '0'),
                )
            )
        );
    }
}
