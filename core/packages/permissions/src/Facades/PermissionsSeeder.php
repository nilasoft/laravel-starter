<?php


	namespace Nila\Permissions\Facades;


	use Illuminate\Support\Facades\Facade;

	class PermissionsSeeder extends Facade {
		protected static function getFacadeAccessor() {
			return \Nila\Permissions\PermissionsSeeder::class;
		}
	}
