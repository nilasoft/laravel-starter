<?php

    namespace Tests\Feature\Payments;

    use Illuminate\Support\Facades\Bus;
    use Nila\Payments\Jobs\CreateWalletForModelJob;
    use Tests\Factories\UserFactory;
    use Tests\TestCase;

    class PaymentsJobTest extends TestCase {
        /**
         * @test
         *
         *
         * @return void
         */
        public function createWallet() {
            Bus::fake();
            UserFactory::createAUser();
            Bus::assertDispatched( CreateWalletForModelJob::class );
        }
    }
