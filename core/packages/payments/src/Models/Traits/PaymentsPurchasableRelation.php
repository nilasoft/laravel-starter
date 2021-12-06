<?php


    use Nila\Payments\Models\Transactions\Purchase;

    trait PaymentsPurchasableRelation {
        /**
         * Get all the post's comments.
         */
        public function items() {
            return $this->morphMany( Purchase::class, 'purchasable' );
        }
    }
