<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace Chamilo\CoreBundle\Entity\Listener;

use Chamilo\CoreBundle\Entity\ResourceNode;
use Chamilo\CoreBundle\Entity\User;
use Chamilo\CoreBundle\Repository\Node\UserRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Exception;
use Symfony\Component\Security\Core\Security;

class UserListener
{
    protected UserRepository $userRepository;
    protected Security $security;

    public function __construct(UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    /**
     * This code is executed when a new user is created.
     */
    public function prePersist(User $user, LifecycleEventArgs $args): void
    {
        error_log('User listener prePersist');
        if ($user) {
            $this->userRepository->updateCanonicalFields($user);
            $this->userRepository->updatePassword($user);

            if (!$user->hasResourceNode()) {
                $em = $args->getEntityManager();
                $resourceNode = new ResourceNode();
                $resourceNode
                    ->setTitle($user->getUsername())
                    ->setCreator($this->security->getUser())
                    ->setResourceType($this->userRepository->getResourceType())
                ;
                $em->persist($resourceNode);
                $user->setResourceNode($resourceNode);
            }
        }
    }

    /**
     * This code is executed when a user is updated.
     *
     * @throws Exception
     */
    public function preUpdate(User $user, LifecycleEventArgs $args): void
    {
        error_log('User listener preUpdate');
        if ($user) {
            $this->userRepository->updatePassword($user);
            $this->userRepository->updateCanonicalFields($user);
        }
    }
}
