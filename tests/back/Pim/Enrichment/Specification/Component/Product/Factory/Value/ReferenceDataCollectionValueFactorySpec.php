<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReferenceDataCollectionValueFactorySpec extends ObjectBehavior
{
    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_reference_data_attribute_type()
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::REFERENCE_DATA_MULTI_SELECT);
    }

    public function it_supports_null()
    {
        $attribute = $this->getAttribute(true, true);
        $value = $this->createByCheckingData($attribute, 'ecommerce', 'fr_FR', null);
        $value->shouldBeLike(ReferenceDataCollectionValue::scopableLocalizableValue('an_attribute', [], 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_and_scopable_value()
    {
        $attribute = $this->getAttribute(true, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', ['blue', 'green']);
        $value->shouldBeLike(ReferenceDataCollectionValue::scopableLocalizableValue('an_attribute', ['blue', 'green'], 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value()
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', ['blue', 'green']);
        $value->shouldBeLike(ReferenceDataCollectionValue::localizableValue('an_attribute', ['blue', 'green'], 'fr_FR'));
    }

    public function it_creates_a_scopable_value()
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, ['blue', 'green']);
        $value->shouldBeLike(ReferenceDataCollectionValue::scopableValue('an_attribute', ['blue', 'green'], 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, ['blue', 'green']);
        $value->shouldBeLike(ReferenceDataCollectionValue::value('an_attribute', ['blue', 'green']));
    }

    public function it_throws_an_exception_when_it_is_not_an_array()
    {
        $attribute = $this->getAttribute(false, false);
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [$attribute, null, null, 'no an array']);
    }

    public function it_throws_an_exception_when_it_is_not_an_array_of_string()
    {
        $attribute = $this->getAttribute(false, false);
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [$attribute, null, null, [[]]]);
    }

    public function it_orders_reference_data_by_alphabetic_order()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createByCheckingData($attribute, null, null, ['green', 'blue']);
        $value->shouldBeLike(ReferenceDataCollectionValue::value('an_attribute', ['blue', 'green']));
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::REFERENCE_DATA_MULTI_SELECT, ['reference_data_name' => 'color'], $isLocalizable, $isScopable, null, false);
    }
}
