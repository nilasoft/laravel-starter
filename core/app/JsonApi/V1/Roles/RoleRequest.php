<?php

    namespace App\JsonApi\V1\Roles;

    use App\JsonApi\JsonapiShorthandTrait;
    use Illuminate\Validation\Rule;
    use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
    use LaravelJsonApi\Validation\Rule as JsonApiRule;

    class RoleRequest extends ResourceRequest {
        use JsonapiShorthandTrait;

        /**
         * Get the validation rules for the resource.
         *
         * @return array
         */
        public function rules(): array {
            return [
                'name' => [ $this->required(), 'min:3', 'max:512' ],
                'area' => [ $this->required(), 'min:3', 'max:512', Rule::in( \AreasEnum::toArray() ) ],

                'users' => JsonApiRule::toMany(),
                'permissions' => JsonApiRule::toMany()
            ];
        }

    }
