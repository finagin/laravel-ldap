<?php

namespace App;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class LdapUserProvider extends EloquentUserProvider
{
    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);

        $this->ldap = ldap_connect('ldap://'.config('ldap.host').':'.config('ldap.port'));
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if ($this->ldapCheck($credentials)) {
            return User::firstOrCreate([
                'name' => $this->getName($credentials),
                'email' => $credentials['email'],
                'password' => '',
            ]);
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array                                      $credentials
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->ldapCheck($credentials);
    }

    protected function ldapCheck(array $credentials): bool
    {
        $username = $credentials['email'];
        $password = $credentials['password'];

        return @ldap_bind($this->ldap, $username, $password);
    }

    public function getName($credentials) {
        $user = $credentials['email'];

        $this->ldapCheck($credentials);
        $filter = "(sAMAccountName=" . $user . ")";
        $attr = array("memberof","givenname");
        $result = ldap_search($this->ldap, env('LDAP_BASE_DN'), $filter, $attr) or exit("Unable to search LDAP server");
        $entries = ldap_get_entries($this->ldap, $result);
        $givenname = $entries[0]['givenname'][0];
        ldap_unbind($this->ldap);

        return $givenname;
    }
}
