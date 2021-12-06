<?php


    namespace App\Http\Controllers\Api\V1;


    use Nila\Payments\Contracts\PaymentsContract;
    use Nila\Payments\Models\Requests\DepositRequest;
    use Nila\Payments\Models\Requests\PurchaseRequest;

    class CallbackController {
        public function __invoke( string $model, int $id ) {
            $obj = app( PaymentsContract::class )->verify( $model, $id );

            return view( 'invoices.default.invoice', [ 'model' => $obj ] );
        }
    }
