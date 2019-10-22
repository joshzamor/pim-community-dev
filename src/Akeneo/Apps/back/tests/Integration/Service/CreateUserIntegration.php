<?php
declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Service;

use Akeneo\Apps\Application\Service\CreateUserInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class CreateUserIntegration extends TestCase
{
    /** @var CreateUserInterface */
    private $createUser;

    /** @var Connection */
    private $dbal;

    public function test_that_it_creates_a_user()
    {
        $this->createUser->execute('magento', 'Magento Connector', 'admin@anemail.com');

        $query = <<<SQL
    SELECT user.first_name, user.last_name, user.email, user.password
    FROM oro_user AS user
    WHERE user.username = 'magento'
SQL;

        $statement = $this->dbal->executeQuery($query);
        $result = $statement->fetch();

        Assert::assertNotEmpty($result);
        Assert::assertSame('Magento Connector', $result['first_name']);
        Assert::assertSame('APP', $result['last_name']);
        Assert::assertSame('admin@anemail.com', $result['email']);
        Assert::assertNotEmpty($result['password']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbal = $this->get('database_connection');
        $this->createUser = $this->get('akeneo_app.service.user.create_user');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
