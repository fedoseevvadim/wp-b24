<?php

/**
* Triggers this gile on Plugin uninstall
*
* @package B24Plugin
*/


if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

// Clear Database stored data
