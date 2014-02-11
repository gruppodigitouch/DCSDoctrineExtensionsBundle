<?php

namespace DCS\DoctrineExtensionsBundle\Filter\OnlineFilterable\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use DCS\DoctrineExtensionsBundle\Filter\OnlineFilterable\OnlineFilterableListener;

class OnlineFilterableFilter extends SQLFilter
{
    protected $listener;
    protected $entityManager;
    protected $disabled = array();

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        $class = $targetEntity->getName();

        if (array_key_exists($class, $this->disabled) && $this->disabled[$class] === true) {
            return '';
        } elseif (array_key_exists($targetEntity->rootEntityName, $this->disabled) && $this->disabled[$targetEntity->rootEntityName] === true) {
            return '';
        }

        $config = $this->getListener()->getConfiguration($this->getEntityManager(), $targetEntity->name);

        if (!isset($config['onlineFilterable']) || !$config['onlineFilterable']) {
            return '';
        }

        $conn       = $this->getEntityManager()->getConnection();
        $platform   = $conn->getDatabasePlatform();
        $column     = $targetEntity->getQuotedColumnName($config['fieldName'], $platform);

        $addCondSql = '';

        if ($config['allowNull']) {
            $addCondSql .= $platform->getIsNullExpression($targetTableAlias.'.'.$column);
            $addCondSql .= ' OR ';
        }

        $now = $conn->quote(date('Y-m-d H:i:s')); // should use UTC in database and PHP
        $addCondSql .= "{$targetTableAlias}.{$column} > {$now}";

        return "({$addCondSql})";
    }

    public function disableForEntity($class)
    {
        $this->disabled[$class] = true;
    }

    public function enableForEntity($class)
    {
        $this->disabled[$class] = false;
    }

    protected function getListener()
    {
        if ($this->listener === null) {
            $em = $this->getEntityManager();
            $evm = $em->getEventManager();

            foreach ($evm->getListeners() as $listeners) {
                foreach ($listeners as $listener) {
                    if ($listener instanceof OnlineFilterableListener) {
                        $this->listener = $listener;

                        break 2;
                    }
                }
            }

            if ($this->listener === null) {
                throw new \RuntimeException('Listener "OnlineFilterableListener" was not added to the EventManager!');
            }
        }

        return $this->listener;
    }

    protected function getEntityManager()
    {
        if ($this->entityManager === null) {
            $refl = new \ReflectionProperty('Doctrine\ORM\Query\Filter\SQLFilter', 'em');
            $refl->setAccessible(true);
            $this->entityManager = $refl->getValue($this);
        }

        return $this->entityManager;
    }
}