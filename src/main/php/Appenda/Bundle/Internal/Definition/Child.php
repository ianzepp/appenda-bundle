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

require_once "Appenda/Bundle/Internal/Definition/Abstract.php";

class Appenda_Bundle_Internal_Definition_Child extends Appenda_Bundle_Internal_Definition_Abstract {
	private $parentName;
	
	/**
	 * Child (string $parentName)
	 * Child (string $parentName, string $beanClassName, array $constructorArgumentValues, array $propertyValues)
	 * Child (string $parentName, array $constructorArgumentValues, array $propertyValues)
	 * Child (string $parentName, array $propertyValues)
	 *
	 * @param string $parentName
	 */
	public function __construct ($parentName, $arg1 = null, $arg2 = null, $arg3 = null) {
		// Parent name is always a requirement
		$this->setParentName ($parentName);
		
		// What about the remaining args?
		switch (count (func_get_args ())) {
			case 2 :
				$this->setPropertyValues ($arg1);
				break;
			
			case 3 :
				$this->setConstructorArgumentValues ($arg1);
				$this->setPropertyValues ($arg2);
				break;
			
			case 4 :
				$this->setBeanClassName ($arg1);
				$this->setConstructorArgumentValues ($arg2);
				$this->setPropertyValues ($arg3);
				break;
		}
	}
	
	/**
	 * @return string
	 */
	public function getParentName () {
		return $this->parentName;
	}
	
	/**
	 * @param string $parentName
	 */
	public function setParentName ($parentName) {
		assert (is_string ($parentName));
		$this->parentName = $parentName;
	}

}

