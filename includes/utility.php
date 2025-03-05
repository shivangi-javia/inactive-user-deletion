<?php

// Get all custom post types
function iaud_all_post_types_with_names() {
    $args = [
        'public' => true,
        '_builtin' => false
    ];
    return get_post_types($args, 'names');
}
?>
