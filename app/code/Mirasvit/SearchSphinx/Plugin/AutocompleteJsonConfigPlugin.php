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



namespace Mirasvit\SearchSphinx\Plugin;

use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\SearchSphinx\Model\Config;

class AutocompleteJsonConfigPlugin
{
    private $config;

    private $indexRepository;

    public function __construct(
        Config $config,
        IndexRepositoryInterface $indexRepository
    ) {
        $this->config = $config;
        $this->indexRepository = $indexRepository;
    }

    public function afterGenerate($subject, $config)
    {
        if ($config['engine'] !== 'sphinx') {
            return $config;
        }

        $config = array_merge($config, $this->getEngineConfig());

        foreach ($config['indexes'] as $i => $data) {
            $identifier = $data['identifier'];
            $data = array_merge($data, $this->getEngineIndexConfig($identifier, 1));
            $config['indexes'][$i] = $data;
        }

        return $config;
    }

    /**
     * @param string $identifier
     * @param $dimension
     * @return array
     */
    public function getEngineIndexConfig($identifier, $dimension)
    {
        $index = $this->indexRepository->get($identifier);

        $instance = $this->indexRepository->getInstance($index);
        $indexName = $instance->getIndexer()->getIndexName($dimension);

        $result = [];
        $result['index'] = $indexName;
        $result['fields'] = $instance->getAttributeWeights();

        return $result;
    }

    /**
     * @return array
     */
    public function getEngineConfig()
    {
        return [
            'host'      => $this->config->getHost(),
            'port'      => $this->config->getPort(),
            'available' => true,
        ];
    }
}
