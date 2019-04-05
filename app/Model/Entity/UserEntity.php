<?php


class UserEntity
{
    protected $user;
    protected $name;
    protected $id;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function name()
    {
        return $this->string('name');
    }

    public function id()
    {
        return $this->string('id');
    }

    protected function string($node)
    {
        if (isset($this->user[$node])) {
            $result = $this->user[$node];
        } else {
            $result = '';
        }
        return $result;

    }

}