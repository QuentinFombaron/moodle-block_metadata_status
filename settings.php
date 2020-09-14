<?php

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->dirroot.'/blocks/metadata_status/lib.php');

if ($ADMIN->fulltree) {
    try {
        $settings->add(new admin_setting_heading('block_metadata_status/metadata_tracking_configuration',
                'Metadata tracking configuration',
                '')
        );

        $settings->add(new admin_setting_configtext('block_metadata_status/shared_metadata_short_name',
            'Shared metadata short name',
            '',
            DEFAULT_METADATA_STATUS_SHARED_SHORT_NAME,
            PARAM_TEXT));

        $settings->add(new admin_setting_configcheckbox(
            'block_metadata_status/enable_percentage_label',
            'Enable percentage label',
            '',
            DEFAULT_METADATA_STATUS_ENABLE_PERCENTAGE_LABEL
        ));

        $settings->add(new admin_setting_confightmleditor(
            'block_metadata_status/config_text_admin',
            get_string('contentinputlabel', 'block_metadata_status'),
            '',
            ''
        ));

        /* ---------------------------------------------------------------------------------------------------------- */

        $settings->add(new admin_setting_heading('block_metadata_status/metadata_tracking',
            'Metadata tracking',
            '')
        );

        $moduleMetadataFields = block_metadata_status_get_module_metadata_fields();

        foreach ($moduleMetadataFields as $metadataField) {
            $settings->add(new admin_setting_configcheckbox(
                'block_metadata_status/enable_metadata_' . $metadataField->id . '_tracking',
                $metadataField->name . ' (' . $metadataField->datatype . ')',
                '',
                DEFAULT_METADATA_STATUS_ENABLE_METADATA_TRACKING
            ));
        }

        /* ---------------------------------------------------------------------------------------------------------- */

        $settings->add(new admin_setting_heading('block_metadata_status/metadata_status_customization',
                'Metadata status customization',
                '')
        );

        $settings->add(new admin_setting_configcolourpicker(
            'block_metadata_status/progress_bar_background_color',
            'Progress bar background color',
            '',
            DEFAULT_METADATA_STATUS_PROGRESS_BAR_BACKGROUND_COLOR
        ));

        $settings->add(new admin_setting_configcolourpicker(
            'block_metadata_status/progress_bar_color',
            'Progress bar color',
            '',
            DEFAULT_METADATA_STATUS_PROGRESS_BAR_COLOR
        ));
    } catch (dml_exception $e) {
    }
}
