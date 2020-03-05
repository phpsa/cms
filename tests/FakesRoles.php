<?php

namespace Tests;

use Statamic\Facades\Role;
use Statamic\Auth\File\Role as FileRole;
use Illuminate\Support\Collection;
use Statamic\Auth\File\RoleRepository;
use Statamic\Contracts\Auth\RoleRepository as RepositoryContract;

trait FakesRoles
{
    private function setTestRoles($roles)
    {
        $roles = collect($roles)
            ->mapWithKeys(function ($permissions, $handle) {
                $handle = is_string($permissions) ? $permissions : $handle;
                $permissions = is_string($permissions) ? [] : $permissions;
                return [$handle => $permissions];
            })
            ->map(function ($permissions, $handle) {
                return $permissions instanceof FileRole
                    ? $permissions->handle($handle)
                    : Role::make()->handle($handle)->addPermission($permissions);
            });

        $fake = new class($roles) extends RoleRepository {
            protected $roles;
            public function __construct($roles) {
                $this->roles = $roles;
            }
            public function all(): Collection {
                return $this->roles;
            }
        };

        app()->instance(RepositoryContract::class, $fake);
        Role::swap($fake);
    }
}
