<?php echo $header; ?>

<div class="head qua_page_title qua_page_title_great qua_image_bck qua_fixed" data-stellar-background-ratio="0.2" data-image="<?php echo HTTP_IMAGE; ?>catalog/masmana/hero-bg-green.png">

      <div class="qua_over" data-color="#fff" data-opacity="0"></div>

      <div class="container parent-height text-left">
        <div class="row">
          <div class="col-md-12 text-center relative">
            <h1 class="qua_h1_title"><?php echo $heading_title ; ?></h1>
          </div>
        </div>
      </div>
    </div>

    <section id="qua_content" class="qua_content">
        <section class="qua_section">
            <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                    <?php if(!empty($articles)):?> 
    					<?php foreach($articles as $article):?>
                        
                            <div class="qua_post_item">
								<div class="post_title">
                                  <h2><a href="<?php echo $article['href'] ?>"><?php echo $article['title'] ?></a></h2>
                                </div>
                                <div class="qua_auto_height">
	                                <div class="col-md-6 post_img">
	                                  <a href="<?php echo $article['href'] ?>"><img src="<?php echo $article['thumb']; ?>" alt="<?php echo $article['title'] ?>"/></a>
	                                </div>
                                  
                                	<div class="col-md-6 post_content ">
		                                <div class="qua_post_info">
		                                    <?php echo date('d.m.Y', strtotime($article['date_published'])); ?>
		                                </div>
		                                <p>
		                                  <?php echo $article['description']?>
		                                </p>
		                                
		                                  <div class="post_more pull-right clearfix">
		                                    <a href="<?php echo $article['href'] ?>" class="btn btn-more"><?php echo $button_read_more ?></a>
		                                  </div>
		                                
	                                </div>
	                            </div> 
                            </div>
                        
                       <?php endforeach; ?>

					<?php endif; ?>
                    </div>
                    <nav class="qua_blog_pag">
                    	<?php echo $pagination; ?>
                    </nav>
                </div>
            </div>
            </div>
        </section>
    </section>

<?php echo $footer; ?>

