<?php use Intervention\Image\Image;

/**
 * A Gallery plugin for the micro CMS Pico.
 *
 * @author Klas Gidlöv
 * @link http://gidlov.com/code
 * @license LGPL
 */

define('GALLERY_PRE_THUMB_NAME', 'thumb_');
define('GALLERY_FLUSH_URL', 'flush');
define('GALLERY_DIR', str_replace(ROOT_DIR, '', CONTENT_DIR));

class Gallery {

	private $url;
	private $config;
	private $gallery;
	private $image;
	private $request;
	private $current;
	private $file;

	/**
	* Load config variables before they are loaded automatically.
	*/
	public function __construct() {
		global $config;
		@include_once(ROOT_DIR .'config.php');
		if (!isset($config['gallery'])) {
			return;
		}
		$this->gallery = $config['gallery'];
	}

	/**
	* Examines the URL address.
	*/
	public function request_url(&$url) {
		if (!isset($this->gallery)) {
			return;
		}
		$this->url = $url;
		$this->url = ($this->url == '') ? 'index' : $this->url;
		$this->request = array();
		foreach (array_keys($this->gallery) as $gallery_name) {
			if ($this->gallery[$gallery_name]['page'] == $this->url) {
				$this->request[] = $gallery_name;
			}
			$url_strip = str_replace($this->gallery[$gallery_name]['page'].'/', '', $this->url);
			$url_part = explode('/', $url_strip);
			if ($url_part[0] == $gallery_name) {
				if (isset($url_part[1]) && $url_part[1] == GALLERY_FLUSH_URL && isset($url_part[2]) && $url_part[2] == $this->gallery[$gallery_name]['flush']) {
					$this->flush($gallery_name);
					$url = '';
				} elseif (isset($url_part[1]) && is_file(GALLERY_DIR.$this->gallery[$gallery_name]['image_path'].'/'.$url_part[1])) {
					$this->current = $gallery_name;
					$this->image = GALLERY_DIR.$this->gallery[$gallery_name]['image_path'].'/'.$url_part[1];
					unset($url_part[count($url_part)-1]);
					$this->file = CONTENT_DIR.$this->gallery[$gallery_name]['page'].CONTENT_EXT;
				}
			}
		}
	}
	
	/**
	* If asked for a picture or a flush request, redirect from error 404 page to your gallery page.
	*/
	public function before_load_content(&$file) {
		if ($this->file) {
			$file = $this->file;
		}
	}

	/**
	* Save the real config variable.
	*/
	public function config_loaded(&$settings) {
		$this->config = $settings;
	}

	/**
	* Checking whether your gallery_id exists in your markdown and replaces in such a case.
	*/
	public function after_render(&$output) {
		if ($this->request) {
			// Get the gallery.
			foreach ($this->request as $gallery_name) {
				if (preg_match('/%'.$gallery_name.'%/mi', $output, $match) && $match[0]) {
					$output = str_replace('%'.$gallery_name.'%', $this->markup($gallery_name), $output);
				}
			}
		} elseif ($this->image && $this->current) {
			// Get the image.
			foreach (array_keys($this->gallery) as $gallery_name) {
				$replace = $gallery_name == $this->current ? $this->markup($this->current) : '';
				$output = str_replace('%'.$gallery_name.'%', $replace, $output);
			}
		}
	}

	/**
	* Deletes thumbnails and generate new ones.
	*/
	protected function flush($name) {
		$image_path = GALLERY_DIR.$this->gallery[$name]['image_path'];
		$thumb_path = GALLERY_DIR.$this->gallery[$name]['thumb_path'];
		if (!is_dir($image_path)) {
			return;
		}
		$files = glob($thumb_path.DIRECTORY_SEPARATOR.'*');
		foreach($files as $file) {
			// Delete all files in the thumbnail folder.
			if (is_file($file)) {
				unlink($file);
			}
		}
		foreach(glob($image_path.'/{*.jpg,*.png,*.gif}', GLOB_BRACE) as $image) {
			// Generate new thumbnails.
			$size = isset($this->gallery[$name]['thumb_size']) ? $this->gallery[$name]['thumb_size'] : array(200, 200);
			$file_name = basename($image);
			$image = Image::make($image)
				->grab($size[0], $size[1])
				->save($thumb_path.DIRECTORY_SEPARATOR.GALLERY_PRE_THUMB_NAME.$file_name, 80);
		}
	}

	/**
	* Generates HTML markup for a gallery or a picture.
	*/
	protected function markup($name) {
		$return = '';
		$image_path = GALLERY_DIR.$this->gallery[$name]['image_path'];
		$thumb_path = GALLERY_DIR.$this->gallery[$name]['thumb_path'];
		if ($this->image) {
			// Markup for a image
			$before_image = (isset($this->gallery[$name]['before_image'])) ? $this->gallery[$name]['before_image'] : "\t\t\t\t".'<div class="thumbnail">'."\n";
			$after_image = (isset($this->gallery[$name]['after_image'])) ? $this->gallery[$name]['after_image'] : "\t\t\t\t".'</div>'."\n";
			$image_class = (isset($this->gallery[$name]['image_class'])) ? $this->gallery[$name]['image_class'] : "\t\t\t\t".''."\n";
			$alt_image = (isset($this->gallery[$name]['alt_image'])) ? $this->gallery[$name]['alt_image'] : pathinfo($this->image, PATHINFO_FILENAME);

			$return .= $before_image;
			$return .= "\t\t\t\t".'<img src="'.$this->config['base_url'].'/'.$this->image.'" alt="'.$alt_image.'" class="'.$image_class.'">'."\n";
			$return .= $after_image;
			return $return;
		} else {
			// Markup for a gallery.
			$before_gallery = (isset($this->gallery[$name]['before_gallery'])) ? $this->gallery[$name]['before_gallery'] : "\t\t\t\t".'<div class="row">'."\n";
			$after_gallery = (isset($this->gallery[$name]['after_gallery'])) ? $this->gallery[$name]['after_gallery'] : "\t\t\t\t".'</div>'."\n";
			$before_thumbnail = (isset($this->gallery[$name]['before_thumbnail'])) ? $this->gallery[$name]['before_thumbnail'] : "\t\t\t\t\t".'<div class="col-sm-2 col-lg-2 thumb">'."\n";
			$after_thumbnail = (isset($this->gallery[$name]['after_thumbnail'])) ? $this->gallery[$name]['after_thumbnail'] : "\t\t\t\t\t".'</div>'."\n";
			$thumbnail_link_class = (isset($this->gallery[$name]['thumbnail_link_class'])) ? $this->gallery[$name]['thumbnail_link_class'] : "thumbnail";
			$thumbnail_image_class = (isset($this->gallery[$name]['thumbnail_image_class'])) ? $this->gallery[$name]['thumbnail_image_class'] : "img-responsive";
			
			$return .= $before_gallery;
			$gallery = glob($image_path.'/{*.jpg,*.png,*.gif}', GLOB_BRACE);
			if (isset($this->gallery[$name]['sort_by']) && $this->gallery[$name]['sort_by'] == 'random') {
				shuffle($gallery);
			}
			if (isset($this->gallery[$name]['order_by']) && $this->gallery[$name]['order_by'] == 'reverse') {
				array_reverse($gallery);
			}
			foreach($gallery as $image) {
				$alt_image = (isset($this->gallery[$name]['alt_image'])) ? $this->gallery[$name]['alt_image'] : pathinfo($image, PATHINFO_FILENAME);
				if (isset($this->gallery[$name]['exclude']) && in_array(substr($image, strrpos($image, '/')+1), $this->gallery[$name]['exclude'])) {
					// Skip files in the exclude array.
					continue;
				}
				$file_name = basename($image);
				$return .= $before_thumbnail;
				$return .= "\t\t\t\t\t\t".'<a href="'.$this->config['base_url'].'/'.$this->url.'/'.$name.'/'.$file_name.'" class="'.$thumbnail_link_class.'">'."\n";
				$return .= "\t\t\t\t\t\t\t".'<img src="'.$this->config['base_url'].'/'.$thumb_path.'/'.GALLERY_PRE_THUMB_NAME.$file_name.'" alt="'.$alt_image.'" class="'.$thumbnail_image_class.'">'."\n";
				$return .= "\t\t\t\t\t\t".'</a>'."\n";
				$return .= $after_thumbnail;
			}
			$return .= $after_gallery;
			return $return;
		}
	}
}