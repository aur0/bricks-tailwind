<?php
/**
 * The main template file for the Bricks Tailwind child theme
 */

// If Bricks is not active, exit
if ( ! defined( 'BRICKS_VERSION' ) ) {
    exit;
}

get_header();
?>

<main id="main" class="site-main">
    <?php
    // Use Bricks builder content
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
    else :
        // Fallback content
        echo '<div class="container mx-auto p-4">';
        echo '<h1 class="text-2xl font-bold mb-4">No Content Found</h1>';
        echo '<p>Sorry, no content was found on this page.</p>';
        echo '</div>';
    endif;
    ?>
</main>

<?php
get_footer();
?>
