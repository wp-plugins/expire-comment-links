<?php
/**
 * Plugin Name: Expire Comment Links
 * Plugin URI: http://xavisys.com/2009/07/expire-comment-links/
 * Description: Allows you to stop displaying links for old comments. PHP 5+ required.
 * Version: 0.1.1
 * Author: Aaron D. Campbell
 * Author URI: http://xavisys.com/
 */

/**
 * expireCommentLinks is the class that handles ALL of the plugin functionality.
 * It helps us avoid name collisions
 * http://codex.wordpress.org/Writing_a_Plugin#Avoiding_Function_Name_Collisions
 */
class expireCommentLinks {
	/**
	 * @var array Plugin settings
	 */
	private $_settings;

	/**
	 * Repository base url
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $_reposUrl = 'http://plugins.svn.wordpress.org/';

	public function __construct() {
		/**
		 * Add update messages that can be attached to the CURRENT release (not
		 * this one), but only for 2.8+
		 */
		global $wp_version;
		if ( version_compare('2.8', $wp_version, '<=') ) {
			add_action ( 'in_plugin_update_message-'.plugin_basename ( __FILE__ ) , array ( $this , '_changelog' ), null, 2 );
		}
	}

	public function commentAuthorLinkFilter ($url) {
		global $comment;
		return ( $this->_isOldComment($comment) )? '':$url;
	}

	public function commentAuthorFilter ($author) {
		global $comment;
		if ( $this->_isOldComment($comment) ) {
			$author .= " - {$comment->comment_author_url}";
		}
		return $author;
	}

	private function _isOldComment($comment) {
		$this->_getSettings();
		$commentDate = strtotime($comment->comment_date_gmt);
		$oldDate = strtotime("-{$this->_settings['expire_period']} {$this->_settings['expire_period_unit']}", current_time('timestamp', true));
		return ( $commentDate < $oldDate );
	}

	public function admin_menu() {
		add_options_page(__('Expire Comment Links', 'expire_comment_links'), __('Expire Comment Links', 'expire_comment_links'), 'manage_options', 'ExpireCommentLinks', array($this, 'options'));
	}

	public function registerOptions() {
		register_setting( 'ecl-options', 'ecl' );
	}

	public function init_locale(){
		$lang_dir = basename(dirname(__FILE__)) . '/languages';
		load_plugin_textdomain('expire_comment_links', 'wp-content/plugins/' . $lang_dir, $lang_dir);
	}

	public function addSettingLink( $links, $file ){
		if ( empty($this->_pluginBasename) ) {
			$this->_pluginBasename = plugin_basename(__FILE__);
		}

		if ( $file == $this->_pluginBasename ) {
			// Add settings link to our plugin
			$link = '<a href="options-general.php?page=ExpireCommentLinks">' . __('Settings', 'expire_comment_links') . '</a>';
			array_unshift( $links, $link );
		}
		return $links;
	}

	/**
	 * This is used to display the options page for this plugin
	 */
	public function options() {
		//Get our options
		$this->_getSettings();
?>
		<div class="wrap">
			<h2><?php _e('Expire Comment Links', 'expire_comment_links') ?></h2>
			<h3><?php _e('General Settings', 'expire_comment_links') ?></h3>
			<form action="options.php" method="post">
				<?php settings_fields( 'ecl-options' ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="ecl_expire_period"><?php _e('Remove links from comments older than:', 'expire_comment_links'); ?></label>
						</th>
						<td>
							<input id="ecl_expire_period" name="ecl[expire_period]" type="text" class="small-text" value="<?php echo attribute_escape($this->_settings['expire_period']); ?>" />
							<select id="ecl_expire_period_unit" name="ecl[expire_period_unit]">
								<option value="seconds" <?php echo selected($this->_settings['expire_period_unit'], 'seconds'); ?>><?php _e('Seconds', 'expire_comment_links');?></a>
								<option value="minutes" <?php echo selected($this->_settings['expire_period_unit'], 'minutes'); ?>><?php _e('Minutes', 'expire_comment_links');?></a>
								<option value="hours" <?php echo selected($this->_settings['expire_period_unit'], 'hours'); ?>><?php _e('Hours', 'expire_comment_links');?></a>
								<option value="days" <?php echo selected($this->_settings['expire_period_unit'], 'days'); ?>><?php _e('Days', 'expire_comment_links');?></a>
								<option value="weeks" <?php echo selected($this->_settings['expire_period_unit'], 'weeks'); ?>><?php _e('Weeks', 'expire_comment_links');?></a>
								<option value="months" <?php echo selected($this->_settings['expire_period_unit'], 'months'); ?>><?php _e('Months', 'expire_comment_links');?></a>
								<option value="years" <?php echo selected($this->_settings['expire_period_unit'], 'years'); ?>><?php _e('Years', 'expire_comment_links');?></a>
							</select>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="Submit" value="<?php _e('Update Options &raquo;', 'expire_comment_links'); ?>" />
				</p>
			</form>
		</div>
<?php
	}

	private function _getSettings() {
		$defaults = array(
			'expire_period'			=> '2',
			'expire_period_unit'	=> 'months',
			'send_sys_info'			=> 'true',
		);
		$this->_settings = get_option('ecl');
		$this->_settings = wp_parse_args($this->_settings, $defaults);
		$this->_settings['expire_period'] = intval($this->_settings['expire_period']);
	}

	public function processCommentText($commentText) {
		global $comment;
		if ( $this->_isOldComment($comment) ) {
			$commentText = $this->_stripLinks($commentText);
		} else {
			$commentText = make_clickable($commentText);
		}
		return $commentText;
	}

	private function _stripLinks($commentText) {
		$commentText = preg_replace_callback("/
			<\s*a					# anchor tag
				(?:\s[^>]*)?		# other attibutes that we don't need
				\s*href\s*=\s*		# href (required)
				(?:
					\"([^\"]*)\"	# double quoted link
				|
					'([^']*)'		# single quoted link
				|
					([^'\"\s]*)		# unquoted link
				)
				(?:\s[^>]*)?		# other attibutes that we don't need
				\s*>				# end of anchor open tag
				(.*)				# Link Text
				<\/a>				# end of anchor tag
			/isUx", array($this, '_handleLink'), $commentText);
		return $commentText;
	}

	private function _handleLink($l) {
		// Get the URL, first checking for double quotes, then single, then none
		if (!empty($l[1])) {
			$url = $l[1];
		} elseif(!empty($l[2])) {
			$url = $l[2];
		} else {
			$url = $l[3];
		}
		return "{$l[4]} [{$url}]";
	}

	public function _changelog ($pluginData, $newPluginData) {
		$url = "{$this->_reposUrl}/{$newPluginData->slug}/tags/{$newPluginData->new_version}/upgrade.html";
		$response = wp_remote_get ( $url );
		$code = (int) wp_remote_retrieve_response_code ( $response );
		if ( $code == 200 ) {
			echo wp_remote_retrieve_body ( $response );
		}
	}
}

// Instantiate our class
$expireCommentLinks = new expireCommentLinks();

/**
 * Add filters and actions
 */
add_action( 'admin_menu', array($expireCommentLinks,'admin_menu') );
add_action( 'admin_init', array( $expireCommentLinks, 'registerOptions' ) );
add_filter( 'admin_init', array( $expireCommentLinks, 'sendSysInfo') );
add_filter( 'init', array( $expireCommentLinks, 'init_locale') );
add_filter( 'get_comment_author_url', array( $expireCommentLinks, 'commentAuthorLinkFilter' ) );
add_filter( 'get_comment_author', array( $expireCommentLinks, 'commentAuthorFilter' ) );
add_filter( 'plugin_action_links', array( $expireCommentLinks, 'addSettingLink' ), 10, 2 );
add_filter( 'comment_text', array( $expireCommentLinks, 'processCommentText' ), 8);
if ( $pri = has_filter('comment_text', 'make_clickable') ) {
	remove_filter ('comment_text', 'make_clickable', $pri);
}

/**
 * For use with debugging
 * @todo Remove this
 */
if ( !function_exists('dump') ) {
	function dump($v, $title = '') {
		if (!empty($title)) {
			echo '<h4>' . htmlentities($title) . '</h4>';
		}
		echo '<pre>' . htmlentities(print_r($v, true)) . '</pre>';
	}
}
