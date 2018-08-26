<?php

namespace MediaWiki\Extension\MetaStore;

use OutputPage, Parser, Skin;

/**
 * Class MW_EXT_Progress
 */
class MW_EXT_Progress {

	/**
	 * Register tag function.
	 *
	 * @param Parser $parser
	 *
	 * @return bool
	 * @throws \MWException
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'progress', [ __CLASS__, 'onRenderTag' ] );

		return true;
	}

	/**
	 * Render tag function.
	 *
	 * @param Parser $parser
	 * @param string $value
	 * @param string $max
	 * @param string $width
	 *
	 * @return null|string
	 */
	public static function onRenderTag( Parser $parser, $value = '', $max = '', $width = '' ) {
		// Argument: value.
		$getValue = MW_EXT_Kernel::outClear( $value ?? '' ?: '' );
		$outValue = $getValue;

		// Argument: max.
		$getMax = MW_EXT_Kernel::outClear( $max ?? '' ?: '' );
		$outMax = $getMax;

		// Argument: width.
		$getWidth = MW_EXT_Kernel::outClear( $width ?? '' ?: '50' );
		$outWidth = $getWidth;

		// Check progress value, set error category.
		if ( ! ctype_digit( $getValue ) || ! ctype_digit( $getMax ) || ! ctype_digit( $getWidth ) || $getValue > $getMax ) {
			$parser->addTrackingCategory( 'mw-ext-progress-error-category' );

			return null;
		}

		// Set progress status.
		if ( $getValue > 0 && $getValue <= 33 ) {
			$outStatus = '00';
		} elseif ( $getValue > 33 && $getValue <= 99 ) {
			$outStatus = '01';
		} elseif ( $getValue == 100 ) {
			$outStatus = '02';
		} else {
			$outStatus = '';
		}

		// Out HTML.
		$outHTML = '<div style="width: ' . $outWidth . '%;" class="mw-ext-progress navigation-not-searchable"><div class="mw-ext-progress-body">';
		$outHTML .= '<div class="mw-ext-progress-count mw-ext-progress-count-status-' . $outStatus . '">' . $outValue . '%</div>';
		$outHTML .= '<div class="mw-ext-progress-content">';
		$outHTML .= '<progress class="mw-ext-progress-bar mw-ext-progress-bar-status-' . $outStatus . '" value="' . $outValue . '" max="' . $outMax . '"></progress>';
		$outHTML .= '</div></div></div>';

		// Out parser.
		$outParser = $parser->insertStripItem( $outHTML, $parser->mStripState );

		return $outParser;
	}

	/**
	 * Load resource function.
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 *
	 * @return bool
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$out->addModuleStyles( [ 'ext.mw.progress.styles' ] );

		return true;
	}
}
