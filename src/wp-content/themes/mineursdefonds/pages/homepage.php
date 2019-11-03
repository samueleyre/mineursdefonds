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

<!--Exploration environment -- hidden behind -->
<div class="exploration" id="exploration" style="z-index: 1; position: absolute; cursor: none"
     data-pointer-events="true">

    <div class="layer fonds" data-depth="0"
         style="background-image: url('<?php echo get_bloginfo( 'template_url' ) ?>/images/11.jpeg')">

    </div>

    <div class="layer roche roche_1" data-depth="3">
        <div>
            <a href="<?php echo get_page_link(21); ?>">
                <img src="<?php echo get_bloginfo( 'template_url' ) ?>/images/mini_roche_1.png" onclick="testClick()"
                     alt="">
                <span class="text">Madame ram dam</span>
            </a>
        </div>
    </div>

    <div class="layer roche roche_2" data-depth="1">
        <img src="<?php echo get_bloginfo( 'template_url' ) ?>/images/mini_roche_2.png" onclick="testClick()" alt="">
        <span class="text">Madame ram doum</span>
    </div>
</div>

<svg class="exploration" id="svg" style="z-index: 5; position: absolute; pointer-events: none">
    <radialGradient id="gradient" x2="1" y2="1">
        <stop offset="0%" stop-color="transparent"/>
        <stop offset="60%" stop-color="transparent"/>
        <stop offset="95%" stop-color="black"/>
        <stop offset="100%" stop-color="black"/>
    </radialGradient>
    <defs>
        <mask id="mask" x="0" y="0" width="100%" height="100%">
            <rect x="0" y="0" width="100%" height="100%" fill="#fff"></rect>
            <circle id="circle-mask" r="130"></circle>
        </mask>
    </defs>
    <image width="120%" mask="url(#mask)"
           xlink:href="<?php echo get_bloginfo( 'template_url' ) ?>/images/rideau.jpeg"></image>
    <circle id="circle-shadow" cx="831" cy="16" r="130"
            style="fill: url(#gradient) transparent; stroke-width: 68px; stroke: black;"></circle>
</svg>


<!--onload home page -->
<header>
    <div class="nav">
        <div class="nav-title">
            <h1>Les Mineurs de fonds</h1>
            <p class="citation"><i>'Un projecteur sur le front,
                Comme au casque du mineur, <br>
                Artiste mineur de fond' <span class="author">C. Nougaro</span></i>
            </p>
        </div>
	    <?php get_template_part( 'template-parts/header/mineursdefonds-menu' ); ?>
    </div>
</header>
<section class="homepage">
    <div class="homepage-explore" onclick="explore()">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 30.738951 30.815145"
            x="0px"
            y="0px"
            version="1.1"
            id="torche"
            width="30.738951"
            height="30.815145"
        >
            <path
                    d="m 13.978951,7.1166237 v 0 a 0.47,0.47 0 0 0 -0.41,0.22 15.82,15.82 0 0 0 -2.59,7.8300003 l -0.89,0.87 c -0.3599996,-0.46 -0.7799996,-1 -1.2999996,-1.13 -0.7,-0.14 -1.24,0.48 -1.68,0.91 -0.44,0.43 -0.63,0.66 -1,1 -0.37,0.34 -0.51,0.37 -0.6,0.68 -0.21,0.67 0.63,1.42 1,1.91 a 68.29,68.29 0 0 0 -6.40999996,6.76 0.46,0.46 0 0 0 -0.07,0.14 v 0 a 0.25,0.25 0 0 0 0,0.07 0.46,0.46 0 0 0 0.1,0.48 27,27 0 0 0 4.35999996,3.84 0.39,0.39 0 0 0 0.56,0 v 0 c 4.06,-3.46 7.5199996,-7.58 11.5899996,-11 a 11.63,11.63 0 0 0 6.47,-2 0.45,0.45 0 0 0 0.18,-0.21 v -0.07 c 0,0 0,0 0,-0.06 a 0.24,0.24 0 0 0 0,-0.09 c 0.56,-5.03 -4.19,-10.3500003 -9.31,-10.1500003 z m -2,7.8100003 a 14,14 0 0 1 1.14,-4.69 c 4,1 7.28,3.29 7.19,7.75 a 11.33,11.33 0 0 1 -3.52,0.66 5.51,5.51 0 0 0 -4.81,-3.72 z m -5.4199996,2.77 c 0,-0.07 0.84,-0.76 1,-0.91 0.16,-0.15 0.77,-1 1.16,-0.82 a 3,3 0 0 1 0.68,0.72 l -2.1,2.01 a 4.1,4.1 0 0 1 -0.74,-1 z m -1.85,12 a 27.25,27.25 0 0 1 -3.52,-3.18 c 3.15,-3.92 6.93,-7.14 10.4899996,-10.61 a 4.49,4.49 0 0 1 4.22,3.1 c -3.92,3.31 -7.2899996,7.26 -11.1899996,10.69 z m 17.6599996,-12.71 q -0.51,0.32 -1,0.57 c -0.1,-4.57 -3.54,-7.19 -7.76,-8.2500003 0.21,-0.4 0.43,-0.79 0.66,-1.18 4.38,0.01 8.33,4.5700003 8.1,8.8600003 z"
                    id="torch-path"
            />
            <path
                    d="m 17.158951,6.3966237 c 0.5,-1.9 0.71,-3.85 1.21,-5.75000005 a 0.51662365,0.51662365 0 0 0 -1,-0.26 c -0.5,1.89000005 -0.71,3.85000005 -1.21,5.74000005 a 0.51790443,0.51790443 0 0 0 1,0.27 z"
                        id="path6" class="light-beam"
            />
            <path
                    d="m 21.978951,9.3366237 q 3.54,-3.64 7.11,-7.31 a 0.5,0.5 0 0 0 -0.7,-0.7 l -7.17,7.31 c -0.45,0.45 0.26,1.16 0.76,0.7 z"
                    id="path8" class="light-beam"
            />
            <path
                    d="m 30.258951,13.146624 a 44.08,44.08 0 0 0 -5.86,0 0.5,0.5 0 0 0 0,1 44.08,44.08 0 0 1 5.86,0 c 0.64,0.06 0.64,-0.94 0,-1 z"
                    id="path10" class="light-beam"
            />
            <path
                    d="m 26.608951,9.4566237 -3.17,1.0000003 c -0.61,0.2 -0.35,1.17 0.26,1 l 3.18,-1 c 0.61,-0.23 0.35,-1.2000003 -0.27,-1.0000003 z"
                    id="path12" class="light-beam"
            />
            <path
                    d="m 19.768951,7.3566237 1.54,-3.1 c 0.29,-0.57 -0.57,-1.08 -0.86,-0.5 l -1.54,3.09 c -0.29,0.58 0.58,1.08 0.86,0.51 z"
                    id="path14" class="light-beam"
            />
        </svg>
        <span>Explorez la mine</span>
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
<script src="<?php echo get_bloginfo( 'template_url' ) ?>/js/mineursdefonds/navigation.js"></script>

</body>
</html>
