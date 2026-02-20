<?php
if($registry->has('theme_options') == false) { 
 header("location: themeinstall/index.php"); 
 exit; 
}
$theme_options = $registry->get('theme_options');
$config = $registry->get('config');
$page_direction = $theme_options->get( 'page_direction' );
?>

<div class="box blog-style box-no-advanced">
    <div class="box-heading"><?php echo $heading_title; ?></div>
    <div class="strip-line"></div>
    <div class="box-content">
        <?php if(!empty($articles)):?> 
        <div id="logancee_blog_home_page" class="owl_carousel">
            <?php foreach($articles as $article):?>
               <div class="item">
                    <div class="blog-item">
                         <a href="<?php echo $article['href']; ?>"><img alt="<?php echo $article['title'] ?>" src="<?php echo $article['thumb'] ?>"></a>
                         
                         <div class="main-post">
                              <h3 class="title-post"><a href="<?php echo $article['href']; ?>"><?php echo $article['title'] ?></a></h3>
                              <span class="main-post-inner">
                                   <span class="date-post"><i aria-hidden="true" class="icon_table"></i> <?php echo date('d-m-Y', strtotime($article['date_published'])) ?></span>
                                   <span class="comment-post"><i aria-hidden="true" class="icon_comment_alt"></i> <?php echo $article['comments_count']; ?> <?php echo $article['comments_text']; ?></span>
                              </span>
                         </div>
                    </div>
               </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
     $("#logancee_blog_home_page").owlCarousel({
          items : 3,
          itemsCustom : false,
          itemsDesktop : [1199,3],
          itemsDesktopSmall : [991,2],
          itemsTablet: [768,2],
          itemsTabletSmall: false,
          itemsMobile : [479,1],
          <?php if($page_direction[$config->get( 'config_language_id' )] == 'RTL'): ?>
          direction: 'rtl'
          <?php endif; ?>
     });
});
</script>