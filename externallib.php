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
                'courseId' => new external_value(PARAM_INT, 'ID of course', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Get course module IDs
     *
     * @param int $courseId
     *
     * @return stdClass Modules IDs
     *
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_modules_status($courseId) {
        self::validate_parameters(self::get_modules_status_parameters(), array(
                'courseId' => $courseId
            )
        );

        return block_metadata_status_get_metadata_status($courseId);
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
