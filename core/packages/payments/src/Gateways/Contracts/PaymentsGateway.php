<?php

    namespace Nila\Payments\Gateways\Contracts;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Support\Arr;
    use Nila\Payments\Models\Requests\DepositRequest;
    use Nila\Payments\Models\Requests\PurchaseRequest;

    abstract class PaymentsGateway {
        protected $configuration;

        public function __construct() {
            $this->loadConfiguration();
        }

        protected function loadConfiguration(): void {
            $config              = config( 'payments.apis.' . $this->getGatewayKey() );
            $this->configuration = $config[ $config[ 'mode' ] ];
        }

        abstract protected function getGatewayKey(): string;

        abstract protected function getCallback( DepositRequest|PurchaseRequest $model ): string;

        abstract public function redirect( DepositRequest|PurchaseRequest $model ): RedirectResponse;

        abstract public function verify( DepositRequest|PurchaseRequest $model ): bool;

        protected function getConfig( string $key ): array|string {
            return Arr::get( $this->configuration, $key );
        }

    }
