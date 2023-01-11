<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataCmsBlockIndexer\Model;

use MateuszMesek\DocumentDataCmsBlock\Model\Command\GetDocumentDataByBlockIdAndStoreId;
use MateuszMesek\DocumentDataIndexIndexerApi\Model\DataResolverInterface;
use MateuszMesek\DocumentDataIndexIndexerApi\Model\DimensionResolverInterface;
use Traversable;

class DataResolver implements DataResolverInterface
{
    public function __construct(
        private readonly DimensionResolverInterface         $storeIdResolver,
        private readonly GetDocumentDataByBlockIdAndStoreId $getDocumentDataByBlockIdAndStoreId
    )
    {
    }

    public function resolve(array $dimensions, Traversable $entityIds): Traversable
    {
        $storeId = $this->storeIdResolver->resolve($dimensions);

        foreach ($entityIds as $entityId) {
            $data = $this->getDocumentDataByBlockIdAndStoreId->execute((int)$entityId, $storeId);

            yield $entityId => $data;
        }
    }
}
