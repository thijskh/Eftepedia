<?php
/**
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
 * @ingroup Pager
 */

/**
 * @ingroup Pager
 */
use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IResultWrapper;
use Wikimedia\Rdbms\FakeResultWrapper;

class ReaderImageListPager extends ImageListPager {

    function __construct( IContextSource $context, $userName = null, $search = '',
        $including = false, $showAll = false
    ) {
        $this->setLimit(60);
	parent::__construct( $context, $username, $search, $including, $showAll );
    }
    function formatValue( $field, $value ) {
        if($field === 'thumb') return parent::formatValue($field, $value);
        return '';
    }

    function getQueryInfo() {
        $qi = $this->getQueryInfoReal( $this->mTableName );
        $qi['tables']['gw_imagelinks'] = 'imagelinks';
        $qi['tables']['gwl_page'] = 'page';
        $qi['fields'] = ['DISTINCT img_name AS thumb', 'img_timestamp'];

        $qi['join_conds']['gw_imagelinks'] = ['JOIN', 'img_name = il_to'];
        $qi['join_conds']['gwl_page'] = ['JOIN', 'il_from=page_id AND il_from_namespace=page_namespace'];
        // Only show images for which the page has also been updated,
        // i.e.: the revision that added them is actually approved
        $qi['conds'] = 'img_timestamp <= page_touched';

        return $qi;
    }

    function isFieldSortable( $field ) {
        return false;
    }

    /**
     * @return array
     */
    function getFieldNames() {
        return $this->mFieldNames = [
            'thumb' => '',
            'img_timestamp' =>'',
        ];
    }
}
