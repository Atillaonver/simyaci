<?php

function by_move(string $image) {
	if (!is_file(DIR_IMAGE . $image)) {
		if (is_file(DIR_ADMIMAGE . $image)) {
			$directories = explode('/', dirname(str_replace('../', '', $image )));
			$path = '';
			foreach($directories as $directory){
				if($path == ''){
					$path = $directory;
				}
				else
				{
					$path = $path . '/' . $directory;
				}
				if(!is_dir(DIR_IMAGE . $path)){
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}
			copy(DIR_ADMIMAGE.$image, DIR_IMAGE.$image);
		}	
	}
	return $image;
}