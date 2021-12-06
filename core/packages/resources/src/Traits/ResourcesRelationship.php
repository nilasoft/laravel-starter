<?php


    namespace Nila\Resources\Traits;


    use Illuminate\Support\Facades\App;
    use Nila\Resources\Contracts\ResourcesContract;
    use Nila\Resources\Models\Resource as ResourceModel;

    trait ResourcesRelationship {
        public function uploads() {
            return $this->morphToMany( ResourceModel::class, 'resourcable' );
        }

        public function removeUploads() {
            $ids = $this->uploads->pluck( 'id' );
            $this->uploads()->detach( $ids );

            return App::make( ResourcesContract::class )->delete( $ids );
        }
    }
