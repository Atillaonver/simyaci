<?php
class ControllerBytaoGallery extends Controller
{
	public function index()
	{
		$this->load->language('bytao/gallery');

		$data['title'] = $this->document->getTitle();

		if($this->request->server['HTTPS'])
		{
			$server = $this->config->get('config_ssl');
		}
		else
		{
			$server = $this->config->get('config_url');
		}

		$so       = $this->request->get['i'];
		$gi       = $this->request->get['gi'];
		$this->load->model('bytao/gallery');
		$this->load->model('tool/image');

		$galImage = $this->model_bytao_gallery->getGalleryImage($gi,$so);
		if(isset($galImage[0]))
		{
			$image_info = $galImage[0];
		}



		if(isset($image_info))
		{



			if($image_info['image']){
				$thumb = $this->model_tool_image->resizeTao($image_info['image']);
				$image = HTTP_SERVER.'image/'.$image_info['image'];
			}
			else
			{
				$image = $thumb = '';
			}



			if($image_info['gallery_image_description'] != ''){
				$text = strip_tags(html_entity_decode($image_info['gallery_image_description'], ENT_QUOTES, 'UTF-8'));
			}
			else
			{
				$text = ' ';
			}

			$this->document->setTitle($image_info['meta_title']);
			$this->document->setDescription($image_info['meta_description']);
			$this->document->setKeywords($image_info['meta_keyword']);

			$data['image'] = $thumb;
			define('IMAGE', $thumb);
			$data['title'] = $image_info['gallery_image_description'];
			$data['gallery_id'] = $gi;
			$data['sort_order'] = $so;
			$data['text'] = $text;


			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer',array('part_id'=>2));
			$data['header'] = $this->load->controller('bytao/header',array('menu_id'=>0,'amenu'  =>false));

			$temp = $this->config->get('config_template') . '/template/bytao/gallery.tpl';

			if(file_exists(DIR_TEMPLATE . $temp))
			{
				$this->response->setOutput($this->load->view($temp, $data));
			}
			else
			{
				$this->response->setOutput($this->load->view($temp, $data));
			}



		}
		else
		{
			$this->response->redirect($this->url->link('common/blog', '', 'SSL'));
		}
	}

	public function agree()
	{
		$this->load->model('catalog/information');

		if(isset($this->request->get['collection_iid']))
		{
			$collection_iid = (int)$this->request->get['collection_iid'];
		}
		else
		{
			$collection_iid = 0;
		}

		$output           = '';

		$collection_iinfo = $this->model_catalog_information->getInformation($collection_iid);

		if($collection_iinfo)
		{
			$output .= html_entity_decode($collection_iinfo['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->setOutput($output);
	}

	public function moreimage()
	{
		$json = array();
		
		$this->load->language('bytao/gallery');
		
		$this->load->model('bytao/gallery');
		$this->load->model('tool/image');
		$this->load->model('bytao/adv');

		$gallery_id = $this->request->get['g'];
		$type       = $this->request->get['t'];
		$last       = $this->request->get['l'];
		$limit      = 4;
		if($gallery_id && $type && $last)
		{


			$items = $this->model_bytao_gallery->getGalleryLastImages($gallery_id,$limit,$last);
			if($items){
				
			
			foreach($items as $item){
				if($item['image']){
					$thumb = $this->model_tool_image->resizeTao($item['image']);
					$image = HTTP_SERVER.'image/'.$item['image'];
				}
				else
				{
					$image = $thumb = '';
				}
				if($item['title'] != ''){
					$text = strip_tags(html_entity_decode($item['title'], ENT_QUOTES, 'UTF-8'));
				}
				else
				{
					$text = ' ';
				}
				$images[] = array (
					'text'      => $text,
					'thumb'     => $thumb,
					'image'     => $thumb,
					'sort_order'=> $item['sort_order'],
					'gallery_id'=> $gallery_id,
					'link'      => $item['link'],
					'bimage'    =>  HTTP_SERVER.'image/'.$item['bimage'],

				);
			}

			$store_id = $this->config->get('config_store_id');
			$advs     = $this->model_bytao_adv->getStoreLayoutAdv($store_id,0, 'content_gallery');
			if(count($advs) > 0 ){
				$addver = $this->load->controller('bytao/adv', $advs);
			}

			$sayac = 0;

			$col   = 1;
			if(isset($addver))
			{
				$adV = ' <div class="w3-white w3-margin"><div class="w3-container w3-padding w3-black"><h4>Reklam</h4></div><div class="w3-container w3-white">';
				$adV .= ' <div class="w3-container w3-display-container w3-section">';
				$adV .= $addver;
				$adV .= '</div>  </div></div>';
			}
			
			$json['view'] = array();

			if(isset($images))
			{
				$last = 0;
				foreach($images as $image)
				{

					$imG = '<div class="w3-display-container home-image">';
					$imG .= '<img src="'.$image['image'].'" alt="'.$image['text'].'" style="width:100%">';
					$imG .= '<div class="w3-display-middle w3-display-hover"><button onclick="clickHref(\'foton?i='.$image['sort_order'] .'&gi='.$image['gallery_id'].'\')" class="w3-button w3-circle w3-teal w3-padding-large"><i class="fa fa-address-card-o"></i></button> <button  onclick="onClick(this)" class="w3-button w3-circle w3-teal w3-padding-large"><i class="fa fa-search-plus"></i></button></div>';
					$imG .= '</div>';
					if($sayac == 3 && isset($addver))
					{
						$imgN = $adV.$imG;
					}
					else
					{
						$imgN = $imG;
					}

					switch($col)
					{
						case 1:
						if(isset($json['view'][1]))
						{
							$json['view'][1] .= $imgN;
						}
						else
						{
							$json['view'][1] = $imgN;
						}

						break;
						case 2:
						if(isset($json['view'][2]))
						{
							$json['view'][2] .= $imgN;
						}
						else
						{
							$json['view'][2] = $imgN;
						}
						break;
						case 3:
						if(isset($json['view'][3]))
						{
							$json['view'][3] .= $imgN;
						}
						else
						{
							$json['view'][3] = $imgN;
						}
						break;
						case 4:
						if(isset($json['view'][4]))
						{
							$json['view'][4] .= $imgN;
						}
						else
						{
							$json['view'][4] = $imgN;
						}
						break;
					}
					$col++;
					$sayac++;

					if($col > $type)
					{
						$col = 1;
					}
					$json['last']     = $item['sort_order'];
					
				}
			}
			}else{
				$json['error']=$this->language->get('error_more');
			}
		}


		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

}