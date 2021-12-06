<?php

    namespace Nila\Permissions\Models\Contracts;

    use Illuminate\Database\Eloquent\Relations\BelongsToMany;

    interface Permission {
        /**
         * A permission can be applied to roles.
         *
         * @return BelongsToMany
         */
        public function roles(): BelongsToMany;

        /**
         * Find a permission by its name.
         *
         * @param string $name
         * @param string $area
         *
         * @return Permission
         */
        public static function findByName( string $name, string $area  ): self;

        /**
         * Find a permission by its id.
         *
         * @param int    $id
         * @param string $area
         *
         * @return Permission
         */
        public static function findById( int $id, string $area  ): self;

        /**
         * Find or Create a permission by its name and guard name.
         *
         * @param string $name
         * @param string $area
         *
         * @return Permission
         */
        public static function findOrCreate( string $name, string $area  ): self;
    }
