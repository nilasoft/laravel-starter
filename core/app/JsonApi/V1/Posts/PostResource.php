<?php

    namespace App\JsonApi\V1\Posts;

    use Illuminate\Http\Request;
    use LaravelJsonApi\Core\Resources\JsonApiResource;

    class PostResource extends JsonApiResource {

        /**
         * Get the resource's attributes.
         *
         * @param Request|null $request
         *
         * @return iterable
         */
        public function attributes( $request ): iterable {
            return [
                'title'       => $this->title,
                'description' => $this->description,
                'content'     => $this->content
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
                $this->relation( 'owner' )
            ];
        }

    }
