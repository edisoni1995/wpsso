<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoGplAdminMeta' ) ) {

	class WpssoGplAdminMeta {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'meta_text_rows' => array(
					'user_text_rows' => 4,
					'term_text_rows' => 4,
				),
				'meta_media_rows' => array(
					'post_media_rows' => 4,
					'user_media_rows' => 4,
					'term_media_rows' => 4,
				),
			) );
		}

		public function filter_meta_text_rows( $table_rows, $form, $head, $mod ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$og_type = isset( $head['og:type'] ) ? $head['og:type'] : 'website';

			$og_title_max_len    = $this->p->options['og_title_len'];
			$og_desc_max_len     = $this->p->options['og_desc_len'];
			$schema_desc_max_len = $this->p->options['schema_desc_len'];
			$seo_desc_max_len    = $this->p->options['seo_desc_len'];
			$tc_desc_max_len     = $this->p->options['tc_desc_len'];

			$def_og_title    = $this->p->page->get_title( $og_title_max_len, '...', $mod, true, false, true, 'none' );
			$def_og_desc     = $this->p->page->get_description( $og_desc_max_len, '...', $mod, true, true, true, 'none' );
			$def_schema_desc = $this->p->page->get_description( $schema_desc_max_len, '...', $mod );
			$def_seo_desc    = $this->p->page->get_description( $seo_desc_max_len, '...', $mod, true, false );
			$def_tc_desc     = $this->p->page->get_description( $tc_desc_max_len, '...', $mod );

			$table_rows[] = '<td colspan="2" align="center">'.$this->p->msgs->get( 'pro-feature-msg' ).'</td>';

			$form_rows = array(
				'og_title' => array(
					'label' => _x( 'Default Title', 'option label', 'wpsso' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_title', 'td_class' => 'blank',
					'content' => $form->get_no_input_value( $def_og_title, 'wide' ),
				),
				'og_desc' => array(
					'label' => _x( 'Default Description (Facebook / Open Graph, LinkedIn, Pinterest Rich Pin)', 'option label', 'wpsso' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_desc', 'td_class' => 'blank',
					'content' => $form->get_no_textarea_value( $def_og_desc, '', '', $og_desc_max_len ),
				),
				'seo_desc' => array(
					'tr_class' => ( $this->p->options['add_meta_name_description'] ? '' : 'hide_in_basic' ),
					'label' => _x( 'Google Search / SEO Description', 'option label', 'wpsso' ),
					'th_class' => 'medium', 'tooltip' => 'meta-seo_desc', 'td_class' => 'blank',
					'content' => $form->get_no_textarea_value( $def_seo_desc, '', '', $seo_desc_max_len ),
				),
				'tc_desc' => array(
					'label' => _x( 'Twitter Card Description', 'option label', 'wpsso' ),
					'th_class' => 'medium', 'tooltip' => 'meta-tc_desc', 'td_class' => 'blank',
					'content' => $form->get_no_textarea_value( $def_tc_desc, '', '', $tc_desc_max_len ),
				),
				'sharing_url' => array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Sharing URL', 'option label', 'wpsso' ),
					'th_class' => 'medium', 'tooltip' => 'meta-sharing_url', 'td_class' => 'blank',
					'content' => $form->get_no_input_value( $this->p->util->get_sharing_url( $mod, false ), 'wide' ), // $add_page = false
				),
				'subsection_schema' => array(
					'td_class' => 'subsection', 'header' => 'h4',
					'label' => _x( 'Structured Data / Schema Markup', 'metabox title', 'wpsso' )
				),
				'schema_desc' => array(
					'label' => _x( 'Schema Description', 'option label', 'wpsso' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_desc', 'td_class' => 'blank',
					'content' => $form->get_no_textarea_value( $def_schema_desc, '', '', $schema_desc_max_len ),
				),
			);

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
		}

		public function filter_meta_media_rows( $table_rows, $form, $head, $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( $mod['is_post'] && ( empty( $mod['post_status'] ) || $mod['post_status'] === 'auto-draft' ) ) {
				$table_rows[] = '<td><blockquote class="status-info"><p class="centered">'.
					sprintf( __( 'Save a draft version or publish the %s to display these options.',
						'wpsso' ), SucomUtil::titleize( $mod['post_type'] ) ).'</p></td>';
				return $table_rows;	// abort
			}

			$media_info = $this->p->og->get_media_info( $this->p->cf['lca'].'-opengraph',
				array( 'pid', 'img_url' ), $mod, 'none', 'og', $head );	// $md_pre = none

			$table_rows[] = '<td colspan="2" align="center">'.
				( $mod['is_post'] ? $this->p->msgs->get( 'pro-about-msg-post-media' ) : '' ).
				$this->p->msgs->get( 'pro-feature-msg' ).
				'</td>';

			$form_rows['subsection_opengraph'] = array(
				'tr_class' => 'hide_in_basic',
				'td_class' => 'subsection top', 'header' => 'h4',
				'label' => _x( 'All Social WebSites / Open Graph', 'metabox title', 'wpsso' )
			);
			$form_rows['subsection_priority_image'] = array(
				'header' => 'h5',
				'label' => _x( 'Priority Image Information', 'metabox title', 'wpsso' )
			);
			$form_rows['og_img_dimensions'] = array(
				'tr_class' => 'hide_in_basic',
				'label' => _x( 'Image Dimensions', 'option label', 'wpsso' ),
				'th_class' => 'medium', 'tooltip' => 'og_img_dimensions', 'td_class' => 'blank',
				'content' => $form->get_no_input_image_dimensions( 'og_img', true ),	// $use_opts = true
			);
			$form_rows['og_img_id'] = array(
				'label' => _x( 'Image ID', 'option label', 'wpsso' ),
				'th_class' => 'medium', 'tooltip' => 'meta-og_img_id', 'td_class' => 'blank',
				'content' => $form->get_no_input_image_upload( 'og_img', $media_info['pid'], true ),
			);
			$form_rows['og_img_url'] = array(
				'label' => _x( 'or an Image URL', 'option label', 'wpsso' ),
				'th_class' => 'medium', 'tooltip' => 'meta-og_img_url', 'td_class' => 'blank',
				'content' => $form->get_no_input_value( $media_info['img_url'], 'wide' ),
			);
			if ( $mod['is_post'] ) {
				$form_rows['og_img_max'] = array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Maximum Images', 'option label', 'wpsso' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_img_max', 'td_class' => 'blank',
					'content' => $form->get_no_select( 'og_img_max',
						range( 0, $this->p->cf['form']['max_media_items'] ), 'medium' ),
				);
			}
			$form_rows['subsection_priority_video'] = array(
				'header' => 'h5',
				'label' => _x( 'Priority Video Information', 'metabox title', 'wpsso' )
			);
			$form_rows['og_vid_embed'] = array(
				'label' => _x( 'Video Embed HTML', 'option label', 'wpsso' ),
				'th_class' => 'medium', 'tooltip' => 'meta-og_vid_embed', 'td_class' => 'blank',
				'content' => $form->get_no_textarea_value( '' ),
			);
			$form_rows['og_vid_url'] = array(
				'label' => _x( 'or a Video URL', 'option label', 'wpsso' ),
				'th_class' => 'medium', 'tooltip' => 'meta-og_vid_url', 'td_class' => 'blank',
				'content' => $form->get_no_input_value( '', 'wide' ),
			);
			$form_rows['og_vid_title'] = array(
				'tr_class' => 'hide_in_basic',
				'label' => _x( 'Video Name / Title', 'option label', 'wpsso' ),
				'th_class' => 'medium', 'tooltip' => 'meta-og_vid_title', 'td_class' => 'blank',
				'content' => $form->get_no_input_value( '', 'wide' ),
			);
			$form_rows['og_vid_desc'] = array(
				'tr_class' => 'hide_in_basic',
				'label' => _x( 'Video Description', 'option label', 'wpsso' ),
				'th_class' => 'medium', 'tooltip' => 'meta-og_vid_desc', 'td_class' => 'blank',
				'content' => $form->get_no_input_value( '', 'wide' ),
			);
			if ( $mod['is_post'] ) {
				$form_rows['og_vid_max'] = array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Maximum Videos', 'option label', 'wpsso' ),
					'th_class' => 'medium', 'tooltip' => 'meta-og_vid_max', 'td_class' => 'blank',
					'content' => $form->get_no_select( 'og_vid_max', 
						range( 0, $this->p->cf['form']['max_media_items'] ), 'medium' ),
				);
			}
			$form_rows['og_vid_prev_img'] = array(
				'tr_class' => 'hide_in_basic',
				'label' => _x( 'Include Preview Images', 'option label', 'wpsso' ),
				'th_class' => 'medium', 'tooltip' => 'meta-og_vid_prev_img', 'td_class' => 'blank',
					'content' => $form->get_no_checkbox( 'og_vid_prev_img' ),
			);

			$media_info = $this->p->og->get_media_info( $this->p->cf['lca'].'-schema',
				array( 'pid', 'img_url' ), $mod, 'og', 'og', $head );
	
			$form_rows['subsection_schema'] = array(
				'tr_class' => 'hide_in_basic',
				'td_class' => 'subsection', 'header' => 'h4',
				'label' => _x( 'Structured Data / Schema Markup / Pinterest', 'metabox title', 'wpsso' )
			);
			$form_rows['schema_img_dimensions'] = array(
				'tr_class' => 'hide_in_basic',
				'label' => _x( 'Image Dimensions', 'option label', 'wpsso' ),
				'th_class' => 'medium', 'tooltip' => 'schema_img_dimensions', 'td_class' => 'blank',
				'content' => $form->get_no_input_image_dimensions( 'schema_img', true ),	// $use_opts = true
			);
			$form_rows['schema_img_id'] = array(
				'tr_class' => 'hide_in_basic',
				'label' => _x( 'Image ID', 'option label', 'wpsso' ),
				'th_class' => 'medium', 'tooltip' => 'meta-schema_img_id', 'td_class' => 'blank',
				'content' => $form->get_no_input_image_upload( 'schema_img', $media_info['pid'], true ),
			);
			$form_rows['schema_img_url'] = array(
				'tr_class' => 'hide_in_basic',
				'label' => _x( 'or an Image URL', 'option label', 'wpsso' ),
				'th_class' => 'medium', 'tooltip' => 'meta-schema_img_url', 'td_class' => 'blank',
				'content' => $form->get_no_input_value( $media_info['img_url'], 'wide' ),
			);
			if ( $mod['is_post'] ) {
				$form_rows['schema_img_max'] = array(
					'tr_class' => 'hide_in_basic',
					'label' => _x( 'Maximum Images', 'option label', 'wpsso' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_img_max', 'td_class' => 'blank',
					'content' => $form->get_no_select( 'schema_img_max', 
						range( 0, $this->p->cf['form']['max_media_items'] ), 'medium' ),
				);
			}

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
		}
	}
}

