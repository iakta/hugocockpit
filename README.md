# Hugo Cockpit addon
a Hugo addon for Cockpit CMS, allowing generating Hugo compatible content

*version 0.3.1*

It is a simple addon that will generate content for every collection in a Cockpit NEXT CMS.

The idea originated from Hugopit, a project to use Cockpit (the old version) as a fronted to Hugo, on https://github.com/sjardim/Hugopit : part of the code is copied from there, but not many anymore. Thanks anyway.

![Screenshot of Hugo Cockpit](https://github.com/zontarian/hugocockpit/blob/master/hugocockpit-screenshot1.png)



## Requirements

* Cockpit CMS Next: https://github.com/COCOPi/cockpit
* Hugo: https://gohugo.io/   a fast static site generator

## Install 

Simply copy the downloaded folder tree under the base directory of your Cockpit installation, in the addons subdir, like this:
`<cockpit_base_dir>/modules/addons/Hugo`  or `<cockpit_base_dir>/addons` for later versions.

IMPORTANT: if cloning from git, remember to rename the dir `hugocockpit` to `Hugo` (with capital `H`). 

Upon reloading Cockpit CMS you should find another menu named Hugo (the newspaper icon). 
Click on it to start exporting your collections as Hugo content.


## Setup

On the main Hugo plugin page, first you have to set the **base Hugo dir**. At the moment you have to set manually the folder (must be on the same server) where you have installed Hugo (i.e. the parent dir of the folder where Hugo usually exports your files)

This can also be done by hand editing a file named `config.yaml` in the root dir of this addon, or via the **Settings file** button on the lower right hand corner.

The file at the moment contains many entries, but this one in particular is needed for the plugin to run:

    # Cockpit-hugo config settings
    
    hugo_base_dir: /users/zontar/web/sites/hugo
    
and this one is needed for images to work correctly. Please use any path under `storage`, just take note of it.    
    
    cockpit_storage_prefix: /storage/hugo

Please see the config section below for the exact meeting

## Configuration

Hugo need some Cockpit configuration to run: in particular it is best to create a group, and a user, that will be the editor of the site, without all the privileges of the admin user.

So, go to `Cockpit menu / System settings / Settings` and add these lines to the config.yaml file 
  
    # Cockpit settings 
    
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
                 
This will create a new group `author` (or whatever name) with some privileges, and most importantly, will set a `finder.path`. This can be whatever you want, under the `cockpit_storage_prefix` direcotry as set in the hugo config file. 

Then create a user belonging to the newly create group, and give it to the editors, or use it to login in Cockpti to edit the site for Hugo.

### Images

Back in Cockpit, open up the Finder and create the dirs and subdirs as needed under `storage`. 

### Multilanguage

Then if you want to have multilanguage support, go to `Cockpit menu / System settings / Settings` add these lines to the config.yaml file

    # Cockpit settings
    # multilanguage s
    languages:
       en: "English"
       fr: "French"
       
Or whatever dictionary you want to have. The key (in this example "en" and "fr" will be the names of the subdirectories created by the Hugo plugin under the publishing directory.
   

## Features

If Cockpit CMS is configured to use multiple languages it will export multiple version of the pages under
`content/default` and `content/LANG` where LANG is whatever you configure cockpit with. 
So for example if you configure Cockpit with multilanguages (Cockpit/Settings/System settings) or edut `config/config.yaml` with
    
    # Cockpit settings
    languages: 
        en: "English"
        frCA: "French (Canada)"
    
LANG will be "en" and "frCA" etc.. 
Of course remember to have at least some fields configured as "Localize" while creating fields for a collection.

Hugo entries in collections can be exported as plain Hugo files, or you can specify some fields as to translate
to special Hugo fields, that will appear in the frontmatter or as the content.
You can do it from the main page, with the big button `Configure Hugo fields`.

![Screenshot of Hugo Cockpit](https://github.com/zontarian/hugocockpit/blob/master/hugocockpit-screenshot2.png)



Here, for every collection, you can see the fields and decide wether to give them some special name.
If you give them some special name, the field value will appear in the frontmatter of the Hugo page with the name you have chosen
If you specify `content` it will appear as the page content.. 
If you don't choose any special name, the field name will be used and the field will appear in the frontmatter.

You can also specify a `featured_image` if you have more than one image in the collection fields. 

Of course, you can specify also standard hugo frontmatter names, they will be translated. Some names have special meaning for the plugin:  At the moment recognized frontmatter special fields are `title`, `slug` (the name of the generated .md file), `date`, `publishdate` and of course the content: `content`.

These values will be stored in the JSON parameter of the Hugo field, under the `hugo` keyword.

## Work with Hugo

The first thing you need to do is to log in in Cockpit as `admin` and setup everything that will be needed by the `Hugo` user:

1. create new group and user as explained above
1. create media/image subdir of `storage` folder via finder
1. configure hugo settings file from the Hugo page with the `cockpit_storage_path`
1. set via gui or file, the `hugo_script` key
1. create a Cockpit collection in the usual way
1. map Cockpit fields of the collection to special hugo ones. At least ensure there is a `title` field and a `content` field (or a field with any name, but with the hugo mapping to `title` or `content`)

Please note that the `author` user won't be able to edit the Hugo settings, since it will be outside of its finder base path. Only root user can edit it, and it also make sense.

## Images

Remember: to have images work correctly, log in in Hugo with the user of group `author`, and upload images and select them accordingly. When translating to Hugo, the path will be set correctly. It won't work using the `Admin` user  

## Configuration

These are the configuration keys of the Hugo addon, as used in the `config.yaml` file

|key|default|use|
---|---|---
`hugo_script`|hugo|path to the hugo binary
`hugo_conf_prefix`|  config | base name of hugo config file
`hugo_conf_extension`|  toml | extension of hugo config file
`hugo_base_dir`| | full path of base Hugo installation dir, where content, config and public dir resides
`hugo_theme`| | theme used to generate Hugo site
`cockpit_storage_prefix`| /storage/hugo | prefix of Hugo specific subdir of the root finder
`hugo_extra_params`| --cleanDestinationDir | any parameter you want to be passed to hugo while generating a site
