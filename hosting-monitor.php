<?php
/*
Plugin Name: Hosting Monitor
Plugin URI: http://wordpress.org/extend/plugins/hosting-monitor/
Description: Displays server storage used by WordPress on the WP-Admin Dashboard
Author: Alive Media Web Development and Mike Bijon
Version: 0.7.3
Author URI: http://www.mbijon.com
License: GPLv2 or later


Copyright 2011-2013 by Mike Bijon (email: mike@etchsoftware.com) and Ryan Dawson (email: ryan@alivemediadev.com), sharing equal-rights

This is Version 0.7.3 as of 4/22/2013

    This 'Hosting Monitor' plugin for WordPress is free software; you can
    redistribute it and/or modify it under the terms of the GNU General
    Public License, version 2, as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA


This program incorporates work covered by the following copyright and
permission notice:

  Disk Space Pie Chart is Copyright 2009-2010 by Jay Versluis
  (email : versluis2000@yahoo.com) http://wpguru.co.uk
  No license file included - implied GPLv2 from inclusion in Wordpress.org Plugin repository

*/

/*
 * Hook for adding Dashboard menu
 * 
 */
add_action( 'admin_menu', 'hostm_pages' );


// Add our submenu under Dashboard item in WP-Admin
function hostm_pages() {
	global $hostm_admin_page;
	
	$hostm_admin_page = add_management_page(
				'Hosting Monitor',
				'Hosting Monitor',
				'manage_options',
				'hosting-monitor-admin',
				'hosting_monitor'
				);
	
	// Add contextual help menu in wp-admin
	add_action( "load-$hostm_admin_page", 'hostm_add_help_menu' );
}


/*
 * Default storage units
 * Set to GB for first-time user
 * @since 0.4
 * 
 */
if ( ! get_option( 'guru_unit' ) ) {
	update_option( 'guru_unit', 'GB' );
}


/*
 * Save options from update action
 * This is called by hosting_monitor() after successful nonce-check
 *
 */
function update_hosting_monitor_options() {
	// ###TODO msb 10-24-2011: Put this & hosting_monitor() in class, de-duplicate these
	// Field and option names 
	$opt_name = 'guru_space';
	$opt_name_db = 'hm_db_space';
	$opt_name2 = 'guru_unit';
	$opt_name_db2 = 'hm_db_unit';
	$data_field_name = 'guru_space';
	$data_field_name_db = 'hm_db_space';
	$data_field_name2 = 'guru_unit';
	$data_field_name_db2 = 'hm_db_unit';
	
	// Read & sanitize(!) user-posted values
	$opt_val = intval( $_POST[ $data_field_name ] );
	$opt_val_db = intval( $_POST[ $data_field_name_db ] );
	$opt_val2 = sanitize_text_field( $_POST[ $data_field_name2 ] );
	$opt_val_db2 = sanitize_text_field( $_POST[ $data_field_name_db2 ] );

	// Save the posted values
	update_option( $opt_name, $opt_val );
	update_option( $opt_name_db, $opt_val_db );
	update_option( $opt_name2, $opt_val2 );
	update_option( $opt_name_db2, $opt_val_db2 );

	// On save: return confirm
	return true;
}


/*
 * Admin Settings page display & update logic
 * Link to it displays in Admin nav in the Tools submenu
 * 
 * Admin pagename: 'hosting-monitor-admin'
 * URL: {wp-admin}/tools.php?page=hosting-monitor-admin
 *
 * %%Mixed code, new & from Disk Space Pie Chart (DSPC)
 *
 */
function hosting_monitor() {
	
	// Check that the user has the required capability 
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'hostm_plugin' ) );
	}
	
	// Check WP nonce to prevent external use
	$update_confirm = NULL;
	if ( ! empty( $_POST ) && check_admin_referer( 'hosting_monitor_admin_options', 'hosting_monitor_nonce' ) ) {
		$update_confirm = update_hosting_monitor_options();
	} elseif ( ! empty( $_POST ) ) {
		wp_die( __( 'Invalid action performed. Please check your login and try again.', 'hostm_plugin' ) );
	}
    
	// Field and option names 
	$opt_name = 'guru_space';
	$opt_name_db = 'hm_db_space';
	$opt_name2 = 'guru_unit';
	$opt_name_db2 = 'hm_db_unit';
	$data_field_name = 'guru_space';
	$data_field_name_db = 'hm_db_space';
	$data_field_name2 = 'guru_unit';
	$data_field_name_db2 = 'hm_db_unit';
	
	// READ existing option values from database
	$opt_val = get_option( $opt_name, false ); // Explicitly set false
	
	// Set default space textbox to "0", so can tell if user is new or has Unlimited
	$opt_val = ( $opt_val === false ? "0" : $opt_val );
	
	$opt_val_db = get_option( $opt_name_db, false ); // Explicitly set false
	
	// Set default space textbox to "0", so can tell if user is new or has Unlimited
	$opt_val_db = ( $opt_val_db === false ? "0" : $opt_val_db );
	// --END READ
	
	// Units: TB, GB, or MB
	$opt_val2 = get_option( $opt_name2 );
	$opt_val_db2 = get_option( $opt_name_db2 );
	
	// Decide which units to use for graph
	switch ( $opt_val2 ) { // DISK
		case 'TB':
			$spacecalc = pow( 1024, 3 );
			break;
		case 'GB':
			$spacecalc = pow( 1024, 2 );
			break;
		default:
			$spacecalc = 1024;
	}
	
	switch ( $opt_val_db2 ) { // DB
		case 'TB':
			$spacecalc_db = pow( 1024, 3 );
			break;
		case 'GB':
			$spacecalc_db = pow( 1024, 2 );
			break;
		default:
			$spacecalc_db = 1024;
	}
	
	// Report status of options update
	if ( $update_confirm === true ) {
		_e( '<div class="updated"><p><strong>Your settings have been saved.</strong></p></div>', 'hostm_plugin' ); // Success
	}
	
	// Display the settings edit screen
	echo '<div class="wrap">';
    
	// Settings screen title
	echo "<h2>" . __( 'Hosting Monitor', 'hostm_plugin' ) . "</h2>";
	
	// Render Settings form, START
	?>
	
	<form name="hosting_monitor_form" method="post" action="">
		<p>
			<?php _e( 'Disk space:', 'hostm_plugin' ); ?> 
			<input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="5">
			&nbsp;
			<select name="guru_unit">
				<option value="TB" <?php if ( $opt_val2 == "TB" ) echo 'selected'; ?>>TB</option>
				<option value="GB" <?php if ( $opt_val2 == "GB" || empty($opt_val2) ) echo 'selected'; ?>>GB</option>
				<option value="MB" <?php if ( $opt_val2 == "MB" ) echo 'selected'; ?>>MB</option> 
			</select>
			&nbsp;&nbsp;
			<em>Leave at &ldquo;0&rdquo; if you have unlimited space.</em>
		</p>
		<p>
			<?php _e( 'Database space:', 'hostm_plugin' ); ?> 
			<input type="text" name="<?php echo $data_field_name_db; ?>" value="<?php echo $opt_val_db; ?>" size="5">
			&nbsp;
			<select name="hm_db_unit">
				<option value="TB" <?php if ( $opt_val_db2 == "TB" ) echo 'selected'; ?>>TB</option>
				<option value="GB" <?php if ( $opt_val_db2 == "GB" || empty($opt_val_db2) ) echo 'selected'; ?>>GB</option>
				<option value="MB" <?php if ( $opt_val_db2 == "MB" ) echo 'selected'; ?>>MB</option> 
			</select>
		</p>
<!--
		<p>
			<?php _e( 'Send Low-on-Space Alerts:', 'hostm_plugin' ); ?> 
			<input type="text" name="<?php //echo $confirm_send_alerts; ?>" value="<?php echo $opt_val_db; ?>" size="5">
			&nbsp;
			
		</p>
-->
		<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ) ?>" />
		</p>
		<?php
			// Add a WordPress nonce for security
			wp_nonce_field( 'hosting_monitor_admin_options', 'hosting_monitor_nonce' );
		?>
	</form>
	<br />
	
	<hr />
	<br />
	
	<?php
	// --END, Settings form
	
	/*
	 * Calculate DB size units
	 *
	 * %%Entire method from Disk Space Pie Chart (DSPC)
	 * 
	 */
	function file_size_info( $filesize ) {
		$bytes = array( 'KB', 'KB', 'MB', 'GB', 'TB' );
	
		# values are always displayed
		if ( $filesize < 1024 ) $filesize = 1;
	
		# in at least kilobytes
		for ( $i = 0; $filesize > 1024; $i++ ) $filesize /= 1024;
	
		$file_size_info['size'] = round( $filesize, 3 );
		$file_size_info['type'] = $bytes[$i];
	
		return $file_size_info;
	}
	
	
	/*
	 * Calculate actual DB size
	 * Echoes DB size to screen, positioned using CSS
	 *
	 * Default values translate to: 10 MB
	 * 
	 */
	function db_size( $opt_val_db = 10, $spacecalc_db = 1024 ) {
		$rows = mysql_query( "SHOW table STATUS" );
		$dbsize = 0;
		
		while ( $row = mysql_fetch_array( $rows ) ) {
			$dbsize += $row['Data_length'] + $row['Index_length'];
		}
		
		if ( $opt_val_db )
			if ( $dbsize > $opt_val_db * $spacecalc_db ) {
				$color = "red";
			} else {
				$color = "green";
			}
		
		$dbsize = file_size_info( $dbsize );
		
		return "{$dbsize ['size']} {$dbsize['type']}";
	}
	
	// Get local working directory (PWD)
	// ### TODO msb 10-22-2011: Bad for Windows (use WP built-in folder vars)
	$output = substr( shell_exec( 'pwd' ), 0, -9 );
	// Calculate actual disk space usage
	$usedspace = substr( shell_exec( 'du -s ' . $output ), 0, -( strlen( $output ) + 1 ) );
	
	// Get storage space set by user
	$totalspace = ( $opt_val * $spacecalc );
	$freespace = ( $totalspace ) - $usedspace;
	$usedspace_percent = ( $totalspace != 0 ? round( ($usedspace / ( $totalspace / 100 ) ), 1 ) : 0 );
	
	// Calculate used space in chosen units
	$usedspace_units = ( $usedspace / $spacecalc );
	
	if ( $usedspace_units < 1 ) $usedspace_units = round( $usedspace_units, 3 );
	else $usedspace_units = round( $usedspace_units, 2 );
	
	?>
	<table width="800" border="0">
		<tr>
			<td>
				<img src="<?php echo plugins_url( 'includes/piechart.php?data=', __FILE__ );
				echo $usedspace_percent . '*' . ( 100 - $usedspace_percent ); ?>&label=Used Space*Free Space" /> 
			</td>
			<td>
				<strong>Disk Space Used</strong><br />
				<strong>Disk Space Free</strong><br />
				<hr>
				<strong>Database Size</strong><br />
			</td>
			<td>
				<?php
				// Disk space used
				echo $usedspace_units . ' ' . $opt_val2;
				?><br />
				<?php
				// Disk space free
				// ###TODO msb 10-25-2011: Handle negative values: 1) bigger alert, 2) change formatting on "- 23MB" text
				if ( $usedspace_percent == 0 )
					echo 'No free space (* Or not configured)';
				else
					echo round( ( $freespace / $spacecalc ), 2 ) . ' ' . $opt_val2; ?><br />
				<hr>
				<?php echo db_size( $opt_val_db, $spacecalc_db ); ?><br />
			</td>
		</tr>
	</table>
	
	</div>
	
	<!-- End %%DSPC code -->
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	
	<div class="hosting-monitor-footer" style="background-color: #ccc;
						position: relative;
						bottom: 0;">
		<p>
			This plugin produced by: <a href="http://www.alivemediadev.com/">Alive Media Web Development</a>,
			&nbsp;and developed by: <a href="http://www.etchsoftware.com/">Mike Bijon</a>
		</p>
		<p>
			Credits for included code:<br />
				<a href="http://wpguru.co.uk/2010/12/disk-space-pie-chart-plugin/" target="_blank">Disk Space by Jay Versluis</a> |
				<a href="http://www.peters1.dk/webtools/php/lagkage.php?sprog=en" target="_blank">Pie Chart Script by Rasmus Peters</a>
		</p>
	</div>
	
	<?php
}

/*
 * Check server memory use
 * 
 */
if ( is_admin() ) {	
	class wp_memory_usage {
		
		var $memory = false;
		
		function __construct() {
			add_action( 'init', array (&$this, 'check_limit') );
			// Add Dashboard widget, lower priority for us
			add_action( 'wp_dashboard_setup', array (&$this, 'add_dashboard'), 20 );
			add_filter( 'admin_footer_text', array (&$this, 'add_footer') );
			
			$this->memory = array();					
		}
		
		function wp_memory_usage() {
			return $this->__construct();
		}
		
		function check_limit() {
			$this->memory['limit'] = (int) ini_get( 'memory_limit' ) ;
		}
		
		/*
		 * 
		 * %%Entire method from Disk Space Pie Chart (DSPC)
		 *
		 */
		function check_memory_usage() {
			
			$this->memory['usage'] = function_exists( 'memory_get_usage' ) ? round( ( memory_get_usage() / pow( 1024, 2 ) ), 2 ) : 0;
			
			if ( ! empty( $this->memory['usage'] ) && ! empty( $this->memory['limit'] ) ) {
				$this->memory['percent'] = round ( $this->memory['usage'] / $this->memory['limit'] * 100, 0 );
				$this->memory['color'] = '#21759B';
				if ( $this->memory['percent'] > 80 ) $this->memory['color'] = '#E66F00';
				if ( $this->memory['percent'] > 95 ) $this->memory['color'] = 'red';
			}		
		}
		
		/*
		 * Calculate DB size units
		 *
		 * ###TODO msb 10-24-2011 - De-duplicate!!! Copied from main plugin method hosting_monitor()
		 * 
		 */
		function get_file_size_info( $filesize ) {
			$bytes = array( 'KB', 'KB', 'MB', 'GB', 'TB' );
		
			# values are always displayed
			if ( $filesize < 1024 ) $filesize = 1;
		
			# in at least kilobytes
			for ( $i = 0; $filesize > 1024; $i++ ) $filesize /= 1024;
		
			$file_size_info['size'] = round( $filesize, 3 );
			$file_size_info['type'] = $bytes[$i];
		
			return $file_size_info;
		}
		
		
		/*
		 * Calculate actual DB size
		 * Echoes DB size to screen, positioned using CSS
		 *
		 * Default values translate to: 10 MB
		 *
		 * 
		 * ###TODO msb 10-24-2011 - De-duplicate!!! Copied from main plugin method hosting_monitor()
		 * 
		 */
		function check_db_size( $opt_val_db = 10, $spacecalc_db = 1024 ) {
		     $rows = mysql_query( "SHOW table STATUS" );
		     $dbsize = 0;
		     
		     while ( $row = mysql_fetch_array( $rows ) ) {
			     $dbsize += $row['Data_length'] + $row['Index_length'];
		     }
		     
		     if ( $dbsize > $opt_val_db * $spacecalc_db ) {
			     $color = "red";
		     } else {
			     $color = "green";
		     }
		     
		     $dbsize = $this->get_file_size_info( $dbsize );
		     
		     return "{$dbsize ['size']} {$dbsize['type']}";
		}
		
		
		/*
		 * Build & output the Dashboard metabox
		 *
		 */
		function dashboard_output() {
			$this->check_memory_usage();
			$this->memory['limit'] = empty( $this->memory['limit'] ) ? __('N/A') : $this->memory['limit'] . __(' MByte');
			$this->memory['usage'] = empty( $this->memory['usage'] ) ? __('N/A') : $this->memory['usage'] . __(' MByte');
			
			// check disk usage and pop into a variable
			$output = substr( shell_exec( 'pwd' ), 0, -9 );
			$usedspace = substr( shell_exec( 'du -s ' . $output ), 0, -( strlen( $output ) + 1 ) );
			
			// Get user settings
			$opt_val = get_option( 'guru_space', false ); // Explicitly set false
			$opt_val_db = get_option( 'hm_db_space', false ); // Explicitly set false
			$opt_val2 = get_option( 'guru_unit' );
			$opt_val_db2 = get_option( 'hm_db_unit' );
			
			// Decide which units to use for graph
			switch ( $opt_val2 ) { // DISK
				case 'TB':
					$spacecalc = pow( 1024, 3 );
					break;
				case 'GB':
					$spacecalc = pow( 1024, 2 );
					break;
				default:
					$spacecalc = 1024;
			}
			switch ( $opt_val_db2 ) { // DB
				case 'TB':
					$spacecalc_db = pow( 1024, 3 );
					break;
				case 'GB':
					$spacecalc_db = pow( 1024, 2 );
					break;
				default:
					$spacecalc_db = 1024;
			}
			
			// Calculate used space in chosen units
			$usedspace_units = ( $usedspace / $spacecalc );
			
			if ( $usedspace_units < 1 ) $usedspace_units = round( $usedspace_units, 3 );
			else $usedspace_units = round( $usedspace_units, 2 );
			
			// Get storage space set by user
			$totalspace = ( $opt_val * $spacecalc );
			$freespace = ( $opt_val * $spacecalc ) - $usedspace;
			
			if ( current_user_can( 'manage_options' ) ) {
				$hm_user_admin = true;
				$config_link = '&nbsp;&nbsp;&nbsp;(<a href="tools.php?page=hosting-monitor-admin"><em>Hosting Monitor</em> config</a>)';
			}
			
			if ( $opt_val === false ) {
				// Means user has not saved a config, prompt them if admin
				if ( $hm_user_admin === true ) {
					$free_space_message = 'Setup not completed. Please <a href="tools.php?page=hosting-monitor-admin">Configure <em>Hosting Monitor</em></a> now';
				}
			} elseif ( $opt_val == 0 ) {
				// Zero is our save-default, assume unlimited space
				$free_space_message  = "UNLIMITED";
				if ( $hm_user_admin === true ) $free_space_message .= $config_link;
			} else {
				$free_space_message  = round( ( $freespace / $spacecalc ), 2 ) . " " . $opt_val2;
				if ( $hm_user_admin === true ) $free_space_message .= $config_link;
			}
			
			
			// Display storage-use text
			?>
			<ul>	
				<li>
					<strong><?php _e( 'Disk Space Used' ); ?></strong>: <span><?php
						echo $usedspace_units . " " . $opt_val2; ?> </span>
					&nbsp;&nbsp;|&nbsp;&nbsp;
					<strong><?php _e( 'Disk Space Free' ); ?></strong>: <span><?php
						echo $free_space_message; ?></span>
				</li>
			</ul>
			
			<?php
			// Display bar graph for storage-use %
			if ( ! empty( $this->memory['percent'] ) ) : ?>
				<div class="progressbar">
				<?php
					###TODO msb 10-22-2011: Remove inline styles
					
					// Calculate space usage % for later
					$space_usage = ($totalspace == 0) ? 0 : ($usedspace / ($totalspace / 100));
				?>
					<div class="" style="height:2em;
							border:1px solid #DDDDDD;
							background-color: #0055cc;">
						<div class="" style="width: <?php echo round($space_usage, 1); ?>%;
								height:100%; 
								background-color:#f55;
								border-width:0px;
								text-shadow:0 1px 0 #000000;
								color:#FFFFFF;
								text-align:right;
								font-weight:bold;">
							<div style="padding:6px"><?php echo round($space_usage, 0); ?>%</div>
						</div>
					</div> 
				</div>
			<?php
			endif;
			
			// Display DB space-used text, if admin
			if ( $hm_user_admin === true ) {
				?>
				<ul>	
					<li><strong><?php _e( 'Database Size' ); ?></strong>: <span><?php
						echo $this->check_db_size( $opt_val_db, $spacecalc_db ); ?></span></li>
				</ul>
				<?php
			}
		}
		
		/*
		 * Render Dashboard widget
		 *
		 */
		function add_dashboard() {
			wp_add_dashboard_widget( 'wp_memory_dashboard', 'Hosting Monitor', array( &$this, 'dashboard_output' ) );
		}
		
		/*
		 * Output memory-use to footer
		 *
		 */
		function add_footer( $content ) {
			$this->check_memory_usage();
			
			// hook for Footer Display
			// ###TODO
			// let's do this another time,...
			//
			
			return $content;
		}

	}
	
	/*
	 * Add contextual help menu
	 *
	 * Using WP v3.3 menus
	 * 
	 */
	function hostm_add_help_menu() {
		global $hostm_admin_page;
		$screen = get_current_screen();
		
		// Do not add help menu if not on our own admin page
		if ( $screen -> id != $hostm_admin_page )
			return;
		
		$help_content_faq = __("
			<h2>Frequently Asked Questions</h2>
			
			<h4>Does this Plugin run on Windows web servers?</h4>
				<p>Not entirely. It works on Windows Apache, but has errors on Windows IIS.</p>
			
			<h4>I've noticed my Dashboard is slow. What gives?</h4>
				<p>The used disk space is calculated when the Dashboard is loaded. It can be slow because the server counts every file, every time. On slow servers this can take some time. We agree that it's annoying and plan to fix it.</p>
				<p>To prevent this, close the dahsboard window using the little arrow in the top-right corner. Alternatively, click on Screen Options and disable the widget.</p>
			
			<h4>Are you going to fix {bug X}?</h4>
				<p>Yes, as quickly as we can. The problems in version 0.5 and some we inherited from a previous plugin should be fixable. We can probably make this work correctly on Windows servers. And, we should be able to cache the disk space stats so the dashboard is not so slow.</p>
			
			<h4>= Where did this come from, and will you keep updating it?</h4>
				<p>Hosting Monitor is produced by: <a href=\"http://www.alivemediadev.com\">Alive Media Web Development</a>, and developed by: <a href=\"http://www.etchsoftware.com\">Mike Bijon</a>.</p>
				<p>This plugin is installed on many of our customer sites. We plan to keep it updated _and_ to add new features as often as time allows. It is more than just a hobby, since it must be updated for new versions of WordPress.</p>
		", 'hostm_plugin');
		
		$help_content_setup = __("
			<h2>Setup Instructions</h2>
				<ol>
					<li>Go to Tools &gt; Hosting Monitor in WordPress Admin</li>
					<li>Set the maximum disk space allowed by your hosting company & press &ldquo;Save Changes&rdquo;</li>
				</ol>
				<p><strong>Why?</strong> Every host is different, so Hosting Monitor can&#039;t automatically tell how much space you&#039;re *allowed* to use by your host.</p>
		", 'hostm_plugin');
		
		if ( method_exists( $screen, 'add_help_tab' ) ) { // Check if this is WP 3.3
			// Do this if we are on own admin page
			$screen->add_help_tab( array(
				'id'      => 'hostm_help_faq',
				'title'   => __( 'Help & FAQ', 'hostm_plugin' ),
				'content' => $help_content_faq,
			));
			$screen->add_help_tab( array(
				'id'      => 'hostm_help_setup',
				'title'   => __( 'Setup Help', 'hostm_plugin' ),
				'content' => $help_content_setup,
			));
		} else { // Earlier than 3.3, use old add_contextual_help
			add_contextual_help( $hostm_admin_page, $help_content_faq . $help_content_setup );
		}
	}
	

	/*
	 * Start the plugin
	 *
	 * Loaded after all other plugins, so memory-use accurate
	 *
	 */
	add_action( 'plugins_loaded', create_function( '', '$memory = new wp_memory_usage();' ) );
}