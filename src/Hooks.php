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

use MediaWiki\Hook\ParserFirstCallInitHook;
use Parser;

class Hooks implements ParserFirstCallInitHook {
	/**
	 * This hook is called when the parser initialises for the first time.
	 * It declares the <svg> tag.
	 *
	 * @since 1.35
	 *
	 * @param Parser $parser Parser object being initialised
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setHook( 'svg', [ 'MediaWiki\Extension\InlineSVG\InlineSVG', 'renderSVG' ] );
	}
}
