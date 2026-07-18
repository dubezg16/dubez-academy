<?php get_header(); ?>

<section class="container" style="padding-top: 40px; padding-bottom: 40px; min-height: 60vh;">
    <?php
    while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 style="color: #003366; margin-bottom: 20px;"><?php the_title(); ?></h1>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</section>

<?php get_footer(); ?>