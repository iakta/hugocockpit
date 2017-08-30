<?php
/**
 * Contains constants for the plugin. Change at your own risk
 * User: walter
 * Date: 25/05/2017
 * Time: 00:05
 */

//--------------------------------------------------------------------
//used during installation, define various defaults

//define where Cockpit will store folders and images for Hugo plugin
define('INSTALL_HUGO_STORAGE_PATH','storage/hugo');
//define sandboxed subdir viewsd by users belonging to group INSTALL_GROUP_NAME
define('INSTALL_FINDER_PATH', INSTALL_HUGO_STORAGE_PATH . '/media');
//default group name that will handle hugo addon in Cockpit
define('INSTALL_GROUP_NAME', 'author_hugo');
//just a comment in cockpit config, if file is empty or created from scratch
define('INSTALL_COCKPIT_SETTINGS_COMMENT', '# Cockpit settings');


//--------------------------------------------------------------------
// used for plugin config

//prefix of Hugo configuration files
define('HUGO_CONFIG_PREFIX','config');
//extensio of Hugo configuration files, Can be "toml", "yaml" or "json"
define('HUGO_CONFIG_EXTENSION','json');

//keys to the Hugo/cockpit configuration file
//stores hugo base dir
define('HUGO_BASE_DIR_KEY','hugo_base_dir');
//store directory of media
define('COCKPIT_STORAGE_PREFIX_KEY', 'cockpit_storage_prefix');
//store config extensions
define('HUGO_CONFIG_EXTENSION_KEY','hugo_conf_extension');

//default comment at beginning of cockit config file
define('HUGO_CONFIG_INTRO',"# Cockpit-hugo config settings");
//sample file.
define('HUGO_CONFIG_SAMPLE',"
hugo_script: hugo
hugo_conf_prefix: ".HUGO_CONFIG_PREFIX." 
".HUGO_CONFIG_EXTENSION_KEY.": ".HUGO_CONFIG_EXTENSION."
".COCKPIT_STORAGE_PREFIX_KEY.": 
hugo_extra_params: --cleanDestinationDir
");

//--------------------------------------------------------------------
// used for staging configuration

//key in HUGO config file for staging dir
define('HUGO_STAGING_DIR_KEY','stagingdir');
//key in HUGO config file for staging URL
define('HUGO_STAGING_URL_KEY','stagingURL');



//--------------------------------------------------------------------
// used to make this plugin work

//by default, search this dir for themes in Hugo
define('HUGO_THEMES_SUBDIR','themes');


//path of the main config file for this plugin to work
if (!defined('COCKPIT_HUGO_CONFIG_PATH')) {
    $_configpath = __DIR__ . '/config.yaml';
    define('COCKPIT_HUGO_CONFIG_PATH', $_configpath);
}