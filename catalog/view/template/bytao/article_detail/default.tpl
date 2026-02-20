<?php echo $header; ?>

<?php if($settings['comments_engine'] == 'FACEBOOK'):?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/pl_PL/sdk.js#xfbml=1&version=v2.4";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<?php endif; ?>

<div class="head qua_page_title qua_page_title_great qua_image_bck qua_fixed" data-stellar-background-ratio="0.2" data-image="<?php echo $settings['himage']?HTTP_IMAGE.$settings['himage']:HTTP_IMAGE.'catalog/masmana/hero-bg-green.png'; ?>">
      <div class="qua_over" data-color="#fff" data-opacity="0"></div>
      <div class="container text-left">
        <div class="row">
          <div class="col-md-12">
            <h1 class="qua_h1_title text-center">Blog</h1>
            
          </div>
        </div>
      </div>
    </div>
    <section id="qua_content" class="qua_content">
    	<div class="container"><a href="<?php echo $toblog ?>"> <-- Blog </a></div>
        <section class="qua_section">
            <div class="container">
<div class="row">
<?php if(!empty($article)):?>
<div class="post">
    <div class="post-entry">
        <?php if(!empty($article['gallery'])):?>
            <?php if($article['article_list_gallery_display'] == 'CLASSIC'):?>
                <div class="post-media">
                    <?php echo $article['gallery'][0]['output'] ?>
                </div>
            <?php endif; ?>
            <?php if($article['article_list_gallery_display'] == 'SLIDER'):?>
                <div class="post-media">
                    <div class="media-slider">
                    <?php foreach($article['gallery'] as $gallery):?>
                        <div class="item"><?php echo $gallery['output'] ?></div>
                    <?php endforeach ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <h2 class="post-title">
        <?php echo $article['title'] ?>
        </h2>
        <ul class="meta">
            <?php if($author):?>
                <li><?php echo $text_posted_by ?> : <a href="<?php echo  $author['href']; ?>"><?php echo $author['name']; ?></a></li>
            <?php endif; ?>
            <?php /*/ ?>
            <li>
                <span class="month">
                    <?php echo date('M', strtotime($article['date_published'])); ?>
                </span>
                <span class="day">
                    <?php echo date('d', strtotime($article['date_published'])); ?>,
                </span>
                <span class="day">
                    <?php echo date('Y', strtotime($article['date_published'])); ?>
                </span>
            </li>
            <?php */ ?>
            <?php if(!empty($article['categories'])):?>
            <?php $i = 0; ?>
            <li class="post-categories">
                <span><?php echo $text_category ?>: </span>
                <?php foreach($article['categories'] as $cat):?>
                <?php $i++; ?>
                <a href="<?php echo $cat['href'] ?>"><?php echo $cat['name'] ?></a><?php if($i < count($article['categories'])):?>, <?php endif; ?>
                <?php endforeach; ?>
            </li>
            <?php endif; ?>
            <?php /*/ ?>
            <?php if($settings['comments_engine'] == 'LOCAL'):?>
            <li><?php echo $text_comments?>: <?php echo $article['comments_count']?></li>
            <?php endif; ?>
            <?php */ ?>
        </ul>

        <div class="post-content">
            <?php echo $article['content']?>
        </div>
        
        <?php if ($tags): ?>
        <div class="meta-row" style="padding-bottom: 20px">
        <div class="inline">
            <label><b><?php echo $text_tags; ?>:</b></label>
            <?php for ($i = 0; $i < count($tags); $i++): ?>
            <?php if ($i < (count($tags) - 1)): ?>
                <span><a href="<?php echo $tags[$i]['href']; ?>"><?php echo $tags[$i]['tag']; ?></a><span>,
                <?php else: ?>
                <span><a href="<?php echo $tags[$i]['href']; ?>"><?php echo $tags[$i]['tag']; ?></a></span>
            <?php endif; ?>
            <?php endfor ?>
        </div>
        </div>
        <?php endif; ?>
        
        <?php if($settings['author_description'] == 1):?>
        <?php if(!empty($author)):?>
        <div class="blog-post-author">
            <div class="media">
                <?php if(isset($author['thumb'])):?>
                <a href="<?php echo $author['href'] ?>" class="photo">
                    <img src="<?php echo $author['thumb'] ?>" alt="<?php echo $author['name'] ?>">
                </a>
                <?php endif; ?>
                <div class="media-body">
                    <h4 class="media-heading"><a href="<?php echo $author['href'] ?>"><?php echo $author['name'] ?></a></h4>
                    <div class="desc"><?php echo $author['description'] ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
     <?php if(!empty($articles) && $settings['article_related_status'] == 1) : ?>
     
         <?php
         include('catalog/view/theme/' . $config->get('theme_' . $config->get('config_theme') . '_directory') . '/template/blog/article_related/default'); 
         ?>
        
     
     <?php endif; ?>
     
     
     <?php if(!empty($products) && $settings['product_related_status'] == 1) : ?>
       <?php 
       $class = 3; 
       $id = rand(0, 5000)*rand(0, 5000); 
       $all = 4; 
       $row = 4; 
       
       if($settings['product_related_per_row'] == 6) { $class = 2; }
       if($settings['product_related_per_row'] == 5) { $class = 25; }
       if($settings['product_related_per_row'] == 3) { $class = 4; }
       
       if($settings['product_related_per_row'] > 1) {
           $row = $settings['product_related_per_row'];
           $all = $settings['product_related_per_row'];
           } 
       ?>
       <div class="box clearfix box-no-advanced box-with-products <?php if($settings['product_scroll_related'] == 1) { echo 'with-scroll'; } ?>" style="padding-bottom: 20px">
         <?php if($settings['product_scroll_related'] == 1) : ?>
         <!-- Carousel nav -->
         <a class="next" href="#myCarousel<?php echo $article_id; ?>" id="myCarousel<?php echo $article_id; ?>_next"><span></span></a>
         <a class="prev" href="#myCarousel<?php echo $article_id; ?>" id="myCarousel<?php echo $article_id; ?>_prev"><span></span></a>
         <?php endif; ?>
       	
         <div class="box-heading"><?php echo $text_related_products; ?></div>
         <div class="strip-line"></div>
         <div class="clear"></div>
         <div class="box-content products related-products">
           <div class="box-product">
           	<div id="myCarousel<?php echo $article_id; ?>" <?php if($settings['product_scroll_related'] == 1) { ?>class="carousel slide"<?php } ?>>
           		<!-- Carousel items -->
           		<div class="carousel-inner">
           			<?php $i = 0; $row_fluid = 0; $item = 0; foreach ($products as $product) { $row_fluid++; ?>
       	    			<?php if($i == 0) { $item++; echo '<div class="active item"><div class="product-grid"><div class="row">'; } ?>
       	    			<?php $r=$row_fluid-floor($row_fluid/$all)*$all; if($row_fluid>$all && $r == 1) { if($settings['product_scroll_related'] == 1) { echo '</div></div></div><div class="item"><div class="product-grid"><div class="row">'; $item++; } else { echo '</div><div class="row">'; } } else { $r=$row_fluid-floor($row_fluid/$row)*$row; if($row_fluid>$row && $r == 1) { echo '</div><div class="row">'; } } ?>
       	    			<div class="col-sm-<?php echo $class; ?> col-xs-6">
       	    				<?php include('catalog/view/theme/'.$config->get('theme_' . $config->get('config_theme') . '_directory').'/template/new_elements/product.tpl'); ?>
       	    			</div>
           			<?php $i++; } ?>
           			<?php if($i > 0) { echo '</div></div></div>'; } ?>
           		</div>
       	  </div>
           </div>
         </div>
       </div>
   <?php endif; ?>   
       
       <?php if($settings['product_scroll_related'] == 1): ?>
       <script type="text/javascript">
       $(document).ready(function() {
         var owl<?php echo $article_id; ?> = $(".box #myCarousel<?php echo $article_id; ?> .carousel-inner");
       	
         $("#myCarousel<?php echo $article_id; ?>_next").click(function(){
             owl<?php echo $article_id; ?>.trigger('owl.next');
             return false;
           })
         $("#myCarousel<?php echo $article_id; ?>_prev").click(function(){
             owl<?php echo $article_id; ?>.trigger('owl.prev');
             return false;
         });
           
         owl<?php echo $article_id; ?>.owlCarousel({
         	  slideSpeed : 500,
             singleItem:true,
             <?php /* if($page_direction[$config->get( 'config_language_id' )] == 'RTL'): ?>
             direction: 'rtl'
             <?php endif;*/ ?>
          });
       });
       </script>
       <?php endif ?>
        
        <?php if($article['status_comments']): ?>
        <?php if($settings['comments_engine'] == 'LOCAL'):?>

            <div class="box box-no-advanced comments" style="padding-bottom: 20px">
                 <div class="box-heading"><?php echo $text_comments ?>: <?php echo $article['comments_count']; ?></div>
                 <div class="strip-line"></div>
                 <div class="box-content">
                      <?php if(!empty($comments)):?>
                      <div class="comments-list">
                          <?php foreach($comments as $comment):?>
                          <div class="author"><span class="name"><?php echo $comment['name']; ?></span> <span class="date"><?php echo date('d-m-Y H:i', strtotime($comment['date_added'])); ?></span></div>
                              <div class="text">
                                  <?php echo $comment['content']; ?>
                              </div>
                          <?php endforeach; ?>
                      </div> 
                      <?php else: ?>
                           <?php echo $text_no_comments ?>
                      <?php endif; ?>
                 </div>
            </div>
            
            <div class="box box-no-advanced leave-reply" id="reply-block">
                 <div class="box-heading"><?php echo $text_leave_reply ?></div>
                 <div class="strip-line"></div>
                 <div class="box-content">
                       <p style="padding-top: 5px"><?php echo $text_required_info ?><abbr class="required">*</abbr> </p>
       
                       <form class="form-horizontal" method="post" id="form-comment">
                           <div class="form-group required">
                               <div class="col-xs-12 col-sm-6">
                                   <label class="control-label" for="input-name"><?php echo $text_name ?></label>
                                   <input type="text" name="name" value="" id="input-name" class="form-control">
                               </div>
                               <div class="col-xs-12 col-sm-6">
                                   <label class="control-label" for="input-email"><?php echo $text_email ?></label>
                                   <input type="text" name="email" value="" id="input-email" class="form-control">
                               </div>
                           </div>
                           <div class="form-group required">
                               <div class="col-xs-12">
                                   <label class="control-label" for="input-content"><?php echo $text_content ?></label>
                                   <textarea rows="10" id="input-content" name="content" class="form-control"></textarea>
                               </div>
                           </div>
       
                           <div class="text-center">
                                <button class="button button-large button-comment" id="button-comment" type="submit"><?php echo $button_post_comment ?></button>
                           </div>
                       </form>
                 </div>
                 
            </div>
            <?php endif; ?>
            <?php if($settings['comments_engine'] == 'DISQUS'):?>
            <div style="height: 40px"></div>
            <div id="disqus_thread"></div>
            <script type="text/javascript">
                /* * * CONFIGURATION VARIABLES * * */
                var disqus_shortname = '<?php echo $settings['disqus_name'] ?>';

                /* * * DON'T EDIT BELOW THIS LINE * * */
                (function() {
                    var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
                    dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
                    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
                })();
            </script>
            <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript" rel="nofollow">comments powered by Disqus.</a></noscript>
            <?php endif; ?>
            <?php if($settings['comments_engine'] == 'FACEBOOK'):?>
                 <div style="height: 40px"></div>
                <div class="fb-comments" data-href="<?php echo "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" data-width="100%"></div>
            <?php endif; ?>
        <?php endif; ?>

            </div>
  
            <?php endif; ?>
            
        </div>

<script>
    $(function(){
        $('.media-slider').owlCarousel({
            navigation : true, // Show next and prev buttons
            slideSpeed : 300,
            paginationSpeed : 400,
            singleItem:true,
            pagination: true,
            autoHeight : true,
            lazyLoad: true,
            navigationText: false,
            <?php /* if($page_direction[$config->get( 'config_language_id' )] == 'RTL'): ?>
            direction: 'rtl'
            <?php endif;*/ ?>
        })
        
        $('#button-comment').on('click', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'index.php?route=blog/article/write&article_id=<?php echo $article_id; ?>',
                type: 'post',
                dataType: 'json',
                data: $("#form-comment").serialize(),
                beforeSend: function() {
                    $('#button-comment').button('loading');
                },
                complete: function() {
                    $('#button-comment').button('reset');
                },
                success: function(json) {
                    $('.alert-success, .alert-danger').remove();

                    if (json['error']) {
                        $('.leave-reply .box-content > *:first-child').before('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '</div>');
                    }

                    if (json['success']) {
                        $('.leave-reply .box-content > *:first-child').before('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '</div>');

                        $('input[name=\'name\']').val('');
                        $('input[name=\'email\']').val('');
                        $('textarea[name=\'content\']').val('');
                    }
                }
            });
        });
    });
</script>
        
</script>

 </div>
 </div>
            <!-- container end -->
</section>
	</section>
<?php echo $footer; ?>