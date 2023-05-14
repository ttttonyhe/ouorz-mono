<?php
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
<?php
if($_GET['from'] !== 'front'){
    header("Location:https://www.ouorz.com/post".$_SERVER['REQUEST_URI']);
}else{
    setPostViews($post->ID);
} ?>
<?php get_footer(); ?>
