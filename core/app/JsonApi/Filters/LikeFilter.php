<?php

    namespace App\JsonApi\Filters;

    use Illuminate\Database\Eloquent\Builder;
    use LaravelJsonApi\Core\Support\Str;
    use LaravelJsonApi\Eloquent\Contracts\Filter;
    use LaravelJsonApi\Eloquent\Filters\Concerns\DeserializesValue;
    use LaravelJsonApi\Eloquent\Filters\Concerns\IsSingular;

    class LikeFilter implements Filter {
        use DeserializesValue;
        use IsSingular;

        /**
         * @var string
         */
        private string $name;

        /**
         * @var string
         */
        private string $column;

        /**
         * Create a new filter.
         *
         * @param string      $name
         * @param string|null $column
         *
         * @return LikeFilter
         */
        public static function make( string $name, string $column = null ): self {
            return new static( $name, $column );
        }

        /**
         * like constructor.
         *
         * @param string      $name
         * @param string|null $column
         */
        public function __construct( string $name, string $column = null ) {
            $this->name   = $name;
            $this->column = $column ? : Str::underscore( $name );
        }

        /**
         * Get the key for the filter.
         *
         * @return string
         */
        public function key(): string {
            return $this->name;
        }

        /**
         * Apply the filter to the query.
         *
         * @param Builder $query
         * @param mixed   $value
         *
         * @return Builder
         */
        public function apply( $query, $value ) {
            return $query->where( $query->getModel()->qualifyColumn( $this->column ), 'LIKE',
                "%" . $this->deserialize( $value ) . "%" );
        }
    }
