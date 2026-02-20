<div class="container">
	<h2 class="widget-title text-center"><?php echo $text_widget_title; ?></h2>

	<?php if(!empty($articles)):?> 
	<?php $reverse = false;?> 
		<?php foreach($articles as $article):?>
			<div class="row qua_auto_height blog-latest">
				<?php if($reverse){ ?> 
				<div class="col-md-6 p0">
					 <?php if($article['thumb']) { ?>
                        <div  class="thumb-holder">
                            <img alt="" src="<?php echo $article['thumb'] ?>" class="by-image">
                        </div>
                     <?php } ?>
				</div>
				<div class="col-md-6 p0">
					<div class="blog-widget-box">
						<h3 class="blog-widget-title"><?php echo $article['title'] ?></h3>
					<div class="blog-widget-desc"><?php echo $article['description'] ?></div>
					<div class="blog-widget-more"><a href="<?php echo $article['href']; ?>"><?php echo $button_read_more; ?></a></div>
					</div>
				</div>
				<?php $reverse = false;?> 
				<?php } else { ?> 
				<div class="col-md-6 p0">
					<div class="blog-widget-box">
						<h3 class="blog-widget-title"><?php echo $article['title'] ?></h3>
					<div class="blog-widget-desc"><?php echo $article['description'] ?></div>
					<div class="blog-widget-more"><a href="<?php echo $article['href']; ?>"><?php echo $button_read_more ?></a></div>
					</div>
				</div>
				<div class="col-md-6 p0">
					<?php if($article['thumb']) { ?>
                        <div  class="thumb-holder">
                            <img alt="" src="<?php echo $article['thumb'] ?>" class="by-image">
                        </div>
                    <?php } ?>
				</div>
				<?php $reverse = true;?> 
				<?php } ?> 
			</div>
		
		<?php endforeach; ?>
	<?php endif; ?>
	
	<div class="text-center bottom-button">
          	<a href="<?php echo $more; ?>" class="btn btn-testimonial"><?php echo $button_more_article; ?></a>
    </div>
</div>
