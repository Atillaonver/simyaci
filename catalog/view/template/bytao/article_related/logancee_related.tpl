<?php
if($registry->has('theme_options') == false) { 
 header("location: themeinstall/index.php"); 
 exit; 
}
$theme_options = $registry->get('theme_options');
$config = $registry->get('config');
$page_direction = $theme_options->get( 'page_direction' );
?>

<div class="box blog-style box-no-advanced" style="padding-bottom: 20px">
    <div class="box-heading"><?php echo $text_related_articles; ?></div>
    <div class="strip-line"></div>
    <div class="box-content">
        <?php if(!empty($articles)):?> 
        <div id="logancee_blog_home_page" class="owl_carousel">
            <?php foreach($articles as $article2):?>
               <div class="item">
                    <div class="blog-item">
                         <a href="<?php echo $article2['href']; ?>"><img alt="<?php echo $article2['title'] ?>" src="<?php echo $article2['thumb'] ?>"></a>
                         
                         <div class="main-post">
                              <h3 class="title-post"><a href="<?php echo $article2['href']; ?>"><?php echo $article2['title'] ?></a></h3>
                              <span class="main-post-inner">
                                   <span class="date-post"><i aria-hidden="true" class="icon_table"></i> <?php echo date('d-m-Y', strtotime($article2['date_published'])) ?></span>
                                   <span class="comment-post"><i aria-hidden="true" class="icon_comment_alt"></i> <?php echo $article2['comments_count']; ?> <?php echo $article2['comments_text']; ?></span>
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