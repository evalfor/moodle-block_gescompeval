<?php
$plugin->version = 2016052000;  // YYYYMMDDHH (year, month, day, 24-hr time)
$plugin->requires = 2010112400; // YYYYMMDDHH (This is the release version for Moodle 2.0)
$plugin->component = 'block_gescompeval_md';
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = array(
    'block_evalcomix' => ANY_VERSION
);
$plugin->release = 'v1.0.0';
