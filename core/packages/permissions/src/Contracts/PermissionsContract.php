<?php

    namespace Nila\Permissions\Contracts;

    use Illuminate\Support\Collection;
    use Nila\Permissions\Models\Permission;
    use Nila\Permissions\Models\Role;

    interface PermissionsContract {
        public function findRole( Role|string|int $role ): Role;

        public function findAllRoles(): Collection;

        public function findAnyRoles( Role|string|int ...$roles ): Collection;

        public function findPermission( Permission|int|string $permission ): Permission;

        public function findAllPermissions(): Collection;

        public function findAnyPermissions( Permission|string|int ...$permissions ): Collection;

    }
