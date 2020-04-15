<?php /* Template Name: HomePage */

$context = Timber::context();

$timber_post     = new Timber\Post();
$context['post'] = $timber_post;
if (class_exists('EM_Events')) {
    // todo : if event is defined
    $events = EM_Events::get( array('limit'=>1,'orderby'=>'start_time') );
    if (count($events) > 0) {
        $EM_Event = EM_Events::get( array('limit'=>1,'orderby'=>'start_time') )[0];
        $context['nextDateImage'] = $EM_Event->output('#_EVENTIMAGE{150,150}');
        $context['nextDateLocation'] = $EM_Event->output('#_LOCATIONNAME, #_LOCATIONADDRESS, #_LOCATIONTOWN');
        $context['nextDateLink'] = $EM_Event->output('#_ATT{link}');
        $context['nextDate'] = $EM_Event;
    } else {
        $context['noNextDate'] = true;
    }
}
Timber::render( ['pages/home-page.twig'], $context );
