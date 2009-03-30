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

interface Appenda_Bundle_Definition {
	/**
	 * Role hint indicating that a Bean Definition is a major part of the application.
	 *
	 * @var integer
	 */
	const ApplicationRole = 10;
	
	/**
	 * Role hint indicating that a Bean Definition is providing an entirely background role
	 * and has no relevance to the end-user.
	 *
	 * @var integer
	 */
	const InfrastructureRole = 100;
	
	/**
	 * Role hint indicating that a Bean Definition is a supporting part of some larger
	 * configuration, typically an outer ComponentDefinition.
	 *
	 * @var integer
	 */
	const SupportRole = 1000;
	
	/**
	 * Scope identifier for the standard prototype scope: "prototype".
	 *
	 * @var string
	 */
	const PrototypeScope = "prototype";
	
	/**
	 * Scope identifier for the standard prototype scope: "singleton".
	 *
	 * @var string
	 */
	const SingletonScope = "singleton";
	
	/**
	 * Return the current bean class name of this bean definition.
	 *
	 * @return string
	 */
	public function getBeanClassName ();
	
	/**
	 *  Return the constructor argument values for this bean.
	 *
	 * @return array(Appenda_Bundle_Property_Constructor)
	 */
	public function getConstructorArgumentValues ();
	
	/**
	 * Return a human-readable description of this bean definition.
	 *
	 * @return string
	 */
	public function getDescription ();
	
	/**
	 * Return a factory method, if any.
	 *
	 * @return string
	 */
	public function getFactoryBeanMethod ();
	
	/**
	 * Return the factory bean name, if any.
	 *
	 * @return string
	 */
	public function getFactoryBeanName ();
	
	/**
	 * Return the name of the parent definition of this bean definition, if any.
	 *
	 * @return string
	 */
	public function getParentName ();
	
	/**
	 * Return the property values to be applied to a new instance of the bean.
	 *
	 * @return array(Appenda_Bundle_Property_Mutable)
	 */
	public function getPropertyValues ();
	
	/**
	 * Enter description here...
	 *
	 * @return string
	 */
	public function getResourceDescription ();
	
	/**
	 * Enter description here...
	 *
	 * @return integer
	 */
	public function getRole ();
	
	/**
	 * Return the name of the current target scope for this bean.
	 *
	 * @return string
	 */
	public function getScope ();
	
	/**
	 * Return whether this bean is "abstract", that is, not meant to be instantiated.
	 *
	 * @return boolean
	 */
	public function isAbstract ();
	
	/**
	 * Return whether this bean should be lazily initialized, that is, not eagerly instantiated on startup.
	 *
	 * @return boolean
	 */
	public function isLazyInit ();
	
	/**
	 * Return whether this a Prototype, with a new instance returned on all calls.
	 *
	 * @return boolean
	 */
	public function isPrototype ();
	
	/**
	 * Return whether this a Singleton, with a single, shared instance returned on all calls.
	 *
	 * @return boolean
	 */
	public function isSingleton ();
	
	/**
	 * Override if the bean is abstract
	 *
	 * @param boolean $abstract
	 */
	public function setAbstract ($abstract);
	
	/**
	 * Override the bean class name of this bean definition.
	 *
	 * @param string $beanClassName
	 */
	public function setBeanClassName ($beanClassName);
	
	/**
	 *  Return the constructor argument values for this bean.
	 *
	 * @return array(Appenda_Bundle_Property_Constructor)
	 */
	public function setConstructorArgumentValues (array $constructorArgs);
	
	/**
	 * Override the target scope of this bean, specifying a new scope name.
	 *
	 * @param string $scope
	 */
	public function setScope ($scope);
}