
<?php
// in views/page.php
// exit if WordPress isn't loaded
!defined('ABSPATH') && exit;
?>
<div class="wrap">
	<h1>Alt Text Updater</h1>
	<?php 
	
	// 1. List items from media library

	$query_images_args = array(
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'post_status'    => 'inherit',
		'posts_per_page' => - 1,
	);
	
	$query_images = new WP_Query( $query_images_args );
	
	$images = array();
	foreach ( $query_images->posts as $image ) {
		$images[] = wp_get_attachment_url( $image->ID );

		echo '<div><img src=" ' . wp_get_attachment_image_src($image->ID)[0] . '" width="150" /></div>';
	}

	// 2. Create a field to add new all text

	// 3. Submit button that queries all instances of that media item on the site and replaces the alt text with the next alt text
	
	?>
</div>
