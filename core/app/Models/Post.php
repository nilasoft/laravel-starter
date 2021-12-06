<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Support\Str;

    /**
 * @mixin IdeHelperPost
 */
    class Post extends Model {
        use HasFactory;

        protected $fillable = [
            'title',
            'content'
        ];

        //public string $title, $content, $description;

        /**
         * Perform any actions required after the model boots.
         *
         * @return void
         */
        protected static function booted() {
            self::creating( function( self $model ) {
                $model->setAttribute( 'description', Str::limit( $model->getAttributeValue( 'content' ), 100 ) );
            } );

            /*self::retrieved( function( self $model ) {
                foreach ( array_merge( $model->getAttributes(), $model->getMutatedAttributes() ) as $field ) :
                    if ( property_exists( $model, $field ) ) {
                        $model->{$field} = $model->getAttributeValue( $field );
                    }
                endforeach;
            } );*/
        }


        public function owner(): BelongsTo {
            return $this->belongsTo( User::class, 'user_id' );
        }
    }
