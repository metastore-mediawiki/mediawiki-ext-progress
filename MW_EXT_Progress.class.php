<?php

namespace MediaWiki\Extension\MW_EXT_Progress;

use OutputPage, Parser, Skin;

/**
 * Class MW_EXT_Progress
 * ------------------------------------------------------------------------------------------------------------------ */
class MW_EXT_Progress {

	/**
	 * Clear DATA (escape html).
	 *
	 * @param $string
	 *
	 * @return string
	 * -------------------------------------------------------------------------------------------------------------- */

	private static function clearData( $string ) {
		$outString = htmlspecialchars( trim( $string ), ENT_QUOTES );

		return $outString;
	}

	/**
	 * Convert DATA (replace space & lower case).
	 *
	 * @param $string
	 *
	 * @return string
	 * -------------------------------------------------------------------------------------------------------------- */

	private static function convertData( $string ) {
		$outString = mb_strtolower( str_replace( ' ', '-', $string ), 'UTF-8' );

		return $outString;
	}

	/**
	 * Get MediaWiki progress.
	 *
	 * @param $string
	 *
	 * @return string
	 * -------------------------------------------------------------------------------------------------------------- */

	private static function getMsgText( $string ) {
		$outString = wfMessage( 'mw-ext-progress-' . $string )->inContentLanguage()->text();

		return $outString;
	}

	/**
	 * Register tag function.
	 *
	 * @param Parser $parser
	 *
	 * @return bool
	 * @throws \MWException
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'progress', __CLASS__ . '::onRenderTag' );

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
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onRenderTag( Parser $parser, $value = '', $max = '', $width = '' ) {
		// Argument: value.
		$getValue = self::clearData( $value ?? '' ?: '' );
		$outValue = $getValue;

		// Argument: max.
		$getMax = self::clearData( $max ?? '' ?: '' );
		$outMax = $getMax;

		// Argument: width.
		$getWidth = self::clearData( $width ?? '' ?: '50' );
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
		$outHTML = '<div style="width: ' . $outWidth . '%;" class="mw-ext-progress"><div class="mw-ext-progress-body">';
		$outHTML .= '<div class="mw-ext-progress-count mw-ext-progress-count-status-' . $outStatus . '">' . $outValue . '%</div>';
		$outHTML .= '<div class="mw-ext-progress-content">';
		$outHTML .= '<progress class="mw-ext-progress-bar mw-ext-progress-bar-status-' . $outStatus . '" value="' . $outValue . '" max="' . $outMax . '"></progress>';
		$outHTML .= '</div>';
		$outHTML .= '</div></div>';

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
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$out->addModuleStyles( [ 'ext.mw.progress.styles' ] );

		return true;
	}
}