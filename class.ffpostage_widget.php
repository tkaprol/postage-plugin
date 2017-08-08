<?php

class ffpostage_widget {
	function control(){
		$data = get_option('ffpostage');
		?>
		<p>
			<label>Title: <input name="ffpostage_title" type="text"
				value="<?php echo $data['title']; ?>" />
			</label>
		</p>
		<p>
			<label>Option 2<input name="ffpostage_option2" type="text"
				value="<?php echo $data['option2']; ?>" />
			</label>
		</p>
		<?php
		if (isset($_POST['ffpostage_title'])){
			$data['title'] = attribute_escape($_POST['ffpostage_title']);
			$data['option2'] = attribute_escape($_POST['ffpostage_option2']);
			update_option('ffpostage', $data);
		}
	}
	
	function widget($args){
		$params = get_option('ffpostage');
		echo $args['before_widget'];
		echo $args['before_title'] . $params['title'] . $args['after_title'];
		
		echo '<form action="#ffpostage" method="post" onsubmit="return ffpostage_check_form();">';
		echo '<p class="ffpostage_form_label"><input type="text" name="ffpostage_email" id="ffpostage_email" class="ffpostage_form_txt" onblur="if (this.value==\'\') this.value=this.defaultValue" onclick="if (this.defaultValue==this.value) this.value=\'\'" value="E-mail"></p>';
		echo '<p class="ffpostage_form_label"><input type="submit" value="Subscribe" class="ffpostage_form_btn"></p>';
		echo '</form>';
		
		
		echo $args['after_widget'];
	}
	function register(){
		register_sidebar_widget('Postage APP for WordPress', array('ffpostage_widget', 'widget'));
		register_widget_control('Postage APP for WordPress', array('ffpostage_widget', 'control'));
	}
}

?>