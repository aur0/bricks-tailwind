<?php

add_action('wp_ajax_regenerate_tailwind_css', 'ajax_regenerate_tailwind_css');
function ajax_regenerate_tailwind_css() {
    // Check if a page_id is sent via POST, fallback to get_the_ID or queried object
    $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;

    // If no page_id is provided, use get_queried_object_id to retrieve the current page's ID
    if (!$page_id && function_exists('get_queried_object_id')) {
        $page_id = get_queried_object_id();
    }

    // Ensure it's a valid page ID
    if ($page_id && get_post_type($page_id) !== 'page') {
        $page_id = 0; // Reset if the ID doesn't correspond to a page
    }

    // Combine page_id for meta retrieval
    $all_ids = [$page_id];

    // Retrieve and extract Tailwind classes for all IDs
    $tailwind_classes = [];
    foreach ($all_ids as $id) {
        $post_meta = get_post_meta($id);
        $id_classes = [];
        
        foreach ($post_meta as $meta_key => $meta_values) {
            foreach ($meta_values as $meta_value) {
                $classes = extract_tailwind_classes($meta_value);
                $id_classes = array_merge($id_classes, $classes);
            }
        }
        
        // Remove duplicates and store
        $tailwind_classes[$id] = array_values(array_unique($id_classes));
    }

    $tailwind_file_path = __DIR__ . '/dist/tailwind.min.css';
    $tailwind_match_count = 0;

    if (file_exists($tailwind_file_path)) {
        $file_contents = file_get_contents($tailwind_file_path);
        
        foreach ($tailwind_classes as $id => $classes) {
            foreach ($classes as $class) {
                if (strpos($file_contents, $class) !== false) {
                    $tailwind_match_count++;
                }
            }
        }
    }

    // Create frontend CSS with matched classes
    $frontend_css_path = create_frontend_tailwind_css($tailwind_classes);

    // Prepare the response with more page-specific context
    $response = [
        'page_id'       => $page_id,
        'post_type'     => $page_id ? get_post_type($page_id) : null,
        'current_page'  => is_front_page() ? 'front_page' : (is_home() ? 'home' : 'inner_page'),
        'message'       => 'Regenerating Tailwind CSS with page details',
        'tailwind_classes' => $tailwind_classes,
        'tailwind_match_count' => $tailwind_match_count,
        'tailwind_frontend_css_path' => $frontend_css_path
    ];

    // Log the response for debugging purposes
    error_log(print_r($response, true));

    // Return JSON response
    wp_send_json_success($response);

    // Immediately exit
    wp_die();
}

function extract_tailwind_classes($meta_value) {
    // Check if it's a serialized array
    if (is_string($meta_value) && is_serialized($meta_value)) {
        $unserialized = unserialize($meta_value);
        
        // Recursive function to extract classes
        $extract_classes = function($item) use (&$extract_classes) {
            $classes = [];
            
            // If item is an array, recursively search for classes
            if (is_array($item)) {
                foreach ($item as $key => $value) {
                    if ($key === '_cssClasses' && is_string($value)) {
                        // Split classes and trim
                        $classes = array_filter(array_map('trim', explode(' ', $value)));
                    } elseif (is_array($value)) {
                        // Recursively search in nested arrays
                        $nested_classes = $extract_classes($value);
                        $classes = array_merge($classes, $nested_classes);
                    }
                }
            }
            
            return $classes;
        };
        
        // Extract classes from the unserialized data
        return $extract_classes($unserialized);
    }
    
    return [];
}

function create_frontend_tailwind_css($tailwind_classes) {
    $input_file_path = __DIR__ . '/dist/tailwind.min.css';
    $output_file_path = __DIR__ . '/dist/bricks-tailwind.min.css';
    $matched_classes_css = '';

    // Collect all unique classes across all pages
    $all_unique_classes = [];
    foreach ($tailwind_classes as $id => $classes) {
        $all_unique_classes = array_merge($all_unique_classes, $classes);
    }
    $all_unique_classes = array_unique($all_unique_classes);

    if (file_exists($input_file_path)) {
        $file_contents = file_get_contents($input_file_path);
        
        foreach ($all_unique_classes as $class) {
            // Escape special regex characters in the class name
            $escaped_class = preg_quote($class, '/');
            
            // More precise regex to match the exact class
            $pattern = '/\.' . $escaped_class . '(?:\s*[,:][^{]*)?{[^}]*}/';
            preg_match_all($pattern, $file_contents, $matches);
            
            if (!empty($matches[0])) {
                $matched_classes_css .= implode("\n", $matches[0]) . "\n";
            }
        }

        // Write matched classes to new CSS file
        file_put_contents($output_file_path, $matched_classes_css);
    }

    return $output_file_path;
}

// Enqueue the JavaScript (tailwind-save-listener.js)
function enqueue_regenerate_css_script() {
    wp_enqueue_script(
        'tailwind-save-listener', 
        get_stylesheet_directory_uri() . '/js/tailwind-save-listener.js', 
        ['jquery'], 
        filemtime(get_stylesheet_directory() . '/js/tailwind-save-listener.js'), 
        true
    );
    
    // Localize script to provide ajax_url and page ID
    wp_localize_script('tailwind-save-listener', 'tailwind_save_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'page_id' => get_the_ID() // Use get_the_ID() to get the current page ID
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_regenerate_css_script');
add_action('admin_enqueue_scripts', 'enqueue_regenerate_css_script');

function enqueue_tailwind_in_bricks() {
    // Enqueue Tailwind CSS only in the Bricks Builder environment
    if ( bricks_is_builder() ) {
        wp_enqueue_style(
            'tailwind-bricks', 
            get_stylesheet_directory_uri() . '/dist/tailwind.min.css',
            [], 
            filemtime( get_stylesheet_directory() . '/dist/tailwind.min.css' )
        );
    }
    
  
}
add_action( 'wp_enqueue_scripts', 'enqueue_tailwind_in_bricks' );


function enqueue_child_theme_styles() {
    // Enqueue the Tailwind CSS file from the child theme's /dist/ directory
    wp_enqueue_style(
        'tailwind-frontend', 
        get_stylesheet_directory_uri() . '/dist/bricks-tailwind.min.css',
        [], // No dependencies, adjust if needed
            filemtime( get_stylesheet_directory() . '/dist/bricks-tailwind.min.css' )
    );
}
add_action( 'wp_enqueue_scripts', 'enqueue_child_theme_styles' );





?>