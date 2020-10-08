<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot.'/blocks/metadata_status/lib.php');

class block_metadata_status_external extends external_api {
    /**
     * All necessary parameters
     *
     * @return external_function_parameters
     */
    public static function get_modules_status_parameters() {
        return new external_function_parameters(
            array(
                'courseId' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
                'context' => self::get_context_parameters()
            )
        );
    }

    /**
     * Returns a prepared structure to use a context parameters.
     * @return external_single_structure
     */
    protected static function get_context_parameters() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context ID. Either use this value, or level and instanceid.', VALUE_DEFAULT, 0),
            'contextlevel' => new external_value(PARAM_INT, 'Context level. To be used with instanceid.', VALUE_DEFAULT, 0),
            'instanceid' => new external_value(PARAM_TEXT, 'Context instance ID. To be used with level', VALUE_DEFAULT, ''),
            'path' => new external_value(PARAM_TEXT, 'Context path.', VALUE_DEFAULT, ''),
            'depth' => new external_value(PARAM_TEXT, 'Context depth.', VALUE_DEFAULT, ''),
            'locked' => new external_value(PARAM_TEXT, 'Context locked.', VALUE_DEFAULT, '')
        ));
    }

    /**
     * Get course module IDs
     *
     * @param int $courseId
     * @param stdClass $context
     *
     * @return stdClass Modules IDs
     *
     * @throws ddl_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_modules_status($courseId, $context) {
        self::validate_parameters(self::get_modules_status_parameters(), array(
                'courseId' => $courseId,
                'context' => $context
            )
        );

        return block_metadata_status_get_metadata_status($courseId, $context, true);
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
                            'id' => new external_value(PARAM_INT, 'Module ID', VALUE_REQUIRED),
                            'status' => new external_single_structure(
                                array(
                                    'percentage' => new external_value(PARAM_INT, 'Percentage of metadata filling', VALUE_REQUIRED),
                                    'shared' => new external_value(PARAM_BOOL, 'Is module shared', VALUE_REQUIRED)
                                )
                            ),
                            'fieldsFilled' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'instanceid' => new external_value(PARAM_INT, 'Metadata module ID', VALUE_OPTIONAL),
                                        'fieldid' => new external_value(PARAM_INT, 'Metadata field ID', VALUE_OPTIONAL),
                                        'data' => new external_value(PARAM_RAW, 'Metadata data', VALUE_OPTIONAL),
                                        'defaultdata' => new external_value(PARAM_RAW, 'Metadata field default value', VALUE_OPTIONAL)
                                    )
                                )
                            ),
                            'fieldsFilledLength' => new external_value(PARAM_INT, 'Module filled fields length', VALUE_OPTIONAL),
                            'fieldsNotFilled' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'instanceid' => new external_value(PARAM_INT, 'Metadata module ID', VALUE_OPTIONAL),
                                        'fieldid' => new external_value(PARAM_INT, 'Metadata field ID', VALUE_OPTIONAL),
                                        'data' => new external_value(PARAM_RAW, 'Metadata data', VALUE_OPTIONAL),
                                        'defaultdata' => new external_value(PARAM_RAW, 'Metadata field default value', VALUE_OPTIONAL)
                                    )
                                )
                            ),
                            'fieldsNotFilledLength' => new external_value(PARAM_INT, 'Module not filled fields length', VALUE_OPTIONAL),
                            'fieldsTrackedLength' => new external_value(PARAM_INT, 'Metadata fields tracked length', VALUE_OPTIONAL)
                        )
                    )
                ),
                'options' => new external_single_structure(
                    array(
                        'enablePercentageLabel' => new external_value(PARAM_BOOL, 'Enable percentage label'),
                        'progressBarBackgroundColor' => new external_value(PARAM_TEXT, 'Progress bar background color'),
                        'progressBarThreshold' => new external_value(PARAM_INT, 'Progress bar threshold'),
                        'progressBarColorBeforeThreshold' => new external_value(PARAM_TEXT, 'Progress bar color before threshold'),
                        'progressBarColorAfterThreshold' => new external_value(PARAM_TEXT, 'Progress bar color after threshold')
                    )
                )
            )
        );
    }
}
