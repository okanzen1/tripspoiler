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

    $name = $activity->getTranslation('name', $locale)
        ?? $activity->getTranslation('name', 'en');

    $slug = $activity->getTranslation('slug', $locale)
        ?? $activity->getTranslation('slug', 'en');

    $trail->push(
        $name,
        route('activities.show', ['slug' => $slug])
    );
});