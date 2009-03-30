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

require_once "Appenda/Bundle/Definition.php";
require_once "Appenda/Bundle/Property.php";

abstract class Appenda_Bundle_Internal_Definition_Abstract implements Appenda_Bundle_Definition {
	/**
	 * Constant that indicates no dependency check at all.
	 *
	 * @var integer
	 */
	const NoDependencyCheck = 0;
	
	/**
	 * Constant that indicates dependency checking for "simple" properties.
	 *
	 * @var integer
	 */
	const SimpleDependencyCheck = 1;
	
	/**
	 *  Constant that indicates dependency checking for object references.
	 *
	 * @var integer
	 */
	const ObjectDependencyCheck = 2;
	
	/**
	 * Constant that indicates dependency checking for all properties (object references as well as "simple" properties).
	 *
	 * @var integer
	 */
	const AllDependencyChecks = 3;
	
	// Private properties
	private $abstract = false;
	private $beanClassName;
	private $constructorArgumentValues = array ();
	private $dependencyCheck = self::AllDependencyChecks;
	private $dependsOn = array ();
	private $factoryBeanName;
	private $factoryBeanMethod;
	private $lazyInit = false;
	private $parentName;
	private $propertyValues = array ();
	private $resourceDescription;
	private $role = self::ApplicationRole;
	private $scope = self::SingletonScope;
	private $synthetic = false;
	
	/**
	 * @see Appenda_Bundle_Definition::getBeanClassName()
	 *
	 * @return string
	 */
	public function getBeanClassName () {
		return $this->beanClassName;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::getConstructorArgumentValues()
	 *
	 * @return array(Appenda_Bundle_Property)
	 */
	public function getConstructorArgumentValues () {
		return $this->constructorArgumentValues;
	}
	
	/**
	 * Return the dependency check code.
	 *
	 * @return int
	 */
	public function getDependencyCheck () {
		return $this->dependencyCheck;
	}
	
	/**
	 * Return the bean names that this bean depends on.
	 *
	 * @return array(string)
	 */
	public function getDependsOn () {
		return $this->dependsOn;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::getDescription()
	 *
	 * @return string
	 */
	public function getDescription () {
		return $this->getResourceDescription ();
	}
	
	/**
	 * @see Appenda_Bundle_Definition::getFactoryBeanMethod()
	 *
	 * @return string
	 */
	public function getFactoryBeanMethod () {
		return $this->factoryBeanMethod;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::getFactoryBeanName()
	 *
	 * @return string
	 */
	public function getFactoryBeanName () {
		return $this->factoryBeanName;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::getParentName()
	 *
	 * @return string
	 */
	public function getParentName () {
		return $this->parentName;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::getPropertyValues()
	 *
	 * @return array(Appenda_Bundle_Property_Mutable)
	 */
	public function getPropertyValues () {
		return $this->propertyValues;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::getResourceDescription()
	 *
	 * @return string
	 */
	public function getResourceDescription () {
		return $this->resourceDescription;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::getRole()
	 *
	 * @return integer
	 */
	public function getRole () {
		return $this->role;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::getScope()
	 *
	 * @return string
	 */
	public function getScope () {
		return $this->scope;
	}
	
	/**
	 * Return whether this definition specifies a bean class.
	 *
	 * @return boolean
	 */
	public function hasBeanClassName () {
		return !empty ($this->beanClassName);
	}
	
	/**
	 * Return if there are constructor argument values defined for this bean.
	 *
	 * @return boolean
	 */
	public function hasConstructorValueArguments () {
		return count ($this->getConstructorArgumentValues ()) > 0;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::isAbstract()
	 *
	 * @return boolean
	 */
	public function isAbstract () {
		return $this->abstract;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::isLazyInit()
	 *
	 * @return boolean
	 */
	public function isLazyInit () {
		return $this->lazyInit;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::isSingleton()
	 *
	 * @return boolean
	 */
	public function isSingleton () {
		return $this->getScope () === self::SingletonScope;
	}
	
	/**
	 * Return whether this a Prototype, with an independent instance returned for each call.
	 *
	 * @return boolean
	 */
	public function isPrototype () {
		return $this->getScope () === self::PrototypeScope;
	}
	
	/**
	 * Return whether this bean definition is 'synthetic', that is, not defined by the application itself.
	 *
	 * @return boolean
	 */
	public function isSynthetic () {
		return $this->synthetic;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param boolean $abstract
	 */
	public function setAbstract ($abstract) {
		assert (is_bool ($abstract));
		$this->abstract = $abstract;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::setBeanClassName()
	 *
	 * @param string $beanClassName
	 */
	public function setBeanClassName ($beanClassName) {
		$this->beanClassName = $beanClassName;
	}
	
	/**
	 *  Specify constructor argument values for this bean.
	 *
	 * @param array(Appenda_Bundle_Property_Constructor) $constructorArgumentValues
	 */
	public function setConstructorArgumentValues (array $constructorArgumentValues) {
		foreach ($constructorArgumentValues as $constructorArgument) {
			assert ($constructorArgument instanceof Appenda_Bundle_Property);
		}
		
		$this->constructorArgumentValues = $constructorArgumentValues;
	}
	
	/**
	 * Set the dependency check code.
	 *
	 * @param int $dependencyCheck
	 */
	public function setDependencyCheck ($dependencyCheck) {
		assert (is_int ($dependencyCheck));
		$this->dependencyCheck = $dependencyCheck;
	}
	
	/**
	 * Set the names of the beans that this bean depends on being initialized.
	 *
	 * @param array(string) $dependsOn
	 */
	public function setDependsOn (array $dependsOn) {
		$this->dependsOn = array_map ("strval", $dependsOn);
	}
	
	/**
	 * @param string $factoryBeanMethod
	 */
	public function setFactoryBeanMethod ($factoryBeanMethod) {
		assert (is_string ($factoryBeanMethod));
		$this->factoryBeanMethod = $factoryBeanMethod;
	}
	
	/**
	 * @param string $factoryBeanName
	 */
	public function setFactoryBeanName ($factoryBeanName) {
		assert (is_string ($factoryBeanName));
		$this->factoryBeanName = $factoryBeanName;
	}
	
	/**
	 * Set whether this bean should be lazily initialized.
	 *
	 * @param boolean $lazyInit
	 */
	public function setLazyInit ($lazyInit) {
		assert (is_bool ($lazyInit) || boolval ($lazyInit));
		$this->lazyInit = boolval ($lazyInit);
	}
	
	/**
	 * @param string $parentName
	 */
	public function setParentName ($parentName) {
		assert (is_string ($parentName));
		$this->parentName = $parentName;
	}
	
	/**
	 * Specify property values for this bean, if any.
	 *
	 * @param array(Appenda_Bundle_Property_Mutable) $propertyValues
	 */
	public function setPropertyValues (array $propertyValues) {
		foreach ($propertyValues as $propertyValue) {
			assert ($propertyValue instanceof Appenda_Bundle_Property);
		}
		
		$this->propertyValues = $propertyValues;
	}
	
	/**
	 * Set a description of the resource that this bean definition came from (for
	 * the purpose of showing context in case of errors).
	 *
	 * @param string $resourceDescription
	 */
	public function setResourceDescription ($resourceDescription) {
		assert (is_string ($resourceDescription));
		$this->resourceDescription = $resourceDescription;
	}
	
	/**
	 * Set the role hint for this bean definition.
	 *
	 * @param integer $role
	 */
	public function setRole ($role) {
		assert (is_integer ($role));
		$this->role = $role;
	}
	
	/**
	 * @see Appenda_Bundle_Definition::setScope()
	 *
	 * @param string $scope
	 */
	public function setScope ($scope) {
		assert (is_string ($scope));
		$this->scope = $scope;
	}
	
	/**
	 * Set if this a Singleton, with a single, shared instance returned on all calls.
	 *
	 * @param boolean $singleton
	 */
	public function setSingleton ($singleton) {
		assert (is_bool ($singleton) || boolval ($singleton));
		
		if (boolval ($singleton)) {
			$this->setScope (self::SingletonScope);
		} else {
			$this->setScope (self::PrototypeScope);
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param boolean $synthetic
	 */
	public function setSynthetic ($synthetic) {
		assert (is_bool ($synthetic) || boolval ($synthetic));
		$this->synthetic = boolval ($synthetic);
	}
}