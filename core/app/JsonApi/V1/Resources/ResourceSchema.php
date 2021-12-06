<?php

    namespace App\JsonApi\V1\Resources;


    use Hans\ResourceManagement\Models\Resource;
    use LaravelJsonApi\Eloquent\Contracts\Paginator;
    use LaravelJsonApi\Eloquent\Fields\Boolean;
    use LaravelJsonApi\Eloquent\Fields\DateTime;
    use LaravelJsonApi\Eloquent\Fields\ID;
    use LaravelJsonApi\Eloquent\Fields\Str;
    use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
    use LaravelJsonApi\Eloquent\Pagination\PagePagination;
    use LaravelJsonApi\Eloquent\Schema;

    class ResourceSchema extends Schema {

        /**
         * The model the schema corresponds to.
         *
         * @var string
         */
        public static string $model = Resource::class;

        /**
         * Get the resource fields.
         *
         * @return array
         */
        public function fields(): array {
            return [
                ID::make(),
                Str::make( 'title' ),
                Boolean::make( 'external' ),
                DateTime::make( 'published_at' ),

                DateTime::make( 'createdAt' )->sortable()->readOnly(),
                DateTime::make( 'updatedAt' )->sortable()->readOnly(),
            ];
        }

        /**
         * Get the resource filters.
         *
         * @return array
         */
        public function filters(): array {
            return [
                WhereIdIn::make( $this ),
            ];
        }

        /**
         * Get the resource paginator.
         *
         * @return Paginator|null
         */
        public function pagination(): ?Paginator {
            return PagePagination::make();
        }

    }
