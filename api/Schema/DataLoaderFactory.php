<?php
/**
 * Created by PhpStorm.
 * User: skambalin
 * Date: 21.10.17
 * Time: 12.57
 */

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\IDFactoryInterface;
use GraphQL\Executor\Promise\PromiseAdapter;
use Overblog\DataLoader\DataLoader;
use Overblog\PromiseAdapter\Adapter\WebonyxGraphQLSyncPromiseAdapter;

class DataLoaderFactory implements DataLoaderFactoryInterface
{
    /**
     * @var WebonyxGraphQLSyncPromiseAdapter
     */
    protected $promiseAdapter;

    /**
     * @var ContextInterface
     */
    protected $context;

    public function __construct(PromiseAdapter $promiseAdapter, ContextInterface $context)
    {
        $this->promiseAdapter = new WebonyxGraphQLSyncPromiseAdapter($promiseAdapter);
        $this->context = $context;
    }

    public function create(callable $source, $emptyValue = null)
    {
        $loader = new DataLoader($this->createBatchLoadFn($source, $emptyValue), $this->promiseAdapter);

        return new DataLoaderDecorator($loader, function($key, $args = []) {
            return $this->buildKey($key, $args);
        });
    }

    protected function createBatchLoadFn(callable $source, $defaultValue = null) {
        return function($keys) use ($source, $defaultValue) {
            $out = array_fill(0, count($keys), $defaultValue);
            $buckets = $this->createBuckets($keys);

            foreach ($buckets as $bucket) {
                list($ids, $args, $bucketKeyIndexes) = $bucket;
                $bucketData = $this->retrieveItems($source, $ids, $args, $defaultValue);

                foreach ($bucketKeyIndexes as $index => $keyIndex) {
                    $out[$keyIndex] = $bucketData[$index];
                }
            }

            return $this->promiseAdapter->createFulfilled(array_values($out));
        };
    }

    protected function buildKey($key, array $args = []) {
        return [$key, $args];
    }

    protected function parseKey($key) {
        $id = null;
        $args = [];

        if (is_array($key)) { // contains arguments
            $id = empty($key[0]) ? null : $key[0];
            $args = empty($key[1]) ? [] : $key[1];
        } else { // simple key = id
            $id = $key;
        }

        return [
            $id,
            $args
        ];
    }

    protected function createBuckets(array $keys) {
        $byArgs = [];
        foreach ($keys as $index => $key) {
            list($id, $args) = $this->parseKey($key);
            $argsKey = json_encode($args);
            $byArgs[$argsKey] = empty($byArgs[$argsKey]) ? [[], $args, []] : $byArgs[$argsKey];

            $byArgs[$argsKey][0][] = $id;
            $byArgs[$argsKey][2][] = $index;
        }

        return array_values($byArgs);
    }

    protected function retrieveItems(callable $source, $ids, $args = [], $defaultValue = null) {
        $dataList = $source($ids, $args, $this->context);
        $out = array_fill(0, count($ids), $defaultValue);

        foreach ($ids as $index => $id) {
            $stringId = (string) $id;
            if (array_key_exists($stringId, $dataList)) {
                $out[$index] = $dataList[$stringId];
            }
        }

        return $out;
    }
}
