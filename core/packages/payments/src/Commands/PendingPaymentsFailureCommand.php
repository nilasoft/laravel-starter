<?php


    namespace Nila\Payments\Commands;


    use Carbon\Carbon;
    use Illuminate\Console\Command;
    use Nila\Payments\Models\Requests\TransactionRequest;
    use PaymentsStatusEnum;
    use TransactionRequestsEnum;

    class PendingPaymentsFailureCommand extends Command {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'payments:pending';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Change expired pending payments statuses to failed';

        private array $configuration;

        /**
         * Create a new command instance.
         *
         * @return void
         */
        public function __construct() {
            parent::__construct();
            $this->configuration = config( 'payments' );
        }

        public function handle() {
            if ( ! $this->configuration[ 'scheduler' ] ) {
                return;
            }
            $requests = TransactionRequest::where( [
                'status' => PaymentsStatusEnum::PENDING,
                [
                    'created_at',
                    '<',
                    Carbon::now()->modify( '-' . ltrim( config( 'payments.expiration' ), '+ -' ) )
                ]
            ] )->whereIn( 'type', [ TransactionRequestsEnum::DEPOSIT, TransactionRequestsEnum::PURCHASE ] );
            foreach ( $requests->cursor() as $item ) {
                $item->updateStatus( PaymentsStatusEnum::FAILED );
            }
        }

    }
