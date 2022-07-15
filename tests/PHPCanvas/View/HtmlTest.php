<?php // $Id: HtmlTest.php 742 2018-03-02 21:22:58Z dev $

use PHPCanvas\View\Html;
use PHPCanvas\View\HtmlInterface;

class HtmlTest extends PHPUnit_Framework_TestCase
{
	protected $Html;

	public function setUp()
	{
		$this->Html = new Html();
	}

	public function testCreate()
	{
		$this->assertInstanceOf(HtmlInterface::class, $this->Html);
	}

	public function testSetAttributes()
	{
		$atts = [
			'key' => 'value',
			'boolean_true' => true,
			'boolean_false' => false,
			'empty' => null,
			'data-key' => 'data-value',
			'onkey' => 'onkey-value',
			'esc' => '£'
		];

		$set_attributes = $this->Html->set_attributes($atts);

		$test = 'key="value" boolean_true ' .
			'data-key="data-value" onkey="onkey-value" ' .
			'esc="&pound;"';

		$this->assertEquals($test, $set_attributes);
	}

	public function testSetInputText()
	{
		$atts = [
			'size' => '32',
			'onclick' => "this.value='test'",
			'placeholder' => 'Example',
		];

		$type = 'text';
		$name = 'test';
		$value = 'test-value';

		$input = $this->Html->input($type, $name, $value, $atts);

		$test = '<input type="text" name="test" value="test-value" ' .
			'size="32" onclick="this.value=\'test\'" placeholder="Example" />';

		$this->assertEquals($test, $input);
	}

	public function testSetCheckbox()
	{
		$atts = [
			'key' => 'value',
		];

		$name = 'test';
		$value = 'test-value';
		$option_value = 'test-value';

		$checkbox = $this->Html->checkbox($name, $value, $option_value, $atts);

		$test = '<input type="checkbox" name="test" value="test-value" ' .
			'key="value" checked />';

		$this->assertEquals($test, $checkbox);
	}

	public function testSetCheckboxSelect()
	{
		$atts = [
			'key' => 'value',
			'options' => [
				'one' => 'One',
				'two' => 'Two',
				'three' => 'Three',
			]
		];

		$name = 'test';
		$value = 'three';

		$select = $this->Html->select($name, $value, $atts);

		$test = '<select name="test" key="value">' .
			'<option value="one">One</option>' .
			'<option value="two">Two</option>' .
			'<option value="three" selected>Three</option>' .
			'</select>';

		$this->assertEquals($test, $select);
	}

	public function testSetTextArea()
	{
		$atts = [
			'key' => 'value',
			'rows' => '10',
		];

		$name = 'test';
		$value = "This is long text\n\nThis is a new line";

		$set_textarea = $this->Html->textarea($name, $value, $atts);

		$test = '<textarea name="test" key="value" rows="10">' .
			"This is long text\n\nThis is a new line" .
			'</textarea>';

		$this->assertEquals($test, $set_textarea);
	}
}