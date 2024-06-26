<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test for the controllers defined inside the BlogController used
 * for managing the blog in the backend.
 *
 * See https://symfony.com/doc/current/testing.html#functional-tests
 *
 * Whenever you test resources protected by a firewall, consider using the
 * technique explained in:
 * https://symfony.com/doc/current/testing/http_authentication.html
 *
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        /** @var User $user */
        $user = $userRepository->findOneByUsername('jane_admin');
        $this->client->loginUser($user);
    }

    /**
     * @dataProvider getUrlsForRegularUsers
     */
    public function testAccessDeniedForRegularUsers(string $httpMethod, string $url): void
    {
        $this->client->getCookieJar()->clear();

        /** @var UserRepository $userRepository */
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        /** @var User $user */
        $user = $userRepository->findOneByUsername('john_user');
        $this->client->loginUser($user);

        $this->client->request($httpMethod, $url);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function getUrlsForRegularUsers(): \Generator
    {
        yield ['GET', '/en/admin/users/'];
    }

    public function testAdminBackendHomePage(): void
    {
        $this->client->request('GET', '/en/admin/users/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists(
            'body#admin_users_index #main tbody tr',
            'The backend homepage displays all users.'
        );
    }

    public function testAdminNewUSER(): void
    {

        $usernametest = 'test';
        $this->client->request('GET', '/en/admin/users/new');
        $this->client->submitForm('Create user', [
            'user[username]' => $usernametest,
            'user[fullName]' => 'full test',
            'user[email]' => 'test@test.fr',
            'user[password]' => 'test123',
            'user[roles]' => ['ROLE_ADMIN'],
        ]);

        $this->assertResponseRedirects('/en/admin/users/', Response::HTTP_SEE_OTHER);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);

        /** @var \App\Entity\User $user */
        $user = $userRepository->findOneByUsername($usernametest);

        $this->assertNotNull($user);
    }
}
