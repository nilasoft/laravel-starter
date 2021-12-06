<?php

    namespace Nila\Permissions\Models\Traits;

    use Nila\Permissions\Models\Permission;
    use Nila\Permissions\Models\Role;
    use Illuminate\Database\Eloquent\Relations\MorphToMany;

    trait HasRelations {
        public function permissions(): MorphToMany {
            return $this->morphToMany( Permission::class, 'permissionable' );
        }

        public function roles(): MorphToMany {
            return $this->morphToMany( Role::class, 'rolable' );
        }
    }
