<?php if(!empty($articles)):?> 
    <?php foreach($articles as $article):?>
    <div class="postWrapper post">
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
         
         <div class="title-blog"><h2><a href="<?php echo $article['href'] ?>"><?php echo $article['title'] ?></a></h2></div>
         <div class="postDetails">
              <?php if($settings['comments_engine'] == 'LOCAL'):?>
              <?php echo $article['comments_count']?> <?php echo $text_comments?> 
              <?php endif; ?>
              <div class="create-time"><?php echo date('d/m/Y G:i', strtotime($article['date_published'])); ?></div>
         </div>
         <div class="postContent"><?php echo $article['description']?></div>
         <div class="bookmark-blog"></div>
         <div class="readmore"><a class="readmore-post" href="<?php echo $article['href'] ?>"><span><?php echo $button_read_more ?></span></a></div>
    </div>
    <?php endforeach; ?>

<?php endif; ?>
