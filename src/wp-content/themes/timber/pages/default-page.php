<?php /* Template Name: DefaultPage */

$context = Timber::context();

$timber_post     = new Timber\Post();
$context['post'] = $timber_post;
Timber::render( ['pages/default-page.twig'], $context );
