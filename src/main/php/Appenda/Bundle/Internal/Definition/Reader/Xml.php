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

require_once "Appenda/Bundle/Internal/Definition/Reader/Abstract.php";
require_once "Appenda/Bundle/Internal/Definition/Child.php";
require_once "Appenda/Bundle/Internal/Definition/Root.php";
require_once "Appenda/Bundle/Exception.php";
require_once "Appenda/Bundle/Property.php";

class Appenda_Bundle_Internal_Definition_Reader_Xml extends Appenda_Bundle_Internal_Definition_Reader_Abstract {
	/**
	 * Enter description here...
	 *
	 * @var string
	 */
	const BEAN_NS_URI = "http://www.springframework.org/schema/beans";
	
	/**
	 * @see Appenda_Bundle_Internal_Definition_Reader_Abstract::loadBeanDefinitions()
	 *
	 * @param string $location
	 * @return integer
	 */
	public function loadBeanDefinitions ($location) {
		assert (is_string ($location));
		
		$beanDefinitionCount = 0;
		$xml = simplexml_load_string (file_get_contents ($location, true));
		$xml->registerXPathNamespace ("beans", self::BEAN_NS_URI);
		
		foreach ($xml->xpath ("//beans:bean") as $beanDefinitionXml) {
			$beanDefinitionCount++;
			$beanDefinition = $this->extractBeanDefinition ($beanDefinitionXml);
			$beanName = $this->extractBeanName ($beanDefinitionXml);
			$this->getBeanRegistry ()->registerBeanDefinition ($beanName, $beanDefinition);
		}
		
		return $beanDefinitionCount;
	}
	
	/**
	 * Extract the bean definition from the xml segment
	 *
	 * @param SimpleXMLElement $beanDefinitionXml
	 * @return Appenda_Bundle_Definition
	 */
	protected function extractBeanDefinition (SimpleXMLElement $beanDefinitionXml) {
		$beanName = $this->extractBeanName ($beanDefinitionXml);
		
		if ($beanDefinitionXml->{"parent"}) {
			$beanDefinition = new Appenda_Bundle_Internal_Definition_Child ();
			$beanDefinition->setParentName (trim ((string) $beanDefinitionXml->{"parent"}));
		} else {
			$beanDefinition = new Appenda_Bundle_Internal_Definition_Root ();
		}
		
		// Process & extract the attributes
		if ($beanDefinitionXml->{"class"}) {
			$beanDefinition->setBeanClass (trim ((string) $beanDefinitionXml->{"class"}));
		}
		
		if ($beanDefinitionXml->{"factory-bean"}) {
			$beanDefinition->setFactoryBeanName (trim ((string) $beanDefinitionXml->{"factory-bean"}));
		}
		
		if ($beanDefinitionXml->{"factory-method"}) {
			$beanDefinition->setFactoryBeanMethod (trim ((string) $beanDefinitionXml->{"factory-method"}));
		}
		
		if ($beanDefinitionXml->{"scope"}) {
			$beanDefinition->setScope (trim ((string) $beanDefinitionXml->{"scope"}));
		}
		
		// Process & extract the constructor-args
		$properties = $this->extractProperties ($beanDefinitionXml->{"constructor-arg"});
		$beanDefinition->setConstructorArgumentValues ($properties);
		
		// Process & extract the properties
		$properties = $this->extractProperties ($beanDefinitionXml->{"property"});
		$beanDefinition->setPropertyValues ($properties);
		
		// Register
		$this->getBeanRegistry ()->registerBeanDefinition ($beanName, $beanDefinition);
		
		// Add aliases
		$beanAliases = trim ((string) $beanDefinitionXml->{"name"});
		$beanAliases = str_ireplace (",", " ", $beanAliases);
		$beanAliases = str_ireplace (";", " ", $beanAliases);
		$beanAliases = array_map ("trim", explode (" ", $beanAliases));
		
		foreach ($beanAliases as $beanAlias) {
			if (!empty ($beanAlias)) {
				$this->getBeanRegistry ()->registerAlias ($beanName, $beanAlias);
			}
		}
		
		// Done
		return $beanDefinition;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param SimpleXMLElement $elementXml
	 * @return array(Appenda_Bundle_Property)
	 */
	protected function extractProperties (SimpleXMLElement $elementXml) {
		$properties = array ();
		
		foreach ($elementXml as $xml) {
			if ($xml ["name"]) {
				$propertyName = trim ((string) $xml ["name"]);
			} else if ($xml ["index"]) {
				$propertyName = trim ((string) $xml ["index"]);
			} else {
				$propertyName = md5 ($xml->asXML ());
			}
			
			switch (false) {
				case empty ($xml ["ref"]) :
					$property = new Appenda_Bundle_Property ($propertyName, (string) $xml ["ref"]);
					$property->setType (Appenda_Bundle_Property::ReferenceType);
					break;
				
				case empty ($xml ["value"]) :
					$property = new Appenda_Bundle_Property ($propertyName, (string) $xml ["value"]);
					$property->setType (Appenda_Bundle_Property::ValueType);
					break;
				
				case empty ($xml->{"props"}) :
					$propertyValue = $this->extractPropsArray ($xml->{"props"});
					$property = new Appenda_Bundle_Property ($propertyName, $propertyValue);
					$property->setType (Appenda_Bundle_Property::ValueType);
					break;
				
				case empty ($xml->{"bean"}) && empty ($xml->{"bean"} ["id"]) :
					$property = new Appenda_Bundle_Property ($propertyName, (string) $xml->{"bean"} ["id"]);
					$property->setType (Appenda_Bundle_Property::ValueType);
					break;
				
				default :
					throw new Appenda_Bundle_Exception ("Invalid property XML");
			}
			
			// Set in the proper places
			if ($xml ["index"]) {
				$indexString = trim ((string) $xml ["index"]);
				$index = (int) $indexString;
			} else {
				$index = count ($properties);
			}
			
			if (!empty ($indexString) && array_key_exists ($index, $properties)) {
				$map ["message"] = "Duplicate index found";
				$map ["index"] = $index;
				throw new Appenda_Bundle_Exception ($map);
			} else {
				$properties [$index] = $property;
			}
		}
		
		return $properties;
	}
	
	/**
	 * Extract the bean name from the xml segment
	 *
	 * @param SimpleXMLElement $beanDefinitionXml
	 * @return string
	 */
	protected function extractBeanName (SimpleXMLElement $beanDefinitionXml) {
		return trim ((string) $beanDefinitionXml->{"id"});
	}
	
	/**
	 * Enter description here...
	 *
	 * @param SimpleXMLElement $propsXml
	 * @return array
	 */
	protected function extractPropsArray (SimpleXMLElement $propsXml) {
		$result = array ();
		
		foreach ($propsXml->{"prop"} as $prop) {
			$result [trim ((string) $prop ["key"])] = trim ((string) $prop);
		}
		
		return $result;
	}
}