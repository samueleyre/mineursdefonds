<?php /* Template Name: AboutPage */

$context = Timber::context();

$timber_post     = new Timber\Post();
$context['post'] = $timber_post;
Timber::render( ['pages/about-page.twig'], $context );
