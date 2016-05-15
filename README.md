# Hugo Cockpit addon
a Hugo addon for Cockpit CMS, allowing generating Hugo compatible content

It is a simple addon that will generate content for every collection in a Cockpit NEXT CMS.

The idea originated from Hugopit, a project to use Cockpit (the old version) as a fronted to Hugo, on https://github.com/sjardim/Hugopit : part of the code is copied from there, but not many anymore. Thanks anyway.

See https://github.com/aheinze/cockpit for Cockpit NEXT CSM

See https://gohugo.io/ for Hugo, a fast static site generator

## Install 

Simply copy the donloaded folder tree under 
`<cockpit_base_dir>/modules/addons/Hugo`

Upon reloading Cockpit CMS you should find another menu named Hugo. 
Click on it to start exporting your collections as Hugo content


## Setup

On the main Hugo plugin page, you will have to set manually the folder (must be on the same server) where
you want the plugin to export the files.

## Features

If Cockpit CMSis configured to use multi languages, it will export multiple version of the pages under 
`content/defaul` and `content/LANG` where LANG is a two-letter ISO code such as 'en', 'fr', 'it' etc..
