<?php

    namespace App\Events;

    use Carbon\Carbon;
    use Illuminate\Broadcasting\Channel;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;

    class ExampleEvent implements ShouldBroadcastNow {
        use Dispatchable, InteractsWithSockets, SerializesModels;

        /**
         * Create a new event instance.
         *
         * @return void
         */
        public function __construct() {
            //
        }

        /**
         * Get the channels the event should broadcast on.
         *
         * @return \Illuminate\Broadcasting\Channel|array
         */
        public function broadcastOn() {
            return new Channel( 'date' );
        }

        /**
         * The event's broadcast name.
         *
         * @return string
         */
        public function broadcastAs() {
            return 'today';
        }

        /**
         * Get the data to broadcast.
         *
         * @return array
         */
        public function broadcastWith() {
            return [ 'today' => Carbon::now()->format( 'm/d/y H:i:s' ) ];
        }
    }
