<?php

    namespace App\JsonApi\V1\Roles;

    use Illuminate\Http\Request;
    use LaravelJsonApi\Core\Resources\JsonApiResource;

    class RoleResource extends JsonApiResource {

        /**
         * Get the resource's attributes.
         *
         * @param Request|null $request
         *
         * @return iterable
         */
        public function attributes( $request ): iterable {
            return [
                'name' => $this->name,
                'area' => $this->area
            ];
        }

        /**
         * Get the resource's relationships.
         *
         * @param Request|null $request
         *
         * @return iterable
         */
        public function relationships( $request ): iterable {
            return [
                $this->relation( 'users' ),
                $this->relation( 'permissions' )
            ];
        }

    }
