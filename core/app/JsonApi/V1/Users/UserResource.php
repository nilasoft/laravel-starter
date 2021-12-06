<?php

    namespace App\JsonApi\V1\Users;

    use Illuminate\Http\Request;
    use LaravelJsonApi\Core\Resources\JsonApiResource;

    class UserResource extends JsonApiResource {

        /**
         * Get the resource's attributes.
         *
         * @param Request|null $request
         *
         * @return iterable
         */
        public function attributes( $request ): iterable {
            return [
                'name'  => $this->name,
                'email' => $this->email
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
                $this->relation('uploads'),
                $this->relation('posts'),
            ];
        }

    }
