<?php
declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\User;

use Akeneo\Apps\Application\Service\CreateUserInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUser implements CreateUserInterface
{
    /** @var SimpleFactoryInterface */
    private $userFactory;

    /** @var ObjectUpdaterInterface */
    private $userUpdater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $userSaver;

    public function __construct(
        SimpleFactoryInterface $userFactory,
        ObjectUpdaterInterface $userUpdater,
        ValidatorInterface $validator,
        SaverInterface $userSaver
    ) {
        $this->userFactory = $userFactory;
        $this->userUpdater = $userUpdater;
        $this->validator = $validator;
        $this->userSaver = $userSaver;
    }

    public function execute(string $username, string $firstname, string $email): void
    {
        $user = $this->userFactory->create();
        $this->userUpdater->update(
            $user,
            [
                'username' => $username,
                'password' => bin2hex(random_bytes(7)),
                'first_name' => $firstname,
                'last_name' => 'APP',
                'email' => $email,
            ]
        );

        $errors = $this->validator->validate($user);
        if (0 < count($errors)) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            throw new \InvalidArgumentException("The user creation failed :\n" . implode("\n", $errorMessages));
        }

        $this->userSaver->save($user);
    }
}
