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
