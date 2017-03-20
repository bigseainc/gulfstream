<?php

namespace BigSea\Gulfstream\Admin\Models;

use Valitron\Validator;

class BaseModel
{

    public $allowed_properties = [];

    public $default_values = [];

    public $properties = [];

    public function __construct($data = null)
    {
        if (is_array($data)) {
            $this->setMany($data);
            return;
        }
        $this->setDefaults();
    }

    /**
     * validation_rules
     * Should be an array of parameters to be passed to Valitron's ->rule method.
     * Example:
     * <code>
     * public $validation_rules = [
     *         [ 'required', [ 'address', 'city', 'state', 'zip' ] ],
     *         [ 'regex', [ 'zip' ], '/[0-9]{5}(-[0-9]{4})?/' ]
     * ];
     * </code>
     *
     * @var    mixed
     * @access public
     */
    public $validation_rules = [];

    public function setDefaults()
    {
        if (!empty($this->default_values)) {
            foreach ($this->default_values as $key => &$value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Set a lot of fields at once (e.g. post data)
     *
     * @param  array $data
     * @access public
     * @return void
     */
    public function setMany(array &$data)
    {
        foreach ($data as $key => &$value) {
            $this->set($key, $value);
        }
    }

    public function toArray()
    {
        return $this->properties;
    }


    /**
     * validate
     * Runs validation rules.
     *
     * @access public
     * @return true|array true on success, array of errors on failure
     */
    public function validate()
    {
        $fields = $this->getValidatedFields();
        $validator = new Validator($fields);
        foreach ($this->validation_rules as $ruleset) {
            call_user_func_array([$validator, 'rule'], $ruleset);
        }
        if (! $validator->validate()) {
            return $validator->errors();
        }
        return true;
    }

    /**
     * set
     * Modify set to only set whitelisted values.
     *
     * @param  mixed $field
     * @param  mixed $value
     * @access public
     * @return void
     */
    public function set($property, $value = null)
    {
        if (! in_array($property, $this->allowed_properties) && ! property_exists($this, $property)) {
            throw new \Exception('Cannot set property ' . $property . ' on model ' . get_class($this));
        }
        $this->properties[$property] = $value;
    }

    public function __set($property, $value = null)
    {
        return $this->set($property, $value);
    }

    public function get($property)
    {
        if (isset($this->properties[$property])) {
            return $this->properties[$property];
        }
        return null;
    }

    public function __get($property)
    {
        return $this->get($property);
    }

    public function __unset($prop)
    {
        if (isset($this->properties[$prop])) {
            unset($this->properties[$prop]);
        }
    }

    public function __isset($prop)
    {
        return isset($this->properties[$prop]);
    }

    /**
     * getValidatedFieldList
     * Gets a list of fields which the model validates based
     * on its validation rules.
     *
     * @access private
     * @return array
     */
    private function getValidatedFieldList()
    {
        $fields = [];
        foreach ($this->validation_rules as &$ruleset) {
            $fields = array_merge($fields, $ruleset[1]);
        }
        return array_unique($fields);
    }

    /**
     * getValidatedFields
     * Returns the data contained in the fields which the model validates
     *
     * @access private
     * @return array
     */
    private function getValidatedFields()
    {
        $fields = $this->getValidatedFieldList();
        $data = [];
        foreach ($fields as &$field) {
            $data[$field] = $this->get($field);
        }
        return $data;
    }
}
