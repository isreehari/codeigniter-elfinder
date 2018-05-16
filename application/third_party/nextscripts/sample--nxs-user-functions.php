<?php
//## User Open/Save functions
if (!function_exists('nxs_settings_open')){ function nxs_settings_open() {  
    $fileData = trim(file_get_contents(dirname(__FILE__).'/nx-snap-settings.txt'));  $options = nxs_maybe_unserialize($fileData);  return $options;
}}
if (!function_exists('nxs_settings_save')){ function nxs_settings_save($options) {  
    $options = serialize($options);  file_put_contents(dirname(__FILE__).'/nx-snap-settings.txt', $options);
}}

?>