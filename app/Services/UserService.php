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

        if (isset($data['email']) && $data['email'] === $user->email) {
            unset($data['email']);
        }
        
        if(!empty($data['avatar'])){
            if($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $data['avatar']->store('avatars', 'public');
            $data['avatar'] = $avatarPath;
        }
        return $this->userrepo->update($id, $data);
    }

}