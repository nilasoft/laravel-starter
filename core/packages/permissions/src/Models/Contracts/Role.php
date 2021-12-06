<?php

    namespace Nila\Permissions\Models\Contracts;

    use Nila\Permissions\Models\Permission;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;

    interface Role {
        /**
         * A role may be given various permissions.
         *
         * @return BelongsToMany
         */
        public function permissions(): BelongsToMany;

        /**
         * Find a role by its name and guard name.
         *
         * @param string $name
         * @param string $area
         *
         * @return Role
         */
        public static function findByName( string $name, string $area  ): self;

        /**
         * Find a role by its id and guard name.
         *
         * @param int    $id
         * @param string $area
         *
         * @return Role
         */
        public static function findById( int $id, string $area  ): self;

        /**
         * Find or create a role by its name and guard name.
         *
         * @param string $name
         * @param string $area
         *
         * @return Role
         */
        public static function findOrCreate( string $name, string $area  ): self;

        /**
         * Determine if the user may perform the given permission.
         *
         * @param Permission|string $permission
         *
         * @return bool
         */
        public function hasPermissionTo( Permission|string $permission ): bool;

        public function getVersion(): int;

        public function increaseVersion(): bool;
    }
