<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2010, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace lithium\tests\mocks\data\model;

use \lithium\data\model\Document;

class MockDocumentMultipleKey extends \lithium\data\Model {

	protected $_meta = array(
		'key' => array('id', 'rev'),
		'name' => null,
		'title' => null,
		'class' => null,
		'source' => null,
		'connection' => 'test',
		'initialized' => false
	);

	public static function __init($options = array()) {}

	public static function schema($field = null) {
		return array();
	}

	public function ret($record, $param1 = null, $param2 = null) {
		if ($param2) {
			return $param2;
		}
		if ($param1) {
			return $param1;
		}
		return null;
	}

	public static function find($type = 'all', $options = array()) {
		switch ($type) {
			case 'first':
				return new Document(array('items' =>
					array('id' => 2, 'rev' => '1-1', 'name' => 'Two', 'content' => 'Lorem ipsum two')
				));
			break;
			case 'all':
			default :
				return new Document(array('items' => array(
					array('id' => 1, 'rev' => '1-1','name' => 'One', 'content' => 'Lorem ipsum one'),
					array('id' => 2, 'rev' => '1-1','name' => 'Two', 'content' => 'Lorem ipsum two'),
					array('id' => 3, 'rev' => '1-1', 'name' => 'Three', 'content' => 'Lorem ipsum three')
				)));
			break;
		}
	}
}

?>