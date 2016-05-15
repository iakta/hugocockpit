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
define('HUGO_CONFIG_INTRO','# Cockpit-hugo config settings');

$this->module("hugo")->extend([

    
    'getHugoDir' => function( ) {
        #TODO generalize this setting
        $HUGO_DIR='';
        # read from YAML
        if(file_exists(COCKPIT_HUGO_CONFIG_PATH)){
            $customconfig =   Spyc::YAMLLoad(COCKPIT_HUGO_CONFIG_PATH);
            $HUGO_DIR=$customconfig[HUGO_BASE_DIR_KEY];
        }
        return   $HUGO_DIR;//json_encode($ret);
    },
    'setHugoDir' => function($path){
        //add to config
        if(!file_exists(COCKPIT_HUGO_CONFIG_PATH)){
            //create sample file
                $myfile = fopen(COCKPIT_HUGO_CONFIG_PATH, "w") or die("Unable to open file!");
                fwrite($myfile, HUGO_CONFIG_INTRO);
                fclose($myfile);
        }
        //load it

        $customconfig =   Spyc::YAMLLoad(COCKPIT_HUGO_CONFIG_PATH);
//        error_log(COCKPIT_HUGO_CONFIG_PATH);
        error_log("YAML LOAD ".print_r($customconfig,1));
        //change hugo_base_dir
        $customconfig[HUGO_BASE_DIR_KEY] = $path;

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
