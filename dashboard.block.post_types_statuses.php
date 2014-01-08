<?php
namespace Habari;
if ( !defined( 'HABARI_PATH' ) ) { die( 'No direct access' ); }
?>
	<ul class="items">
		<li class="item">
			<span class="title"><?php _e( 'Category' ); ?></span>
			<span span class="user"><?php _e( 'Yours' ); ?></span>
			<span class="site"><?php _e( 'Site' ) ?></span>
		</li>
		<?php foreach( $content->messages as $message ) : ?>
		<li class="item">
			<span class="title"><?php echo $message['label']; ?></span>
			<span class="user"><?php echo $message['user_count'] ?></span>
			<span class="site"><?php echo $message['site_count']; ?></span>
		</li>
		<?php endforeach; ?>
	</ul>
