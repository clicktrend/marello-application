<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\WarehouseAddedToUserGroup;

class WarehouseAddedToUserGroupValidator extends ConstraintValidator
{
    const ALIAS = 'marelloenterprise_inventory.warehouse_added_to_user_group';

    /**
     * @var ExecutionContextInterface
     */
    protected $context;

    /**
     * @param mixed $value
     * @param WarehouseAddedToUserGroup|Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Warehouse) {
            throw new UnexpectedTypeException($value, Warehouse::class);
        }
        $group = $value->getGroup();
        if ($group && !$group->isSystem() &&
            $value->getWarehouseType()->getName() !== WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL) {
            $this->context->buildViolation($constraint->message)
                ->atPath('warehouseType')
                ->addViolation();
        }
    }
}
