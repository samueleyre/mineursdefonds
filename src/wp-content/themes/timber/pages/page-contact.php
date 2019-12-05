<?php /* Template Name: ContactPage */

$context = Timber::context();

$timber_post     = new Timber\Post();
$context['post'] = $timber_post;
Timber::render( ['pages/contact-page.twig'], $context );
