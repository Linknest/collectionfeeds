<?php
/*
Plugin Name: Linknest Collection Feeds
Description: A plugin giving the collection on Linknest custom RSS feeds. Making it easier to follow them.
Plugin URI: https://linknest.cc
Author: Urban Sandén
Author URI: https://urre.me
*/

// Add dates for rss feed
function collectionfeeds_rss_date( $timestamp = null ) {
	$timestamp = ($timestamp==null) ? time() : $timestamp;
	echo date(DATE_RSS, $timestamp);
}

// Add endpoint and specify EP mask
function collectionfeeds_add_endpoint() {
	add_rewrite_endpoint( 'rssfeed', EP_PERMALINK | EP_PAGES );
}

add_action( 'init', 'collectionfeeds_add_endpoint' );

// Check if /rssfeed is used
function collectionfeeds_template_redirect() {
	global $wp_query;
	if ( ! isset( $wp_query->query_vars['rssfeed'] )  )
		return;
	collectionfeeds_output_feed();
	exit;
}

add_action( 'template_redirect', 'collectionfeeds_template_redirect' );

// Output rss feed
function collectionfeeds_output_feed() {

	// Get collection object
	$post = get_queried_object();

	// Get links connected to this collection
	$posts = query_posts(array(
		'post_type' => 'link',
		'posts_per_page' => -1,
		'meta_key' => 'listid',
		'meta_value' => $post->ID
	) );


	header("Content-Type: application/rss+xml; charset=UTF-8");
	echo '<?xml version="1.0"?>';
	?>
	<rss version="2.0">
		<channel>
			<title><?php the_title(); ?> on Linknest</title>
			<link>https://linknest.cc/</link>
			<description>A collection by <?php echo get_the_title($post->post_parent); ?></description>
			<language>en-us</language>
			<?php foreach ($posts as $post) :

			$url = get_post_meta( $post->ID, 'url' );
			$screenshoturl = get_post_meta( $post->ID, 'screenshoturl' );

			?>
			<item>
				<title><?php echo html_entity_decode(get_the_title($post->ID)); ?></title>
				<link><?php echo get_permalink($post->ID); ?></link>
				<description>
					<?php echo '<![CDATA[<img src="'.$screenshoturl ? $screenshoturl : ''.'" height="600" width="800">]]>'; ?>
					<?php echo '<![CDATA[<a href="'.$url[0].'">'.$url[0].'</a>]]>'; ?>
				</description>
				<pubDate><?php collectionfeeds_rss_date( strtotime($post->post_date_gmt) ); ?></pubDate>
				<guid><?php echo get_permalink($post->ID); ?></guid>
			</item>
		<?php endforeach; ?>
	</channel>
</rss>
<?php
}

function collectionfeeds_activate() {
	collectionfeeds_add_endpoint();
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'collectionfeeds_activate' );

function collectionfeeds_deactivate() {
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'collectionfeeds_deactivate' );
