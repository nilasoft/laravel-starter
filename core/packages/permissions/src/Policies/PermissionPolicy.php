<?php

    namespace Nila\Permissions\Policies;

    use App\Models\User;
    use App\Policies\PolicyShortHandTrait;
    use Nila\Permissions\Models\Permission;
    use Illuminate\Auth\Access\HandlesAuthorization;
    use Illuminate\Database\Eloquent\Model;
    use LaravelJsonApi\Core\Store\LazyRelation;

    class PermissionPolicy {
        use HandlesAuthorization, PolicyShortHandTrait;

        /**
         * Determine whether the user can view any models.
         *
         * @param User $user
         *
         * @return bool
         */
        public function viewAny( User $user ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can view the model.
         *
         * @param User       $user
         * @param Permission $permission
         *
         * @return bool
         */
        public function view( User $user, Permission $permission ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can create models.
         *
         * @param User $user
         *
         * @return bool
         */
        public function create( User $user ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can update the model.
         *
         * @param User       $user
         * @param Permission $permission
         *
         * @return bool
         */
        public function update( User $user, Permission $permission ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can delete the model.
         *
         * @param User       $user
         * @param Permission $permission
         *
         * @return bool
         */
        public function delete( User $user, Permission $permission ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can restore the model.
         *
         * @param User       $user
         * @param Permission $permission
         *
         * @return bool
         */
        public function restore( User $user, Permission $permission ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can permanently delete the model.
         *
         * @param User       $user
         * @param Permission $permission
         *
         * @return bool
         */
        public function forceDelete( User $user, Permission $permission ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }


        /**
         * @param User $user
         *
         * @return bool
         */
        public function viewRoles( User $user ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function updateRoles( User $user, Model $model, LazyRelation $relation ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function attachRoles( User $user, Model $model, LazyRelation $relation ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function detachRoles( User $user, Model $model, LazyRelation $relation ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

    }
