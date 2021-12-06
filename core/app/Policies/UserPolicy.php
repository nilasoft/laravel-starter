<?php

    namespace App\Policies;

    use App\Models\User;
    use Illuminate\Auth\Access\HandlesAuthorization;
    use Illuminate\Database\Eloquent\Model;
    use LaravelJsonApi\Core\Store\LazyRelation;

    class UserPolicy {
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
         * @param User $model
         *
         * @return mixed
         */
        public function view( User $user, User $model ): bool {
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
         * @param User $model
         *
         * @return mixed
         */
        public function update( User $user, User $model ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can delete the model.
         *
         * @param User $user
         * @param User $model
         *
         * @return mixed
         */
        public function delete( User $user, User $model ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can restore the model.
         *
         * @param User $user
         * @param User $model
         *
         * @return mixed
         */
        public function restore( User $user, User $model ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * Determine whether the user can permanently delete the model.
         *
         * @param User $user
         * @param User $model
         *
         * @return mixed
         */
        public function forceDelete( User $user, User $model ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }


        /**
         * @param User $user
         *
         * @return bool
         */
        public function viewPosts( User $user ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function updatePosts( User $user, Model $model, LazyRelation $relation ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function attachPosts( User $user, Model $model, LazyRelation $relation ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

        /**
         * @param User         $user
         * @param Model        $model
         * @param LazyRelation $relation
         *
         * @return bool
         */
        public function detachPosts( User $user, Model $model, LazyRelation $relation ): bool {
            return $user->can( $this->getModel() . '-' . __FUNCTION__ );
        }

    }
