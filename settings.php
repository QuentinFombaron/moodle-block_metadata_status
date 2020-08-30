<?php

defined('MOODLE_INTERNAL') || die;

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
    } catch (dml_exception $e) {
    }
}
