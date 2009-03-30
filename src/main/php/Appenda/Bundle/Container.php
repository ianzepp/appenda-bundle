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

class Appenda_Bundle_Container
{
	/**
	 * Enter description here...
	 *
	 * @var string
	 */
	const BEAN_NS_URI = "http://www.springframework.org/schema/beans";
	
	/**
	 *
	 * @var array(object)
	 */
	private static $beans = array ();
	
	/**
	 *
	 * @var array(SimpleXMLElement)
	 */
	private $beansXml = array ();
	
	/**
	 * Enter description here...
	 *
	 * @var SimpleXMLElement
	 */
	private $configurationXml;
	
	/**
	 *
	 * @param string $configPath
	 * @return void
	 */
	public function processConfig ($configPath)
	{
		assert (is_string ($configPath));
		
		// Load and process the bean configuration xml
		$configData = file_get_contents ($configPath, true);
		$configData = $this->processEnvironmentProperties ($configData);
		
		// Convert to XML
		$this->setConfigurationXml (simplexml_load_string ($configData));
		
		foreach ($this->getConfigurationXml ()->{"bean"} as $beanXml )
		{
			if (!$beanXml ["id"])
			{
				$message = "Missing or empty attribute 'id' for bean with xml ";
				$message .= $beanXml->asXml ();
				throw new Appenda_Exception ($message);
			}
			
			$this->loadBean ((string) $beanXml ["id"]);
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $configData
	 * @return string
	 */
	private function processEnvironmentProperties ($configData)
	{
		$properties = array ();
		
		if (preg_match_all ('/\(${(.+?)})/', $configData, $properties))
		{
			foreach ($properties as $property )
			{
				$regex = '/' . $property [1] . '/';
				$configData = preg_replace ($regex, $_ENV [$property [2]], $configData);
			}
		}
		
		return $configData;
	}
	
	/**
	 * Enter description here...
	 *
	 */
	public function loadBean ($beanId)
	{
		// Debugging
		assert (is_string ($beanId));
		assert (file_put_contents ("php://stdout", "{$this}::loadBean({$beanId})" . "\n"));
		
		// Get the bean xml
		$beanXml = $this->loadBeanXml ($beanId);
		
		// Is the class name missing from a concrete bean?
		if (!$beanXml ["class"] && (strval ($beanXml ["abstract"]) !== 'true'))
		{
			$message = "Missing or empty attribute 'class' for concrete bean id '{$beanId}'";
			throw new Appenda_Exception ($message);
		}
		
		// Instantiate the bean instance
		$beanClass = (string) $beanXml ["class"];
		$beanConstructorArgs = $this->extractConstructorArgs ($beanXml);
		$beanInstance = $this->instantiateBeanInstance ($beanClass, $beanConstructorArgs);
		
		// Set the properties as defined in the configuration, and save the bean
		$this->applyProperties ($beanInstance, $beanXml);
		$this->setBean ($beanId, $beanInstance);
		
		// Done!
		return $beanInstance;
	}
	
	/**
	 *
	 * @param SimpleXMLElement $xml
	 * @param string $beanId
	 * @return SimpleXMLElement
	 * @throws Appenda_Exception
	 */
	public function loadBeanXml ($beanId)
	{
		// Debugging
		assert (is_string ($beanId));
		assert (file_put_contents ("php://stdout", "{$this}::loadBeanXml({$beanId})" . "\n"));
		
		// Is the beanXml already loaded?
		if (array_key_exists ($beanId, $this->beansXml))
		{
			return $this->beansXml [$beanId];
		}
		
		// Find the bean by id
		$beanXml = $this->findBeanInConfig ($beanId);
		
		// Do we need to inherit from a parent?
		if ((string) $beanXml ["parent"])
		{
			// This will recurse through multiple parents, if needed
			$beanParentId = strval ($beanXml ["parent"]);
			$beanParentXml = $this->loadBeanXml ($beanParentId);
			
			// Inherit items from the parent
			$this->applyParentAttributes ($beanXml, $beanParentXml);
			$this->applyParentProperties ($beanXml, $beanParentXml);
			$this->applyParentConstructorArg ($beanXml, $beanParentXml);
		}
		
		// Done
		return $this->beansXml [$beanId] = $beanXml;
	}
	
	/**
	 *
	 * @param $beanName
	 * @return Appenda_Bundle_Bean
	 */
	public function findBean ($beanId)
	{
		// Debugging
		assert (is_string ($beanId));
		assert (file_put_contents ("php://stdout", "{$this}::findBean({$beanId})" . "\n"));
		
		// Does the bean name exist?
		if (array_key_exists ($beanId, self::$beans) === false)
		{
			throw new Appenda_Exception ("Invalid bean id '{$beanId}'");
		}
		
		return self::$beans [$beanId];
	}
	
	/**
	 * Enter description here...
	 *
	 * @param SimpleXMLElement $xml
	 * @param string $beanId
	 * @return SimpleXMLElement
	 */
	public function findBeanInConfig ($beanId)
	{
		// Debugging
		assert (is_string ($beanId));
		assert (file_put_contents ("php://stdout", "{$this}::findBeanInConfig({$beanId})" . "\n"));
		
		// Due to odd behavior in SimpleXMLElement, we have to define the namespace and prefix,
		// even if there is no prefix in use in the actual configuration file.
		$xml = $this->getConfigurationXml ();
		$xml->registerXPathNamespace ("beans", self::BEAN_NS_URI);
		$beanMatches = $xml->xpath ("//beans:bean[@id='{$beanId}']");
		
		if (count ($beanMatches) == 0)
		{
			$message = "Unable to resolve bean id '{$beanId}' with ";
			$message .= $xml->asXml ();
			throw new Appenda_Exception ($message);
		}
		else if (count ($beanMatches) > 1)
		{
			$message = "Invalid configuration, more than one bean exists ";
			$message .= "for bean id '{$beanId}' with ";
			$message .= $xml->asXml ();
			throw new Appenda_Exception ($message);
		}
		else
		{
			return array_shift ($beanMatches);
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @return array
	 */
	public static function getBeans ()
	{
		return self::$beans;
	}
	
	/**
	 *
	 * @param string $name
	 * @return Appenda_Bundle_Bean
	 */
	public static function getBean ($beanName)
	{
		assert (is_string ($beanName));
		
		if (array_key_exists ($beanName, self::$beans))
		{
			return self::$beans [$beanName];
		}
		else
		{
			return null;
		}
	}
	
	/**
	 *
	 * @param string $name
	 * @param object $instance
	 * @return void
	 */
	public static function setBean ($beanName, $beanInstance)
	{
		assert (is_string ($beanName));
		assert (is_object ($beanInstance));
		
		$bean = new Appenda_Bundle_Bean ();
		$bean->setBeanInstance ($beanInstance);
		self::$beans [$beanName] = $bean;
	}
	
	/**
	 * @return SimpleXMLElement;
	 */
	public function getConfigurationXml ()
	{
		return $this->configurationXml;
	}
	
	/**
	 *
	 * @param SimpleXMLElement $configurationXml
	 */
	public function setConfigurationXml (SimpleXMLElement $configurationXml)
	{
		$this->configurationXml = $configurationXml;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param SimpleXMLElement
	 * @return array
	 */
	public function extractProps (SimpleXMLElement $propsRoot)
	{
		$result = array ();
		
		foreach ($propsRoot->{"prop"} as $prop )
		{
			$result [trim ((string) $prop ["key"])] = trim ((string) $prop);
		}
		
		return $result;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param SimpleXMLElement $beanXml
	 * @return mixed
	 */
	public function extractConstructorArgs (SimpleXMLElement $beanXml)
	{
		$results = array ();
		
		foreach ($beanXml->{"constructor-arg"} as $arg )
		{
			if ($arg ["ref"])
			{
				$value = $this->loadBean (trim ((string) $arg ["ref"]));
			}
			else if ($arg ["value"])
			{
				$value = trim ((string) $arg ["value"]);
			}
			else if ($arg->{"props"})
			{
				$value = $this->extractProps ($arg->{"props"});
			}
			else if ((string) $arg)
			{
				$value = trim ((string) $arg);
			}
			
			// Set in the proper places
			$indexString = trim ((string) $arg ["index"]);
			$index = (int) $indexString;
			
			if (!empty ($indexString) && array_key_exists ($index, $results))
			{
				$map ["message"] = "Duplicate constructor-arg index found";
				$map ["index"] = $index;
				$map ["beanXml"] = $beanXml->asXML ();
				$map ["this"] = $this;
				throw new Appenda_Exception ($map);
			}
			
			if (!empty ($indexString))
			{
				$results [$index] = $value;
			}
			else
			{
				$results [] = $value;
			}
		}
		
		return $results;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param SimpleXMLElement $beanXml
	 * @param SimpleXMLElement $beanParentXml
	 */
	public function applyParentAttributes (SimpleXMLElement $beanXml, SimpleXMLElement $beanParentXml)
	{
		foreach ($beanParentXml->attributes () as $name => $data )
		{
			if ((string) $name == "abstract")
			{
				continue;
			}
			
			if (!empty ($beanXml [$name]))
			{
				$beanXml [$name] = (string) $data;
			}
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param SimpleXMLElement $beanXml
	 * @param SimpleXMLElement $beanParentXml
	 */
	public function applyParentProperties (SimpleXMLElement $beanXml, SimpleXMLElement $beanParentXml)
	{
		foreach ($beanParentXml->{"property"} as $property )
		{
			$propertyName = (string) $property ["name"];
			$beanXml->registerXPathNamespace ("beans", self::BEAN_NS_URI);
			$propertyMatches = $beanXml->xpath ("beans:property[@name='{$propertyName}']");
			
			if (empty ($propertyMatches))
			{
				continue;
			}
			
			// Debugging
			if (ASSERT_ACTIVE)
			{
				$message = "{$this}::applyParentProperties(): ";
				$message .= "copying property '{$propertyName}' ";
				$message .= "from parent bean id '{$beanParentXml ["id"]}' ";
				$message .= "to bean id '{$beanXml ["id"]}'";
				file_put_contents ("php://stdout", $message . "\n");
			}
			
			// Copy property manually (unfortunately, SimpleXMLElement doesn't support inserting a clone)
			$propertyXml = $beanXml->addChild ("property", (string) $property);
			
			foreach ($property->attributes () as $name => $data )
			{
				$propertyXml->addAttribute ($name, $data);
			}
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param SimpleXMLElement $beanXml
	 * @param SimpleXMLElement $beanParentXml
	 */
	public function applyParentConstructorArg (SimpleXMLElement $beanXml, SimpleXMLElement $beanParentXml)
	{
		$beanXml->registerXPathNamespace ("beans", self::BEAN_NS_URI);
		$beanArg = $beanXml->{"constructor-arg"};
		$beanParentArg = $beanParentXml->{"constructor-arg"};
		
		if (!$beanParentArg || $beanArg)
		{
			return;
		}
		
		// Debugging
		if (ASSERT_ACTIVE)
		{
			$message = "{$this}::applyParentConstructorArg(): ";
			$message .= "copying constructor-arg '{$beanParentArg->asXML()}' ";
			$message .= "from parent bean id '{$beanParentXml ["id"]}' ";
			$message .= "to bean id '{$beanXml ["id"]}'";
			file_put_contents ("php://stdout", $message . "\n");
		}
		
		// Copy
		$beanXml->addChild ("constructor-arg", (string) $beanParentArg);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param object $beanInstance
	 * @param SimpleXMLElement $beanXml
	 */
	public function applyProperties ($beanInstance, SimpleXMLElement $beanXml)
	{
		foreach ($beanXml->{"property"} as $property )
		{
			// Debugging
			if (ASSERT_ACTIVE)
			{
				$message = "{$this}::applyProperties(): ";
				$message .= "applying to an instance of " . get_class ($beanInstance) . ", ";
				$message .= "using property xml of " . $property->asXml ();
				file_put_contents ("php://stdout", $message . "\n");
			}
			
			// Build the parts
			$propertyName = (string) $property ["name"];
			$propertyRef = (string) $property ["ref"];
			
			// Must have a name attribute
			if (!$propertyName)
			{
				$message = "Missing or empty attribute 'name' for unnammed property ";
				$message .= "in bean '{$beanXml ["id"]}'";
				throw new Appenda_Exception ($message);
			}
			else
			{
				$setterMethod = "set" . ucfirst ($propertyName);
			}
			
			// What do we apply as the property?
			if (!empty ($propertyRef))
			{
				$setterData = $this->loadBean ($propertyRef);
			}
			else if ($property->{"props"})
			{
				$setterData = $this->extractProps ($property->{"props"});
			}
			else if ($property ["value"])
			{
				$setterData = trim ((string) $property ["value"]);
			}
			else
			{
				$setterData = trim ((string) $property);
			}
			
			// Debugging
			if (ASSERT_ACTIVE)
			{
				$message = "{$this}::applyProperties(): ";
				$message .= "calling the setter method '{$setterMethod}' ";
				$message .= "using '";
				$message .= is_object ($setterData) ? get_class ($setterData) : $setterData;
				$message .= "'";
				file_put_contents ("php://stdout", $message . "\n");
			}
			
			// Set it
			$beanInstance->$setterMethod ($setterData);
		}
	
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $beanClass
	 * @param array(mixed) $constructorArgs
	 * @return object
	 */
	public function instantiateBeanInstance ($beanClass, array $constructorArgs = array())
	{
		assert (is_string ($beanClass));
		
		$level = error_reporting (E_ALL ^ E_NOTICE); // Suppress notices
		list ($arg0, $arg1, $arg2, $arg3, $arg4, $arg5) = $constructorArgs;
		error_reporting ($level); // restore
		return new $beanClass ($arg0, $arg1, $arg2, $arg3, $arg4, $arg5);
	}
}
