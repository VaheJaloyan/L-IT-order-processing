<?php

namespace App\Repository;

use App\Dto\Order\CustomerDataDto;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Finds a user by email address.
     *
     * @param string $email
     *
     * @return User|null Null if no user with the given email exists
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Returns the existing user with the given email, or builds a new (unpersisted) User from the DTO.
     * The caller is responsible for persisting.
     *
     * @param CustomerDataDto $data
     *
     * @return User Existing or new User
     */
    public function findOrCreate(CustomerDataDto $data): User
    {
        $user = $this->findByEmail($data->email);
        if ($user === null) {
            $user = new User();
            $user->setName($data->name);
            $user->setEmail($data->email);
        }

        return $user;
    }
}
