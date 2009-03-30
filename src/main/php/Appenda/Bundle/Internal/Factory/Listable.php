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
require_once "Appenda/Bundle/Exception.php";
require_once "Appenda/Bundle/Factory.php";
require_once "Appenda/Bundle/Internal/Factory/Configurable.php";
require_once "Appenda/Bundle/PostProcessor.php";

class Appenda_Bundle_Internal_Factory_Listable implements Appenda_Bundle_Internal_Factory_Configurable {
	private $allowBeanDefinitionOverriding = false;
	private $beanAliases = array ();
	private $beanDefinitions = array ();
	private $beanPostProcessors = array ();
	private $parentBeanFactory;
	private $singletons = array ();
	
	/**
	 * @see Appenda_Bundle_Internal_Factory_Configurable::addBeanPostProcessor()
	 *
	 * @param Appenda_Bundle_PostProcessor $beanPostProcessor
	 */
	public function addBeanPostProcessor (Appenda_Bundle_PostProcessor $beanPostProcessor) {
		$this->beanPostProcessors [] = $beanPostProcessor;
	}
	
	/**
	 * @see Appenda_Bundle_Factory::containsBean()
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function containsBean ($beanName) {
		assert (is_string ($beanName));
		return array_key_exists ($beanName, $this->beanDefinitions);
	}
	
	/**
	 * Check if this bean factory contains a bean definition with the given name.
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function containsBeanDefinition ($beanName) {
		assert (is_string ($beanName));
		return array_key_exists ($beanName, $this->beanDefinitions);
	}
	
	/**
	 * @see Appenda_Bundle_Factory_Configurable::containsSingleton()
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function containsSingleton ($beanName) {
		assert (is_string ($beanName));
		return array_key_exists ($beanName, $this->singletons);
	}
	
	/**
	 * @see Appenda_Bundle_Factory_Configurable::destroySingletons()
	 *
	 */
	public function destroySingletons () {}
	
	/**
	 * Enter description here...
	 *
	 * @param string $className
	 * @return array(Appenda_Bundle_Definition)
	 */
	public function findMatchingBeans ($className) {
		assert (is_string ($className));
		
		$matchingBeans = array ();
		
		foreach ($this->beanDefinitions as $beanName => $beanDefinition) {
			if ($beanDefinition->getBeanClassName () === $className) {
				$matchingBeans [$beanName] = $beanDefinition;
			}
		}
		
		return $matchingBeans;
	}
	
	/**
	 * @see Appenda_Bundle_Factory::getAliases()
	 *
	 * @param string $beanName
	 * @return array(string)
	 */
	public function getAliases ($beanName) {
		assert (is_string ($beanName));
		return array_key_exists ($beanName, $this->aliases) ? $this->aliases [$beanName] : array ();
	}
	
	/**
	 * @see Appenda_Bundle_Factory::getBean()
	 *
	 * @param string $beanName
	 * @param string $requiredType
	 * @return object
	 */
	public function getBean ($beanName, $requiredType = null) {
		assert (is_string ($beanName));
		assert (is_string ($requiredType) || is_null ($requiredType));
		
		$beanDefinition = $this->getBeanDefinition ($beanName);
		
		if ($beanDefinition->isAbstract ()) {
			$map ["message"] = "Bean is abstract";
			$map ["name"] = $beanName;
			$map ["definition"] = $beanDefinition;
			throw new Appenda_Bundle_Exception ($map);
		}
		
		// TODO
		return null;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $beanName
	 * @return Appenda_Bundle_Definition
	 * @throws Appenda_Bundle_Exception If the bean name is not registered
	 */
	public function getBeanDefinition ($beanName) {
		assert (is_string ($beanName));
		
		if ($this->containsBean ($beanName)) {
			return $this->beanDefinitions [$beanName];
		} else {
			$map ["message"] = "Bean name is not registered";
			$map ["name"] = $beanName;
			throw new Appenda_Bundle_Exception ($map);
		}
	}
	
	/**
	 * Return the number of beans defined in the factory.
	 *
	 * @return integer
	 */
	public function getBeanDefinitionCount () {
		return count ($this->beanDefinitions);
	}
	
	/**
	 * Return the names of all beans defined in this factory.
	 *
	 * @return array(string)
	 */
	public function getBeanDefinitionNames () {
		return array_keys ($this->beanDefinitions);
	}
	
	/**
	 * Return the names of beans matching the given type (including subclasses), judging from either bean
	 * definitions or the value of getObjectType  in the case of FactoryBeans.
	 *
	 * TODO Factory beans are currently ignored / not implemented.
	 *
	 * @param string $className
	 * @param boolean $includePrototypes Default is true.
	 * @param boolean $includeFactoryBeans Default is true.
	 * @return array(string)
	 */
	public function getBeanNamesForType ($className, $includePrototypes = true, $includeFactoryBeans = true) {
		assert (is_string ($className));
		assert (is_bool ($includePrototypes));
		assert (is_bool ($includeFactoryBeans));
		
		$matchingNames = array ();
		
		foreach ($this->beanDefinitions as $beanName => $beanDefinition) {
			if ($beanDefinition->isPrototype () && !$includePrototypes) {
				continue;
			}
			
			if ($beanDefinition->getBeanClassName () instanceof $className) {
				$matchingNames [] = $beanName;
			}
		}
		
		return $matchingNames;
	}
	
	/**
	 * @see Appenda_Bundle_Factory_Configurable::getBeanPostProcessorCount()
	 *
	 * @return integer
	 */
	public function getBeanPostProcessorCount () {
		return count ($this->beanPostProcessors);
	}
	
	/**
	 * Enter description here...
	 *
	 * @return array(Appenda_Bundle_PostProcessor)
	 */
	public function getBeanPostProcessors () {
		return $this->beanPostProcessors;
	}
	
	/**
	 * Return the bean instances that match the given object type (including subclasses), judging from either
	 * bean definitions or the value of getObjectType in the case of FactoryBeans.
	 *
	 * TODO Factory beans are currently ignored / not implemented.
	 *
	 * @param string $className
	 * @param boolean $includePrototypes Default is true.
	 * @param boolean $includeFactoryBeans Default is true.
	 * @return array(object)
	 */
	public function getBeansOfType ($className, $includePrototypes = true, $includeFactoryBeans = true) {
		assert (is_string ($className));
		assert (is_bool ($includePrototypes));
		assert (is_bool ($includeFactoryBeans));
		
		$matchingBeans = array ();
		
		foreach ($this->beanDefinitions as $beanName => $beanDefinition) {
			if ($beanDefinition->isPrototype () && !$includePrototypes) {
				continue;
			}
			
			if ($beanDefinition->getBeanClassName () instanceof $className) {
				$matchingBeans [] = $this->getBean ($beanName);
			}
		}
		
		return $matchingBeans;
	}
	
	/**
	 * @see Appenda_Bundle_Factory_Configurable::getDependenciesForBean()
	 *
	 * @param string $beanName
	 * @return array(string)
	 */
	public function getDependenciesForBean ($beanName) {
		throw new Appenda_Bundle_Exception ("Unimplemented");
	}
	
	/**
	 * @see Appenda_Bundle_Factory_Configurable::getDependentBeans()
	 *
	 * @param string $beanName
	 * @return array(string)
	 */
	public function getDependentBeans ($beanName) {
		throw new Appenda_Bundle_Exception ("Unimplemented");
	}
	
	/**
	 * @see Appenda_Bundle_Factory_Configurable::getMergedBeanDefinition()
	 *
	 * @param string $beanName
	 * @return Appenda_Bundle_Definition
	 */
	public function getMergedBeanDefinition ($beanName) {
		throw new Appenda_Bundle_Exception ("Unimplemented");
	}
	
	/**
	 * @see Appenda_Bundle_Factory::getType()
	 *
	 * @param string $beanName
	 * @return string
	 */
	public function getType ($beanName) {
		assert (is_string ($beanName));
		return $this->getBeanDefinition ($beanName)->getBeanClassName ();
	}
	
	/**
	 * Return whether it should be allowed to override bean definitions by registering a different definition
	 * with the same name, automatically replacing the former.
	 *
	 * @return boolean
	 */
	public function isAllowBeanDefinitionOverriding () {
		return $this->allowBeanDefinitionOverriding;
	}
	
	/**
	 * @see Appenda_Bundle_Factory_Configurable::isFactoryBean()
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function isFactoryBean ($beanName) {
		throw new Appenda_Bundle_Exception ("Unimplemented");
	}
	
	/**
	 * @see Appenda_Bundle_Factory::isPrototype()
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function isPrototype ($beanName) {
		assert (is_string ($beanName));
		return $this->getBeanDefinition ($beanName)->isPrototype ();
	}
	
	/**
	 * @see Appenda_Bundle_Factory::isSingleton()
	 *
	 * @param string $beanName
	 * @return boolean
	 */
	public function isSingleton ($beanName) {
		assert (is_string ($beanName));
		return $this->getBeanDefinition ($beanName)->isSingleton ();
	}
	
	/**
	 * @see Appenda_Bundle_Factory::isTypeMatch()
	 *
	 * @param string $beanName
	 * @param string $targetType
	 * @return boolean
	 */
	public function isTypeMatch ($beanName, $targetType) {}
	
	/**
	 * Ensure that all non-lazy-init singletons are instantiated, also considering FactoryBeans.
	 *
	 * TODO
	 */
	public function preInstantiateSingletons () {}
	
	/**
	 * @see Appenda_Bundle_Factory_Configurable::registerAlias()
	 *
	 * @param string $beanName
	 * @param string $alias
	 */
	public function registerAlias ($beanName, $alias) {
		assert (is_string ($beanName));
		assert (is_string ($alias));
		
		if (!array_key_exists ($beanName, $this->aliases)) {
			$this->aliases [$beanName] = array ();
		}
		
		if (!in_array ($alias, $this->aliases [$beanName])) {
			$this->aliases [$beanName] [] = $alias;
		}
	}
	
	/**
	 * Register a new bean definition with this registry.
	 *
	 * @param string $beanName
	 * @param Appenda_Bundle_Definition $beanDefinition
	 */
	public function registerBeanDefinition ($beanName, Appenda_Bundle_Definition $beanDefinition) {
		assert (is_string ($beanName));
		
		if ($this->containsBeanDefinition ($beanName) && !$this->isAllowBeanDefinitionOverriding ()) {
			$map ["message"] = "Bean definition is already registered";
			$map ["beanName"] = $beanName;
			$map ["beanDefinition"] = $beanDefinition;
			throw new Appenda_Bundle_Exception ($map);
		}
		
		$this->beanDefinitions [$beanName] = $beanDefinition;
	}
	
	/**
	 * @see Appenda_Bundle_Factory_Configurable::registerDependentBean()
	 *
	 * @param string $beanName
	 * @param string $dependentBeanName
	 */
	public function registerDependentBean ($beanName, $dependentBeanName) {
		throw new Appenda_Bundle_Exception ("Unimplemented");
	}
	
	/**
	 * @see Appenda_Bundle_Factory_Configurable::registerSingleton()
	 *
	 * @param string $beanName
	 * @param object $singletonObject
	 */
	public function registerSingleton ($beanName, $singletonObject) {
		assert (is_string ($beanName));
		assert (is_object ($singletonObject));
		$this->singletons [$beanName] = $singletonObject;
	}
	
	/**
	 * Set whether it should be allowed to override bean definitions by registering a different definition
	 * with the same name, automatically replacing the former.
	 *
	 * @param boolean $allowBeanDefinitionOverriding
	 */
	public function setAllowBeanDefinitionOverriding ($allowBeanDefinitionOverriding) {
		assert (is_bool ($allowBeanDefinitionOverriding));
		$this->allowBeanDefinitionOverriding = $allowBeanDefinitionOverriding;
	}
	
	/**
	 * @see Appenda_Bundle_Factory_Configurable::setParentBeanFactory()
	 *
	 * @param Appenda_Bundle_Factory $parentBeanFactory
	 */
	public function setParentBeanFactory (Appenda_Bundle_Factory $parentBeanFactory) {
		$this->parentBeanFactory = $parentBeanFactory;
	}

}