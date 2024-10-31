<?php
/*
Plugin Name: Ohloh Widget
Plugin URI: http://wordpress.org/extend/plugins/ohloh-widget/
Description: Allows the user to present their personal or their project's Ohloh-metrics as a widget.
Version: 0.2
Author: hangy
Author URI: http://hangy.de/
*/

class OhlohWidget
{
	/**
	 * Widget initialization code.
	 *
	 * This function registers our display and control functions with
	 * WordPress' widget- and sidebar-system.
	 */
	function init()
	{
		// check whether sidebar functions exist, only continue if they do.
		if ( function_exists('register_sidebar_widget') && !function_exists('register_widget_control') )
		{
			// register the ohloh widget with the sidebar
			register_sidebar_widget( 'ohloh', 'OhlohWidget::display' );
			// register the ohloh widget with the admin centre
			register_widget_control( 'ohloh', 'OhlohWidget::control' );
		}
	}

	/**
	 * The actual widget function.
	 *
	 * This function prints out the widget in your sidebar or where ever you placed it. :)
	 */
	function display( $args ) {
		extract( $args );
		$options = get_option('widget_ohloh');
	
		// If any of these options is empty, we do not even try to continue
		// executing the widget-method.
		if ( empty( $options['id'] ) || empty( $options['targetType'] ) || empty( $options['widgetType'] ) )
			return;
	
		$title = empty( $options['title'] ) ? 'Ohloh' : $options['title'];
		$id = empty( $options['id'] ) ? 0 : $options['id'];
		$targetType = empty( $options['targetType'] ) ? 0 : $options['targetType'];
		$widgetType = empty( $options['widgetType'] ) ? 0 : $options['widgetType'];
	
		$out = OhlohWidget::getCode( $targetType, $widgetType, $id );
		if ( !empty( $out ) ) {
	?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			<?php echo $out; ?>
		<?php echo $after_widget; ?>
	<?php
		}
	}

	/**
	 * Returns the (X)HTML code required to show the widget.
	 *
	 * @param string $targetType Defines of what kind of information we want to show a widget for. Can either be 'account' or 'project'.
	 * @param stromg $widgetType Defines the actual widget's type. Valid values depend on the $targetType.
	 * @param int $id account or project (defined by $targetType) to show the widget for.
	 * @return string (X)HTML code representing the widget.
	 */
	function getCode( $targetType, $widgetType, $id ) {
		// this is the var which contains our result.
		$result = "";
	
		// firstly, determine what target type we want a widget for
		switch ( $targetType ) {
			case 'account':
				$result = OhlohWidget::getAccountCode( $widgetType, $id );
				break;
			case 'project':
				$result = OhlohWidget::getProjectCode( $widgetType, $id );
				break;
		}
	
		return $result;
	}

	/**
	 * Returns the (X)HTML code required to show the account widget.
	 *
	 * @param stromg $widgetType Defines the actual widget's type.
	 * @param int $id account to show the widget for.
	 * @return string (X)HTML code representing the account widget.
	 */
	function getAccountCode( $widgetType, $id ) {
		// this is the var which contains our result.
		$result = "";
	
		// temporary var for the image
		$image = "";
	
		// base html string which is later on formatted to match the actual widget.
		$htmlLink = sprintf( '<a href="http://www.ohloh.net/accounts/%d?ref=%%s">%%s</a>', $id );
		$htmlImage = '<img width="%d" height="%d" alt="ohloh profile" src="%s"/>';
	
		// determine what kind of widget we need
		switch ( $widgetType ) {
			case 'tiny':
				$image = sprintf( $htmlImage, 80, 15, 'http://www.ohloh.net/images/icons/ohloh_profile.png' );
				$result = sprintf( $htmlLink, 'Tiny', $image );
				break;
			case 'rank':
				$url = sprintf( 'http://www.ohloh.net/accounts/%d/widgets/account_rank.gif', $id );
				$image = sprintf( $htmlImage, 32, 24, $url );
				$result = sprintf( $htmlLink, 'Rank', $image );
				break;
			case 'detailed':
				$url = sprintf( 'http://www.ohloh.net/accounts/%d/widgets/account_detailed.gif', $id );
				$image = sprintf( $htmlImage, 191, 35, $url );
				$result = sprintf( $htmlLink, 'Detailed', $image );
				break;
		}
	
		return $result;
	}
	
	/**
	 * Returns the (X)HTML code required to show the project widget.
	 *
	 * @param stromg $widgetType Defines the actual widget's type.
	 * @param int $id project to show the widget for.
	 * @return string (X)HTML code representing the project widget.
	 */
	function getProjectCode( $widgetType, $id ) {
		// this is the var which contains our result.
		$result = "";
	
		// base html string which is later on formatted to match the actual widget.
		$html = '<script type="text/javascript" src="http://www.ohloh.net/projects/%d/widgets/%s"></script>';
	
		// determine what kind of widget we need
		switch ( $widgetType ) {
			case 'thinBadge':
				$result = sprintf( $html, $id, 'project_thin_badge' );
				break;
			case 'partnerBadge':
				$result = sprintf( $html, $id, 'project_partner_badge' );
				break;
			case 'languages':
				$result = sprintf( $html, $id, 'project_languages' );
				break;
			case 'cocomo':
				$result = sprintf( $html, $id, 'project_cocomo' );
				break;
			case 'factoids':
				$result = sprintf( $html, $id, 'project_factoids' );
				break;
		}
	
		return $result;
	}
	
	/**
	 * This function is responsible for having the ohloh widget available
	 * in the Presentation -> Widgets menu.
	 * it just prints out some stuff based on the current settings, if those exist.
	 */
	function control() {
		// get options saved for the ohloh widget (in wp-database)
		$options = $newoptions = get_option('widget_ohloh');
		// if someone just submitted the form,
		if ( $_POST['ohloh-submit'] ) {
			// we want to set new options and process the input.
			$newoptions['title'] = strip_tags( stripslashes( $_POST['ohloh-title'] ) );
			$newoptions['id'] = strip_tags( stripslashes( $_POST['ohloh-id'] ) );
			$newoptions['targetType'] = strip_tags( stripslashes( $_POST['ohloh-targetType'] ) );
			$newoptions['widgetType'] = strip_tags( stripslashes( $_POST['ohloh-widgetType'] ) );
		}
		// if the new options array does not match the old options array (=> options changed)
		if ( $options != $newoptions ) {
			// we need to update the options!
			$options = $newoptions;
			update_option('widget_ohloh', $options);
		}
		// escape some stuff to be put out on the admin page.
		$title = attribute_escape( $options['title'] );
		$id = attribute_escape( $options['id'] );
		$targetType = attribute_escape( $options['targetType'] );
		$widgetType = attribute_escape( $options['widgetType'] );
	?>
				<p><label for="ohloh-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="ohloh-title" name="ohloh-title" type="text" value="<?php echo $title; ?>" /></label></p>
				<p><label for="ohloh-id">ID: <input stlye="width: 250px;" id="ohloh-id" name="ohloh-id" type="text" value="<?php echo $id; ?>" /></label></p>
				<p><label for="ohloh-targetType">Target type (account/project):
					<select name="ohloh-targetType" id="ohloh-targetType">
						<option value="account"<?php selected( $options['targetType'], 'account' ); ?>>Account</option>
						<option value="project"<?php selected( $options['targetType'], 'project' ); ?>>Project</option>
					</select></label></p>
				<p><label for="ohloh-widgetType">Widget type:
					<select name="ohloh-widgetType" id="ohloh-widgetType">
						<optgroup label="Account widgets">
							<option value="tiny"<?php selected( $options['widgetType'], 'tiny' ); ?>>Tiny</option>
							<option value="rank"<?php selected( $options['widgetType'], 'rank' ); ?>>Rank</option>
							<option value="detailed"<?php selected( $options['widgetType'], 'detailed' ); ?>>Detailed</option>
						</optgroup>
						<optgroup label="Project widgets">
							<option value="thinBadge"<?php selected( $options['widgetType'], 'thinBadge' ); ?>>Thin Badge</option>
							<option value="partnerBadge"<?php selected( $options['widgetType'], 'partnerBadge' ); ?>>Partner Badge</option>
							<option value="languages"<?php selected( $options['widgetType'], 'languages' ); ?>>Languages</option>
							<option value="cocomo"<?php selected( $options['widgetType'], 'cocomo' ); ?>>Cocomo</option>
							<option value="factoids"<?php selected( $options['widgetType'], 'factoids' ); ?>>Factoids</option>
						</optgroup>
					</select></label></p>
				<input type="hidden" id="ohloh-submit" name="ohloh-submit" value="1" />
	<?php
	}
}

// widgets can only be added after the plugin stuff has been loaded successfully, add that action.
add_action('plugins_loaded', 'OhlohWidget::init');
?>
