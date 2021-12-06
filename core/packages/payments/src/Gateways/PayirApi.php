<?php

    namespace Nila\Payments\Gateways;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Http\RedirectResponse;
    use Nila\Payments\Exceptions\PaymentsGatewayException;
    use Nila\Payments\Exceptions\PaymentsErrorCode;
    use Nila\Payments\Gateways\Contracts\PaymentsGateway;
    use Nila\Payments\Models\Requests\DepositRequest;
    use Nila\Payments\Models\Requests\PurchaseRequest;
    use PaymentsEnum;
    use PaymentsStatusEnum;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    class PayirApi extends PaymentsGateway {

        protected function getCallback( Model $model ): string {
            return route( 'payments.api.callback', [ 'model' => class_basename( $model ), 'id' => $model->id ] );
        }

        protected function getGatewayKey(): string {
            return PaymentsEnum::PAYIR;
        }

        public function redirect( DepositRequest|PurchaseRequest $model ): RedirectResponse {
            $api          = $this->getConfig( 'key' );
            $amount       = $model->amount_request;
            $mobile       = null;
            $factorNumber = null;
            $description  = 'description goes here.';
            $redirect     = $this->getCallback( $model );
            $result       = $this->send( $api, $amount, $redirect, $mobile, $factorNumber, $description );
            $result       = json_decode( $result );

            if ( optional( $result )->status ) {
                $model->update( [
                    'extra' => [ 'token' => $result->token ]
                ] );

                return redirect()->to( $this->getConfig( 'url' ) . $result->token );
            } else {
                $model->updateStatus( PaymentsStatusEnum::FAILED );
                throw new PaymentsGatewayException( $result->errorMessage, PaymentsErrorCode::GATEWAY_ERROR,
                    ResponseAlias::HTTP_BAD_REQUEST );
            }
        }

        public function verify( Model $model ): bool {
            if ( $model->status != PaymentsStatusEnum::PENDING ) {
                throw new PaymentsGatewayException( "Duplicate request!", PaymentsErrorCode::DUPLICATE_REQUEST,
                    ResponseAlias::HTTP_FORBIDDEN );
            }
            if ( app()->runningUnitTests() ) {
                return true;
            }
            $token = request()->input( 'token', false );
            if ( ! $token or $model->extra[ 'token' ] != $token ) {
                throw new PaymentsGatewayException( 'Token is invalid!', PaymentsErrorCode::TOKEN_IS_INVALID,
                    ResponseAlias::HTTP_BAD_REQUEST );
            }
            $api    = $this->getConfig( 'key' );
            $result = json_decode( $this->validate( $api, $token ) );
            if ( $result?->status == 1 ) {
                return true;
            } else {
                return false;
            }
        }

        private function send( $api, $amount, $redirect, $mobile = null, $factorNumber = null, $description = null ) {
            return $this->curl_post( $this->getConfig( 'send' ), [
                'api'          => $api,
                'amount'       => $amount,
                'redirect'     => $redirect,
                'mobile'       => $mobile,
                'factorNumber' => $factorNumber,
                'description'  => $description,
            ] );
        }

        private function validate( $api, $token ) {
            return $this->curl_post( $this->getConfig( 'verify' ), [
                'api'   => $api,
                'token' => $token,
            ] );
        }

        private function curl_post( $url, $params ) {
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $params ) );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ] );
            $res = curl_exec( $ch );
            curl_close( $ch );

            return $res;
        }
    }
