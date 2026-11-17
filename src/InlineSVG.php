<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 * @author Alexander Mashin
 */

namespace MediaWiki\Extension\InlineSVG;

use enshrined\svgSanitize\Sanitizer;
use Parser;
use PPFrame;

class InlineSVG {
	/** @var Sanitizer $sanitizer An instance of Sanitizer. */
	private static $sanitizer;

	/**
	 * @param string $input SVG tag contents.
	 * @param array $args SVG tag attributes.
	 * @param Parser $parser The Parser object.
	 * @param PPFrame $frame The Frame object.
	 * @return array The sanitized SVG tag.
	 */
	public static function renderSVG( string $input, array $args, Parser $parser, PPFrame $frame ): array {
		// Remove any leading XML declaration (an opening `<` + `?xml` PI up to its
		// closing PI marker), whatever its exact attributes (e.g. Inkscape's
		// version/encoding/standalone trio), and an UTF-8 BOM, if any:
		$svg = (string)preg_replace( '/^(?:\xEF\xBB\xBF)?\s*<\?xml\s.*?\?>\s*/s', '', $input );

		// Rebuild the <svg> opening tag, unless the fragment already starts with one:
		if ( !preg_match( '/^\s*<svg[^>]*>/is', $svg ) ) {
			$assignments = [];
			foreach ( $args as $attribute => $value ) {
				$assignments[] = htmlspecialchars( $attribute, ENT_QUOTES ) . '="'
					. htmlspecialchars( $value, ENT_QUOTES ) . '"';
			}
			$svg = '<svg ' . implode( ' ', $assignments ) . '>' . $svg . '</svg>';
		}

		// Sanitize SVG with enshrined/svg-sanitize:
		if ( !self::$sanitizer ) {
			self::$sanitizer = new Sanitizer();
		}
		self::$sanitizer->removeRemoteReferences( true );
		// Don't prepend the XML declaration to the parser output:
		self::$sanitizer->removeXMLTag( true );
		$sanitized = self::$sanitizer->sanitize( $svg );
		$issues = self::$sanitizer->getXmlIssues();

		// Return the sanitized SVG or an error message:
		if ( $sanitized ) {
			return [ $sanitized, 'markerType' => 'nowiki' ];
		} else {
			$message = wfMessage( 'inlinesvg-error' )->inContentLanguage()->escaped();
			if ( $issues ) {
				$details = array_map( static function ( $issueMessage ): string {
					return htmlspecialchars( (string)$issueMessage, ENT_QUOTES );
				}, array_column( $issues, 'message' ) );
				$message .= '. ' . implode( ', ', $details );
			}
			return [ '<span class="error">' . $message . '</span>' ];
		}
	}
}
