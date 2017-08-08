<?php

class ffpostage_admin extends ffpostage_main {
	
 	public function __construct()
	{
		
		$this->path = plugins_url('' , __FILE__);
		
		$this->init();

		add_action('admin_init', array($this, 'admin_init'));
		add_action('admin_menu', array($this, 'admin_menu'));
		add_filter('admin_head', array($this, 'ShowTinyMCE'));
	}
	
	function admin_menu() {
		$home = add_menu_page(__('PostageApp', 'ffpostage'), __('PostageApp', 'ffpostage'), 'manage_options', 'ffpostage', array($this, 'dashboard'), $this->path . '/img/icon.png');
		add_submenu_page('ffpostage', __('PostageApp Options', 'ffpostage'), __('PostageApp Options', 'ffpostage'), 'manage_options', 'ffpostage-options', array($this, 'admin_settings'));
	}
	
	function dashboard($param) {
		echo '<div class="wrap">';
		echo '<div id="icon-options-general" class="icon32"><br></div>';
		
		// Single Page Title
		//echo '<h2>' . __('PostageApp', 'ffpostage') . '</h2>';
		
		// Multi Page Title
		$tabs = array( 'generate' => __('Generate Newsletter', 'ffpostage'), 'template' => __('PostageApp Templates', 'ffpostage'), 'custom' => __('Customize Content', 'ffpostage') );
		echo '<h2 class="nav-tab-wrapper">';
		
		if (isset($_GET['tab'])){$current = $_GET['tab'];} 
		
		
		foreach( $tabs as $tab => $name ){
			if (empty($current)) { $current = $tab; }
			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=".$_GET["page"]."&tab=$tab'>$name</a>";
		}
		echo '</h2>';
		
		// End of Title Section
		
		global $wpdb;
		$qry = "SELECT COUNT(email_email) as count FROM $wpdb->prefix$this->tablename " .
		" WHERE email_status=1";
		$totalMails = $wpdb->get_var( $qry );
		
		// check nonce form and update
		$nonce=$_REQUEST['_wpnonce'];
		$to = array();
		
		if (wp_verify_nonce($nonce, 'ffpostage-nonce') ) {
			
			//var_dump($_POST); die();
			
			// SET default
			
			if ($_POST['postageapp_default'] == 1){
				$option_name = 'postageapp_default';
				$newvalue = $_POST['postageapp_content'];
				
				if ( get_option( $option_name ) != $newvalue ) {
					update_option( $option_name, $newvalue );
					
				} else {
					$deprecated = ' ';
					$autoload = 'no';
					add_option( $option_name, $newvalue, $deprecated, $autoload );
				}
			}
			
			
			$postage	= new PostageApp();
			
			if ($_POST['subscribers'] == 0) {
				global $current_user;
				get_currentuserinfo();
				$to 	= array($current_user->user_email);
				
			} else {
				
				
				// find list of mails in DB
				$qry = "SELECT email_email FROM $wpdb->prefix$this->tablename " .
				      " WHERE email_status=1";
				$mails = $wpdb->get_results( $qry );
				
				
				foreach ($mails as $value) {
					$to[] = $value->email_email;
				}
				
				
			}

		    

			if ($current== 'generate'):
			
			/* gets the data from a URL */
			
				$ch = curl_init();
				$timeout = 5;
				
				$ffpostage_options = (array)json_decode(get_option( 'ffpostage_options' ));
				
				//var_dump($ffpostage_options); die();
				
				curl_setopt($ch,CURLOPT_URL,$ffpostage_options['genereatedurl']);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
				$mail_body = curl_exec($ch);
				curl_close($ch);
				
			
			
				

			elseif ($current== 'custom'): 
				$mail_body 	= stripcslashes($_POST['postageapp_content']);
			else: 
				$mail_body 	= $_POST['postageapp_content'];
			endif;
			
			$subject 	= ($_POST['postageapp_subject']);
			$header 	= array();
			
			// Send it all
			if ($current== 'template'):
			$ret = $postage->template($to, $subject, $mail_body, $header,NULL);
			else:
			$ret = $postage->mail($to, $subject, $mail_body, $header,NULL);
			endif;
			
			
			// Checkout the response
			if ($ret->response->status == 'ok') {
				echo '<div id="message" class="updated highlight"><p>' . __('<b>An email was sent and the following response was received:</b>','ffpostage') . $ret->response->message . '</p></div>';
			} else {
				echo '<div id="message" class="error"><p>' . __('<b>Error sending your email:</b>','ffpostage') . $ret->response->message . '</p></div>';
			}
				
				
		}
		
		
		
		echo '<h3>' .__('To send e-mail to subscribers using PostageApp, please use the following form: ', 'ffpostage') . '</h3>';
		?>
				<form method="post" action="<?php echo $_SERVER['PHP_SELF'].'?page='. @$_GET['page'] . '&tab=' . @$current; ?>">
				<?php wp_nonce_field('ffpostage-nonce'); ?>
				<table class="form-table">
					<tbody>
						<tr>
							<th><h3><?php _e('Subscriber Selection', 'ffpostage') ?></h3></th>
						</tr><tr>
							<td>
								<input id="api_key" type="radio" name="subscribers" value="0" checked="checked"  />
								<label for="api_key"><?php _e('Just me', 'ffpostage') ?></label>
							</td>
							<td>
								<input id="api_key" type="radio" name="subscribers" value="1"  />
								<label for="api_key"><?php printf(__('All Subscribers [%d user(s)]', 'ffpostage'),$totalMails) ?></label>
							</td>
						</tr>
						<tr>
							<th><h3><?php _e('Content Selection', 'ffpostage') ?></h3></th>
						</tr>
						<tr>
						<td>
								<label for="postageapp_subject"><?php _e('Subject', 'ffpostage') ?>:</label>
						</td>
						<td>
								<input id="postageapp_subject" type="text" name="postageapp_subject" value="" placeholder="<?php _e('Type your subject here', 'ffpostage') ?>"  />
						</td>
						</tr>
						<?php if($current == 'custom'):?>
						<tr>
						<td colspan="2">
								<?php wp_editor(stripslashes(get_option('postageapp_default')), 'postageapp_content'); ?>
								<p><?php _e('If you defined any template at PostageApp website previously, please just type template name into content area. ', 'ffpostage') ?></p>
						</td>
						</tr>
						<tr>
						<td colspan="2">
								<input id="postageapp_default" type="checkbox" name="postageapp_default" value="1"  />
								<label for="postageapp_default"><?php _e('Make this content as default', 'ffpostage') ?></label>
						</td>
						</tr>
						<?php 
						endif;
						if($current == 'template'):?>
						<tr>
						<td>
								<label for="postageapp_content"><?php _e('Template Name', 'ffpostage') ?>:</label>
						</td>
						<td>
								<input id="postageapp_content" type="text" name="postageapp_content" value="" placeholder="<?php _e('Type template name here', 'ffpostage') ?>"  />
								<p><?php _e('In order to use templates, you need to create custom newsletter from Project templates at PostageApp website', 'ffpostage') ?></p>
								<p><?php _e('P.S. To learn more about message templates, visit <a href="http://help.postageapp.com/faqs/application-features/message-templates" target="_blank">http://help.postageapp.com/faqs/application-features/message-templates</a>.', 'ffpostage') ?></p>
						</td>
						</tr>
						<?php endif;
						if($current == 'generate'):?>
						
						<?php
						endif;?>
					</tbody>
				</table>
				<p class="submit"><input type="submit" name="Submit" value="<?php _e('Send Mail using PostageApp', 'ffpostage') ?>" class="button-primary" /></p>
				</form>
				<?php
		echo '</div>';
	}
	
	function admin_init() {
		
		if ( function_exists('load_plugin_textdomain') ) {
			if ( !defined('WP_PLUGIN_DIR') ) {
				load_plugin_textdomain('ffpostage', str_replace( ABSPATH, '', dirname(__FILE__) ) );
			} else {
				load_plugin_textdomain('ffpostage', false, dirname( plugin_basename(__FILE__) ) );
			}
		}
		
	}
	
	
	function ShowTinyMCE() {
		// conditions here
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'jquery-color' );
		wp_print_scripts('editor');
		if (function_exists('add_thickbox')) add_thickbox();
		wp_print_scripts('media-upload');
		if (function_exists('wp_tiny_mce')) wp_tiny_mce();
		wp_admin_css();
		wp_enqueue_script('utils');
		do_action("admin_print_styles-post-php");
		do_action('admin_print_styles');
	}
	
	function admin_settings() {
		
		echo '<div class="wrap">';
		echo '<div id="icon-options-general" class="icon32"><br></div>';
		echo '<h2>' . __('PostageApp Options', 'ffpostage') . '</h2>';
		
		// check nonce form and update
		$nonce=$_REQUEST['_wpnonce'];
		if (wp_verify_nonce($nonce, 'ffpostage-nonce') ) {
			
			$newvalue = json_encode(array(
				'apikey' => $_REQUEST['api_key'],
				'genereatedurl' => $_REQUEST['genereatedurl']
					
			));
			
			$option_name = 'ffpostage_options';
			
			if ( get_option( $option_name ) != $newvalue ) {
				update_option( $option_name, $newvalue );
				echo '<div class="updated"><p><strong>'.__('Settings updated', 'ffpostage').'</strong></p></div>';
			} else {
				$deprecated = ' ';
				$autoload = 'no';
				add_option( $option_name, $newvalue, $deprecated, $autoload );
				echo '<div class="updated"><p><strong>'.__('Settings saved', 'ffpostage').'</strong></p></div>';
			}
			
			if (is_wp_error($result)){
				$error_string = $result->get_error_message();
				echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
			}
			
		}
		
		// get Settings to decode JSON and set vars
		$ffpostage_options = (array) json_decode(get_option('ffpostage_options'));
	

		
		?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'].'?page='. @$_GET['page']; ?>">
		<?php wp_nonce_field('ffpostage-nonce'); ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><h3><?php _e('API Settings', 'ffpostage') ?></h3></th>
				</tr><tr>
					<td>
						<label for="api_key"><?php _e('API Key', 'ffpostage') ?>:</label>
					</td><td>
						<input id="api_key" type="text" name="api_key" value="<?php echo $ffpostage_options['apikey'] ?>" placeholder="<?php _e('Enter your API key here', 'ffpostage') ?>"  />
					</td>
				</tr><tr>
					<td>
						<label for="genereatedurl"><?php _e('Generated Newsletter URL', 'ffpostage') ?>:</label>
					</td><td>
						<input id="genereatedurl" type="text" name="genereatedurl" value="<?php echo $ffpostage_options['genereatedurl'] ?>" placeholder="<?php _e('Enter your URL here', 'ffpostage') ?>"  />
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Save Settings', 'ffpostage') ?>" class="button-primary" /></p>
		</form>
		<?php
		
		
		echo '</div>';
	}
}