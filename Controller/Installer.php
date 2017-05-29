<?php

namespace Hugo\Controller;

require_once(__DIR__."/../config.php");
require_once("lib/Toml.php");

### use this to change defaults


### end of defaults
/*
 # Cockpit settings
# multilanguage s
languages:
   en: "English"

# admin specific
groups:
    author:
        $admin: true
        $vars:
            finder.path: "storage/hugo/media"
        cockpit:
             backend: true
             finder: true
             setting: true
 */
define('INSTALL_COCKPIT_NEW_SETTINGS',serialize(array(
        '$admin' => true,
        '$vars' => array(
            'finder.path' => INSTALL_FINDER_PATH
        )
    )
));

define('FRONTMATTER','__FRONtMATTER__');
require_once(__DIR__.'/../bootstrap.php');


class Installer extends \Cockpit\AuthController {

    //private $HUGO_DIR='/Users/walter/web/sites/hugo';

    public function index() {

        //cockpit('hugo')->createHugoSettings();
        //read cockpit settings..
        $configexists =  COCKPIT_CONFIG_PATH ;
        if (!file_exists(COCKPIT_CONFIG_PATH)){
            //now write file
            $myfile = fopen(COCKPIT_CONFIG_PATH, "w") or die("Unable to open file!");
            fwrite($myfile, INSTALL_COCKPIT_SETTINGS_COMMENT."\n");
            fclose($myfile);
        }
        $settings_content = file_get_contents($configexists);
        $groups    = $this->app->module('cockpit')->getGroups();
        $default_group_name = INSTALL_GROUP_NAME;
        $default_hugo_media_dir = INSTALL_FINDER_PATH;
        foreach($groups as $group  ){
            error_log(print_r($group,1));
        }
        return $this->render('hugo:views/installer.php', compact('configexists', 'settings_content',
            'groups', 'default_group_name','default_hugo_media_dir') );
    }


    public function addCockpitGroup(){
        $group_name = $this->param('group');

        error_log("Add cockpit group $group_name");
        //now load content
        if (file_exists(COCKPIT_CONFIG_PATH)) {
            $config = preg_match('/\.yaml$/', COCKPIT_CONFIG_PATH) ? spyc_load_file(COCKPIT_CONFIG_PATH) : include(COCKPIT_CONFIG_PATH);
        } else{
            $config=array();
        }
//        error_log("Config is b4 ".print_r($config, 1));
        $author = array(
            'groups' => array(
                $group_name => unserialize(INSTALL_COCKPIT_NEW_SETTINGS)
            )
        );
        $config = array_replace_recursive($config, $author);
//        error_log("Config is after ".print_r($author, 1));
//        error_log("Config is after ".print_r($config, 1));

        //now get string from array
        if( preg_match('/\.yaml$/', COCKPIT_CONFIG_PATH)){
            //write YAML
            $str =  INSTALL_COCKPIT_SETTINGS_COMMENT . "\n" . spyc_dump($config);
        }else{
            $str =  "<php\n " . INSTALL_COCKPIT_SETTINGS_COMMENT . "\n\n return " . var_export($config). ";";
        }
        //now write file
        $myfile = fopen(COCKPIT_CONFIG_PATH, "w") or die("Unable to open file!");
        fwrite($myfile, $str);
        fclose($myfile);

        $settings_content = file_get_contents(COCKPIT_CONFIG_PATH);


        $ret=array("status"=>"ok", "config_str" => $settings_content);
        return json_encode($ret);
    }

    public function createSubdirs(){
        $media_subdir =  INSTALL_FINDER_PATH;// $this->param('subdir');
        //split
        $subdirs = explode('/', $media_subdir);
        $rootdir = COCKPIT_STORAGE_FOLDER;
        foreach($subdirs as $subdir){
            if($subdir == 'storage'){
                continue;
            }
            $error = 1;
            if(is_writable($rootdir)){
                $to_create = "$rootdir/$subdir";
                if(file_exists($to_create) && is_dir($to_create)){
                    $rootdir = $to_create;
                    $error = 0;
                }elseif(mkdir($to_create)){
                    $rootdir = $to_create;
                    $error = 0;
                }
            }

            if($error){
                $ret=array("status"=>"ko","err"=>"cannot write to storage folder. Check permissions");
                return json_encode($ret);
            }
        }
        $ret=array("status"=>"ok");
        return json_encode($ret);
    }

    public function selectHugoDir()
    {
        $hugo_dir = $this->param('dir');
        error_log("Seting hugo dir to " . $hugo_dir);
        //safety first
        if (!file_exists($hugo_dir) || !is_dir($hugo_dir)) {
            return json_encode(array("status" => "ko", "error" => "hugo dir does not exist"));
        }
        //first.. look for 'themes' subdir and at least
        $themesdir = $hugo_dir . '/' . 'themes';
        if (!file_exists($themesdir) || !is_dir($themesdir)) {
            return json_encode(array("status" => "ko", "error" => "themes dir does not exist"));
        }

        //now look for config
        $config = null;
        $config_paths = array("$hugo_dir/config.toml", "$hugo_dir/config.yaml", "$hugo_dir/config.json");
        foreach ($config_paths as $config_path) {
            if (file_exists($config_path) && is_writable($config_path)) {
                $config = $config_path;
                break;
            }
        }

        if (!$config) {
            return json_encode(array("status" => "ko", "error" => "config file not found"));
        }

        return json_encode(array("status"=>"ok"));
    }

    public function addParamsToHugoConfig(){
        $hugo_dir = $this->param('dir');

        //now look for config
        $config = $this->getFirstHugoConfigFile($hugo_dir);

        if (!$config) {
            return json_encode(array("status" => "ko", "error" => "config file not found"));
        }

        $storage_path = COCKPIT_DIR . '/' . INSTALL_HUGO_STORAGE_PATH;
        error_log("Adding config setting in $config");
        if(!$this->addConfigFileParam($config, "staticdir", $storage_path)){
            return json_encode(array("status"=>"ko","error"=>"config not modified"));
        }
        //add file to record
        $modified_files = array($config);

        //get extension
        $ext = pathinfo($config, PATHINFO_EXTENSION);
        //change default
        //now try for every file named "config*.EXT" with EXT= same extension as the one found..
        foreach (glob("$hugo_dir/config*.$ext") as $config) {
            if(in_array($config, $modified_files))
                continue;

            error_log("Adding config setting in $config");
            if(!$this->addConfigFileParam($config, "staticdir", $storage_path)){
                return json_encode(array("status"=>"ko","error"=>"config not modified"));
            }
            //add file to record
            array_push($modified_files, $config);
        }

        return json_encode(array("status"=>"ok","static_dir"=>$storage_path,"modified_files"=>$modified_files));
    }

    private function getFirstHugoConfigFile($hugo_dir){
        //now look for config
        $config = null;
        $config_paths = array(
            "$hugo_dir/".HUGO_CONFIG_PREFIX.".toml",
            "$hugo_dir/".HUGO_CONFIG_PREFIX.".yaml",
            "$hugo_dir/".HUGO_CONFIG_PREFIX.".json"
        );
        foreach ($config_paths as $config_path) {
            if (file_exists($config_path) && is_writable($config_path)) {
                return $config_path;
            }
        }

        return null;
    }

    private function addConfigFileParam($config_file, $param, $value){
        if( preg_match('/\.yaml$/', $config_file)){
           $config= spyc_load_file($config_file);
        }elseif (preg_match('/\.toml/', $config_file)){
            //$config = Toml::parseFile($config_file);
            $tomlStr = file_get_contents($config_file);
            //now parse line per line..
            //look for 'staticDir' or 'staticdir'
            $handle = fopen($config_file, "r");
            $lines = array();
            $index = -1;
            $found=0;
            $i=0;
            while (($line = fgets($handle)) !== false) {
                    // process the line read.
                if(preg_match("/^\\s*$param/i", $line)){
                    //modify..
                    $line ="$param = \"$value\"\n";
                    $found = 1;
                }
                array_push($lines, $line);
                if($i>=0 && $index < 0 && preg_match("/^\\s*$/", $line)){
                    $index = $i;
                }
                if($index < 0 && preg_match("/^\\[/",$line)){
                    $index = $i;
                }

                $i++;
            }
            fclose($handle);

            if(!$found){
                //insert in array
                if($index < 0)
                    $index =0;
                array_splice($lines,$index,0, "$param = \"$value\"\n");
            }
            $config=array();


        }elseif (preg_match('/\.json/', $config_file)){
            $jsonStr = file_get_contents($config_file);
            $config=json_decode($jsonStr);
        }
        //add key
        $config[$param] = $value;
        //write back
        if( preg_match('/\.yaml$/', $config_file)){
            $config_str= spyc_dump($config);
        }elseif (preg_match('/\.toml/', $config_file)){
            $config_str=implode("",$lines);
        }elseif (preg_match('/\.json/', $config_file)){
            $config_str = json_encode($config);
        }

        $myfile = fopen($config_file, "w");
        if(!$myfile){
            error_log("Unable to open config file for writing!");
            return false;
        }

        fwrite($myfile, $config_str);
        fclose($myfile);
        return true;
    }

    public function createHugocockpitConfig(){
        $hugo_dir = $this->param('hugo_dir');
        $storage_prefix = '/'.INSTALL_HUGO_STORAGE_PATH;
        $config = $this->getFirstHugoConfigFile($hugo_dir);

        cockpit('hugo')->createHugoSettings();
        cockpit('hugo')->setHugoDir($hugo_dir);
        cockpit('hugo')->setHugoSetting(COCKPIT_STORAGE_PREFIX_KEY, $storage_prefix);
        if($config){
            $ext = pathinfo($config, PATHINFO_EXTENSION);
            cockpit('hugo')->setHugoSetting(HUGO_CONFIG_EXTENSION_KEY, $ext);
        }

        return json_encode(array("status"=>"ok" ));
    }
}
