<?php

namespace Teebb\SBAdmin2Bundle\Twig\Extension;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Teebb\SBAdmin2Bundle\Voter\FieldVote;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TeebbSBAdmin2TwigExtension extends AbstractExtension
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [$this, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_granted_affirmative', [$this, 'isGrantedAffirmative']),
        ];
    }

    /**
     * @param string|array $role
     * @param object|null  $object
     * @param string|null  $field
     *
     * @return bool
     */
    public function isGrantedAffirmative($role, $object = null, $field = null)
    {
        if (null === $this->authorizationChecker) {
            return false;
        }

        if (null !== $field) {
            $object = new FieldVote($object, $field);
        }

        if (!\is_array($role)) {
            $role = [$role];
        }

        foreach ($role as $oneRole) {
            try {
                if ($this->authorizationChecker->isGranted($oneRole, $object)) {
                    return true;
                }
            } catch (AuthenticationCredentialsNotFoundException $e) {
                // empty on purpose
            }
        }

        return false;
    }
}
