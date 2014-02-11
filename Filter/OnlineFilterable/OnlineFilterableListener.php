<?php

namespace DCS\DoctrineExtensionsBundle\Filter\OnlineFilterable;

use Gedmo\Mapping\MappedEventSubscriber;
use Doctrine\Common\EventArgs;

/**
 * OnlineFilterable listener
 */
class OnlineFilterableListener extends MappedEventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
        );
    }

    /**
     * Mapps additional metadata
     *
     * @param EventArgs $eventArgs
     * @return void
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
