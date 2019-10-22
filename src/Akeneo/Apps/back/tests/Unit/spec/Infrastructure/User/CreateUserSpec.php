<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Infrastructure\User;

use Akeneo\Apps\Application\Service\CreateUserInterface;
use Akeneo\Apps\Infrastructure\User\CreateUser;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserSpec extends ObjectBehavior
{
    public function let(
        SimpleFactoryInterface $userFactory,
        ObjectUpdaterInterface $userUpdater,
        ValidatorInterface $validator,
        SaverInterface $userSaver
    ): void {
        $this->beConstructedWith($userFactory, $userUpdater, $validator, $userSaver);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(CreateUser::class);
        $this->shouldImplement(CreateUserInterface::class);
    }

    function it_creates_a_user($userFactory, $userUpdater, $validator, $userSaver): void
    {
        $user = new User();
        $userFactory->create()->willReturn($user);
        $userUpdater->update(
            $user,
            Argument::that(function (array $params) {
                return 'foo' === $params['username'] &&
                    'bar' === $params['first_name'] &&
                    'APP' === $params['last_name'] &&
                    'foo.bar@akeneo.com' === $params['email'] &&
                    is_string($params['password']);
            })
        )->shouldBeCalled();
        $violations = new ConstraintViolationList([]);
        $validator->validate($user)->willReturn($violations);
        $userSaver->save($user)->shouldBeCalled();

        $this->execute('foo', 'bar', 'foo.bar@akeneo.com');
    }

    function it_prevents_to_create_a_not_valid_user($userFactory, $userUpdater, $validator, $userSaver): void
    {
        $user = new User();
        $userFactory->create()->willReturn($user);
        $userUpdater->update(
            $user,
            Argument::that(function (array $params) {
                return 'foo' === $params['username'] &&
                    'bar' === $params['first_name'] &&
                    'APP' === $params['last_name'] &&
                    'foo.bar@akeneo.com' === $params['email'] &&
                    is_string($params['password']);
            })
        )->shouldBeCalled();
        $violations = new ConstraintViolationList([
            new ConstraintViolation('wrong', 'wrong', [], 'wrong', 'path', 'wrong'),
            new ConstraintViolation('wrong2', 'wrong2', [], 'wrong2', 'path2', 'wrong2'),
        ]);
        $validator->validate($user)->willReturn($violations);
        $userSaver->save(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    'The user creation failed :' . PHP_EOL .
                    'path: wrong' . PHP_EOL .
                    'path2: wrong2'
                )
            )
            ->during('execute', ['foo', 'bar', 'foo.bar@akeneo.com']);
    }
}
