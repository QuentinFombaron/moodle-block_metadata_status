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
     * @return stdClass Modules IDs
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

    /**
     * Return course module IDs
     *
     * @return external_description
     */
    public static function get_modules_status_returns() {
        return new external_single_structure(
            array(
                'modules' => new external_multiple_structure(
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
                ),
                'options' => new external_single_structure(
                    array(
                        'enablePercentageLabel' => new external_value(PARAM_BOOL, 'Enable percentage label'),
                        'progressBarBackgroundColor' => new external_value(PARAM_TEXT, 'Progress bar background color'),
                        'progressBarColor' => new external_value(PARAM_TEXT, 'Progress bar color')
                    )
                )
            )
        );
    }
}
