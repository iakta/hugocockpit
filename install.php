<?php
/**
 * Created by IntelliJ IDEA.
 * User: walter
 * Date: 23/05/2017
 * Time: 18:18
 */

// ACL
$app("acl")->addResource("hugo", ['manage.hugo']);


$app->on('admin.init', function() {

    $this->helper('admin')->addAssets('hugo:assets/components/cp-dirselect.tag');
    $this->helper('admin')->addAssets('hugo:assets/components/cp-themeselect.tag');


    if (!$this->module('cockpit')->hasaccess('hugo', ['manage.hugo'])) {
        return;
    }

    // bind admin routes /collections/*
    $this->bindClass('Hugo\\Controller\\Installer', 'hugo_installer');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Hugo Installer',
        'icon'  => 'hugo:icon.svg',
        'route' => '/hugo_installer',
        'active' => strpos($this['route'], '/hugo_installer') === 0
    ]);


});
