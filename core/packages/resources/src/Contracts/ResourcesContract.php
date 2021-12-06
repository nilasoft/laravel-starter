<?php

    namespace Nila\Resources\Contracts;

    use Nila\Resources\Models\Resource;
    use Illuminate\Support\Collection;

    interface ResourcesContract {
        public function upload( string $field ): self;

        public function external( string $field ): self;

        public function batch( string $field ): self;

        public function deleteFile( string $path ): bool;

        public function generateName( string $driver = null, int $length = 16 ): string;

        public function generateFolder(): string;

        public function getModel(): array|Resource;

        public function getModels(): Collection;

        public function getId(): int;

        public function getIds(): array;

        public function delete( $id ): bool;

        public function batchDelete( array $ids ): array;
    }
