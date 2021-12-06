<?php

    namespace App\JsonApi\V1\Users;

    use App\JsonApi\Filters\LikeFilter;
    use App\Models\User;
    use LaravelJsonApi\Eloquent\Contracts\Paginator;
    use LaravelJsonApi\Eloquent\Fields\DateTime;
    use LaravelJsonApi\Eloquent\Fields\ID;
    use LaravelJsonApi\Eloquent\Fields\Relations\BelongsToMany;
    use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
    use LaravelJsonApi\Eloquent\Fields\Str;
    use LaravelJsonApi\Eloquent\Filters\Where;
    use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
    use LaravelJsonApi\Eloquent\Pagination\PagePagination;
    use LaravelJsonApi\Eloquent\Schema;

    class UserSchema extends Schema {

        /**
         * The model the schema corresponds to.
         *
         * @var string
         */
        public static string $model = User::class;

        /**
         * Get the resource fields.
         *
         * @return array
         */
        public function fields(): array {
            return [
                ID::make(),

                Str::make( 'name' ),
                Str::make( 'email' ),
                Str::make( 'password' )->deserializeUsing( fn( $password ) => bcrypt( $password ) ),

                BelongsToMany::make( 'roles' ),
                BelongsToMany::make( 'uploads' ),
                HasMany::make( 'posts' ),

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
                Where::make( 'nameWhere', 'name' ),
                LikeFilter::make( 'nameLike', 'name' )
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
