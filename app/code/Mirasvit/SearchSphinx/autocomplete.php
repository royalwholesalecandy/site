<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-sphinx
 * @version   1.1.40
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



use Mirasvit\SearchSphinx\SphinxQL\SphinxQL;
use Mirasvit\SearchSphinx\SphinxQL\Expression as QLExpression;

if (php_sapi_name() == "cli") {
    return;
}

$configFile = BP . '/app/etc/autocomplete.json';
if (!file_exists($configFile)) {
    return;
}

$config = \Zend_Json::decode(file_get_contents($configFile));

if ($config['engine'] !== 'sphinx') {
    return;
}

class SphinxAutocomplete
{
    private $config;

    public function __construct(
        array $config
    ) {
        $this->config = $config;
    }

    public function process()
    {
        $result = [];
        $totalItems = 0;

        foreach ($this->config['indexes'] as $i => $config) {
            $identifier = $config['identifier'];
            $sphinxQL = new SphinxQL($this->getConnection());
            $metaQL = new SphinxQL($this->getConnection());

            $response = $sphinxQL
                ->select(['autocomplete'])
                ->from($config['index'])
                ->match('*', $this->getQuery())
                ->limit(0, $config['limit'])
                ->option('max_matches', 1000000)
                ->option('field_weights', $this->getWeights($i))
                ->enqueue($metaQL->query('SHOW META'))
                ->enqueue()
                ->executeBatch();

            $total = $response[1][0]['Value'];
            $items = $this->mapHits($response[0], $config);

            if ($total && $items) {
                $result['indices'][] = [
                    'identifier'   => $identifier == 'catalogsearch_fulltext' ? 'magento_catalog_product' : $identifier,
                    'isShowTotals' => true,
                    'order'        => $config['order'],
                    'title'        => $config['title'],
                    'totalItems'   => $total,
                    'items'        => $items,
                ];
                $totalItems += $total;
            }
        }

        $result['query'] = $this->getQueryText();
        $result['totalItems'] = $totalItems;
        $result['noResults'] = $totalItems == 0;
        $result['textEmpty'] = sprintf($this->config['textEmpty'], $this->getQueryText());
        $result['textAll'] = sprintf($this->config['textAll'], $result['totalItems']);
        $result['urlAll'] = $this->config['urlAll'] . $this->getQueryText();

        return $result;
    }

    private function getConnection()
    {
        $connection = new \Mirasvit\SearchSphinx\SphinxQL\Connection();
        $connection->setParams([
                'host' => $this->config['host'],
                'port' => $this->config['port'],
            ]);

        return $connection;
    }

    private function getWeights($identifier)
    {
        $weights = [];
        foreach ($this->config['indexes'][$identifier]['fields'] as $f => $w) {
            $weights['`'. $f .'`'] = pow(2, $w);
        }

        return $weights;
    }

    private function getQueryText()
    {
        return isset($_GET['q']) ? $_GET['q'] : '';
    }

    private function getQuery()
    {
        $terms = array_filter(explode(" ", $this->getQueryText()));

        $conditions = [];
        foreach ($terms as $term) {
            $term = $this->escape($term);
            $conditions[] = "($term | *$term*)";
        }

        return new QLExpression(implode(" ", $conditions));
    }

    private function escape($value)
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }

    private function mapHits($response, $config)
    {
        $items = [];
        foreach ($response as $hit) {
            if (count($items) > $config['limit']) {
                break;
            }

            $item = [
                'name'        => null,
                'url'         => null,
                'sku'         => null,
                'image'       => null,
                'description' => null,
                'price'       => null,
                'rating'      => null,
            ];

            try {
                $item = array_merge($item, \Zend_Json::decode($hit['autocomplete']));

                $item['cart'] = [
                    'visible' => false,
                    'params'  => [
                        'action' => null,
                        'data'   => [
                            'product' => null,
                            'uenc'    => null,
                        ],
                    ],
                ];

                $items[] = $item;
            } catch (\Exception $e) {
            }
        }

        return $items;
    }
}

$result = (new \SphinxAutocomplete($config))->process();

echo \Zend_Json::encode($result);
die();
