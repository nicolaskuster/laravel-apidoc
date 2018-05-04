<?php

namespace Nicolaskuster\ApiDoc\Support;


use Illuminate\Support\Collection;
use Nicolaskuster\ApiDoc\Models\Route;

class DefaultRouteSorter implements RouteSorter
{
    /**
     * The sorting order
     *
     * @var array
     */
    protected $order = [];

    /**
     * DefaultRouteSorter constructor.
     * @param [] $config
     */
    public function __construct($config)
    {
        $this->order = $config['order'];
    }

    /**
     * {@inheritdoc}
     */
    public function sort(Collection $collection): Collection
    {
        $collection = $collection->groupBy(function (Route $route) {
            return $route->getGroupId();
        });

        $collection = $collection->map(function (Collection $item) {
            return $item->sort(function (Route $a, Route $b) {
                return $this->getSortNumber($a->getSortKey()) > $this->getSortNumber($b->getSortKey());
            });
        });

        return $collection->flatten(1);
    }

    /**
     * Evaluate the sort number for the given key
     *
     * @param $sortKey
     * @return false|int
     */
    protected function getSortNumber($sortKey): int
    {
        $needle = array_search($sortKey, $this->order);
        return $needle === false ? count($this->order) + 1 : $needle;
    }
}