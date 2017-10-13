<?php


namespace Hugo\Controller;
define('FRONTMATTER','__FRONtMATTER__');
require_once(__DIR__.'/../bootstrap.php');

class Admin extends \Cockpit\AuthController {

    //private $HUGO_DIR='/Users/walter/web/sites/hugo';

    public function index() {

        cockpit('hugo')->createHugoSettings();
        return $this->render('hugo:views/index.php' );
    }


    public function fields(){
        return $this->render('hugo:views/fields.php' );
    }


    public function settings($createsettings=false){

        $group = $this->app->module('cockpit')->getGroup();
        error_log("SETTINGS, create=$createsettings group=$group");

        //this will create ONLY if it does not exists already
        cockpit('hugo')->createHugoSettings();

        if($group!= 'admin'){

            $file = $this->app->path(COCKPIT_HUGO_CONFIG_PATH);
            $settings_content = file_get_contents($file);
            return $this->render('hugo:views/settings.php', compact('settings_content'));
        }

        if($createsettings == 'create'){
            //create file
            cockpit('hugo')->createHugoSettings();
        }
        $file = $this->app->path(COCKPIT_HUGO_CONFIG_PATH);
        $settingspath = $file;
        //now strip HUGO PATH
        $BASE_DIR=cockpit('hugo')->getHugoDir();
        error_log("FIle is \n$file, home is \n$BASE_DIR");
        if(strpos($file, $BASE_DIR )===0){
            //strip it
            $file = substr($file,  strlen($BASE_DIR));
            if(substr($file,0,1)=='/'){
                $file=substr($file,1);
            }
            error_log("FIle $BASE_DIR is stripped from $file");

        }else{
            error_log("POS is ".strpos($file, $BASE_DIR ));

            $file='';
        }
        $settingsexists = $file;
        $settings_cockpit_path =str_replace(COCKPIT_DIR == COCKPIT_DOCS_ROOT ? COCKPIT_DIR : dirname(COCKPIT_DIR).'/', '', $settingspath);
        $settingsexists = $settings_cockpit_path;
        return $this->render('hugo:views/settings.php', compact('settingsexists','settingspath','settings_cockpit_path') );
    }

    public function generate(){
        $data = $this->param('data') ;
        $languages = $this->param('languages');

        # if true, generate one page per language in a sperate dir
        # i.e.
        # public/default/.. pages for default language
        # public/en/.. pages for english
        # pages/fr/.. pages for french etc
        # if false, it's a mess
        $separate_language_sites = 1;

        //this flag forces HUGO to behave in a multilanguage way even if only one language is passed, maybe we want
        //to regenerate just one language?
        error_log("GENERATE!".print_r($data,1)."\n".print_r($languages,1));

        //read from Lime config
        $language_extensions=$this->app->retrieve("languages", null);
        if(!$language_extensions)
            $language_extensions=array();
        $hasLanguages = count($language_extensions) >= 1 || $this->param('has_languages');

        error_log("Languages? $hasLanguages Hardwired ".print_r($language_extensions,1));

        if(!$languages || count($languages)==0 || !$hasLanguages){
            $languages= array('default');
        }

        error_log("GENERATING SITE FOR THESE LANGUAGES ".print_r($languages,1));

        foreach ($data as $collectionName) {
            error_log("Coll name is $collectionName");
            //get collection
            $c = cockpit("collections")->collection($collectionName);
//            error_log("collection!".print_r($c,1));

            $collid = $c["_id"];
            $fields = $c['fields'];
            //get entries
            $entries = (array)$this->app->storage->find("collections/{$collid}");
            //
//            error_log("ENTRIES!".print_r($entries,1));
//            error_log("FIELDS!".print_r($fields,1));


            $BASE_DIR = cockpit('hugo')->getHugoDir();
            error_log("BASE DIR " . print_r($BASE_DIR, 1));
            //now iterate over every item in every language

            //now clean up dir before doing anything
            foreach ($languages as $language) {
                if ($hasLanguages) {
                    $output_dir = $BASE_DIR . "/content/$language/$collectionName";
                    if (!is_dir("$BASE_DIR/content/$language")) {
                        mkdir("$BASE_DIR/content/$language");
                        continue;
                    }
                } else {
                    $language = null; //not used
                    $output_dir = $BASE_DIR . "/content/$collectionName";
                }
                //create Section dir
                if (!is_dir($output_dir)) {
                    mkdir($output_dir);
                    continue;
                }

                $files = glob("$output_dir/*"); // get all file names
                foreach ($files as $file) { // iterate files
                    if (is_file($file))
                        unlink($file); // delete file
                }
            }

            $count = 0;
            foreach ($entries as $entry){
                $count++;
//                error_log("ENTRY!".print_r($entry,1));

                foreach ($languages as $language) {
                    error_log("LANGUAGE1 $language for entry " . print_r($entry, 1));
                    if ($hasLanguages) {
                        $output_dir = $BASE_DIR . "/content/$language/$collectionName";
                    } else {
                        $language = null; //not used
                        $output_dir = $BASE_DIR . "/content/$collectionName";
                    }

                    //auto generated dates
                    $created_at = date("c", $entry["_created"]);
                    error_log("\n\n");
                    error_log("DATE CREATED $created_at");

                    $modified_at = date("c", $entry["_modified"]);

                    //now if we examine the metadata for the field
                    //we can have.. field name, and options
                    //wehere there are options for Hugo..

                    ///store in entry list of fields taken
                    $entry[FRONTMATTER] = array();

                    //title - look for a hugo field named title
                    //if not localized, try default field
                    $title = $this->getHugoField($entry, $fields, $language, 'title', true);
                    $slug = $this->getHugoField($entry, $fields, $language, 'slug', true);
                    if (!$slug)
                        $slug = str_replace(' ', '_', $title);

                    $content = $this->getHugoField($entry, $fields, $language, 'content');
                    $featured_image = $this->getHugoFeaturedImage($entry, $fields, $language);
                    error_log("Found title $title, slug $slug    ");

                    //post fields
                    $publishdate = date('c');

                    $type = $collectionName;
                    $cockpit_storage_prefix = cockpit('hugo')->getCockpitStoragePrefix();
                    error_log("cockpit_storage_prefix = $cockpit_storage_prefix");
                    if($cockpit_storage_prefix) {
                        if (substr($cockpit_storage_prefix, -1) != '/') {
                            $cockpit_storage_prefix = $cockpit_storage_prefix . '/';
                        }
                        if (substr($cockpit_storage_prefix, 0, 1) != '/') {
                            $cockpit_storage_prefix = '/' . $cockpit_storage_prefix;
                        }
                        error_log("CSP: $cockpit_storage_prefix");
                    }

                    $file_content = "+++\n";
                    if ($created_at)
                        $file_content .= "date = " . $this->normalizeTOMLValue($created_at) . "\n";
                    if ($publishdate)
                        $file_content .= "publishdate = " . $this->normalizeTOMLValue($publishdate) . "\n";
                    if ($title)
                        $file_content .= "title = " . $this->normalizeTOMLValue($title) . "\n";
                    if ($slug)
                        $file_content .= "slug = " . $this->normalizeTOMLValue($slug) . "\n";
                    if ($featured_image) {
                        //adjust images.. subst /static/media with .. /en/media or /default/media
                        // for example i have images in
                        //  COCKPIT: /storage/hugo/media/image.jpg
                        // and I'd like to end up with
                        //  HUGO MD: /media/image.jpg
                        // define('COCKPIT_STORAGE_PREFIX_KEY', 'cockpit_storage_prefix');

                        if (strpos($featured_image, '/') !== 0) {
                            $featured_image = '/' . $featured_image;
                        }

                        $fiprev=$featured_image;
                        if($cockpit_storage_prefix) {
                            $featured_image = str_replace($cockpit_storage_prefix, "/", $featured_image);
                        }else {
                            $featured_image = str_replace('/static/', '/', $featured_image);
                        }
                        array_push($entry[FRONTMATTER], 'featured_image');
                        $file_content .= "featured_image = " . '"' . $featured_image . '"' . "\n";
//                        error_log("IMAGE PATH!!! Converted $featured_image to $fiprev");
                    }
                    if($content){
//                        $prevcont = $content;
                        if($cockpit_storage_prefix) {

                            $content = str_replace($cockpit_storage_prefix, "/", $content);
                            error_log("replaced !");
                        }else {
                            $content = str_replace('/static/', "/", $content);
                        }
//                        if(strcmp($prevcont, $content)!=0)
//                            error_log("IMAGE PATH!!! Converted $prevcont to $content");
                    }
                    $file_content .= "type = " . '"' . $type . '"' . "\n";

                    //now append all metadata
                    if (!$language || $language == 'default') {
                        $suffix = null;
                    } else {
                        $suffix = "_$language";
                    }
                    $frontmatter = $entry[FRONTMATTER];
                    error_log("GENERATING AUTOFIELDS: skipping " . print_r($frontmatter, 1));

                    // now simply iterate over fields, an pick the one in the correct language
                    foreach ($fields as $field) {
                        //skip fields already put in frontmatter
                        $fieldname = $field['name'];
                        if ($fieldname == FRONTMATTER || (isset($frontmatter) && in_array($fieldname, $frontmatter))) {
                            error_log("Skipping field for frontmatter " . $fieldname);
                            continue;
                        }
                        //now get field value, in entry
                        $value = '';
                        if (!$field['localize'] || !$language || $language == 'default') {
                            //get plain name.. i.e.  'text' if not a localized field, or language is default
                            if (key_exists($fieldname, $entry))
                                $value = $entry[$fieldname];
                        } else {
                            //loog for field named 'text_en' for example
                            if (key_exists($fieldname . '_' . $language, $entry))
                                $value = $entry[$fieldname . '_' . $language];
                        }

                        if (is_array($value)) { //image
                            error_log("VALUE IS ARRAY:" . print_r($value, 1));
                            $file_content .= $fieldname . ' = "' . $value['path'] . '"' . "\n";

                        } else {
                            $file_content .= $fieldname . ' =  ' . $this->normalizeTOMLValue($value) . "\n";
                        }
                    }

                    $file_content .= "+++\n\n$content";

                    error_log("Creating " . $file_content);
                    error_log("Creating post named $output_dir/$slug.md");
                    $file = $output_dir . "/" . $slug . ".md";

                    //if file exists, remove it first
                    if (file_exists($file)) {
                        unlink($file);
                    }

                    file_put_contents($file, $file_content);
                }
            }
        }
        $ret=array("status"=>"ok");
        return json_encode($ret);
    }

    public function runHugo(){

        $theme = $this->param('theme');
        $languages = $this->param('languages');
        $env = $this->param('env'); //can be 'prod' or 'staging'
        if(!$env){
            $env = 'prod';
        }

        //this flag forces HUGO to behae in a multilanguage way even if only one langauge is passed, maybe we want
        //to regenerate just one language?
        error_log("RUNNING HUGO! theme $theme and env $env");

        //read from Lime config
        $language_extensions=$this->app->retrieve("languages", null);
        if(!$language_extensions)
            $language_extensions=array();
        $hasLanguages = count($language_extensions) >= 1 || $this->param('has_languages');

        $BASE_DIR = cockpit('hugo')->getHugoDir();
        error_log("BASE DIR " . print_r($BASE_DIR, 1));
        //now iterate over every item in every language
        error_log("LANGUAGES " . print_r($languages, 1));

        #hugo -t iakta --config="$HUGO_DIR/config.toml"

        foreach ($languages as $lang){
            $ext= '';
            if($lang != 'default'){
                $ext="_$lang";
            }

            #build command
            #read params from hugo config
            $hugo_script= cockpit('hugo')->getHugoSetting('hugo_script');
            $hugo_config_prefix= cockpit('hugo')->getHugoSetting('hugo_conf_prefix');
            $hugo_config_extension= cockpit('hugo')->getHugoSetting('hugo_conf_extension');

            $hugo_extra_params=cockpit('hugo')->getHugoSetting('hugo_extra_params');
            if(!$hugo_extra_params)
                $hugo_extra_params = "";

            #build config file name
            $conf_filename="$hugo_config_prefix$ext.$hugo_config_extension";

            # build absolute config file path
            $hugo_config_file = "$BASE_DIR/$conf_filename";
            if(!file_exists($hugo_config_file)){
                $error = "Cannot fine hugo config file $hugo_config_file for language $lang";
                error_log($error);
                $ret=array("status"=>"error","error"=>$error);
                return json_encode($ret);
            }

            if($env == 'staging'){
                //read Hugo config file..
                $staging_dir = readKeyFromHugoConfig($hugo_config_file,HUGO_STAGING_DIR_KEY);
                $staging_url = readKeyFromHugoConfig($hugo_config_file, HUGO_STAGING_URL_KEY);
                if($staging_dir && $staging_url) {
                    $hugo_extra_params .= " --destination=\"$staging_dir\" --baseURL \"$staging_url\" ";
                    error_log("Extra params [$hugo_extra_params]");
                }else{
                    error_log("Missing key ".HUGO_STAGING_DIR_KEY." and/or ".
                        HUGO_STAGING_URL_KEY." in hugo config file $hugo_config_file");
                    error_log("SD $staging_dir SU $staging_url");
                }
            }

            $command = "cd $BASE_DIR ;$hugo_script -t $theme $hugo_extra_params --config=\"$hugo_config_file\"";
            error_log("Running $command");

            #now issue command
            $ret_lines=[];
            $ret=exec($command, $ret_lines, $cmd_out);
            //look for ERROR in every line
            $error=false;
            foreach ($ret_lines as $line){
                if(stripos($line,'ERROR')!==false){
                    $error=$line;
                    break;
                }
            }
            if($cmd_out || $error){
                error_log("CMD OUT .$cmd_out.");
                error_log("ERROR .$error.");

                error_log("Returning error $cmd_out, $error".print_r($ret,1)." -- ".print_r($ret_lines,1 ));
                $ret=array("status"=>"error","error"=>($error? $error : $ret));
//                $this->app->response->status=500;
                return json_encode($ret);
            }
            error_log("Ran with $cmd_out and $ret and ".print_r($ret_lines,1));
        }

        $ret=array("status"=>"ok");
        return json_encode($ret);
    }


    private function normalizeTOMLValue($value){
        //if string is multiline, surround with triple double-quotes """" and """"
        //in any case, escape quotes
        #$value=str_replace('"','\"',$value);
        #maybe escape
        #$value = htmlspecialchars($value, ENT_NOQUOTES );
//        $value = htmlentities($value,ENT_COMPAT|ENT_NOQUOTES, 'UTF-8');

        if(strpos($value,"\n")){
            return '"""'.$value.'"""';
        }
        //escape?
        $value=str_replace('"','\"',$value);

        return '"'.$value.'"';
    }

    private function getField($fields, $fieldname, $fixed_languages){
        //first, check if there is a field with the given name
        foreach ($fields as $field) {
            if ($field['name'] == $fieldname)
                return $field;
        }

        //then, if there is not, strip language extension if any
        foreach($fixed_languages as $ext){
            if($this->endsWith($fieldname, "_$ext")) {
                $fieldname2 = $this->removeSuffix($fieldname, "_$ext");
                $ret = $this->getField($fields, $fieldname2, array());
                if($ret)
                    return $ret;
            }
        }
        return null;
    }

    private function endsWith($string, $ending){
        return strpos(strrev($string), strrev($ending)) ===0;
    }

    private function removeSuffix($string, $ending){
        return substr($string, 0, strlen($string) - strlen($ending));
    }

    private function getHugoField(&$entry, $fields, $language, $name, $take_default_if_missing=false){
        //look throught all the fields of the given entry
        //and look if one has the options.hugo.name == $name
        //if not, look for a field that has the name == $name
        $value=null;
        if(!$language || $language=='default'){
            $suffix=null;
        }else{
            $suffix="_$language";
        }

        foreach ($fields as $field){
            if(isset($field['options']) && isset($field['options']['hugo'])){
                if($field['options']['hugo']['name'] == $name){
                    if($suffix && $field['localize']){
                        //look for localized value
                        if(key_exists($field['name'].$suffix, $entry)) {
                            array_push($entry[FRONTMATTER], $field['name'] . $suffix);
                            array_push($entry[FRONTMATTER], $field['name']);
                            return $entry[$field['name'] . $suffix];
                        }
                        if(!$take_default_if_missing)
                            return null;
                        // else execute following IF
                    }
                    if(key_exists($field['name'], $entry)) {
                        array_push($entry[FRONTMATTER], $field['name']);
                        return $entry[$field['name']];
                    }
                    return null;
                }
            }
        }
        //if not found, look for field name
        foreach ($fields as $field){
            if($field['name']== $name){
                if($suffix && $field['localize']){
                    //look for localized value
                    if(key_exists($field['name'].$suffix, $entry)) {
                        array_push($entry[FRONTMATTER], $field['name'] . $suffix);
                        array_push($entry[FRONTMATTER], $field['name']);
                        return $entry[$field['name'] . $suffix];
                    }
                    if(!$take_default_if_missing)
                        return null;
                    // else execute following IF
                }
                if(key_exists($field['name'], $entry)) {
                    array_push($entry[FRONTMATTER], $field['name']);
                    return $entry[$field['name']];
                }
                return null;
            }
        }

        return null;
    }

    private function getHugoFeaturedImage(&$entry, $fields, $language){
        //look throught all the fields of the given entry
        //and look if one has the options.hugo.name == $name
        //if not, look for a field that has the name == $name
        $value=null;
        if(!$language || $language=='default'){
            $suffix=null;
        }else{
            $suffix="_$language";
        }
        foreach ($fields as $field){
            if(isset($field['options']) && isset($field['options']['hugo'])){
                if($field['options']['hugo']['isfeatured'] == true){
                    if($suffix && $field['localize'] && key_exists($field['name'].$suffix, $entry)){
                        //look for localized value
                        array_push($entry[FRONTMATTER],$field['name'].$suffix);
                        array_push($entry[FRONTMATTER],$field['name']);
                        return $entry[$field['name'].$suffix]['path'];
                    }
                    if(key_exists($field['name'], $entry)) {
                        array_push($entry[FRONTMATTER], $field['name']);
//                        error_log("Entry di ".$field['name'].' = '.print_r($entry[$field['name']],1));
                        if(is_array($entry[$field['name']]) && key_exists('path', $entry[$field['name']]))
                            return $entry[$field['name']]['path'];
                        return null;
                    }
                    return null;
                }
            }
        }
        //if not found, look for field name
        foreach ($fields as $field){
            if($field['type']== 'image'){
                if($suffix && $field['localize'] && key_exists($field['name'].$suffix, $entry)){
                    //look for localized value
                    array_push($entry[FRONTMATTER],$field['name'].$suffix);
                    array_push($entry[FRONTMATTER],$field['name']);
                    return $entry[$field['name'].$suffix]['path'];
                }
                if(key_exists($field['name'], $entry)) {
                    array_push($entry[FRONTMATTER], $field['name']);
                    return $entry[$field['name']]['path'];
                }
                return null;
            }
        }

        return null;
    }

    public function export($collection) {

        if (!$this->app->module("cockpit")->hasaccess("hugo", 'manage.hugo')) {
            return false;
        }

        $collection = $this->module('hugo')->collection($collection);

        if (!$collection) return false;

        $entries = $this->module('hugo')->find($collection['name']);

        return json_encode($entries, JSON_PRETTY_PRINT);
    }
}


function readKeyFromHugoConfig($hugoconfig, $param){
    if( preg_match('/\.yaml$/', $hugoconfig)){
        $config= spyc_load_file($hugoconfig);
        return $config[$param];
    }elseif (preg_match('/\.toml/', $hugoconfig)){
        //$config = Toml::parseFile($config_file);
        $tomlStr = file_get_contents($hugoconfig);
        //now parse line per line..
        //look for 'staticDir' or 'staticdir'
        $handle = fopen($hugoconfig, "r");
        $lines = array();
        $index = -1;
        $found=0;
        $match=null;
        while (($line = fgets($handle)) !== false) {
            // process the line read.
            if(preg_match("/^\\s*$param \s*=\s*/i", $line)){
                //modify..
                $match = $line;
                $found = 1;
                break;
            }
        }
        fclose($handle);

        if(!$found){
            //insert in array
            return null;
        }
        $p = explode("=", $match);
        if(count($p)==2){
            $v = $p[1];
            $v = trim($v, " \t\"\'\n");
            return $v;
        }

        return null;

    }elseif (preg_match('/\.json/', $hugoconfig)){
        $jsonStr = file_get_contents($hugoconfig);
        $config=json_decode($jsonStr);
        return $config[$param];
    }
}