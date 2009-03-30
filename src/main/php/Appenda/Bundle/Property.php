<?php

/**
 * The MIT License
 * 
 * Copyright (c) 2009 Ian Zepp
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * @author Ian Zepp
 * @package 
 */

require_once "Appenda/Bundle/Exception.php";
require_once "Appenda/Property/Set.php";

/**
 * Enter description here...
 *
 * @method string getConvertedValue() getConvertedValue()
 * @method string getName() getName()
 * @method string getSource() getSource()
 * @method string getType() getType()
 * @method string getValue() getValue()
 * @method void setConvertedValue() setConvertedValue(string $convertedValue)
 * @method void setName() setName(string $name)
 * @method void setSource() setSource(string $source)
 * @method void setType() setType(string $type)
 * @method void setValue() setValue(string $value)
 * 
 */
class Appenda_Bundle_Property extends Appenda_Property_Set {
	/**
	 * Enter description here...
	 *
	 * @var string
	 */
	const ReferenceType = "ref";
	
	/**
	 * Enter description here...
	 *
	 * @var string
	 */
	const ValueType = "value";
	
	/**
	 * Enter description here...
	 *
	 * @param Appenda_Bundle_Property|string $arg0
	 * @param mixed $arg1
	 */
	public function __construct ($arg0, $arg1 = null) {
		// Register properties
		$this->register ("ConvertedValue", "String");
		$this->register ("Name", "String");
		$this->register ("Source", "String");
		$this->register ("Type", "String");
		$this->register ("Value", "String");
		
		// Determine the correct constructor
		if (is_string ($arg0) && !is_null ($arg1)) {
			return $this->__constructFromName ($arg0, $arg1);
		}
		
		if ($arg0 instanceof self && is_null ($arg1)) {
			return $this->__constructCopy ($arg0);
		}
		
		if ($arg0 instanceof self) {
			return $this->__constructCopyNew ($arg0, $arg1);
		}
		
		throw new Appenda_Bundle_Exception ("Invalid constructor arguments");
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	private function __constructFromName ($name, $value) {
		$this->setName ($name);
		$this->setValue ($value);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param Appenda_Bundle_Property $copy
	 */
	private function __constructCopy (Appenda_Bundle_Property $copy) {
		$this->setConvertedValue ($copy->getConvertedValue ());
		$this->setName ($copy->getName ());
		$this->setSource ($copy->getSource ());
		$this->setValue ($copy->getValue ());
	}
	
	/**
	 * Enter description here...
	 *
	 * @param Appenda_Bundle_Property $copy
	 * @param mixed $convertedValue
	 */
	private function __constructCopyNew (Appenda_Bundle_Property $copy, $convertedValue) {
		$this->setConvertedValue ($convertedValue);
		$this->setName ($copy->getName ());
		$this->setSource ($copy->getSource ());
		$this->setValue ($copy->getValue ());
	}
	
	/**
	 * Enter description here...
	 *
	 * @return boolean
	 */
	public function isConverted () {
		return !is_null ($this->getConvertedValue ());
	}
}