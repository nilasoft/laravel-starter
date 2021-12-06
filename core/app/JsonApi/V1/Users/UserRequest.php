<?php

    namespace App\JsonApi\V1\Users;

    use App\JsonApi\JsonapiShorthandTrait;
    use Illuminate\Validation\Rule;
    use Illuminate\Validation\Rules\Unique;
    use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
    use LaravelJsonApi\Validation\Rule as JsonApiRule;

    class UserRequest extends ResourceRequest {
        use JsonapiShorthandTrait;

        /**
         * Get the validation rules for the resource.
         *
         * @return array
         */
        public function rules(): array {
            return [
                'name'     => [ $this->required(), 'string', 'min:3', 'max:512' ],
                'email'    => [ $this->required(), 'email', $this->unique() ],
                'password' => [ $this->required(), 'string', 'min:8', 'max:512' ],
                'posts'    => [ JsonApiRule::toMany() ]
            ];
        }

    }
