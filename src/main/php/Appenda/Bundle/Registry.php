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

interface Appenda_Bundle_Registry {
	/**
	 * Check if this registry contains a bean definition with the given name
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function containsBeanDefinition ($beanName);
	
	/**
	 * Return the aliases for the given bean name, if defined.
	 *
	 * @param string $beanName
	 * @return array(string)
	 */
	public function getAliases ($beanName);
	
	/**
	 * Return the BeanDefinition for the given bean name
	 *
	 * @param string $beanName
	 * @return Appenda_Bundle_Definition
	 */
	public function getBeanDefinition ($beanName);
	
	/**
	 * Return the number of beans defined in the registry
	 *
	 * @return integer
	 */
	public function getBeanDefinitionCount ();
	
	/**
	 * Return the names of all beans defined in this registry
	 *
	 * @return array(string)
	 */
	public function getBeanDefinitionNames ();
	
	/**
	 * Determine whether this given name is defines as an alias (as opposed to the name of an actually registered component).
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function isAlias ($beanName);
	
	/**
	 * Given a bean name, create an alias
	 *
	 * @param string $beanName
	 * @param string $alias
	 */
	public function registerAlias ($beanName, $alias);
	
	/**
	 * Register a new bean definition with this registry
	 *
	 * @param string $beanName
	 * @param Appenda_Bundle_Definition $beanDefinition
	 */
	public function registerBeanDefinition ($beanName, Appenda_Bundle_Definition $beanDefinition);
	
	/**
	 * Remove the specified alias from this registry.
	 *
	 * @param string $beanName
	 */
	public function removeAlias ($beanName);
}