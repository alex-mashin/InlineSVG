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

namespace MediaWiki\Extension\InlineSVG\Tests;

use MediaWiki\Extension\InlineSVG\Hooks;
use Parser;

/**
 * @coversDefaultClass Hooks
 */
class HooksTest extends \MediaWikiUnitTestCase {
	/** @var Parser $parserMock A mock Parser object. */
	private $parserMock;

	/**
	 * @return void
	 */
	protected function setUp(): void {
		$this->parserMock = $this->getMockBuilder( Parser::class )
			->disableOriginalConstructor()
			->getMock();
		parent::setUp();
	}

	/**
	 * @covers ::onParserFirstCallInit
	 * @return void
	 */
	public function testOnParserFirstCallInit(): void {
		( new Hooks )->onParserFirstCallInit( $this->parserMock );
	}
}
