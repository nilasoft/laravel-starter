<?php

    namespace Nila\Resources\Contracts;

    interface SignatureContract {
        public function create(): string;

        public function isValid( string $signature ): bool;

        public function isNotValid( string $signature ): bool;

        public function key(): string;
    }
