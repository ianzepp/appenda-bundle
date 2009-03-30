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

interface Appenda_Bundle_Internal_Registry_Singleton {
	/**
	 * Check if this registry contains a singleton instance with the given name.
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function containsSingleton ($beanName);
	
	/**
	 * Return the (raw) singleton object registered under the given name.
	 *
	 * @param string $beanName
	 * @return object|null
	 */
	public function getSingleton ($beanName);
	
	/**
	 * Return the number of singleton beans registered in this registry.
	 *
	 * @return integer
	 */
	public function getSingletonCount ();
	
	/**
	 * Return the names of singleton beans registered in this registry.
	 *
	 * @return array(string)
	 */
	public function getSingletonNames ();
	
	/**
	 * Register the given existing object as singleton in the bean registry, under the given bean name.
	 *
	 * @param string $beanName
	 * @param object $singletonObject
	 */
	public function registerSingleton ($beanName, $singletonObject);
}