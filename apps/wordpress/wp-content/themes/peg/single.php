<?php
if (($_GET['from'] ?? '') !== 'front') {
    wp_redirect('https://www.ouorz.com/post/'.get_queried_object_id(), 301);
    exit;
}
setPostViews(get_queried_object_id());
header('Access-Control-Allow-Origin: *');
get_header(); ?>
<div class="container">
    <h1>TonyHe</h1>
    <p>Just A Poor Lifesinger</p>
    <div class="contact">
        <a class="github" href="https://github.com/ttttonyhe">Github: Helipengtony</a>
        <a class="email" href="mailto:tony.hlp@hotmail.com">Email: tony.hlp@hotmail.com</a>
    </div>
</div>
<?php get_footer(); ?>
