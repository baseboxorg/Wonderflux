<?php header("Content-type: text/css"); ?>
/**
 *
 * Flux Layout
 *
 * A dynamic percentage based CSS layout engine - https://github.com/Jonnyauk/flux-layout
 * Built for the Wonderflux WordPress theme framework - https://github.com/Jonnyauk/Wonderflux
 *
 * Free to use on any project and released under the GPLv2 license
 * Created by Jonny Allbut (copyright 2014). Exceptions include, but are not limited to:
 * Normalize - https://git.io/normalize - MIT License - project by Nicolas Gallagher, co-created with Jonathan Neal
 *
 * !DANGER WILL ROBINSON!
 * THIS IS AN EXPERIMENTAL - NOT FOR PRODUCTION JUST YET!!
 *
 * @package Wonderflux
 * @since Wonderflux 2.0
 *
 */


<?php

/* DO IT! Just for testing and development */
$wf_grid = new wflux_layout;
$wf_grid->grid_containers();
//$wf_grid->grid_blocks();
//$wf_grid->grid_space_loops();
//$wf_grid->grid_push_loops();
$wf_grid->grid_relative_loops();
$wf_grid->grid_columns();
$wf_grid->grid_media_queries();

/**
 * Percent based CSS and media query layout generator
 * @since 2.0
 */
class wflux_layout {

	protected $width;				// INPUT - Width of main container
	protected $width_units;			// INPUT - Units for main container width (% or pixels)
	protected $columns_basic;		// INPUT - Number of basic (no gutter) columns in layout
	protected $class_prepend;		// INPUT - Prepend all CSS main selectors
	protected $columns_prepend;		// INPUT - Prepend all CSS column selectors
	protected $columns;				// ARRAY - Advanced columns with gutters
	protected $columns_gutter;		// INPUT - Target gutter (%)
	protected $relative;			// ARRAY - General relative sizes
	protected $mq_config;			// ARRAY - Media queries cofig
	protected $mq_box_sizes;		// ARRAY - Media query box size loops
	protected $mq_column_sizes;		// ARRAY - Media query column size loops
	protected $content_css;			// WONDERFLUX INPUT - #content CSS depending on sidebar admin option

	protected $class_space_left;	// INTERNAL - CSS selector - padding left
	protected $class_space_right;	// INTERNAL - CSS selector - padding right
	protected $class_move_left;		// INTERNAL - CSS selector - margin left
	protected $class_move_right;	// INTERNAL - CSS selector - margin right
	protected $column_width;		// INTERNAL - Width of columns (%)
	protected $minify;				// INTERNAL - CSS selector - column width blocks

	function __construct() {

		// Cleanup all data ready to be used
		$this->width_units = ( $_GET['wu'] == 'percent' ) ? '%' : 'px';

		if ($this->width_units == 'px') {
			$this->width = ( is_numeric( $_GET['w'] ) && $_GET['w'] <= 4000 ) ? $_GET['w'] : 950;
		} else {
			$this->width = ( is_numeric( $_GET['w'] ) && $_GET['w'] <= 101 ) ? $_GET['w'] : 80;
		}

		$this->columns_basic = ( is_numeric( $_GET['c'] ) && $_GET['c'] <= 101 ) ? $_GET['c'] : 16;
		$this->class_prepend = ( !isset($this->class_prepend) ) ? 'box-' : strtolower( preg_replace('/[^a-z0-9_\-]/', '', $this->class_prepend) );
		$this->columns_prepend = ( !isset($this->columns_prepend) ) ? 'column-' : strtolower( preg_replace('/[^a-z0-9_\-]/', '', $this->columns_prepend) );

		// WONDERFLUX SPECIFIC
		$this->content_css = ( isset($_GET['sbp']) && $_GET['sbp'] == 'right' ) ? false : 'left';

		// Loops of output
		$this->relative = array(1,2,4,5,8,10);
		// Add core column option to box array for output
		if ( !in_array($this->columns_basic, $this->relative) ){
			array_unshift( $this->relative, $this->columns_basic );
			sort($this->relative);
		}

		$this->columns = array(1,2,4,5,8,10);
		// Add core column option to columns array for output
		if ( !in_array($this->columns_basic, $this->columns) ){
			array_unshift( $this->columns, $this->columns_basic );
			sort($this->columns);
		}

		$this->mq_box_sizes = array(1,2,4,5,8,10);
		$this->columns_gutter = 2;

		if ( isset( $_GET['mq_cols'] ) && is_array($_GET['mq_cols']) ){
			$this->mq_column_sizes = $_GET['mq_cols'];
		} else {
			// Just generate basic grid values
			$this->mq_column_sizes = array( $this->columns_basic );
		}

		$this->mq_config = array(
			'tiny'	=> array(
							'def'	=> 'mq-tiny',
							'max'	=> 480,
							'units'	=> 'px',
							'note'	=> 'Tiny screens - small portrait phones'
						),
			'small'		=> array(
							'def'	=> 'mq-small',
							'min'	=> 481,
							'max'	=> 768,
							'units'	=> 'px',
							'note'	=> 'Small screens - Lower spec landscape phones and some portrait tablets'
						),
			'medium'		=> array(
							'def'	=> 'mq-medium',
							'min'	=> 769,
							'max'	=> 1409,
							'units'	=> 'px',
							'note'	=> 'Medium screens - Standard computers and landscape tablets'
						),
			'large'		=> array(
							'def'	=> 'mq-large',
							'min'	=> 1410,
							'units'	=> 'px',
							'note'	=> 'Large screens - Swanky hi-res screens'
						),

		);

		// Internal values
		$this->column_width = 100 / $this->columns_basic;
		$this->class_space_left = $this->class_prepend . 'pad-left';
		$this->class_space_right = $this->class_prepend . 'pad-right';
		$this->class_move_left = $this->class_prepend . 'move-left';
		$this->class_move_right = $this->class_prepend . 'move-right';
		$this->minify = "\n";
		$this->minify_2 = $this->minify . $this->minify;

	}

	/**
	 * Outputs main site .container and .row classes
	 */
	function grid_containers() {

		echo '/********** Core containers **********/' . $this->minify_2 . $this->minify;

		echo '.container { ' . 'width:' . $this->width . $this->width_units . '; margin:0 auto; }' . $this->minify
		. '.row { ' . 'width:100%; margin:0 auto; }' . $this->minify;
		// WONDERFLUX SPECIFIC
		echo ( $this->content_css == 'left' ) ? '#content { float: right; }'. $this->minify : '';

		echo $this->minify_2;

	}

	/**
	 * Outputs percent widths for blocks
	 * REMOVED FOR THE MOMENT - avoid alternative CSS definitions and repeated code
	 */
	//function grid_blocks() {

		//echo '/********** Grid boxes **********/' . $this->minify_2;

		// Main output
		/*
		for ( $limit=1; $limit <= $this->columns_basic; $limit++ ) {
			echo '.' . $this->class_prepend . $limit . ' { width: '
			. $this->column_width * $limit . '%; }' . $this->minify;
		}
		echo $this->minify;
		*/

	//}

	/**
	 * Outputs columns rules
	 */
	function grid_columns() {

		echo '/********** Traditional columns **********/' . $this->minify_2 . $this->minify;

		// CSS attribute wildcard selectors
		echo 'div[class*="' . $this->columns_prepend . '"] { '
		. 'float:left; margin-left: ' . $this->columns_gutter . '%; }'
		 . $this->minify;

		echo '.row.' . rtrim($this->columns_prepend, '-') . ' div:first-child { margin-left: 0; }' . $this->minify;

		foreach ( $this->columns as $size_r ) {
			if ( intval($size_r) < 101 ) {

				for ( $limit=1; $limit < $size_r || $limit == 1; $limit++ ) {

					if ( $size_r!=1 ){

						$width = (((100 - ($size_r - 1) * $this->columns_gutter) / $size_r ) * $limit)
						+ ( $this->columns_gutter * ($limit - 1) );

						echo '.' . $this->columns_prepend . $limit . '-' . $size_r
						. ' { width:'
						. $width
						. '%; }'
						. $this->minify;

					}

				}

				echo $this->minify;

			}
		}

		echo $this->minify;

	}

	/**
	 * Outputs margin + padding rules
	 */
	function grid_mover( $type, $definition, $direction ) {

		$negpos = ( $type == 'push' ) ? '-' : '';
		$css_type = ( $type == 'push' ) ? 'margin' : 'padding';

		$css_1 = ( $direction == 'l' ) ? '{ ' . $css_type . ': 0 ' . $negpos : '{ ' . $css_type . ': 0 0 0 ' . $negpos;
		$css_2 = ( $direction == 'l' ) ? ' 0 0; ' : '; ';

		for ( $limit=1; $limit <= $this->columns_basic; $limit++ ) {
			echo '.' . $definition . '-' . $limit . ' ' . $css_1
			. $this->column_width * $limit . '%' . $css_2 . '}' . $this->minify;
		}

	}

	function grid_space_loops() {

		$this->grid_mover( 'space', $this->class_space_left, 'l' );
		$this->grid_mover( 'space', $this->class_space_right, 'r' );
		echo $this->minify;

	}

	function grid_push_loops() {

		$this->grid_mover( 'push', $this->class_move_left, 'l' );
		$this->grid_mover( 'push', $this->class_move_right, 'r' );
		echo $this->minify;

	}

	/**
	 * Outputs relative sized CSS
	 * $sizes = array of integers representing what sizes to output
	 */
	function grid_relative_loops() {

		if ( !is_array($this->relative) ) return;

		echo '/********** Grid boxes **********/' . $this->minify_2 . $this->minify;

		// CSS attribute wildcard selectors
		echo 'div[class*="' . $this->class_prepend . '"] { '
		. 'float:left; margin: 0; }'
		. $this->minify_2;

		foreach ( $this->relative as $size ) {

			if ( intval($size) >= 1 && intval($size) < 101 ) {

				if ( $size == 1 ){

					echo '.' . $this->class_prepend . '1-1'
					. ', .' . $this->class_prepend . $this->columns_basic
					. ', .box-full'
					.' { width:100%; }' . $this->minify;

				} else {

					for ( $limit=1; $limit < $size; $limit++ ) {

						echo '.' . $this->class_prepend . $limit . '-' . $size;
						echo ( $size == $this->columns_basic ) ? ', .' . $this->class_prepend . $limit : '';
						echo ' { width:' . $limit * ( 100 / $size ) . '%; }' . $this->minify;

					}

				}

				echo $this->minify;

			}

		}

		echo $this->minify;

	}

	/**
	 * Media queries output for different sized screens
	 * 4 definitions:
	 * rwd-tiny Tiny screens - small portrait phones
	 * rwd-small Small screens - Lower spec landscape phones and some portrait tablets
	 * rwd-medium Medium screens - Standard computers and landscape tablets
	 * rwd-large Large screens - Swanky hi-res screens
	 */
	function grid_media_queries() {

		// Array of just definitions - used for -hide-except rules
		$all_defs = array();

		foreach ( $this->mq_config as $size ) {
			$all_defs[] = $size['def']; // Used to exclude in hider media queries
			$sizes_min[] = $size['min']; // Used to exclude in hider media queries
			$sizes_max[] = $size['max']; // Used to exclude in hider media queries
		}

		$all_defs_count = count( $all_defs );

		echo '/********** Media Queries **********/' . $this->minify_2 . $this->minify;

		// CSS attribute wildcard selectors
		$w_count = 2;
		foreach ( $all_defs as $def ) {
			$seperator = ( ($all_defs_count) == $w_count-1 ) ? ' ' : ', ';
			//echo '.' . $def . '-' . $prepend;
			echo 'div[class*="' . $def . '-' . $prepend . '"]' . $seperator;
			$w_count = ( $def != $size['def'] ) ? $w_count+1 : $w_count;
		}
		echo '{ float:left; }' . $this->minify_2;

		foreach ( $this->mq_config as $size ) {

			$units = ( !$size[units] && $size[units] == 'px' ) ? 'px' : substr( $size[units], 0, 2 );
			$min = ( !$size[min] && !is_numeric($size[min]) ) ? '' : 'and ( min-width:' . $size[min] . $units . ' )';
			$max = ( !$size[max] && !is_numeric($size[max]) ) ? '' : 'and ( max-width:' . $size[max] . $units . ' )';
			$size_queries = ( !empty($min) && !empty($max) ) ? $min . ' ' . $max : $min . $max;

			// Open media query
			echo '/* ' . $size['def'] . ': ' . $size['note'] . ' */' . $this->minify
			. '@media screen ' . $size_queries . ' {' . $this->minify;

			// Keep span
			echo ' span.' . $size['def'] . '-keep { display:block; }' . $this->minify;

			// Specific breakpoint hider
			echo ' .' . $size['def'] . '-hide { display: none; }' . $this->minify;

			// Other breakpoint hiders
			$o_count = 2;
			foreach ( $all_defs as $def ) {
				$prepend = ( ($all_defs_count) == $o_count ) ? ' ' : ',';
				echo ( $def != $size['def'] ) ? ' .' . $def . '-only' . $prepend : '';
				$o_count = ( $def != $size['def'] ) ? $o_count+1 : $o_count;
			}
			echo '{ display: none; }' . $this->minify;

			echo ' /***** Boxes *****/' . $this->minify;

			// Box size loops
			foreach ( $this->mq_box_sizes as $size_r ) {
				if ( intval($size_r) < 101 ) {
					for ( $limit=1; $limit < $size_r || $limit == 1; $limit++ ) {

						echo ' .' . $size['def'] . '-' . $limit . '-' . $size_r;

						for ( $limit_def=0; $limit_def < ($all_defs_count); $limit_def++ ) {
							echo ( $all_defs[$limit_def] <= $size['def'] ) ? ', .' . $all_defs[$limit_def] . '-min-' . $limit . '-' . $size_r : '';
						}

						echo ' { width:' . ( 100/$size_r ) * $limit . '%; ';
						//echo ( $size_r == 1 ) ? '' : 'float:left; ';
						echo '}' . $this->minify;

					}
				}
			}

			echo ' /***** Columns *****/' . $this->minify;

			// Column size loops
			foreach ( $this->mq_column_sizes as $size_c ) {
				if ( intval($size_c) < 101 ) {
					for ( $limit=1; $limit < $size_c || $limit == 1; $limit++ ) {

						echo ' .' . $size['def'] . '-c-' . $limit . '-' . $size_c;

						for ( $limit_def=0; $limit_def < ($all_defs_count); $limit_def++ ) {
							echo ( $all_defs[$limit_def] <= $size['def'] ) ? ', .' . $all_defs[$limit_def] . '-c-min-' . $limit . '-' . $size_c : '';
						}

						$width = ( ((100 - ($size_c - 1) * $this->columns_gutter) / $size_c) * $limit )
						+ ( $this->columns_gutter * ($limit - 1) );

						echo ' { width:' . $width . '%; ';
						//echo ( $size_c == 1 ) ? '' : 'float:left; ';
						echo '}' . $this->minify;

					}

				}
			}

			// Close media query
			echo '}' . $this->minify_2;

		}

	}

}
?>