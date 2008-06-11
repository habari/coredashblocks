<?php

/**
 * CoreDashModules - Provides a core set of dashboard modules for the dashboard.
 */

class CoreDashModules extends Plugin
{
	private $theme;

	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Core Dash Modules',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Provides a core set of dashboard modules for the dashboard.',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * action_plugin_activation
	 * Registers the core modules with the Modules class. Add these modules to the
	 * dashboard if the dashboard is currently empty.
	 * @param string $file plugin file
	 */
	function filter_dash_modules( $modules )
	{
		array_push( $modules, 'Latest Entries', 'Latest Comments', 'Latest Log Activity' );
		return $modules;
	}

	/**
	 * get_theme
	 * Creates a theme object if it does not already exist
	 * @return object A theme object
	 */
	private function get_theme()
	{
		if ( ! isset( $this->theme ) ) {
			$this->theme = Themes::create( 'coredashmodules', 'RawPHPEngine', dirname( __FILE__ ) . '/' );
		}
		return $this->theme;
	}
	
	/**
	 * filter_dash_module_latest_log_activity
	 * Sets theme variables and handles logic for the
	 * dashboard's log history module.
	 * @param string $module_id
	 * @return string The contents of the module
	 */
	public function filter_dash_module_latest_log_activity( $module, $module_id )
	{
		
		$theme = $this->get_theme();

		if ( FALSE === ( $num_logs = Modules::get_option( $module_id, 'logs_number_display' ) ) ) {
			$num_logs = 8;
		}

		$params = array(
			'where' => array(
				'user_id' => User::identify()->id
			),
			'orderby' => 'id DESC', /* Otherwise, exactly same timestamp values muck it up... Plus, this is more efficient to sort on the primary key... */
			'limit' => $num_logs,
		);
		$theme->logs = EventLog::get( $params );
		
		// Create options form
		$form = new FormUI( 'dash_logs' );
		$form->append( 'text', 'logs_number_display', 'option:' . Modules::storage_name( $module_id, 'logs_number_display' ), _t('Number of items') );
		$form->append( 'submit', 'submit', _t('Submit') );
		$form->properties['onsubmit'] = "dashboard.updateModule({$module_id}); return false;";
		
		$module['title'] = '<a href="' . Site::get_url('admin') . '/logs">' . _t('Latest Log Activity') . '</a>';
		$module['options'] = $form->get();
		$module['content'] = $theme->fetch( 'dash_logs' );
		return $module;
	}
	
	/**
	 * filter_dash_module_latest_entries
	 * Gets the latest entries module
	 * @param string $module_id
	 * @return string The contents of the module
	 */
	public function filter_dash_module_latest_entries( $module, $module_id )
	{
		$theme = $this->get_theme();

		$theme->recent_posts= Posts::get( array( 'status' => 'published', 'limit' => 8, 'type' => Post::type('entry') ) );
		
		$module['title'] = '<a href="' . Site::get_url('admin') . '/posts?type=1">' . _t('Latest Entries') . '</a>';
		$module['content'] = $theme->fetch( 'dash_latestentries' );
		return $module;
	}

	/**
	 * filter_dash_module_latest_comments
	 * Function used to set theme variables to the latest comments dashboard widget
	 * @param string $module_id
	 * @return string The contents of the module
	 */
	public function filter_dash_module_latest_comments( $module, $module_id )
	{
		$theme = $this->get_theme();

		$post_ids = DB::get_results( 'SELECT DISTINCT post_id FROM ( SELECT date, post_id FROM {comments} WHERE status = ? AND type = ? ORDER BY date DESC, post_id ) AS post_ids LIMIT 5', array( Comment::STATUS_APPROVED, Comment::COMMENT ), 'Post' );
		$posts = array();
		$latestcomments = array();

		foreach( $post_ids as $comment_post ) {
			$post = DB::get_row( 'select * from {posts} where id = ?', array( $comment_post->post_id ) , 'Post' );
			$comments = DB::get_results( 'SELECT * FROM {comments} WHERE post_id = ? AND status = ? AND type = ? ORDER BY date DESC LIMIT 5;', array( $comment_post->post_id, Comment::STATUS_APPROVED, Comment::COMMENT ), 'Comment' );
			$posts[] = $post;
			$latestcomments[$post->id] = $comments;
		}

		$theme->latestcomments_posts = $posts;
		$theme->latestcomments = $latestcomments;
		
		$module['title'] = '<a href="' . Site::get_url('admin') . '/comments">' . _t('Latest Comments') . '</a>';
		$module['content'] = $theme->fetch( 'dash_latestcomments' );
		return $module;
	}
}

?>
