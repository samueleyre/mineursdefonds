<?php /* Template Name: HomePage */

$context = Timber::context();

$timber_post     = new Timber\Post();
$context['post'] = $timber_post;
if (class_exists('EM_Events')) {
	$context['nextDate'] = EM_Events::get( array('limit'=>1,'orderby'=>'start_time') )[0];
}
Timber::render( ['pages/home-page.twig'], $context );
