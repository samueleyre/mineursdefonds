<?php /* Template Name: EventsPage */

$context = Timber::context();

$timber_post     = new Timber\Post();
$context['post'] = $timber_post;
Timber::render( ['pages/events-page.twig'], $context );
