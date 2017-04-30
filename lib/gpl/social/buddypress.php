<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'NgfbGplSocialBuddypress' ) ) {

	class NgfbGplSocialBuddypress {

		private $p;
		private $sharing;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( is_admin() || bp_current_component() ) {

				$this->p->util->add_plugin_filters( $this, array( 
					'get_defaults' => 1,
					'plugin_integration_rows' => 2,
					'messages_tooltip_plugin' => 2,
				), 200 );
			}
		}

		public function filter_get_defaults( $def_opts ) {

			$lca = $this->p->cf['lca'];
			$bio_const_name = strtoupper( $lca ).'_BP_MEMBER_BIOGRAPHICAL_FIELD';
			$def_opts['plugin_bp_bio_field'] = SucomUtil::get_const( $bio_const_name );

			return $def_opts;
		}

		public function filter_plugin_integration_rows( $table_rows, $form ) {

			$table_rows['plugin_bp_bio_field'] = $form->get_th_html( _x( 'BuddyPress Member Bio Field Name',
				'option label', 'wpsso' ), '', 'plugin_bp_bio_field' ).
			'<td class="blank">'.$this->p->options['plugin_bp_bio_field'].'</td>';

			return $table_rows;
		}

		public function filter_messages_tooltip_plugin( $text, $idx ) {
			if ( strpos( $idx, 'tooltip-plugin_bp_' ) !== 0 ) {
				return $text;
			}
			switch ( $idx ) {
				case 'tooltip-plugin_bp_bio_field':
					$text = __( 'The BuddyPress member profile page does not include the <em>Biographical Info</em> text from the WordPress user profile. If you\'ve created an additional BuddyPress Profile Field for members to enter their profile description, enter the field name here (example: Biographical Info, About Me, etc.).', 'wpsso' );
					break;
			}
			return $text;
		}
	}
}

?>
