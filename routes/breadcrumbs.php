<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

/*
| Activities list
*/
Breadcrumbs::for('activities.index', function (BreadcrumbTrail $trail) {
    $trail->push('Activities', route('activities.index'));
});

/*
| Activity detail
*/
Breadcrumbs::for('activities.show', function (BreadcrumbTrail $trail, $activity) {

    $locale = app()->getLocale();

    $trail->parent('activities.index');

    $trail->push(
        $activity->getTranslation('name', $locale),
        route(
            'activities.show',
            $activity->getTranslation('slug', $locale)
        )
    );
});