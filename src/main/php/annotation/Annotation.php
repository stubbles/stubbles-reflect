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
     * original annotation type
     */
    private string $type;

    /**
     * constructor
     *
     * @param  string               $name    name of annotation, in case of casted annotations it the casted type
     * @param  string               $target  optional  name of target where annotation is for, i.e. the class, method, function, property or parameter
     * @param  array<string,mixed>  $values  optional  map of all annotation values
     * @param  string               $type    optional  type of annotation in case $name reflects a casted type
     */
    public function __construct(
            private string $name,
            private string $target,
            private array $values = [],
            ?string $type = null
    ) {
        $this->type   = (null === $type) ? $name : $type;
    }

    /**
     * Returns the name under which the annotation is stored.
     *
     * @api
     */
    public function getAnnotationName(): string
    {
        return $this->name;
    }

    /**
     * returns name of target where annotation is for, i.e. the class, method, function, property or parameter
     *
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
    public function getValueByName(string $name, $default = null): mixed
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
     * @param   array<string,mixed>  $arguments
     * @throws  \ReflectionException
     */
    public function  __call(string $name, array $arguments): mixed
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
     * @param   array<string,mixed>  $arguments
     */
    protected function extractDefaultValue(array $arguments): mixed
    {
        if (empty($arguments)) {
            return null;
        }

        return array_shift($arguments);
    }

    /**
     * returns property which is retrieved via get$PROPERTYNAME()
     */
    protected function getProperty(string $propertyName, mixed $defaultValue): mixed
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
     */
    private function parseType(string $value): mixed
    {
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            return substr($value, 1, strlen($value) - 2);
        }

        return Parse::toType($value);
    }

    /**
     * returns boolean property which is retrieved via is$PROPERTYNAME()
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
     */
    protected function hasProperty(string $propertyName): bool
    {
        if (count($this->values) === 1
          && isset($this->values['__value'])
          && 'value' === $propertyName) {
            return true;
        }

        return isset($this->values[$propertyName]);
    }

    /**
     * returns a string representation of the class
     *
     * @XmlIgnore
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
