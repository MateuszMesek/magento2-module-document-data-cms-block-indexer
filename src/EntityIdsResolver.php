<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataCmsBlockIndexer;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\DB\Select;
use MateuszMesek\DocumentDataIndexerApi\DimensionResolverInterface;
use MateuszMesek\DocumentDataIndexerApi\EntityIdsResolverInterface;
use Traversable;

class EntityIdsResolver implements EntityIdsResolverInterface
{
    private DimensionResolverInterface $storeIdResolver;
    private CollectionFactory $collectionFactory;

    public function __construct(
        DimensionResolverInterface $storeIdResolver,
        CollectionFactory $collectionFactory
    )
    {
        $this->storeIdResolver = $storeIdResolver;
        $this->collectionFactory = $collectionFactory;
    }

    public function resolve(array $dimensions): Traversable
    {
        $storeId = $this->storeIdResolver->resolve($dimensions);

        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter($storeId);
        $collection->setOrder(BlockInterface::BLOCK_ID, $collection::SORT_ORDER_ASC);
        $collection->setPageSize(100);
        $collection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns([BlockInterface::BLOCK_ID])
            ->setPart('disable_staging_preview', true);

        $lastId = 0;

        while (true) {
            $part = (clone $collection);
            $part->getSelect()->where('block_id > ?', $lastId);
            $part->load();

            $ids = array_map(
                static function (BlockInterface $block) {
                    return (int)$block->getId();
                },
                $part->getItems()
            );

            if (empty($ids)) {
                return;
            }

            $lastId = end($ids);

            yield from $ids;
        }
    }
}
