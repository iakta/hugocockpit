<?php

// ACL
$app("acl")->addResource("hugo", ['manage.hugo']);


$app->on('admin.init', function() {

    $this->helper('admin')->addAssets('hugo:assets/components/cp-dirselect.tag'); 

    if (!$this->module('cockpit')->hasaccess('hugo', ['manage.hugo'])) {
        return;
    }

    // bind admin routes /collections/*
    $this->bindClass('Hugo\\Controller\\Admin', 'hugo');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Hugo',
        'icon'  => 'newspaper-o',
        'route' => '/hugo',
        'active' => strpos($this['route'], '/hugo') === 0
    ]);

    /**
     * listen to app search to filter collections
     */
    $this->on('cockpit.search', function($search, $list) {

        foreach ($this->module('hugo')->collections() as $collection => $meta) {

            if (stripos($collection, $search)!==false || stripos($meta['label'], $search)!==false) {

                $list[] = [
                    'icon'  => 'newspaper-o',
                    'title' => $meta['label'] ? $meta['label'] : $meta['name'],
                    'url'   => $this->routeUrl('/hugo/admin/')
                ];
            }
        }
    });

    /*
    $this->on('cockpit.menu.aside', function() {

        $cols        = $this->module('hugo')->collections();
        $collections = [];

        foreach($cols as $collection) {
            if ($collection['in_menu']) $collections[] = $collection;
        }

        if (count($collections)) {
            $this->renderView("hugo:views/partials/menu.php", compact('collections'));
        }
    });
    */

    // dashboard widgets
    $this->on("admin.dashboard.widgets", function($widgets) {

        $collections = $this->module("collections")->collections(true);

        $widgets[] = [
            "name"    => "hugo",
            "content" => $this->view("hugo:views/widgets/dashboard.php", compact('collections')),
            "area"    => 'aside-left'
        ];

    }, 100);
});
