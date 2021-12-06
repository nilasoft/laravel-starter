<?php

    namespace Nila\Payments\Jobs;


    trait CreateWalletTrait {
        protected static function booted() {
            static::created( function( self $model ) {
                if ( env( 'APP_ENV' ) == 'local' ) {
                    CreateWalletForModelJob::dispatchSync( $model );
                } else {
                    CreateWalletForModelJob::dispatch( $model );
                }
            } );
        }
    }
