<?php

namespace App\Services;

use App\Repositories\UsersRepository;
use App\Services\Service;

class UsersService extends Service
{

     public function __construct(protected UsersRepository $usersRepository){}
}
