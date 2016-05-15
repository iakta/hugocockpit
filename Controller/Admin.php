<?php


namespace Hugo\Controller;
define('FRONTMATTER','__FRONtMATTER__');


class Admin extends \Cockpit\AuthController {

    //private $HUGO_DIR='/Users/walter/web/sites/hugo';

    public function index() {
        return $this->render('hugo:views/index.php' );
    }


    public function fields(){
        return $this->render('hugo:views/fields.php' );

    }

//    public function getHugoDir(){
//        $ret=array("hugo_dir"=>$this->HUGO_DIR);
//        return json_encode($ret);
//    }

    public function generate(){
        $data = $this->param('data') ;
        $languages = $this->param('languages');
        //this flag forces HUGO to behae in a multilanguage way even if only one langauge is passed, maybe we want
        //to regenerate just one language?
        error_log("GENERATE!".print_r($data,1));

        //read from Lime config
        $language_extensions=$this->app->retrieve("languages", null);
        if(!$language_extensions)
            $language_extensions=array();
        $hasLanguages = count($language_extensions) >= 1 || $this->param('has_languages');

        error_log("Languages? $hasLanguages Hardwired ".print_r($language_extensions,1));

        foreach ($data as $collectionName){
            error_log("Coll name is $collectionName");
            //get collection
            $c=cockpit("collections")->collection($collectionName);
            error_log("collection!".print_r($c,1));

            $collid = $c["_id"];
            $fields=$c['fields'];
            //get entries
            $entries = (array)$this->app->storage->find("collections/{$collid}");
            //
            error_log("ENTRIES!".print_r($entries,1));


            $BASE_DIR=cockpit('hugo')->getHugoDir();
            error_log("BASE DIR ".print_r($BASE_DIR,1));
            //now iterate over every item in every language
            error_log("LANGUAGES ".print_r($languages,1));


            if(!$languages || count($languages)==0 || !$hasLanguages){
                $languages=['default'];
            }

            $count=0;
            foreach ($entries as $entry):
                $count++;


                foreach($languages as $language) {

                    if($hasLanguages) {
                        $output_dir = $BASE_DIR . "/content/$language/$collectionName";
                        if(!is_dir("$BASE_DIR/content/$language"))
                            mkdir("$BASE_DIR/content/$language");
                    }
                    else {
                        $language=null; //not used
                        $output_dir = $BASE_DIR . "/content/$collectionName";
                    }
                    //create Section dir
                    if (!is_dir($output_dir)) {
                        mkdir($output_dir);
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
                    $entry[FRONTMATTER]=array();

                    //title - look for a hugo field named title
                    $title = $this->getHugoField($entry, $fields, $language,  'title');
                    $slug = $this->getHugoField($entry, $fields, $language, 'slug');
                    if (!$slug)
                        $slug = str_replace(' ', '_', $title);

                    $content = $this->getHugoField($entry, $fields, $language, 'content');
                    $featured_image = $this->getHugoFeaturedImage($entry, $fields, $language);
                    error_log("Found title $title, slug $slug     "  );

                    //post fields
                    $publishdate = date('c');

                    $type = $collectionName;

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
                        if(strpos($featured_image,'/')!==0) {
                            $featured_image = '/' . $featured_image;

                        }
                        if($language=='default'){
                            $featured_image = str_replace('/static/',"/",$featured_image);
                        }elseif($language){
                            $featured_image = str_replace('/static/',"/",$featured_image);
                        }else{
                            $featured_image = str_replace('/static/','/',$featured_image);
                        } 

                        $file_content .= "featured_image = " . '"' . $featured_image . '"' . "\n";
                    }
                    $file_content .= "type = " . '"' . $type. '"' . "\n";

                    //now append all metadata
                    if(!$language || $language=='default'){
                        $suffix=null;
                    }else{
                        $suffix="_$language";
                    }
                    $frontmatter=$entry[FRONTMATTER];
                    error_log("GENERATING AUTOFIELDS: skipping ".print_r($frontmatter,1));

                    foreach ($entry as $fieldname => $value) {
                        //skip fields already put in frontmatter
                        if ( $fieldname==FRONTMATTER || ( isset($frontmatter)  && in_array($fieldname, $frontmatter))) {
                            error_log("Skipping field for frontmatter ".$fieldname);
                            continue;
                        }

                        //get wether field need to be localized
                        $field = $this->getField($fields, $fieldname, $language_extensions);

//error_log("GEtting field for $fieldname: field: ".$field['name']." l ".$field['localize']);
                        //skip fields not in current language, if any
                        if($field['localize']) {
                            if ($language && $language != 'default') {
                                //look for suffix, else skip
                                if (!$this->endsWith($fieldname, $suffix))
                                    continue;
                                //change fieldname by stripping
                                $fieldname = substr($fieldname, 0, strlen($fieldname) - strlen($suffix));
                            }
                        }

                        if($language=='default' && $hasLanguages){
                                //skip if field ends in one of the available language
                            $skip=false;
                            foreach($language_extensions as $ext){
                                //error_log("Testing $fieldname with $ext");
                                if($this->endsWith($fieldname, "_$ext")) {
                                    $skip = true;
                                    break;
                                }
                            }
                            if($skip)
                                continue;

                        }

                        error_log("Generating field for $language -> $fieldname");


                        if (is_array($value)) { //image
                            error_log("VALUE IS ARRAY:".print_r($value,1));
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

            endforeach;

        }
        $ret=array("status"=>"ok");
        return json_encode($ret);
    }

    private function normalizeTOMLValue($value){
        //if string is multiline, surround with triple double-quotes """" and """"
        //in any case, escape quotes
        $value=str_replace('"','\"',$value);
        if(strpos($value,"\n")){
            return '"""'.$value.'"""';
        }
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

    private function getHugoField(&$entry, $fields, $language, $name){
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
            if($field['options'] && $field['options']['hugo']){
                if($field['options']['hugo']['name'] == $name){
                    if($suffix && $field['localize']){
                        //look for localized value
                        array_push($entry[FRONTMATTER],$field['name'].$suffix);
                        return $entry[$field['name'].$suffix];
                    }
                    array_push($entry[FRONTMATTER],$field['name']);
//                    error_log("***PUSHED".print_r($entry,1));
                    return $entry[$field['name']];
                }
            }
        }
        //if not found, look for field name
        foreach ($fields as $field){
            if($field['name']== $name){
                if($suffix && $field['localize']){
                    //look for localized value
                    array_push($entry[FRONTMATTER],$field['name'].$suffix);
                    return $entry[$field['name'].$suffix];
                }
                array_push($entry[FRONTMATTER],$field['name']);
                return $entry[$field['name']];
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
            if($field['options'] && $field['options']['hugo']){
                if($field['options']['hugo']['isfeatured'] == true){
                    if($suffix && $field['localize']){
                        //look for localized value
                        array_push($entry[FRONTMATTER],$field['name'].$suffix);
                        return $entry[$field['name'].$suffix]['path'];
                    }
                    array_push($entry[FRONTMATTER],$field['name']);
                    return $entry[$field['name']]['path'];
                }
            }
        }
        //if not found, look for field name
        foreach ($fields as $field){
            if($field['type']== 'image'){
                if($suffix && $field['localize']){
                    //look for localized value
                    array_push($entry[FRONTMATTER],$field['name'].$suffix);
                    return $entry[$field['name'].$suffix]['path'];
                }
                array_push($entry[FRONTMATTER],$field['name']);
                return $entry[$field['name']]['path'];
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
