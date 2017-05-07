<?php
namespace MIA3\GraphQL;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "MIA3.GraphQL".          *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
use Doctrine\Common\Annotations\AnnotationReader;

/**
 */
class ReflectionService
{
    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * ReflectionService constructor.
     * @param AnnotationReader $annotationReader
     */
    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param $className
     * @return array
     */
    public function getPropertyNames($className)
    {
        $propertyNames = array();
        $reflection = new \ReflectionClass($className);
        foreach ($reflection->getProperties() as $key => $property) {
            $propertyNames[] = $property->name;
        }

        return $propertyNames;
    }

    /**
     * @param $className
     * @return array
     */
    public function getClassAnnotations($className)
    {
        $reflectionClass = new \ReflectionClass($className);

        return $this->annotationReader->getClassAnnotations($reflectionClass);
    }

    /**
     * @param $className
     * @param null $propertyName
     * @return array
     */
    public function getPropertyAnnotations($className, $propertyName = null)
    {
        $reflectionProperty = new \ReflectionProperty($className, $propertyName);

        return $this->annotationReader->getPropertyAnnotations($reflectionProperty);
    }

    /**
     * @param $className
     * @param null $propertyName
     * @return array
     */
    public function getPropertyAnnotationClassNames($className, $propertyName = null)
    {
        $reflectionProperty = new \ReflectionProperty($className, $propertyName);
        $annotations = $this->annotationReader->getPropertyAnnotations($reflectionProperty);
        $classNames = array();
        foreach ($annotations as $annotation) {
            $classNames[] = get_class(($annotation));
        }

        return array_unique($classNames);
    }

    /**
     * @param $className
     * @param $propertyName
     * @return array
     */
    public function getPropertyTags($className, $propertyName)
    {
        $reflectionProperty = new \ReflectionProperty($className, $propertyName);

        return $this->parseDocComment($reflectionProperty->getDocComment());
    }

    /**
     * Parses the given doc comment and saves the result (description and
     * tags) in the parser's object. They can be retrieved by the
     * getTags() getTagValues() and getDescription() methods.
     *
     * @param string $docComment A doc comment as returned by the reflection getDocComment() method
     * @return array
     */
    protected function parseDocComment($docComment)
    {
        $tags = array();
        $lines = explode(chr(10), $docComment);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '*/') {
                break;
            }
            if ($line !== '' && strpos($line, '* @') !== false) {
                $this->parseTag(substr($line, strpos($line, '@')), $tags);
            }
        }

        return $tags;
    }

    /**
     * Parses a line of a doc comment for a tag and its value.
     * The result is stored in the internal tags array.
     *
     * @param string $line A line of a doc comment which starts with an @-sign
     * @return void
     */
    protected function parseTag($line, &$tags)
    {
        $tagAndValue = array();
        if (preg_match('/@[A-Za-z0-9\\\\]+\\\\([A-Za-z0-9]+)(?:\\((.*)\\))?$/', $line, $tagAndValue) === 0) {
            $tagAndValue = preg_split('/\s/', $line, 2);
        } else {
            array_shift($tagAndValue);
        }
        $tag = strtolower(trim($tagAndValue[0], '@'));
        if (count($tagAndValue) > 1) {
            $tags[$tag][] = trim($tagAndValue[1], ' "');
        } else {
            $tags[$tag] = array();
        }
    }

}
