<?php

use Intervention\Image\ImageManager;

/**
 * A Gallery plugin for the micro Pico CMS.
 *
 *
 * @author  Klas Gidlöv
 * @link    http://gidlov.com/en/code/pico-gallery
 * @license http://www.gnu.org/licenses/lgpl-3.0-standalone.html
 * @version 1.2
 */
final class Gallery extends AbstractPicoPlugin
{

    private $requested_url;
    private $pico_config;
    private $gallery_config;
    private $single_image;
    private $requested_gallery;
    private $current_gallery;
    private $file;

    private $root_dir;
    private $gallery_dir;
    private $content_dir;
    private $config_dir;
    private $themes_dir;

    private $pre_thumb;

    /**
     * This plugin is enabled by default?
     *
     * @see AbstractPicoPlugin::$enabled
     * @var boolean
     */
    protected $enabled = true;

    /**
     * This plugin depends on ...
     *
     * @see AbstractPicoPlugin::$dependsOn
     * @var string[]
     */
    protected $dependsOn = array();

    /**
     * Triggered after Pico has loaded all available plugins
     *
     * This event is triggered nevertheless the plugin is enabled or not.
     * It is NOT guaranteed that plugin dependencies are fulfilled!
     *
     * @see    Pico::getPlugin()
     * @see    Pico::getPlugins()
     * @param  object[] &$plugins loaded plugin instances
     * @return void
     */
    public function onPluginsLoaded(array &$plugins)
    {
        $this->root_dir = $this->__call('getRootDir', array());
        $this->pico_config_dir = $this->__call('getConfigDir', array());
        $this->themes_dir = $this->__call('getThemesDir', array());
    }

    /**
     * Triggered after Pico has read its configuration
     *
     * @see    Pico::getConfig()
     * @param  array &$config array of config variables
     * @return void
     */
    public function onConfigLoaded(array &$config)
    {
      $this->pico_config = $config;
      if (isset($config['gallery'])) {
        $this->gallery_config = $config['gallery'];
  		}
      if (null !== ($this->__call('getConfig', array('content_dir')))) {
        $this->content_dir = $this->__call('getConfig', array('content_dir'));
      }
      $this->gallery_dir = str_replace($this->root_dir, '', $this->themes_dir);
    }

    /**
     * Triggered after Pico has evaluated the request URL
     *
     * @see    Pico::getRequestUrl()
     * @param  string &$url part of the URL describing the requested contents
     * @return void
     */
    public function onRequestUrl(&$url)
    {
      if (!isset($this->gallery_config)) {
        return;
      }
      $this->requested_url = $url;
      $this->requested_url = ($this->requested_url == '') ? 'index' : $this->requested_url;
      $this->requested_gallery = array();
      foreach (array_keys($this->gallery_config) as $gallery_name) {
				echo $this->gallery_dir.$this->gallery_config[$gallery_name]['image_path'].'/'.$url_part[1];
        if ($this->gallery_config[$gallery_name]['page'] == $this->requested_url) {
          $this->requested_gallery[] = $gallery_name;
        }
        $url_strip = str_replace($this->gallery_config[$gallery_name]['page'].'/', '', $this->requested_url);
        $url_part = explode('/', $url_strip);
        if ($url_part[0] == $gallery_name) {
          if (isset($url_part[1]) && $url_part[1] == 'flush' && isset($url_part[2]) && $url_part[2] == $this->gallery_config[$gallery_name]['flush']) {
            $this->flush($gallery_name);
            $url = '';
          } elseif (isset($url_part[1]) && is_file($this->gallery_dir.$this->gallery_config[$gallery_name]['image_path'].'/'.$url_part[1])) {
            $this->current_gallery = $gallery_name;
            $this->single_image = $this->gallery_dir.$this->gallery_config[$gallery_name]['image_path'].'/'.$url_part[1];
            unset($url_part[count($url_part)-1]);
            $this->file = $this->content_dir.$this->gallery_config[$gallery_name]['page'].$this->pico_config['content_ext'];
          }
        }
      }
    }

    /**
     * Triggered after Pico has discovered the content file to serve
     *
     * @see    Pico::getBaseUrl()
     * @see    Pico::getRequestFile()
     * @param  string &$file absolute path to the content file to serve
     * @return void
     */
    public function onRequestFile(&$file)
    {
        // your code
    }

    /**
     * Triggered before Pico reads the contents of the file to serve
     *
     * @see    Pico::loadFileContent()
     * @see    DummyPlugin::onContentLoaded()
     * @param  string &$file path to the file which contents will be read
     * @return void
     */
    public function onContentLoading(&$file)
    {
      if ($this->file) {
        $file = $this->file;
      }
    }

    /**
     * Triggered after Pico has read the contents of the file to serve
     *
     * @see    Pico::getRawContent()
     * @param  string &$rawContent raw file contents
     * @return void
     */
    public function onContentLoaded(&$rawContent)
    {
        // your code
    }

    /**
     * Triggered before Pico reads the contents of a 404 file
     *
     * @see    Pico::load404Content()
     * @see    DummyPlugin::on404ContentLoaded()
     * @param  string &$file path to the file which contents were requested
     * @return void
     */
    public function on404ContentLoading(&$file)
    {
        // your code
    }

    /**
     * Triggered after Pico has read the contents of the 404 file
     *
     * @see    Pico::getRawContent()
     * @param  string &$rawContent raw file contents
     * @return void
     */
    public function on404ContentLoaded(&$rawContent)
    {
        // your code
    }

    /**
     * Triggered when Pico reads its known meta header fields
     *
     * @see    Pico::getMetaHeaders()
     * @param  string[] &$headers list of known meta header
     *     fields; the array value specifies the YAML key to search for, the
     *     array key is later used to access the found value
     * @return void
     */
    public function onMetaHeaders(array &$headers)
    {
        // your code
    }

    /**
     * Triggered before Pico parses the meta header
     *
     * @see    Pico::parseFileMeta()
     * @see    DummyPlugin::onMetaParsed()
     * @param  string   &$rawContent raw file contents
     * @param  string[] &$headers    known meta header fields
     * @return void
     */
    public function onMetaParsing(&$rawContent, array &$headers)
    {
        // your code
    }

    /**
     * Triggered after Pico has parsed the meta header
     *
     * @see    Pico::getFileMeta()
     * @param  string[] &$meta parsed meta data
     * @return void
     */
    public function onMetaParsed(array &$meta)
    {
        // your code
    }

    /**
     * Triggered before Pico parses the pages content
     *
     * @see    Pico::prepareFileContent()
     * @see    DummyPlugin::prepareFileContent()
     * @see    DummyPlugin::onContentParsed()
     * @param  string &$rawContent raw file contents
     * @return void
     */
    public function onContentParsing(&$rawContent)
    {
        // your code
    }

    /**
     * Triggered after Pico has prepared the raw file contents for parsing
     *
     * @see    Pico::parseFileContent()
     * @see    DummyPlugin::onContentParsed()
     * @param  string &$content prepared file contents for parsing
     * @return void
     */
    public function onContentPrepared(&$content)
    {
        // your code
    }

    /**
     * Triggered after Pico has parsed the contents of the file to serve
     *
     * @see    Pico::getFileContent()
     * @param  string &$content parsed contents
     * @return void
     */
    public function onContentParsed(&$content)
    {
        // your code
    }

    /**
     * Triggered before Pico reads all known pages
     *
     * @see    Pico::readPages()
     * @see    DummyPlugin::onSinglePageLoaded()
     * @see    DummyPlugin::onPagesLoaded()
     * @return void
     */
    public function onPagesLoading()
    {
        // your code
    }

    /**
     * Triggered when Pico reads a single page from the list of all known pages
     *
     * The `$pageData` parameter consists of the following values:
     *
     * | Array key      | Type   | Description                              |
     * | -------------- | ------ | ---------------------------------------- |
     * | id             | string | relative path to the content file        |
     * | url            | string | URL to the page                          |
     * | title          | string | title of the page (YAML header)          |
     * | description    | string | description of the page (YAML header)    |
     * | author         | string | author of the page (YAML header)         |
     * | time           | string | timestamp derived from the Date header   |
     * | date           | string | date of the page (YAML header)           |
     * | date_formatted | string | formatted date of the page               |
     * | raw_content    | string | raw, not yet parsed contents of the page |
     * | meta           | string | parsed meta data of the page             |
     *
     * @see    DummyPlugin::onPagesLoaded()
     * @param  array &$pageData data of the loaded page
     * @return void
     */
    public function onSinglePageLoaded(array &$pageData)
    {
        // your code
    }

    /**
     * Triggered after Pico has read all known pages
     *
     * See {@link DummyPlugin::onSinglePageLoaded()} for details about the
     * structure of the page data.
     *
     * @see    Pico::getPages()
     * @see    Pico::getCurrentPage()
     * @see    Pico::getPreviousPage()
     * @see    Pico::getNextPage()
     * @param  array[]    &$pages        data of all known pages
     * @param  array|null &$currentPage  data of the page being served
     * @param  array|null &$previousPage data of the previous page
     * @param  array|null &$nextPage     data of the next page
     * @return void
     */
    public function onPagesLoaded(
        array &$pages,
        array &$currentPage = null,
        array &$previousPage = null,
        array &$nextPage = null
    ) {
        // your code
    }

    /**
     * Triggered before Pico registers the twig template engine
     *
     * @return void
     */
    public function onTwigRegistration()
    {
        // your code
    }

    /**
     * Triggered before Pico renders the page
     *
     * @see    Pico::getTwig()
     * @see    DummyPlugin::onPageRendered()
     * @param  Twig_Environment &$twig          twig template engine
     * @param  array            &$twigVariables template variables
     * @param  string           &$templateName  file name of the template
     * @return void
     */
    public function onPageRendering(Twig_Environment &$twig, array &$twigVariables, &$templateName)
    {
        // your code
    }

    /**
     * Triggered after Pico has rendered the page
     *
     * @param  string &$output contents which will be sent to the user
     * @return void
     */
    public function onPageRendered(&$output)
    {
      if ($this->requested_gallery) {
        // Get the gallery.
        foreach ($this->requested_gallery as $gallery_name) {
          if (preg_match('/%'.$gallery_name.'%/mi', $output, $match) && $match[0]) {
            $output = str_replace('%'.$gallery_name.'%', $this->markup($gallery_name), $output);
          }
        }
      } elseif ($this->single_image && $this->current_gallery) {
        // Get the image.
        foreach (array_keys($this->gallery_config) as $gallery_name) {
          $replace = $gallery_name == $this->current_gallery ? $this->markup($this->current_gallery) : '';
          $output = str_replace('%'.$gallery_name.'%', $replace, $output);
        }
      }
    }

    static protected function getDir(string $name)
    {
      return $name;
      $names = array('root', 'config', 'gallery', 'themes', 'config');
    }

    protected function flush($name)
    {
  		$image_path = $this->gallery_dir.$this->gallery_config[$name]['image_path'];
  		$thumb_path = $this->gallery_dir.$this->gallery_config[$name]['thumb_path'];
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
  			$size = isset($this->gallery_config[$name]['thumb_size']) ? $this->gallery_config[$name]['thumb_size'] : array(200, 200);
  			$file_name = basename($image);
  			$img = new ImageManager();
  			$img->make($image)
  				->fit($size[0], $size[1])
  				->save($thumb_path.DIRECTORY_SEPARATOR.'thumb_'.$file_name, 80);
  		}
      header('Location: ' . $this->pico_config['base_url'].$this->gallery_config[$name]['page']);
  	}

    protected function markup($name)
    {
  		$return = '';
  		$image_path = $this->gallery_dir.$this->gallery_config[$name]['image_path'];
  		$thumb_path = $this->gallery_dir.$this->gallery_config[$name]['thumb_path'];
  		if ($this->single_image) {
  			// Markup for a image
  			$before_image = (isset($this->gallery_config[$name]['before_image'])) ? $this->gallery_config[$name]['before_image'] : "\t\t\t\t".'<div class="thumbnail">'."\n";
  			$after_image = (isset($this->gallery_config[$name]['after_image'])) ? $this->gallery_config[$name]['after_image'] : "\t\t\t\t".'</div>'."\n";
  			$image_class = (isset($this->gallery_config[$name]['image_class'])) ? $this->gallery_config[$name]['image_class'] : "\t\t\t\t".''."\n";
  			$alt_image = (isset($this->gallery_config[$name]['alt_image'])) ? $this->gallery_config[$name]['alt_image'] : pathinfo($this->single_image, PATHINFO_FILENAME);

  			$return .= $before_image;
  			$return .= "\t\t\t\t".'<img src="'.$this->pico_config['base_url'].'/'.$this->single_image.'" alt="'.$alt_image.'" class="'.$image_class.'">'."\n";
  			$return .= $after_image;
  			return $return;
  		} else {
  			// Markup for a gallery.
  			$before_gallery = (isset($this->gallery_config[$name]['before_gallery'])) ? $this->gallery_config[$name]['before_gallery'] : "\t\t\t\t".'<div class="row">'."\n";
  			$after_gallery = (isset($this->gallery_config[$name]['after_gallery'])) ? $this->gallery_config[$name]['after_gallery'] : "\t\t\t\t".'</div>'."\n";
  			$before_thumbnail = (isset($this->gallery_config[$name]['before_thumbnail'])) ? $this->gallery_config[$name]['before_thumbnail'] : "\t\t\t\t\t".'<div class="col-sm-2 col-lg-2 thumb">'."\n";
  			$after_thumbnail = (isset($this->gallery_config[$name]['after_thumbnail'])) ? $this->gallery_config[$name]['after_thumbnail'] : "\t\t\t\t\t".'</div>'."\n";
  			$thumbnail_link_class = (isset($this->gallery_config[$name]['thumbnail_link_class'])) ? $this->gallery_config[$name]['thumbnail_link_class'] : "thumbnail";
  			$thumbnail_image_class = (isset($this->gallery_config[$name]['thumbnail_image_class'])) ? $this->gallery_config[$name]['thumbnail_image_class'] : "img-responsive";

  			$return .= $before_gallery;
  			$gallery = glob($image_path.'/{*.jpg,*.png,*.gif}', GLOB_BRACE);
  			if (isset($this->gallery_config[$name]['sort_by']) && $this->gallery_config[$name]['sort_by'] == 'random') {
  				shuffle($gallery);
  			}
  			if (isset($this->gallery_config[$name]['order_by']) && $this->gallery_config[$name]['order_by'] == 'reverse') {
  				array_reverse($gallery);
  			}
  			foreach($gallery as $image) {
  				$alt_image = (isset($this->gallery_config[$name]['alt_image'])) ? $this->gallery_config[$name]['alt_image'] : pathinfo($image, PATHINFO_FILENAME);
  				if (isset($this->gallery_config[$name]['exclude']) && in_array(substr($image, strrpos($image, '/')+1), $this->gallery_config[$name]['exclude'])) {
  					// Skip files in the exclude array.
  					continue;
  				}
  				$file_name = basename($image);
  				$return .= $before_thumbnail;
  				$return .= "\t\t\t\t\t\t".'<a href="'.$this->pico_config['base_url'].''.$this->requested_url.'/'.$name.'/'.$file_name.'" class="'.$thumbnail_link_class.'">'."\n";
  				$return .= "\t\t\t\t\t\t\t".'<img src="'.$this->pico_config['base_url'].''.$thumb_path.'/'.'thumb_'.$file_name.'" alt="'.$alt_image.'" class="'.$thumbnail_image_class.'">'."\n";
  				$return .= "\t\t\t\t\t\t".'</a>'."\n";
  				$return .= $after_thumbnail;
  			}
  			$return .= $after_gallery;
  			return $return;
  		}
  	}
}
