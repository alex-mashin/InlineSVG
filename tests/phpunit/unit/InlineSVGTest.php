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

use MediaWikiUnitTestCase;
use Parser;
use PPFrame;

class InlineSVGTest extends MediaWikiUnitTestCase {
	/** @var Parser $parserMock */
	private $parserMock;
	/** @var PPFrame $frameMock */
	private $frameMock;

	/**
	 * @return void
	 */
	protected function setUp(): void {
		$this->parserMock = $this->getMockBuilder( Parser::class )
			->disableOriginalConstructor()
			->getMock();
		$this->frameMock = $this->getMockBuilder( PPFrame::class )
			->disableOriginalConstructor()
			->getMock();
		parent::setUp();
	}

	/**
	 * Data provider to test InlineSVGTest::renderSVG().
	 * @return array
	 */
	public function renderSVGProvider(): array {
		return [
			[
				<<<'CASE_1_INPUT'
					<rect fill="#fff" width="9" height="3"/>
					<rect fill="#d52b1e" y="3" width="9" height="3"/>
					<rect fill="#0039a6" y="2" width="9" height="2"/>
				CASE_1_INPUT,
				[
					'xmlns' => 'http://www.w3.org/2000/svg',
					'viewBox' => '0 0 9 6',
					'width' => '900', 'height' => '600'
				],
				<<<'CASE_1_EXPECTED'
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 6" width="900" height="600">
					<rect fill="#fff" width="9" height="3"/>
					<rect fill="#d52b1e" y="3" width="9" height="3"/>
					<rect fill="#0039a6" y="2" width="9" height="2"/>
				</svg>
				CASE_1_EXPECTED,
			]
		];
	}

	/**
	 * Test InlineSVGTest::renderSVG().
	 * @covers InlineSVG::renderSVG
	 * @dataProvider renderSVGProvider
	 * @param string $input
	 * @param array $args
	 * @param string $expected
	 * @return void
	 */
	public function testRenderSVG( string $input, array $args, string $expected ): void {
		$actual = InlineSVG::renderSVG( $input, $args, $this->parserMock, $this->frameMock );
		$this->assertEquals( $expected, $actual );
	}

}
