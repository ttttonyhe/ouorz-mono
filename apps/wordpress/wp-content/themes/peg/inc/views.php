<?php
function getPostViews($postID){   
    $count_key = 'post_views_count';   
    $count = get_post_meta($postID, $count_key, true);   
    if($count==''){   
        return "0 Views";   
    }   
    return $count.' Views';   
}   
function setPostViews($postID) {
    return peg_increment_post_counter((int)$postID, 'post_views_count');
}
?>
