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
			],
			[
				// Attribute values must be escaped when the <svg> tag is rebuilt (D3):
				<<<'CASE_2_INPUT'
					<rect width="10" height="10"/>
				CASE_2_INPUT,
				[
					'data-title' => 'A "quoted" title'
				],
				<<<'CASE_2_EXPECTED'
					<svg data-title="A &quot;quoted&quot; title">
						<rect width="10" height="10"/>
					</svg>
					CASE_2_EXPECTED,
			],
			[
				// Any leading XML declaration must be stripped, not only the narrow
				// version/encoding form (REVIEW.md D15):
				<<<'CASE_3_INPUT'
					<?xml version="1.0" encoding="UTF-8" standalone="no"?>
					<rect width="10" height="10"/>
				CASE_3_INPUT,
				[
					'width' => '10',
					'height' => '10'
				],
				<<<'CASE_3_EXPECTED'
					<svg width="10" height="10">
						<rect width="10" height="10"/>
						</svg>
				CASE_3_EXPECTED,
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
		$result = InlineSVG::renderSVG(
			$input,
			$args,
			$this->createMock( Parser::class ),
			$this->createMock( PPFrame::class )
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 0, $result );
		$this->assertSame( 'nowiki', $result['markerType'] ?? null );

		$normalize = static function ( string $xml ): string {
			// Drop the XML declaration that Sanitizer::sanitize() may prepend
			// (removeXMLTag() is set to true, this is a safety net), then collapse
			// whitespace, so that the comparison is insensitive to indentation:
			$xml = (string)preg_replace( '/^\s*<\?xml[^>]*\?>\s*/', '', $xml );
			return trim( (string)preg_replace( '/\s+/', ' ', $xml ) );
		};
		$this->assertSame( $normalize( $expected ), $normalize( $result[0] ) );
	}

}
