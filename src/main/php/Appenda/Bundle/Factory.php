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

interface Appenda_Bundle_Factory {
	/**
	 * Does this bean factory contain a bean with the given name?
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function containsBean ($beanName);
	
	/**
	 * Return the aliases for the given bean name, if any.
	 *
	 * @param string $beanName
	 * @return array(string)
	 */
	public function getAliases ($beanName);
	
	/**
	 * Return an instance, which may be shared or independent, of the specified bean.
	 *
	 * @param string $beanName
	 * @param string $requiredType
	 * @return object
	 */
	public function getBean ($beanName, $requiredType = null);
	
	/**
	 * Determine the type of the bean with the given name.
	 *
	 * @param string $beanName
	 * @return string
	 */
	public function getType ($beanName);
	
	/**
	 * Is this bean a prototype?
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function isPrototype ($beanName);
	
	/**
	 * Is this bean a shared singleton?
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function isSingleton ($beanName);
	
	/**
	 * Check whether the bean with the given name matches the specified type.
	 *
	 * @param string $beanName
	 * @param string $targetType
	 * @return boolean
	 */
	public function isTypeMatch ($beanName, $targetType);
}