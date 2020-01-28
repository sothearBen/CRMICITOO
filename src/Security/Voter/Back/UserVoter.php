<?php

namespace App\Security\Voter\Back;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter
{
    const SEARCH = 'back_user_search';
    const CREATE = 'back_user_create';
    const READ = 'back_user_read';
    const UPDATE = 'back_user_update';
    const DELETE = 'back_user_delete';
    /**
     * @var Security
     */
    private $security;
    
    /** 
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    protected function supports($attribute, $subject)
    {        
        return in_array($attribute, [
            self::SEARCH,
            self::CREATE,
            self::READ,
            self::UPDATE,
            self::DELETE
        ]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::SEARCH:
                return $this->canSearch($subject, $user);
            case self::CREATE:
                return $this->canCreate($subject, $user);
            case self::READ:
                return $this->canRead($subject, $user);
            case self::UPDATE:
                return $this->canUpdate($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }
        throw new \LogicException('This code should not be reached!');
    }
    
    private function canSearch($subject, User $user)
    {
        return true;
    }

    private function canCreate($subject, User $user)
    {
        return true;
    }

    private function canRead($subject, User $user)
    {
        return true;
    }

    private function canUpdate($subject, User $user)
    {
        return false;
    }

    private function canDelete($subject, User $user)
    {
        return false;
    }
}
