<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Resource\Entity\Attribute;

/**
 * Eav attribute set resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Set extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * EAV cache ids
     */
    const ATTRIBUTES_CACHE_ID = 'EAV_ENTITY_ATTRIBUTES_BY_SET_ID';

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\GroupFactory
     */
    protected $_attrGroupFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param GroupFactory $attrGroupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Eav\Model\Resource\Entity\Attribute\GroupFactory $attrGroupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_attrGroupFactory = $attrGroupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_attribute_set', 'attribute_set_id');
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getGroups()) {
            /* @var $group \Magento\Eav\Model\Entity\Attribute\Group */
            foreach ($object->getGroups() as $group) {
                $group->setAttributeSetId($object->getId());
                if ($group->itemExists() && !$group->getId()) {
                    continue;
                }
                $group->save();
            }
        }
        if ($object->getRemoveGroups()) {
            foreach ($object->getRemoveGroups() as $group) {
                /* @var $group \Magento\Eav\Model\Entity\Attribute\Group */
                $group->delete();
            }
            $this->_attrGroupFactory->create()->updateDefaultGroup($object->getId());
        }
        if ($object->getRemoveAttributes()) {
            foreach ($object->getRemoveAttributes() as $attribute) {
                /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $attribute->deleteEntity();
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * Perform actions before object delete
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Eav\Api\Data\AttributeSetInterface $object */
        $defaultAttributeSetId = $this->eavConfig
            ->getEntityType($object->getEntityTypeId())
            ->getDefaultAttributeSetId();
        if ($object->getAttributeSetId() == $defaultAttributeSetId) {
            throw new \Magento\Framework\Exception\StateException('Default attribute set can not be deleted');
        }
        return parent::_beforeDelete($object);
    }

    /**
     * Validate attribute set name
     *
     * @param \Magento\Eav\Model\Entity\Attribute\Set $object
     * @param string $attributeSetName
     * @return bool
     */
    public function validate($object, $attributeSetName)
    {
        $adapter = $this->_getReadAdapter();
        $bind = ['attribute_set_name' => trim($attributeSetName), 'entity_type_id' => $object->getEntityTypeId()];
        $select = $adapter->select()->from(
            $this->getMainTable()
        )->where(
            'attribute_set_name = :attribute_set_name'
        )->where(
            'entity_type_id = :entity_type_id'
        );

        if ($object->getId()) {
            $bind['attribute_set_id'] = $object->getId();
            $select->where('attribute_set_id != :attribute_set_id');
        }

        return !$adapter->fetchOne($select, $bind) ? true : false;
    }

    /**
     * Retrieve Set info by attributes
     *
     * @param array $attributeIds
     * @param int $setId
     * @return array
     */
    public function getSetInfo(array $attributeIds, $setId = null)
    {
        $cacheKey = self::ATTRIBUTES_CACHE_ID . $setId;
        $adapter = $this->_getReadAdapter();
        $setInfo = [];
        $setInfoData = [];

        if ($this->eavConfig->isCacheEnabled() && ($cache = $this->eavConfig->getCache()->load($cacheKey))) {
            $setInfoData = unserialize($cache);
            foreach ($attributeIds as $attributeId) {
                $setInfo[$attributeId] = isset($setInfoData[$attributeId]) ? $setInfoData[$attributeId] : [];
            }
        } else {
            $select = $adapter->select()->from(
                ['entity' => $this->getTable('eav_entity_attribute')],
                ['attribute_id', 'attribute_set_id', 'attribute_group_id', 'sort_order']
            )->joinLeft(
                ['attribute_group' => $this->getTable('eav_attribute_group')],
                'entity.attribute_group_id = attribute_group.attribute_group_id',
                ['group_sort_order' => 'sort_order']
            );
            $bind = [];
            if (is_numeric($setId)) {
                $bind[':attribute_set_id'] = $setId;
                $select->where('entity.attribute_set_id = :attribute_set_id');
            }
            $result = $adapter->fetchAll($select, $bind);

            foreach ($result as $row) {
                $data = [
                    'group_id' => $row['attribute_group_id'],
                    'group_sort' => $row['group_sort_order'],
                    'sort' => $row['sort_order'],
                ];
                $setInfoData[$row['attribute_id']][$row['attribute_set_id']] = $data;
            }

            if ($this->eavConfig->isCacheEnabled()) {
                $this->eavConfig->getCache()->save(
                    serialize($setInfoData),
                    $cacheKey,
                    [
                        \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                        \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                    ]
                );
            }

            foreach ($attributeIds as $attributeId) {
                $setInfo[$attributeId] = isset($setInfoData[$attributeId]) ? $setInfoData[$attributeId] : [];
            }
        }

        return $setInfo;
    }

    /**
     * Retrurn default attribute group id for attribute set id
     *
     * @param int $setId
     * @return int|null
     */
    public function getDefaultGroupId($setId)
    {
        $adapter = $this->_getReadAdapter();
        $bind = ['attribute_set_id' => (int)$setId];
        $select = $adapter->select()->from(
            $this->getTable('eav_attribute_group'),
            'attribute_group_id'
        )->where(
            'attribute_set_id = :attribute_set_id'
        )->where(
            'default_id = 1'
        )->limit(
            1
        );
        return $adapter->fetchOne($select, $bind);
    }
}
