<?php

    namespace App\Policies;

    use App\Models\Post;
    use App\Models\User;
    use Illuminate\Auth\Access\HandlesAuthorization;

    class PostPolicy {
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
         * @param Post $model
         *
         * @return mixed
         */
        public function view( User $user, Post $model ): bool {
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
         * @param Post $model
         *
         * @return mixed
         */
        public function update( User $user, Post $model ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can delete the model.
         *
         * @param User $user
         * @param Post $model
         *
         * @return mixed
         */
        public function delete( User $user, Post $model ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can restore the model.
         *
         * @param User $user
         * @param Post $model
         *
         * @return mixed
         */
        public function restore( User $user, Post $model ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can permanently delete the model.
         *
         * @param User $user
         * @param Post $model
         *
         * @return mixed
         */
        public function forceDelete( User $user, Post $model ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }


        /**
         * @param User $user
         *
         * @return bool
         */
        public function viewOwner( User $user ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User  $user
         * @param Model $model
         *
         * @return bool
         */
        public function updateOwner( User $user, Model $model ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }
    }
