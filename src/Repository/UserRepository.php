<?php

namespace App\Repository;

use App\Dto\CustomerDataDto;
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

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

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
