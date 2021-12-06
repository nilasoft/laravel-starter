<?php

    namespace Nila\Menus\Contracts;

    use Illuminate\Support\Collection;

    interface MenusContract {
        public function find( string $key ): Collection;

        public function findAny( string ...$keys ): Collection;

        public function findAll(): Collection;
    }
