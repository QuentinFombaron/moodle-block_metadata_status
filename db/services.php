<?php
defined('MOODLE_INTERNAL') || die();

$functions = array(
    'block_metadata_status_get_modules_status' => array(
        'classname'   => 'block_metadata_status_external',
        'methodname'  => 'get_modules_status',
        'classpath'   => 'blocks/metadata_status/externallib.php',
        'description' => 'Get modules status',
        'type'        => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    )
);

$services = array(
    'Metadata Status Service' => array(
        'functions' => array(
            'block_metadata_status_get_modules_status'
        ),
        'requiredcapability' => '',
        'restrictedusers' => 0,
        'enabled' => 1,
    )
);