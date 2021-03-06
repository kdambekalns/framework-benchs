<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2010, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace lithium\tests\cases\analysis;

use \ReflectionClass;
use \ReflectionMethod;
use \lithium\core\Libraries;
use \lithium\analysis\Inspector;

class InspectorTest extends \lithium\test\Unit {

	public $test = 'foo';

	public static $test2 = 'bar';

	protected $_test = 'baz';

	/**
	 * Tests that basic method lists and information are queried properly.
	 *
	 * @return void
	 */
	public function testBasicMethodInspection() {
		$class = '\lithium\analysis\Inspector';
		$parent = '\lithium\core\StaticObject';

		$expected = array_diff(get_class_methods($class), get_class_methods($parent));
		$result = array_keys(Inspector::methods($class, 'extents'));
		$this->assertEqual(array_intersect($result, $expected), $result);

		$result = array_keys(Inspector::methods($class, 'extents', array(
			'self' => true, 'public' => true
		)));
		$this->assertEqual($expected, $result);

		$result = Inspector::methods($class, 'ranges');
	}

	public function testMethodInspection() {
		$result = Inspector::methods($this, null);
		$this->assertTrue($result[0] instanceof ReflectionMethod);

		$result = Inspector::info('lithium\core\Object::_init()');
		$expected = '_init';
		$this->assertEqual($expected, $result['name']);

		$expected = 'void';
		$this->assertEqual($expected, $result['tags']['return']);
	}

	/**
	 * Tests that the range of executable lines of this test method is properly calculated.
	 * Recursively meta.
	 *
	 * @return void
	 */
	public function testMethodRange() {
		$result = Inspector::methods(__CLASS__, 'ranges', array('methods' => __FUNCTION__));
		$expected = array(__FUNCTION__ => array(__LINE__ - 1, __LINE__, __LINE__ + 1));
		$this->assertEqual($expected, $result);
	}

	/**
	 * Gets the executable line numbers of this file based on a manual entry of line ranges. Will
	 * need to be updated manually if this method changes.
	 *
	 * @return void
	 */
	public function testExecutableLines() {
		do {
			// These lines should be ignored
		} while (false);

		$result = Inspector::executable($this, array('methods' => __FUNCTION__));
		$expected = array(__LINE__ - 1, __LINE__, __LINE__ + 1);
		$this->assertEqual($expected, $result);
	}

	/**
	 * Tests reading specific line numbers of a file.
	 *
	 * @return void
	 */
	public function testLineIntrospection() {
		$result = Inspector::lines(__FILE__, array(__LINE__ - 1));
		$expected = array(__LINE__ - 2 => "\tpublic function testLineIntrospection() {");
		$this->assertEqual($expected, $result);

		$result = Inspector::lines(__CLASS__, array(16));
		$expected = array(16 => 'class InspectorTest extends \lithium\test\Unit {');
		$this->assertEqual($expected, $result);

		$this->expectException('/Missing argument 2/');
		$this->assertNull(Inspector::lines('\lithium\core\Foo'));
		$this->assertNull(Inspector::lines(__CLASS__, array()));
	}

	/**
	 * Tests getting a list of parent classes from an object or string class name.
	 *
	 * @return void
	 */
	public function testClassParents() {
		$result = Inspector::parents($this);
		$this->assertEqual('lithium\test\Unit', current($result));

		$result2 = Inspector::parents(__CLASS__);
		$this->assertEqual($result2, $result);

		$this->assertFalse(Inspector::parents('lithium\core\Foo', array('autoLoad' => false)));
	}

	public function testClassFileIntrospection() {
		$result = Inspector::classes(array('file' => __FILE__));
		$this->assertEqual(array(__CLASS__ => __FILE__), $result);

		$result = Inspector::classes(array('file' => __FILE__, 'group' => 'files'));
		$this->assertEqual(1, count($result));
		$this->assertEqual(__FILE__, key($result));

		$result = Inspector::classes(array('file' => __FILE__, 'group' => 'foo'));
		$this->assertEqual(array(), $result);
	}

	/**
	 * Tests that names of classes, methods, properties and namespaces are parsed properly from
	 * strings.
	 *
	 * @return void
	 */
	public function testTypeDetection() {
		$this->assertEqual('namespace', Inspector::type('\lithium\util'));
		$this->assertEqual('namespace', Inspector::type('\lithium\analysis'));
		$this->assertEqual('class', Inspector::type('\lithium\analysis\Inspector'));
		$this->assertEqual('property', Inspector::type('Inspector::$_classes'));
		$this->assertEqual('method', Inspector::type('Inspector::type'));
		$this->assertEqual('method', Inspector::type('Inspector::type()'));
	}

	/**
	 * Tests getting reflection information based on a string identifier.
	 *
	 * @return void
	 */
	public function testIdentifierIntrospection() {
		$result = Inspector::info(__METHOD__);
		$this->assertEqual(array('public'), $result['modifiers']);
		$this->assertEqual(__FUNCTION__, $result['name']);

		$this->assertNull(Inspector::info('\lithium\util'));

		$result = Inspector::info('\lithium\analysis\Inspector');
		$this->assertTrue(strpos(
			str_replace('\\', '/', $result['file']),
			'lithium/analysis/Inspector.php'
		));
		$this->assertEqual('lithium\analysis', $result['namespace']);
		$this->assertEqual('Inspector', $result['shortName']);

		$result = Inspector::info('\lithium\analysis\Inspector::$_methodMap');
		$this->assertEqual('_methodMap', $result['name']);

		$expected = 'Maps reflect method names to result array keys.';
		$this->assertEqual($expected, $result['description']);
		$this->assertEqual(array('var' => 'array'), $result['tags']);

		$result = Inspector::info('\lithium\analysis\Inspector::info()', array(
			'modifiers', 'namespace', 'foo'
		));
		$this->assertEqual(array('modifiers', 'namespace'), array_keys($result));

		$this->assertNull(Inspector::info('\lithium\analysis\Inspector::$foo'));
	}

	public function testClassDependencies() {
		$expected = array(
			'Exception', 'ReflectionClass', 'ReflectionException',
			'lithium\\core\\Libraries', 'lithium\\util\\Collection'
		);
		$result = Inspector::dependencies($this->subject());
		$this->assertEqual($expected, $result);

		$result = Inspector::dependencies($this->subject(), array('type' => 'static'));
		$this->assertEqual($expected, $result);
	}

	/**
	 * Tests that class and namepace names which are equivalent in a case-insensitive search still
	 * match properly.
	 *
	 * @return void
	 */
	public function testCaseSensitiveIdentifiers() {
		$result = Inspector::type('lithium\storage\Cache');
		$expected = 'class';
		$this->assertEqual($expected, $result);

		$result = Inspector::type('lithium\storage\cache');
		$expected = 'namespace';
		$this->assertEqual($expected, $result);
	}

	/**
	 * Tests getting static and non-static properties from various types of classes.
	 *
	 * @return void
	 */
	public function testGetClassProperties() {
		$result = array_map(
			function($property) { return $property['name']; },
			Inspector::properties(__CLASS__)
		);
		$expected = array('test', 'test2');
		$this->assertEqual($expected, $result);

		$result = array_map(
			function($property) { return $property['name']; },
			Inspector::properties(__CLASS__, array('public' => false))
		);
		$expected = array('test', 'test2', '_test');
		$this->assertEqual($expected, $result);

		$result = Inspector::properties(__CLASS__);
		$expected = array(
			array(
				'modifiers' => array('public'),
				'docComment' => false,
				'name' => 'test',
				'value' => null
			),
			array(
				'modifiers' => array('public', 'static'),
				'docComment' => false,
				'name' => 'test2',
				'value' => 'bar'
			)
		);
		$this->assertEqual($expected, $result);
	}
}

?>