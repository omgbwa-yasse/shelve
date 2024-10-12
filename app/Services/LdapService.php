<?php

namespace App\Services;

use Adldap\Adldap;

class LdapService
{
    protected $ldap;

    public function __construct()
    {
        $this->ldap = new Adldap();
        $config = [
            'hosts'    => [env('LDAP_HOST')],
            'base_dn'  => env('LDAP_BASE_DN'),
            'username' => env('LDAP_USERNAME'),
            'password' => env('LDAP_PASSWORD'),
        ];
        $this->ldap->addProvider($config);
    }

    public function authenticate($username, $password)
    {
        try {
            $provider = $this->ldap->connect();
            if ($provider->auth()->attempt($username, $password)) {
                return true;
            }
        } catch (\Exception $e) {

        }
        return false;
    }

    public function getUser($username)
    {
        try {
            $provider = $this->ldap->connect();
            return $provider->search()->findBy('samaccountname', $username);
        } catch (\Exception $e) {

        }
        return null;
    }
}
