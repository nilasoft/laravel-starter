<?php

    namespace App\JsonApi\V1\Resources;

    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use LaravelJsonApi\Core\Resources\JsonApiResource;

    class ResourceResource extends JsonApiResource {

        /**
         * Get the resource's attributes.
         *
         * @param Request|null $request
         *
         * @return iterable
         */
        public function attributes( $request ): iterable {
            return [
                //'id'        => $this->id,
                'title'     => $this->title,
                'external'  => $this->external,
                'published' => $this->published_at ? Carbon::createFromTimestamp( $this->published_at )
                                                           ->toFormattedDateString() : 'not yet',
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
            return [// @TODO
            ];
        }

    }
