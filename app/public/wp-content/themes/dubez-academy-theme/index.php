<?php get_header(); ?>

<section class="container" style="padding-top: 40px; padding-bottom: 40px;">
    <div class="section-title">
        <h2>School News</h2>
    </div>
    <div class="grid">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) : the_post(); ?>
                <div class="card">
                    <h3><?php the_title(); ?></h3>
                    <p><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
                    <a href="<?php the_permalink(); ?>" style="color: #ffcc00; font-weight: bold;">Read More →</a>
                </div>
            <?php endwhile;
        else :
            echo '<p>No posts found.</p>';
        endif;
        ?>
    </div>
</section>

<?php get_footer(); ?>