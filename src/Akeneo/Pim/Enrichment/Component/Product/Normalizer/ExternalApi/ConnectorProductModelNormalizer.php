<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectorProductModelNormalizer
{
    /** @var ValuesNormalizer */
    private $valuesNormalizer;

    /** @var DateTimeNormalizer */
    private $dateTimeNormalizer;

    /** @var FillMissingValuesInterface */
    private $fillMissingValues;

    public function __construct(
        ValuesNormalizer $valuesNormalizer,
        DateTimeNormalizer $dateTimeNormalizer,
        FillMissingValuesInterface $fillMissingValues
    ) {
        $this->valuesNormalizer = $valuesNormalizer;
        $this->dateTimeNormalizer = $dateTimeNormalizer;
        $this->fillMissingValues = $fillMissingValues;
    }

    public function normalizeConnectorProductModelList(ConnectorProductModelList $list): array
    {
        return array_map(function (ConnectorProductModel $connectorProductModel): array {
            return $this->normalizeConnectorProductModel($connectorProductModel);
        }, $list->connectorProductModels());
    }

    public function normalizeConnectorProductModel(ConnectorProductModel $connectorProductModel): array
    {

        $normalizedProductModel = [
            'code' => $connectorProductModel->code(),
            'family' => $connectorProductModel->familyCode(),
            'family_variant' => $connectorProductModel->familyVariantCode(),
            'parent' => $connectorProductModel->parentCode(),
            'categories' => $connectorProductModel->categoryCodes(),
            'values' => $values = $this->valuesNormalizer->normalize($connectorProductModel->values(), 'standard'),
            'created' => $this->dateTimeNormalizer->normalize($connectorProductModel->createdDate()),
            'updated' => $this->dateTimeNormalizer->normalize($connectorProductModel->updatedDate()),
            'associations' => empty($connectorProductModel->associations()) ? (object) [] : $connectorProductModel->associations(),
        ];

        if (!empty($connectorProductModel->metadata())) {
            $normalizedProductModel['metadata'] = $connectorProductModel->metadata();
        }

        $standardFilled = $this->fillMissingValues->fromStandardFormat($normalizedProductModel);
        $normalizedProductModel['values'] = empty($standardFilled['values']) ? (object) [] : $standardFilled['values'];

        return $normalizedProductModel;
    }
}
