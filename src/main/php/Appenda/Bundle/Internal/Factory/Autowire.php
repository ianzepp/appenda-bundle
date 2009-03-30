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

require_once "Appenda/Bundle/Internal/Definition/Root.php";
require_once "Appenda/Bundle/Internal/Factory/Listable.php";
require_once "Appenda/Bundle/InstantiationStrategy.php";
require_once "Appenda/Bundle/Property.php";

class Appenda_Bundle_Internal_Factory_Autowire extends Appenda_Bundle_Internal_Factory_Listable {
	const AUTOWIRE_NO = 0x00;
	const AUTOWIRE_BY_NAME = 0x02;
	const AUTOWIRE_BY_TYPE = null;
	const AUTOWIRE_CONSTRUCTOR = null;
	const AUTOWIRE_AUTODETECT = 0x01;
	
	private $allowCircularReferences = false;
	private $ignoredDependencyInterfaces = array ();
	private $ignoredDependencyTypes = array ();
	private $instantiationStrategy;
	
	/**
	 * Enter description here...
	 *
	 * @param object $existingBean
	 * @param string $beanName
	 * @return object
	 */
	public function applyBeanPostProcessorsAfterInitialization ($existingBean, $beanName) {
		assert (is_object ($existingBean));
		assert (is_string ($beanName));
		
		foreach ($this->getBeanPostProcessors () as $postProcessor) {
			$existingBean = $postProcessor->postProcessAfterInitialization ($existingBean, $beanName);
		}
		
		return $existingBean;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param object $existingBean
	 * @param string $beanName
	 * @return object
	 */
	public function applyBeanPostProcessorsBeforeInitialization ($existingBean, $beanName) {
		assert (is_object ($existingBean));
		assert (is_string ($beanName));
		
		foreach ($this->getBeanPostProcessors () as $postProcessor) {
			$existingBean = $postProcessor->postProcessBeforeInitialization ($existingBean, $beanName);
		}
		
		return $existingBean;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $beanClass
	 * @param string $beanName
	 */
	public function applyBeanPostProcessorsBeforeInstantiation ($beanClass, $beanName) {
		assert (is_string ($beanClass));
		assert (is_string ($beanName));
		return $beanClass;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param object $existingBean
	 * @param string $beanName
	 */
	public function applyBeanPropertyValues ($existingBean, $beanName) {
		assert (is_object ($existingBean));
		assert (is_string ($beanName));
		
		foreach ($this->getBeanDefinition ($beanName)->getPropertyValues () as $property) {
			$setterMethod = "set" . ucfirst ($property->getName ());
			$existingBean->$setterMethod ($property->getValue ());
		}
		
		return $existingBean;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $beanName
	 * @param integer $autowireMode
	 * @param boolean $dependencyCheck
	 */
	public function createBean ($beanName, $autowireMode = null, $dependencyCheck = null) {
		assert (is_string ($beanName));
		assert (is_null ($autowireMode) || is_integer ($autowireMode));
		assert (is_null ($dependencyCheck) || is_bool ($dependencyCheck));
		
		$beanDefinition = $this->getBeanDefinition ($beanName);
		$beanArgs = array ();
		
		// Process the constructor args
		foreach ($beanDefinition->getConstructorArgumentValues () as $argument) {
			if ($argument->isConverted ()) {
				$beanArgs [] = $argument->getConvertedValue ();
			}
			
			// Determine if the value is a reference or not
			switch ($argument->getType ()) {
				case Appenda_Bundle_Property::ReferenceType :
					$convertedValue = $this->createBean ($argument->getName ());
					break;
				
				case Appenda_Bundle_Property::ValueType :
					$convertedValue = $argument->getValue ();
					break;
				
				default :
					$map ["message"] = "Unrecognized value type";
					$map ["className"] = "Appenda_Bundle_Property";
					$map ["type"] = $argument->getType ();
					throw new Appenda_Bundle_Exception ($map);
			}
			
			// Save the converted value
			$argument->setConvertedValue ($convertedValue);
			$beanArgs [] = $convertedValue;
		}
		
		return $this->createBeanFromDefinition ($beanName, $beanDefinition, $beanArgs);
	}
	
	/**
	 * Central method of this class: creates a bean instance, populates the bean instance, applies post-processors, etc.
	 *
	 * @param string $beanName
	 * @param Appenda_Bundle_Definition_Root $mbd
	 * @param array $args
	 * @return object
	 */
	protected function createBeanFromDefinition ($beanName, Appenda_Bundle_Definition_Root $mbd, array $args) {
		assert (is_string ($beanName));
		
		$bean = $this->createBeanInstance ($beanName, $mbd, $args);
		$bean = $this->applyBeanPostProcessorsBeforeInitialization ($bean, $beanName);
		$bean = $this->applyBeanPropertyValues ($bean, $beanName);
		$bean = $this->applyBeanPostProcessorsAfterInitialization ($bean, $beanName);
		return $bean;
	}
	
	/**
	 * Create a new instance for the specified bean, using an appropriate instantiation strategy:
	 * - factory method,
	 * - constructor autowiring, or
	 * - simple instantiation.
	 *
	 * @param string $beanName
	 * @param Appenda_Bundle_Definition_Root $mbd
	 * @param array $args
	 * @return object
	 */
	protected function createBeanInstance ($beanName, Appenda_Bundle_Definition_Root $mbd, array $args) {
		assert (is_string ($beanName));
		
		$beanClass = $mbd->getBeanClass ();
		$beanClass = $this->applyBeanPostProcessorsBeforeInstantiation ($beanClass, $beanName);
		
		if ($mdb->getFactoryBeanName ()) {
			return $this->instantiateUsingFactoryMethod ($beanName, $mdb, $args);
		} else {
			return $this->instantiateBean ($beanName, $mbd, $args);
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @return array
	 */
	public function getIgnoredDependencyInterfaces () {
		return $this->ignoredDependencyInterfaces;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return array
	 */
	public function getIgnoredDependencyTypes () {
		return $this->ignoredDependencyInterfaces;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Appenda_Bundle_InstantiationStrategy
	 */
	public function getInstantiationStrategy () {
		return $this->instantiationStrategy;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $interfaceName
	 */
	public function ignoreDependencyInterface ($interfaceName) {
		assert (is_string ($interfaceName));
		$this->ignoredDependencyInterfaces [] = $interfaceName;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $className
	 */
	public function ignoreDependencyType ($className) {
		assert (is_string ($className));
		$this->ignoredDependencyTypes [] = $className;
	}
	
	/**
	 *  Instantiate the given bean using its constructor.
	 *
	 * @param string $beanName
	 * @param Appenda_Bundle_Internal_Definition_Root $mbd
	 * @param array $args
	 * @return object
	 */
	public function instantiateBean ($beanName, Appenda_Bundle_Internal_Definition_Root $mbd, array $args) {
		assert (is_string ($beanName));
		
		list ($a0, $a1, $a2, $a3, $a4) = $args;
		$beanClassName = $mbd->getBeanClassName ();
		$beanClassName = $this->applyBeanPostProcessorsBeforeInstantiation ($beanClassName, $beanName);
		return new $beanClassName ($a0, $a1, $a2, $a3, $a4);
	}
	
	/**
	 *  Instantiate the bean using a named factory method.
	 *
	 * @param string $beanName
	 * @param Appenda_Bundle_Internal_Definition_Root $mbd
	 * @param array $args
	 */
	public function instantiateUsingFactoryMethod ($beanName, Appenda_Bundle_Internal_Definition_Root $mbd, array $args) {
		assert (is_string ($beanName));
		
		$factoryClassName = $this->getBeanDefinition ($beanName)->getBeanClass ();
		$factoryMethod = $factoryClassName . '::' . $mbd->getFactoryBeanMethod ();
		$beanClassName = $mbd->getBeanClassName ();
		$beanClassName = $this->applyBeanPostProcessorsBeforeInstantiation ($beanClassName, $beanName);
		return call_user_func_array ($factoryMethod, $args);
	}
	
	/**
	 * Enter description here...
	 *
	 * @return boolean
	 */
	public function isAllowCircularReferences () {
		return $this->allowCircularReferences;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param boolean $allowCircularReferences
	 */
	public function setAllowCircularReferences ($allowCircularReferences) {
		assert (is_bool ($allowCircularReferences));
		$this->allowCircularReferences = boolval ($allowCircularReferences);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param Appenda_Bundle_InstantiationStrategy $instantiationStrategy
	 */
	public function setInstantiationStrategy (Appenda_Bundle_InstantiationStrategy $instantiationStrategy) {
		$this->instantiationStrategy = $instantiationStrategy;
	}
}