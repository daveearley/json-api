<?php

/*
 * This file is part of JSON-API.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tobscure\JsonApi;

use Tobscure\JsonApi\Exception\InvalidParameterException;

class Parameters
{
    /**
     * @var array
     */
    protected $input;

    /**
     * @param array $input
     */
    public function __construct(array $input)
    {
        $this->input = $input;
    }

    /**
     * Get the includes.
     *
     * @param array $available
     * @return array
     */
    public function getInclude($available = [])
    {
        if ($include = $this->getInput('include')) {
            $relationships = explode(',', $include);

            $invalid = array_diff($relationships, $available);

            if (count($invalid)) {
                throw new InvalidParameterException('Invalid includes [' . implode(',', $invalid) . ']');
            }

            return $relationships;
        }

        return [];
    }

    /**
     * Get number of offset.
     *
     * @param int|null $perPage
     * @return int
     */
    public function getOffset($perPage = null)
    {
        if ($perPage) {
            return $this->getOffsetFromNumber($perPage);
        }

        $offset = (int) $this->getPage('offset');

        if ($offset < 0) {
            throw new InvalidParameterException('page[offset] must be >=0');
        }

        return $offset;
    }

    protected function getOffsetFromNumber($perPage)
    {
        $page = (int) $this->getPage('number');

        if ($page < 1) {
            throw new InvalidParameterException('page[number] must be >=1');
        }

        return ($page - 1) * $perPage;
    }

    /**
     * Get the limit.
     *
     * @return string
     */
    public function getLimit($max = null)
    {
        $limit = $this->getPage('limit') ?: $this->getPage('size');

        if ($max) {
            $limit = min($max, $limit);
        }

        return $limit;
    }

    /**
     * Get the sort.
     *
     * @return array
     */
    public function getSort($available = [])
    {
        $sort = [];

        if ($input = $this->getInput('sort')) {
            $fields = explode(',', $input);

            foreach ($fields as $field) {
                if (substr($field, 0, 1) === '-') {
                    $field = substr($field, 1);
                    $order = 'desc';
                } else {
                    $order = 'asc';
                }

                $sort[$field] = $order;
            }

            $invalid = array_diff(array_keys($sort), $available);

            if (count($invalid)) {
                throw new InvalidParameterException('Invalid sort fields [' . implode(',', $invalid) . ']');
            }
        }

        return $sort;
    }

    /**
     * Get the fields requested for inclusion.
     *
     * @return array
     */
    public function getFields()
    {
        $fields = $this->getInput('fields');

        return array_map(function ($fields) {
            return explode(',', $fields);
        }, $fields);
    }

    /**
     * Get a filter item.
     *
     * @param string $key
     * @return string|null
     */
    public function getFilter()
    {
        return $this->getInput('filter');
    }

    /**
     * Get an input item.
     *
     * @param string $key
     * @return string|null
     */
    protected function getInput($key, $default = null)
    {
        return isset($this->input[$key]) ? $this->input[$key] : $default;
    }

    /**
     * Get the page.
     *
     * @param string $key
     * @return string
     */
    protected function getPage($key)
    {
        $page = $this->getInput('page');

        return isset($page[$key]) ? $page[$key] : '';
    }
}
