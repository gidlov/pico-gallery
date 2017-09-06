A Gallery plugin for the micro CMS Pico
=====================
You may find more info on [gidlov.com/code/gallery](http://gidlov.com/code/gallery)

## About

You can have as many galleries you want, with customizable markup. You can view all on the same page or different pages, and you can choose where on the page. Not so sophisticated, but it gets the job done.

## Example
[Running example](http://gidlov.com/gallery)

### Setup
As we make use of the excellent [Image](http://intervention.olivervogel.net/) class we have to update the `require` key of `composer.json` and add the following:

	"intervention/image": "dev-master"

Run the `composer update` comand.

Create two folders, one for your pre-scaled large images and one for the thumbnails. For example:

	content/gallery/fullsize
	content/gallery/thumbnails
	...
	content/index.md

For Gallery >1.2, keep in mind that content-dir is by default protected by `.htaccess`.

Copy your photos to the fullsize-folder.

Add a `gallery` array to your `config` variable and modify according to your needs. This is a Gallery >1.2 example:

	$config['gallery'] = array(
		'gallery_one' => array(					// Add %gallery_one% some place in your page.
			'page' => 'index',				// The page you want to view the gallery.
			'image_path' => 'media/gallery_two/images',	// Original Images.
			'thumb_path' => 'media/gallery_two/thumbs',	// Must be a empty separate folder.
			'flush' => '44571b842274ac45',			// A unique string to delete and regenerate thumbnails.
									// Visit http://SITE.COM/index/gallery_one/flush/47b71a856274ac45

			// These are possible, but not mandatory...
			'thumb_size' => array(200, 200),
			'before_gallery' => '',
			'after_gallery' => '',
			'before_thumbnail' => '',
			'after_thumbnail' => '',
			'before_image' => false,
			'after_image' => '',
			'thumbnail_link_class' => '',
			'thumbnail_image_class' => '',
			'image_class' => '',
			'alt_image' => '',
			'exclude' => array(),				// Exclude file name(s).
			'sort_by' => '',	 			// Sort by empty or 'random'.
			'order_by' => '', 				// Order by ampty or 'reverse'.
		),
	);

A pre-1.2 config example:

	$config['gallery'] = array(
		'my_gallery' => array(					// Add %my_gallery% some place in your page.
			'page' => 'index',				// The page you want to view the gallery.
			'image_path' => 'gallery/fullsize',		// Original Images.
			'thumb_path' => 'gallery/thumbnails',		// Must be a empty separate folder.
			'flush' => '47b71a856274ac45',			// A unique string to delete and regenerate thumbnails.

			// These are possible, but not mandatory...
			'thumb_size' => array(200, 200),
			'before_gallery' => '',
			'after_gallery' => '',
			'before_thumbnail' => '',
			'after_thumbnail' => '',
			'thumbnail_link_class' => '',
			'thumbnail_image_class' => '',
			'before_image' => '',
			'after_image' => '',
			'image_class' => '',
			'alt_image' => '',
			'exclude' => array(),				// Exclude file name(s).
			'sort_by' => '',	 			// Sort by empty or 'random'.
			'order_by' => '', 				// Order by ampty or 'reverse'.
		),
	);

Save your `config.php` and enter the URL `http://example.com/index/my_gallery/flush/47b71a856274ac45` to generate the thumbnails.

Add `%gallery_one%` or `%my_gallery%` to your `index.md` file, or wherever you want.

`gallery/thumbnails`-folder must have write permission.

Visit http://example.com/index to see your new gallery.

#### Done!

## Requirements

 - PHP 5.3

## License

Gallery is released under [LGPL](http://www.gnu.org/licenses/lgpl-3.0-standalone.html).
