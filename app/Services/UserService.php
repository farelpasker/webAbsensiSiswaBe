<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Storage;


class UserService
{
    private $userrepo;

    public function __construct(UserRepository $userrepo)
    {
        $this->userrepo = $userrepo;
    }

    public function updateUser($id, $data)
    {
        $user = $this->userrepo->getDetail($id);
        if(!empty($data['avatar'])){
            if($user->avatar) {
                Storage::delete($user->avatar);
            }
            $avatarPath = $data['avatar']->store('avatars');
            $data['avatar'] = $avatarPath;
        }
        return $this->userrepo->update($id, $data);
    }

}