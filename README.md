# Hugo Cockpit addon
a Hugo addon for Cockpit CMS, allowing generating Hugo compatible content

*version 0.1*

It is a simple addon that will generate content for every collection in a Cockpit NEXT CMS.

The idea originated from Hugopit, a project to use Cockpit (the old version) as a fronted to Hugo, on https://github.com/sjardim/Hugopit : part of the code is copied from there, but not many anymore. Thanks anyway.

![Screenshot of Hugo Cockpit](https://github.com/zontarian/hugocockpit/blob/master/hugocockpit-screenshot1.png)



## Requirements

* Cockpit CMS Next: https://github.com/aheinze/cockpit
* Hugo: https://gohugo.io/   a fast static site generator

## Install 

Simply copy the downloaded folder tree under the base directory of your Cockpit installation, in the addons subdir, like this:
`<cockpit_base_dir>/modules/addons/Hugo`

Upon reloading Cockpit CMS you should find another menu named Hugo (the newspaper icon). 
Click on it to start exporting your collections as Hugo content.


## Setup

On the main Hugo plugin page, you will have to set manually the folder (must be on the same server) where
you want the plugin to export the files.

This can also be done by hand editing a file named `config.yaml` in the root dir of this addon.
The file at the moment contains only one entry:

    # Cockpit-hugo config settings
    
    hugo_base_dir: /users/zontar/web/sites/hugo




## Features

If Cockpit CMSis configured to use multi languages, it will export multiple version of the pages under 
`content/defaul` and `content/LANG` where LANG is a two-letter ISO code such as 'en', 'fr', 'it' etc..

Hugo entries in collections can be exported as plain Hugo files, or you can specify some fields as to translate
to special Hugo fields, as will appear in the frontmatter.
You can do it from the main page, with the big button `Configure Hugo fields`.

![Screenshot of Hugo Cockpit](https://github.com/zontarian/hugocockpit/blob/master/hugocockpit-screenshot2.png)



Here, for every collection, you can see the fields and decide wether to give them some special name.
If you give them some special name, the field value will appear in the frontmatter of the Hugo page, with the name you have chosen.
If you don't choose any name, the field name will be used.

You can also specify a `featured_image` if you have more than one image in the collection fields. 

Of course, you can specify standard hugo frontmatter, such as `title`, `content`, etc..

These values will be stored in the JSON parameter of the Hugo field, under the `hugo` keyword.
