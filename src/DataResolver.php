<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataCmsBlockIndexer;

use MateuszMesek\DocumentDataCmsBlock\Command\GetDocumentDataByBlockIdAndStoreId;
use MateuszMesek\DocumentDataIndexerApi\DataResolverInterface;
use MateuszMesek\DocumentDataIndexerApi\DimensionResolverInterface;
use Traversable;

class DataResolver implements DataResolverInterface
{
    private DimensionResolverInterface $storeIdResolver;
    private GetDocumentDataByBlockIdAndStoreId $getDocumentDataByBlockIdAndStoreId;

    public function __construct(
        DimensionResolverInterface $storeIdResolver,
        GetDocumentDataByBlockIdAndStoreId $getDocumentDataByBlockIdAndStoreId
    )
    {
        $this->storeIdResolver = $storeIdResolver;
        $this->getDocumentDataByBlockIdAndStoreId = $getDocumentDataByBlockIdAndStoreId;
    }

    public function resolve(array $dimensions, Traversable $entityIds): Traversable
    {
        $storeId = $this->storeIdResolver->resolve($dimensions);

        foreach ($entityIds as $entityId) {
            $data = $this->getDocumentDataByBlockIdAndStoreId->execute($entityId, $storeId);

            if (empty($data)) {
                return;
            }

            yield $data;
        }
    }
}
