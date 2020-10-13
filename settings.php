<?php

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->dirroot.'/blocks/metadata_status/lib.php');

if ($ADMIN->fulltree) {
    try {
        $settings->add(new admin_setting_heading('block_metadata_status/metadata_tracking_configuration',
                get_string('config_header_block_configuration', 'block_metadata_status'),
                get_string('config_header_block_configuration_desc', 'block_metadata_status'))
        );

        $settings->add(new admin_setting_configtext('block_metadata_status/shared_metadata_short_name',
            get_string('config_shared_metadata', 'block_metadata_status'),
            get_string('config_shared_metadata_desc', 'block_metadata_status'),
            DEFAULT_METADATA_STATUS_SHARED_SHORT_NAME,
            PARAM_TEXT));

        $thresholds = range(0, 90, 10);

        $settings->add(new admin_setting_configselect('block_metadata_status/progress_bar_threshold',
            get_string('config_progress_bar_threshold', 'block_metadata_status'),
            get_string('config_progress_bar_threshold_desc', 'block_metadata_status'),
            DEFAULT_METADATA_STATUS_PROGRESS_BAR_THRESHOLD,
            $thresholds
        ));

        $settings->add(new admin_setting_configcheckbox(
            'block_metadata_status/enable_percentage_label',
            get_string('config_enable_percentage', 'block_metadata_status'),
            get_string('config_enable_percentage_desc', 'block_metadata_status'),
            DEFAULT_METADATA_STATUS_ENABLE_PERCENTAGE_LABEL
        ));

        $settings->add(new admin_setting_confightmleditor(
            'block_metadata_status/config_text_admin',
            get_string('config_block_content', 'block_metadata_status'),
            get_string('config_block_content_desc', 'block_metadata_status'),
            get_string('config_block_content_default', 'block_metadata_status')
        ));

        /* ---------------------------------------------------------------------------------------------------------- */

        $settings->add(new admin_setting_heading('block_metadata_status/metadata_tracking',
                get_string('config_header_metadata_tracking', 'block_metadata_status'),
                get_string('config_header_metadata_tracking_desc', 'block_metadata_status')
        ));

        $moduleMetadataFields = block_metadata_status_get_module_metadata_fields();

        foreach ($moduleMetadataFields as $metadataField) {
            $locked = $metadataField->locked === '1';
            $settings->add(new admin_setting_configcheckbox(
                'block_metadata_status/enable_metadata_' . $metadataField->id . '_tracking',
                ($locked ? '<div class="icon fa fa-lock"></div>' : '').'<b>' . $metadataField->name . '</b> <code> - ' . get_string('metadata_type_' . $metadataField->datatype, 'block_metadata_status') . '</code>',
                '',
                $locked ? 0 : 1
            ));
        }

        /* ---------------------------------------------------------------------------------------------------------- */

        $settings->add(new admin_setting_heading('block_metadata_status/metadata_status_customization',
            get_string('config_header_metadata_customization', 'block_metadata_status'),
            get_string('config_header_metadata_customization_desc', 'block_metadata_status')
        ));

        $settings->add(new admin_setting_configcolourpicker(
            'block_metadata_status/progress_bar_background_color',
            get_string('config_progress_bar_background_color', 'block_metadata_status'),
            get_string('config_progress_bar_background_color_desc', 'block_metadata_status'),
            DEFAULT_METADATA_STATUS_PROGRESS_BAR_BACKGROUND_COLOR
        ));

        $settings->add(new admin_setting_configcolourpicker(
            'block_metadata_status/progress_bar_color_before_threshold',
            get_string('config_progress_bar_color_before_threshold', 'block_metadata_status'),
            get_string('config_progress_bar_color_before_threshold_desc', 'block_metadata_status'),
            DEFAULT_METADATA_STATUS_PROGRESS_BAR_COLOR_BEFORE_THRESHOLD
        ));

        $settings->add(new admin_setting_configcolourpicker(
            'block_metadata_status/progress_bar_color_after_threshold',
            get_string('config_progress_bar_color_after_threshold', 'block_metadata_status'),
            get_string('config_progress_bar_color_after_threshold_desc', 'block_metadata_status'),
            DEFAULT_METADATA_STATUS_PROGRESS_BAR_COLOR_AFTER_THRESHOLD
        ));
    } catch (dml_exception $e) {
    } catch (coding_exception $e) {
    }
}
