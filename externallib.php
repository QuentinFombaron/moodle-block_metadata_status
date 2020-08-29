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

        $sql = 'SELECT id, shortname, datatype, defaultdata
            FROM {local_metadata_field}
            WHERE contextlevel = :contextlevel';

        $params = ['contextlevel' => 70];

        $moduleMetadataFields = $DB->get_records_sql($sql, $params);

        $moduleMetadataFieldIds = array_map(function ($item) { return $item->id; }, $moduleMetadataFields);

        $moduleMetadataFieldIdsTracked = [];
        foreach ($moduleMetadataFields as $moduleMetadataField) {
            if ($moduleMetadataField->datatype !== 'checkbox'
            || get_config('block_metadata_status', 'enable_metadata_' . $moduleMetadataField->id . '_tracking')) {
                array_push($moduleMetadataFieldIdsTracked, $moduleMetadataField->id);
            }
        }

        $moduleMetadataFieldIdsTrackedLength = count($moduleMetadataFieldIdsTracked);

        $sharedMetadataShortName = get_config('block_metadata_status', 'shared_metadata_short_name');
        $sharedMetadataId = array_values(
            array_filter(
                $moduleMetadataFields,
                function($item) use ($sharedMetadataShortName) {
                    return $item->shortname === $sharedMetadataShortName;
                }
            )
        )[0]->id;

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

        $metadataStatus = [];

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
            $metadataStatus[] = ['moduleId' => $module->id, 'status' => ['percentage' => $percentage, 'shared' => $shared]];
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
     * @return boolean
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

        $sql = 'SELECT id, shortname, datatype, defaultdata
            FROM {local_metadata_field}
            WHERE contextlevel = :contextlevel';

        $params = ['contextlevel' => 70];

        $moduleMetadataFields = $DB->get_records_sql($sql, $params);

        return $moduleMetadataFields;
    }

    /**
     * Return course module IDs array
     *
     * @return external_description
     */
    public static function get_debug_returns() {
        /*
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'instanceid' => new external_value(PARAM_INT, 'Module ID'),
                    'fieldid' => new external_value(PARAM_INT, 'Module ID'),
                    'data' => new external_value(PARAM_TEXT, 'Module ID'),
                )
            )
        );
        */

        //return new external_value(PARAM_TEXT, 'Boolean');

        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Module ID'),
                    'shortname' => new external_value(PARAM_TEXT, 'Module ID'),
                    'datatype' => new external_value(PARAM_TEXT, 'Module ID'),
                    'defaultdata' => new external_value(PARAM_TEXT, 'Module ID'),
                )
            )
        );
    }
}
