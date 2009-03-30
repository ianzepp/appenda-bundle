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

require_once "Appenda/Bundle/Factory.php";
require_once "Appenda/Bundle/PostProcessor.php";

interface Appenda_Bundle_Internal_Factory_Configurable extends Appenda_Bundle_Factory {
	/**
	 * Scope identifier for the standard prototype scope: "prototype".
	 *
	 * @var string
	 */
	const PrototypeScope = "prototype";
	
	/**
	 * Scope identifier for the standard singleton scope: "singleton".
	 *
	 * @var string
	 */
	const SingletonScope = "singleton";
	
	/**
	 * Add a new BeanPostProcessor that will get applied to beans created by this factory.
	 *
	 * @param Appenda_Bundle_PostProcessor $beanPostProcessor
	 */
	public function addBeanPostProcessor (Appenda_Bundle_PostProcessor $beanPostProcessor);
	
	/**
	 * Check if this bean factory contains a singleton instance with the given name.
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function containsSingleton ($beanName);
	
	/**
	 * Destroy all cached singletons in this factory.
	 *
	 */
	public function destroySingletons ();
	
	/**
	 * Return the current number of registered BeanPostProcessors.
	 *
	 * @return integer
	 */
	public function getBeanPostProcessorCount ();
	
	/**
	 * Return the names of all beans that the specified bean depends on, if any.
	 *
	 * @param string $beanName
	 * @return array(string)
	 */
	public function getDependenciesForBean ($beanName);
	
	/**
	 * Return the names of all beans which depend on the specified bean, if any.
	 *
	 * @param string $beanName
	 * @return array(string)
	 */
	public function getDependentBeans ($beanName);
	
	/**
	 * Return a merged BeanDefinition for the given bean name, merging a child bean definition with its parent if necessary.
	 *
	 * @param string $beanName
	 * @return Appenda_Bundle_Definition
	 */
	public function getMergedBeanDefinition ($beanName);
	
	/**
	 * Determine whether the bean with the given name is a FactoryBean.
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function isFactoryBean ($beanName);
	
	/**
	 * Given a bean name, create an alias.
	 *
	 * @param string $beanName
	 * @param string $alias
	 */
	public function registerAlias ($beanName, $alias);
	
	/**
	 * Register a dependent bean for the given bean, to be destroyed before the given bean is destroyed.
	 *
	 * @param string $beanName
	 * @param string $dependentBeanName
	 */
	public function registerDependentBean ($beanName, $dependentBeanName);
	
	/**
	 * Register the given existing object as singleton in the bean factory, under the given bean name.
	 *
	 * @param string $beanName
	 * @param object $singletonObject
	 */
	public function registerSingleton ($beanName, $singletonObject);
	
	/**
	 * Set the parent of this bean factory.
	 *
	 * @param Appenda_Bundle_Factory $parentBeanFactory
	 */
	public function setParentBeanFactory (Appenda_Bundle_Factory $parentBeanFactory);
}