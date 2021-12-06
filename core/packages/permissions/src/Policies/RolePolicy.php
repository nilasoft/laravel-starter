<?php

    namespace Nila\Permissions\Policies;

    use App\Models\User;
    use App\Policies\PolicyShortHandTrait;
    use Nila\Permissions\Models\Role;
    use Illuminate\Auth\Access\HandlesAuthorization;
    use Illuminate\Database\Eloquent\Model;
    use LaravelJsonApi\Core\Store\LazyRelation;

    class RolePolicy {
        use HandlesAuthorization, PolicyShortHandTrait;

        /**
         * Determine whether the user can view any models.
         *
         * @param User $user
         *
         * @return mixed
         */
        public function viewAny( User $user ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can view the model.
         *
         * @param User $user
         * @param Role $role
         *
         * @return mixed
         */
        public function view( User $user, Role $role ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can create models.
         *
         * @param User $user
         *
         * @return mixed
         */
        public function create( User $user ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can update the model.
         *
         * @param User $user
         * @param Role $role
         *
         * @return mixed
         */
        public function update( User $user, Role $role ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can delete the model.
         *
         * @param User $user
         * @param Role $role
         *
         * @return mixed
         */
        public function delete( User $user, Role $role ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can restore the model.
         *
         * @param User $user
         * @param Role $role
         *
         * @return mixed
         */
        public function restore( User $user, Role $role ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can permanently delete the model.
         *
         * @param User $user
         * @param Role $role
         *
         * @return mixed
         */
        public function forceDelete( User $user, Role $role ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }


        /**
         * @param User $user
         *
         * @return bool
         */
        public function viewUsers( User $user ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function updateUsers( User $user, Model $model, LazyRelation $relation ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function attachUsers( User $user, Model $model, LazyRelation $relation ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function detachUsers( User $user, Model $model, LazyRelation $relation ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User $user
         *
         * @return bool
         */
        public function viewPermissions( User $user ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function updatePermissions( User $user, Model $model, LazyRelation $relation ): bool {
            $guardCheck = $relation->collect()->every( fn( $item ) => $item->guard_name == $model->getGuard() );

            return $user->can( $this->getModel() . '-' . __FUNCTION__ ) and $guardCheck;
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function attachPermissions( User $user, Model $model, LazyRelation $relation ): bool {
            $guardCheck = $relation->collect()->every( fn( $item ) => $item->guard_name == $model->getGuard() );

            return $user->can( $this->getModel() . '-' . __FUNCTION__ ) and $guardCheck;
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function detachPermissions( User $user, Model $model, LazyRelation $relation ): bool {
            $guardCheck = $relation->collect()->every( fn( $item ) => $item->guard_name == $model->getGuard() );

            return $user->can( $this->getModel() . '-' . __FUNCTION__ ) and $guardCheck;
        }


    }
