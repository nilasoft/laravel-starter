<?php

    namespace App\Notifications;

    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Notifications\Notification;
    use Illuminate\Support\Arr;
    use Kutia\Larafirebase\Messages\FirebaseMessage;

    class FcmNotification extends Notification implements ShouldQueue {
        use Queueable;

        private array $tokens;

        /**
         * Create a new notification instance.
         *
         * @return void
         */
        public function __construct( $tokens ) {
            $this->tokens = Arr::wrap( $tokens );
        }

        /**
         * Get the notification's delivery channels.
         *
         * @param mixed $notifiable
         *
         * @return array
         */
        public function via( $notifiable ): array {
            return [ 'firebase' ];
        }


        /**
         * Get the firebase representation of the notification.
         */
        public function toFirebase( $notifiable ) {
            return ( new FirebaseMessage )->withTitle( 'Hey, ' . $notifiable->first_name )
                                          ->withBody( 'Happy Birthday!' )
                                          ->asNotification( $this->tokens ); // OR ->asMessage($deviceTokens);
        }
    }
