<?php
/**
 * Implements Special:Randomunapprovedpage
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup SpecialPage
 * @author Jos Visser <goleztrol@hotmail.com>
 */

/**
 * Special page to direct the user to a random never stabilized page.
 *
 * @ingroup SpecialPage
 */
class SpecialRandomunapprovedpage extends RandomPage {
	function __construct(){
		parent::__construct( 'Randomunapprovedpage' );
	}

  protected function getQueryInfo( $randstr ) {
		$redirect = $this->isRedirect() ? 1 : 0;

		return array(
			'tables' => array( 'p' => 'page', 'f' => 'flaggedpages' ),
			'fields' => array( 'page_title', 'page_namespace' ),
			'conds' => array_merge( array(
				'p.page_namespace' => $this->getNamespaces(),
				'p.page_is_redirect' => $redirect,
				'p.page_random >= ' . $randstr,
        'f.fp_page_id IS NULL'
			), $this->extra ),
			'options' => array(
				'ORDER BY' => 'page_random',
				'USE INDEX' => array('p' => 'page_random'),
				'LIMIT' => 1,
			),
			'join_conds' => array('f' => array('LEFT JOIN', 'f.fp_page_id = p.page_id AND f.fp_quality = 1'))
		);
	}
}
