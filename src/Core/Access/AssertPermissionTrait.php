<?php
/*
 * This file is part of Flarum.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flarum\Core\Access;

use DateTime;
use Flarum\Api\Exception\InvalidAccessTokenException;
use Flarum\Core\Exception\PermissionDeniedException;
use Flarum\Core\User;
use Psr\Http\Message\ServerRequestInterface;

trait AssertPermissionTrait
{
    /**
     * @param $condition
     * @throws PermissionDeniedException
     */
    protected function assertPermission($condition)
    {
        if (! $condition) {
            throw new PermissionDeniedException;
        }
    }

    /**
     * @param User $actor
     * @param string $ability
     * @param mixed $arguments
     * @throws PermissionDeniedException
     */
    protected function assertCan(User $actor, $ability, $arguments = [])
    {
        $this->assertPermission($actor->can($ability, $arguments));
    }

    /**
     * @param User $actor
     * @throws PermissionDeniedException
     */
    protected function assertGuest(User $actor)
    {
        $this->assertPermission($actor->isGuest());
    }

    /**
     * @param User $actor
     * @throws PermissionDeniedException
     */
    protected function assertRegistered(User $actor)
    {
        $this->assertPermission(! $actor->isGuest());
    }

    /**
     * @param User $actor
     * @throws PermissionDeniedException
     */
    protected function assertAdmin(User $actor)
    {
        $this->assertCan($actor, 'administrate');
    }

    /**
     * @param ServerRequestInterface $request
     * @throws InvalidAccessTokenException
     */
    protected function assertSudo(ServerRequestInterface $request)
    {
        $session = $request->getAttribute('session');

        if ($session && $session->get('sudo_expiry') < new DateTime) {
            throw new InvalidAccessTokenException;
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @throws PermissionDeniedException
     */
    protected function assertAdminAndSudo(ServerRequestInterface $request)
    {
        $this->assertAdmin($request->getAttribute('actor'));

        $this->assertSudo($request);
    }
}
