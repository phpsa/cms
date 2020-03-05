<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmented;
use Statamic\Fields\Value;
use Statamic\Support\Arr;
use Statamic\Support\Str;

abstract class AbstractAugmented implements Augmented
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function all()
    {
        return $this->select();
    }

    public function except($keys)
    {
        return $this->select(array_diff($this->augmentableKeys(), Arr::wrap($keys)));
    }

    public function select($keys = null)
    {
        $arr = [];

        $keys = Arr::wrap($keys ?: $this->augmentableKeys());

        foreach ($keys as $key) {
            $arr[$key] = $this->get($key);
        }

        return $arr;
    }

    abstract protected function keys();

    public function get($handle)
    {
        if (method_exists($this, $method = Str::camel($handle)) && !in_array($method, ['select', 'except'])) {
            return $this->$method();
        }

        if (method_exists($this->data, $method = Str::camel($handle))) {
            return $this->wrapValue($this->data->$method(), $handle);
        }

        return $this->wrapValue($this->getFromData($handle), $handle);
    }

    protected function getFromData($handle)
    {
        $value = $this->data->get($handle);

        if (method_exists($this->data, 'getSupplement')) {
            $value = $this->data->getSupplement($handle) ?? $value;
        }

        return $value;
    }

    private function wrapValue($value, $handle)
    {
        $fields = $this->blueprintFields();

        if (! $fields->has($handle)) {
            return $value;
        }

        return new Value(
            $value,
            $handle,
            $fields->get($handle)->fieldtype(),
            $this->data
        );
    }

    private function blueprintFields()
    {
        return (method_exists($this->data, 'blueprint') && $blueprint = $this->data->blueprint())
            ? $blueprint->fields()->all()
            : collect();
    }

    protected function augmentableKeys()
    {
        return $this->blueprintFields()->keys()->merge($this->keys())->all();
    }
}
