<?php
/**
 * Created by IntelliJ IDEA.
 * User: walter
 * Date: 30/04/16
 * Time: 22:02
 */
if (!defined('COCKPIT_HUGO_CONFIG_PATH')) {
    $_configpath = __DIR__.'/config.yaml';
    define('COCKPIT_HUGO_CONFIG_PATH', $_configpath);
}
define('HUGO_BASE_DIR_KEY','hugo_base_dir');
define('HUGO_CONFIG_INTRO',"# Cockpit-hugo config settings");
define('HUGO_CONFIG_SAMPLE',"
hugo_script: hugo
hugo_conf_prefix: config
hugo_conf_extension: toml
cockpit_storage_prefix: 
hugo_extra_params: --cleanDestinationDir
");

define('HUGO_THEMES_SUBDIR','themes');
define('COCKPIT_STORAGE_PREFIX', 'cockpit_storage_prefix');

$this->module("hugo")->extend([

    'createHugoSettings'=>function(){
        $SETTINGS_FILE=COCKPIT_HUGO_CONFIG_PATH;
        error_log("Creating hugo file $SETTINGS_FILE");

        if(!file_exists($SETTINGS_FILE)){
            //create sample file
            $myfile = fopen($SETTINGS_FILE, "w") or die("Unable to open file!");
            fwrite($myfile, HUGO_CONFIG_INTRO);
            fwrite($myfile, HUGO_CONFIG_SAMPLE);
            fclose($myfile);
        }
    },
    'getHugoSettings' => function( ) {
        $settings=[];
        # read from YAML
        if(file_exists(COCKPIT_HUGO_CONFIG_PATH)){
            $settings =   Spyc::YAMLLoad(COCKPIT_HUGO_CONFIG_PATH);
        }
        return  $settings;//json_encode($ret);
    },
    'getHugoDir' => function(){
        $s =cockpit('hugo')->getHugoSettings();
        error_log("Config file is ".print_r($s,1));
        if(key_exists(HUGO_BASE_DIR_KEY, $s))
            return $s[HUGO_BASE_DIR_KEY];
        return null;
    },
    'getHugoSetting'=>function($key){
        $value='';
        # read from YAML
        if(file_exists(COCKPIT_HUGO_CONFIG_PATH)){
            $customconfig =   Spyc::YAMLLoad(COCKPIT_HUGO_CONFIG_PATH);
            if(isset($customconfig[$key]))
                $value=$customconfig[$key];
        }
        return   $value;//json_encode($ret);
    },
    'setHugoDir' => function($path){
        cockpit('hugo')->setHugoSetting(HUGO_BASE_DIR_KEY,$path);
    },
    'setHugoSetting'=>function($key,$value){
        //create if not exists
        cockpit('hugo')->createHugoSettings();

        //load it and add to config
        $customconfig =   Spyc::YAMLLoad(COCKPIT_HUGO_CONFIG_PATH);
        error_log("YAML LOAD ($key,$value)".print_r($customconfig,1));
        //change hugo_base_dir
        $customconfig[$key] = $value;

        //now write YAML
        $yaml = spyc_dump($customconfig);
        $myfile = fopen(COCKPIT_HUGO_CONFIG_PATH, "w") or die("Unable to open file!");
        fwrite($myfile, HUGO_CONFIG_INTRO);
        fwrite($myfile, "\n\n");
        fwrite($myfile, $yaml);
        fwrite($myfile, "\n");

        fclose($myfile);
    },
    'isDir' => function($path){
         error_log("IS DIR $path   " );
        return is_dir($path);
    },
    'getHugoThemes' => function(){
        //1st, get Hugo Dir..
        $HUGO_DIR=cockpit('hugo')->getHugoDir();
        if($HUGO_DIR==''){
            return [];
        }
        //check if "themes" dir exists..
        if (!is_dir($HUGO_DIR .'/'. HUGO_THEMES_SUBDIR)){
            error_log("WEIRD! Hugo dir has no themes subdir!");
            return [];
        }
        $directories = glob($HUGO_DIR . '/'.HUGO_THEMES_SUBDIR.'/*' , GLOB_ONLYDIR);
        //strip last part only..
        $themes=[];
        foreach($directories as $dir){
            $dir=basename($dir);
            array_push($themes, $dir);
        }

        return $themes;
    },
    'getCockpitStoragePrefix' => function(){
        $s =cockpit('hugo')->getHugoSettings();
        if(key_exists(COCKPIT_STORAGE_PREFIX, $s))
            return $s[COCKPIT_STORAGE_PREFIX];
        return null;
    }

]);

// REST
if (COCKPIT_REST) {

    $app->on('cockpit.rest.init', function($routes) {
        $routes['hugo'] = 'Hugo\\Controller\\RestApi';
    });
}

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) {

    include_once(__DIR__.'/admin.php');
}
