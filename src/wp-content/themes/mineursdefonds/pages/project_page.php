<?php /* Template Name: ProjectPage */ ?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="profile" href="https://gmpg.org/xfn/11"/>
    <link rel="stylesheet" href="<?php echo get_bloginfo( 'template_url' ) ?>/sass/mineursdefonds/projectPage.css">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?> >

    <div class="projectPage">

	    <?php
	    if ( have_posts() ) {
		    while ( have_posts() ) {
			    the_post();
        ?>
            <div class="roche_bg" style="background-image: url('<?php echo get_bloginfo( 'template_url' ) ?>/images/ampoule.jpeg');">
                <div class="roche" style="background-image: url('<?php echo get_bloginfo( 'template_url' ) ?>/images/roche.png')"></div>
            </div>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="entry-content">
                    <?php the_title( '<h1 class="projectPage-title">', '</h1>' ); ?>
                    <?php the_content('<div class="test">', '</div>'); ?>
                </div>
            </article>
        <?php
		    }
	    }
	    ?>
    </div>

</body>
</html>
