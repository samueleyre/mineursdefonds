<?php /* Template Name: HomePage */ ?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="profile" href="https://gmpg.org/xfn/11"/>
    <link rel="stylesheet" href="<?php echo get_bloginfo( 'template_url' ) ?>/sass/mineursdefonds/homepage.css">
	<?php wp_head(); ?>
</head>
<body onmousemove="mouseMove(event)">

<!--Exploration environment-->
<div class="exploration" id="container" style="z-index: 1; position: absolute; cursor: none" data-pointer-events="true">

    <div class="layer planet" data-depth="0"
         style="background-image: url('<?php echo get_bloginfo( 'template_url' ) ?>/images/11.jpeg')">

    </div>

    <div class="layer cloud" data-depth="3">
        <img src="<?php echo get_bloginfo( 'template_url' ) ?>/images/cailloux_1.png" onclick="testClick()" alt="">
        <span class="text">Madame ram dam</span>
    </div>

    <div class="layer cloud2" data-depth="1">
        <img src="<?php echo get_bloginfo( 'template_url' ) ?>/images/cailloux_1.png" onclick="testClick()" alt="">
        <span class="text">Madame ram doum</span>
    </div>
</div>

<svg class="exploration" id="svg" style="z-index: 5; position: absolute; pointer-events: none">
    <radialGradient id="gradient" x2="1" y2="1">
        <stop offset="0%" stop-color="transparent"/>
        <stop offset="60%" stop-color="transparent"/>
        <stop offset="90%" stop-color="white"/>
        <stop offset="100%" stop-color="black"/>
    </radialGradient>
    <defs>
        <mask id="mask" x="0" y="0" width="100%" height="100%">
            <rect x="0" y="0" width="100%" height="100%" fill="#fff"></rect>
            <circle id="circle-mask" r="130"></circle>
        </mask>
    </defs>
    <image width="100%" mask="url(#mask)"
           xlink:href="<?php echo get_bloginfo( 'template_url' ) ?>/images/15.jpeg"></image>
    <circle id="circle-shadow" cx="831" cy="16" r="130"
            style="fill: url(#gradient) transparent; stroke-width: 1;"></circle>
</svg>


<!--onload home page -->

<header>
    <div class="nav">
        <div></div>
        <div id="nav-menu-icon" onclick="rotate()" class="nav-menu-icon">
            <img type="svg" src="<?php echo get_bloginfo( 'template_url' ) ?>/images/noun_menu.svg" alt="">
        </div>
        <div class="nav-title">
            <h1>Les Mineurs de fonds</h1>
            <p class="citation"><i>'Un projecteur sur le front,
                Comme au casque du mineur,
                Artiste mineur de fond'    C.Nougaro</i></p>
        </div>
    </div>
</header>
<section class="homepage">
    <div class="homepage-explore" onclick="explore()">
        <img type="svg" src="<?php echo get_bloginfo( 'template_url' ) ?>/images/noun_torch.svg"
             alt="">
        <span>Explorer la mine</span>
    </div>
    <div class="homepage-left">
        <img src="<?php echo get_bloginfo( 'template_url' ) ?>/images/roche_gauche.png" alt="">
    </div>
    <div class="homepage-right">
        <img src="<?php echo get_bloginfo( 'template_url' ) ?>/images/roche_droite.png" alt="">
    </div>


</section>

<script src="<?php echo get_bloginfo( 'template_url' ) ?>/js/mineursdefonds/librairies/parallax-js.3.1.0.min.js"></script>
<script src="<?php echo get_bloginfo( 'template_url' ) ?>/js/mineursdefonds/homepage.js"></script>

</body>
</html>
