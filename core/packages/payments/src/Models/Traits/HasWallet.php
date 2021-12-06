<?php


    namespace Nila\Payments\Models\Traits;


    use Nila\Payments\Models\Wallet;

    trait HasWallet {
        public function wallet() {
            return $this->hasOne( Wallet::class );
        }
    }
