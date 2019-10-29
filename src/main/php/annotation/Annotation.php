<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\reflect\annotation;
use stubbles\values\Parse;
/**
 * Represents an annotation on the code.
 */
class Annotation
{
    /**
     * name of annotation
     *
     * @type  string
     */
    private $name;
    /**
     * values of annotation
     *
     * @type  array
     */
    private $values     = [];
    /**
     * target from which annotation was retrieved
     *
     * @type  string
     */
    private $target;
    /**
     * original annotation type
     *
     * @type  string
     */
    private $type;

    /**
     * constructor
     *
     * @param  string  $name    name of annotation, in case of casted annotations it the casted type
     * @param  string  $target  optional  name of target where annotation is for, i.e. the class, method, function, property or parameter
     * @param  array   $values  optional  map of all annotation values
     * @param  string  $type    optional  type of annotation in case $name reflects a casted type
     */
    public function __construct(
            string $name,
            string $target = null,
            array $values = [],
            string $type = null
    ) {
        $this->name   = $name;
        $this->target = $target;
        $this->values = $values;
        $this->type   = (null === $type) ? $name : $type;
    }

    /**
     * Returns the name under which the annotation is stored.
     *
     * @api
     * @return  string
     */
    public function getAnnotationName(): string
    {
        return $this->name;
    }

    /**
     * returns name of target where annotation is for, i.e. the class, method, function, property or parameter
     *
     * @return  string
     * @since   4.0.0
     */
    public function target(): string
    {
        return $this->target;
    }

    /**
     * annotation type
     *
     * Contains always the real annotation type, as the annotation name in case
     * of casted annotations reflects the casted type.
     *
     * @return  string
     * @since   5.0.0
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * checks whether a value with given name exists
     *
     * Returns null if a value with given name does not exist or is not set.
     *
     * @api
     * @param   string  $name
     * @return  bool
     * @since   1.7.0
     */
    public function hasValueByName(string $name): bool
    {
        return isset($this->values[$name]);
    }

    /**
     * returns a value by its name
     *
     * Returns null if a value with given name does not exist or is not set.
     *
     * @api
     * @param   string  $name
     * @param   mixed   $default  optional  value to return if value not set
     * @return  mixed
     * @since   1.7.0
     */
    public function getValueByName(string $name, $default = null)
    {
        if (isset($this->values[$name])) {
            return $this->parseType($this->values[$name]);
        }

        return $default;
    }

    /**
     * returns a parser instance for the value
     *
     * Actual call the parsing methods on the parser returns null if a value
     * with given name does not exist or is not set.
     *
     * @api
     * @param   string  $name
     * @return  \stubbles\values\Parse
     * @since   5.0.0
     */
    public function parse(string $name): Parse
    {
        if (isset($this->values[$name])) {
            return new Parse($this->values[$name]);
        }

        return new Parse(null);
    }

    /**
     * responds to a method call of an undefined method
     *
     * @param   string  $name
     * @param   array   $arguments
     * @return  mixed
     * @throws  \ReflectionException
     */
    public function  __call(string $name, array $arguments)
    {
        if (isset($this->values[$name])) {
            return $this->parseType($this->values[$name]);
        }

        if (substr($name, 0, 3) === 'get') {
            return $this->getProperty(
                            strtolower(substr($name, 3, 1)) . substr($name, 4),
                            $this->extractDefaultValue($arguments)
                    );
        }

        if (substr($name, 0, 2) === 'is') {
            return $this->getBooleanProperty(strtolower(substr($name, 2, 1)) . substr($name, 3));
        }

        if (substr($name, 0, 3) === 'has') {
            return $this->hasProperty(strtolower(substr($name, 3, 1)) . substr($name, 4));
        }

        $annotationName = $this->type . ($this->name !== $this->type ? ('[' . $this->name . ']') : '');
        throw new \ReflectionException(
                'The value with name "' . $name . '" for annotation @'
                . $annotationName . ' at ' . $this->target . ' does not exist'
        );
    }

    /**
     * returns first value in array or null if it does not exist
     *
     * @param   array  $arguments
     * @return  mixed
     */
    protected function extractDefaultValue(array $arguments)
    {
        if (count($arguments) === 0) {
            return null;
        }

        return array_shift($arguments);
    }

    /**
     * returns property which is retrieved via get$PROPERTYNAME()
     *
     * @param   string  $propertyName
     * @param   mixed   $defaultValue
     * @return  mixed
     */
    protected function getProperty(string $propertyName, $defaultValue)
    {
        if (count($this->values) === 1 && isset($this->values['__value'])) {
            return $this->parseType($this->values['__value']);
        }

        if (isset($this->values[$propertyName])) {
            return $this->parseType($this->values[$propertyName]);
        }

        return $defaultValue;
    }

    /**
     * parses value to correct type
     *
     * @param   string  $value
     * @return  mixed
     */
    private function parseType(string $value)
    {
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            return substr($value, 1, strlen($value) - 2);
        }

        return Parse::toType($value);
    }

    /**
     * returns boolean property which is retrieved via is$PROPERTYNAME()
     *
     * @param   string  $propertyName
     * @return  bool
     */
    protected function getBooleanProperty(string $propertyName): bool
    {
        if (count($this->values) === 1 && isset($this->values['__value'])) {
            return Parse::toBool($this->values['__value']);
        }

        if (isset($this->values[$propertyName])) {
            return Parse::toBool($this->values[$propertyName]);
        }

        return false;
    }

    /**
     * checks if property which is checked via has$PROPERTYNAME() is set
     *
     * @param   string  $propertyName
     * @return  bool
     */
    protected function hasProperty(string $propertyName): bool
    {
        if (count($this->values) === 1
          && isset($this->values['__value'])
          && 'value' === $propertyName) {
            return isset($this->values['__value']);
        }

        return isset($this->values[$propertyName]);
    }

    /**
     * returns a string representation of the class
     *
     * @XmlIgnore
     * @return  string
     */
    public function __toString(): string
    {
        $result = '@' . $this->name;
        if (null !== $this->type) {
            $result .= '[' . $this->type . ']';
        }

        $result .= '(';
        if (count($this->values) === 1 && isset($this->values['__value'])) {
            $result .= $this->values['__value'];
        } else {
            $params = [];
            foreach ($this->values as $name => $value) {
                $params[] = $name .'=' . $value;
            }

            $result .= join(', ', $params);
        }

        return $result . ')';
    }
}
